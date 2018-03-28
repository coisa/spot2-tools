<?php

namespace CoiSA\Spot\Tool\Console;

use CoiSA\Spot\Tool\SchemaTool;
use Spot\Locator;
use Symfony\Component\Console\Application;

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

        parent::__construct('Spot Console Runner Tool');
    }

    /**
     * @return SchemaTool
     */
    public function getSchemaTool(): SchemaTool
    {
        return $this->schemaTool;
    }
}