<?php

declare(strict_types=1);

namespace CoiSA\Spot\Tool\Console\Command\SchemaTool;

use CoiSA\Spot\Tool\Console\SpotTools;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateCommand
 *
 * @package CoiSA\Spot\Tool\Console\SchemaTool\Command
 *
 * @method SpotTools getApplication
 */
class CreateCommand extends Command
{
    /**
     * Configure command definition
     */
    public function configure()
    {
        $this
            ->setDescription('Creates the database schema')
            ->setHelp('This command creates or print the database create schema')
            ->setDefinition([
                new InputOption('dump-sql', null, InputOption::VALUE_NONE, 'Outputs the create SQL instead of apply into the connection'),
                new InputOption('drop', null, InputOption::VALUE_NONE, 'Drop tables if already exists'),
            ]);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $schemaTool = $this->getApplication()->getSchemaTool();

        $dropExists = $input->getOption('drop');

        if ($input->getOption('dump-sql')) {
            if ($dropExists) {
                $this->writeQueries($output, $schemaTool->getDropSchemaSql());
            }
            $this->writeQueries($output, $schemaTool->getCreateSchemaSql());
        } else {
            $schemaTool->createScrema($dropExists);
        }
    }

    /**
     * Write formatted queries to output
     *
     * @param OutputInterface $output
     * @param array $queries
     */
    private function writeQueries(OutputInterface $output, array $queries): void
    {
        $output->writeln(
            array_map(
                function($query) {
                    return $query . ';';
                },
                $queries
            )
        );
    }
}