<?php

declare(strict_types=1);

namespace CoiSA\Spot\Tool\Console;

use CoiSA\Spot\Tool\SchemaTool;
use Spot\Locator;
use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;

/**
 * Class SpotTools
 *
 * @package CoiSA\Spot\Tool\Console
 */
class SpotTools extends Application
{
    /** @var SchemaTool */
    private $schemaTool;

    /**
     * SpotTools constructor.
     *
     * @param Locator $locator
     */
    public function __construct(Locator $locator)
    {
        $this->schemaTool = new SchemaTool($locator);

        $this->addCommands([
            new Command\SchemaTool\CreateCommand('schema-tool:create'),
            new Command\SchemaTool\UpdateCommand('schema-tool:update'),
            new Command\SchemaTool\DropCommand('schema-tool:drop'),
        ]);

        parent::__construct('Spot2 ORM Console Runner Tool');
    }

    /**
     * @return SchemaTool
     */
    public function getSchemaTool(): SchemaTool
    {
        return $this->schemaTool;
    }

    /**
     * @return string[]
     */
    public function getEntityClassNames(): array
    {
        $classNames = [];

        foreach ($this->getEntityFiles() as $file) {
            include_once $file->getRealPath();
        }

        foreach (get_declared_classes() as $className) {
            if ($className === 'Spot\\Entity') {
                continue;
            }

            if (in_array('Spot\\EntityInterface', class_implements($className))) {
                $classNames []= $className;
            }
        }

        return $classNames;
    }

    /**
     * @return \Iterator
     */
    private function getEntityFiles(): \Iterator
    {
        $finder = new Finder();

        $finder->files()
            ->in(getcwd())
            ->name('/\.php$/')
            ->exclude('vendor')
            ->contains('Spot\\Entity');

        return $finder->getIterator();
    }
}