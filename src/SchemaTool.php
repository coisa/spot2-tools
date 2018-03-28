<?php

namespace CoiSA\Spot\Tool;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Event\SchemaAlterTableChangeColumnEventArgs;
use Doctrine\DBAL\Events;
use Spot\Locator;
use Spot\Mapper;
use Symfony\Component\Finder\Finder;

/**
 * Class SchemaTool
 *
 * @package CoiSA\Spot\Tool
 */
class SchemaTool
{
    /** @var Locator Spot Mapper Locator Connection Object */
    private $locator;

    /**
     * SchemaTool constructor.
     *
     * @param Locator $locator Spot Mapper Locator Connection Object
     */
    public function __construct(Locator $locator)
    {
        $this->locator = $locator;

        $this->getConnection()->getEventManager()->addEventListener(Events::onSchemaAlterTableChangeColumn, $this);
    }

    /**
     * Gets the database connection object used by the Spot Locator.
     *
     * @return Connection
     */
    private function getConnection(): Connection
    {
        return $this->locator->config()->connection();
    }

    /**
     * Creates the database schema for the given entities found in given path.
     *
     * @param bool $dropExists optional
     *
     * @return bool
     */
    public function createScrema($dropExists = true): bool
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();

        try {
            if ($dropExists) {
                foreach ($this->getDropSchemaSql() as $query) {
                    $connection->exec($query);
                }
            }

            foreach ($this->getCreateSchemaSql() as $query) {
                $connection->exec($query);
            }

            $connection->commit();
        } catch (\Exception $exception) {
            $connection->rollBack();

            return false;
        }

        return true;
    }

    /**
     * Gets the list of DDL statements that are required to create the database schema for
     * the entities found in given path.
     *
     * @return array
     */
    public function getCreateSchemaSql(): array
    {
        $queries = [];

        foreach ($this->getAllMappers() as $mapper) {
            $resolver = $mapper->resolver();

            $schema = $resolver->migrateCreateSchema();
            $query = $schema->toSql($mapper->connection()->getDatabasePlatform());

            $queries = array_merge($queries, $query);
        }

        return $queries;
    }

    /**
     * Updates the database schema using the native Spot migrate method
     *
     * @return bool
     */
    public function updateSchema(): bool
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();

        try {
            foreach ($this->getAllMappers() as $mapper) {
                $mapper->migrate();
            }

            $connection->commit();
        } catch (\Exception $exception) {
            $connection->rollBack();

            return false;
        }

        return true;
    }

    /**
     * Returns the difference between the connection schema and the current state of entity files
     *
     * @return array
     */
    public function getUpdateSchemaSql(): array
    {
        $queries = [];

        foreach ($this->getAllMappers() as $mapper) {
            $table = $mapper->table();
            $connection = $mapper->connection();
            $schemaManager = $connection->getSchemaManager();

            if (false === $schemaManager->tablesExist([$table])) {
                // Create new table
                $newSchema = $mapper->resolver()->migrateCreateSchema();
                $query = $newSchema->toSql($connection->getDatabasePlatform());
            } else {
                // Update existing table
                $fromSchema = new \Doctrine\DBAL\Schema\Schema([
                    $schemaManager->listTableDetails($table)
                ]);
                $newSchema = $mapper->resolver()->migrateCreateSchema();
                $query = $fromSchema->getMigrateToSql($newSchema, $connection->getDatabasePlatform());
            }

            $queries = array_merge($queries, $query);
        }

        return $queries;
    }

    /**
     * Event listener responsible for ignore alter tables incorrectly marked as modified
     *
     * @param SchemaAlterTableChangeColumnEventArgs $eventArgs
     */
    public function onSchemaAlterTableChangeColumn(SchemaAlterTableChangeColumnEventArgs $eventArgs)
    {
        $columnDiff = $eventArgs->getColumnDiff();
        $platform = $eventArgs->getPlatform();

        $fromColumn = $columnDiff->fromColumn;
        $fromColumnSql = $platform->getColumnDeclarationSQL($fromColumn->getQuotedName($platform), $fromColumn->toArray());

        $toColumn = $columnDiff->column;
        $toColumnSql = $platform->getColumnDeclarationSQL($toColumn->getQuotedName($platform), $toColumn->toArray());

        if (str_replace('`', '', $fromColumnSql) === str_replace('`', '', $toColumnSql)) {
            $eventArgs->preventDefault();
        }
    }

    /**
     * Drops the database schema for tables found in entities files
     *
     * @return bool
     */
    public function dropSchema(): bool
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();

        try {
            foreach ($this->getDropSchemaSql() as $query) {
                $connection->exec($query);
            }
            $connection->commit();
        } catch (\Exception $exception) {
            $connection->rollBack();

            return false;
        }

        return true;
    }

    /**
     * Returns the drop queries of tables found in entities files
     *
     * @return array
     */
    public function getDropSchemaSql(): array
    {
        $queries = [];

        foreach ($this->getAllMappers() as $mapper) {
            $table = $mapper->table();
            $schemaManager = $mapper->connection()->getSchemaManager();

            if ($schemaManager->tablesExist([$table])) {
                $queries []= $schemaManager->getDatabasePlatform()->getDropTableSQL($table);
            }
        }

        return $queries;
    }

    /**
     * Returns a collection of Mappers for each entity file found in the provided path
     *
     * @return Mapper[]
     */
    private function getAllMappers(): array
    {
        foreach ($this->getFinder()->getIterator() as $file) {
            include_once $file->getRealPath();
        }

        $mappers = [];

        foreach (get_declared_classes() as $className) {
            if ($className === 'Spot\\Entity') {
                continue;
            }

            if (in_array('Spot\\EntityInterface', class_implements($className))) {
                $mappers []= $this->locator->mapper($className);
            }
        }

        return $mappers;
    }

    /**
     * @return Finder
     */
    private function getFinder(): Finder
    {
        $finder = new Finder();

        $finder->files()
            ->in(getcwd())
            ->name('/\.php$/')
            ->exclude('vendor')
            ->contains('Spot\\Entity');

        return $finder;
    }
}