<?php

declare(strict_types=1);

namespace CoiSA\Spot\Tool\Console\Command\SchemaTool;

use CoiSA\Spot\Tool\Console\SpotTools;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DropCommand
 *
 * @package CoiSA\Spot\Tool\Console\SchemaTool\Command
 *
 * @method SpotTools getApplication
 */
class DropCommand extends Command
{
    use LockableTrait;

    /**
     * Configure command definition
     */
    public function configure()
    {
        $this
            ->setDescription('Drop the database schema')
            ->setHelp('This command updates or print the database diff schema')
            ->setDefinition([
                new InputOption('dump-sql', null, InputOption::VALUE_NONE, 'Outputs the difference between your entities instead of apply into the connection'),
            ]);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $schemaTool = $this->getApplication()->getSchemaTool();

        if ($input->hasOption('dump-sql')) {
            $this->writeQueries($output, $schemaTool->getDropSchemaSql());
        } else {
            $this->lock();
            $schemaTool->dropSchema();
            $this->release();
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