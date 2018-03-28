<?php

declare(strict_types=1);

use CoiSA\Spot\Tool\Console\SpotTools;
use CoiSA\Spot\Tool\SchemaTool;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Tools\Console\ConsoleRunner;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Spot\Config;
use Spot\Locator;
use Symfony\Component\Console\Helper\HelperSet;

$autoloadFiles = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php'
];

foreach ($autoloadFiles as $autoloadFile) {
    if (file_exists($autoloadFile)) {
        require_once $autoloadFile;
        break;
    }
}

$directories = [getcwd(), getcwd() . DIRECTORY_SEPARATOR . 'config'];
$configFile = null;

foreach ($directories as $directory) {
    $configFile = $directory . DIRECTORY_SEPARATOR . 'cli-config.php';

    if (file_exists($configFile)) {
        break;
    }
}

if (false === file_exists($configFile)) {
    ConsoleRunner::printCliConfigTemplate();
    exit(1);
}

if ( ! is_readable($configFile)) {
    echo 'Configuration file [' . $configFile . '] does not have read permission.' . "\n";
    exit(1);
}
$commands = [];
$helperSet = require $configFile;


if ( ! ($helperSet instanceof HelperSet)) {
    foreach ($GLOBALS as $helperSetCandidate) {
        if ($helperSetCandidate instanceof HelperSet) {
            $helperSet = $helperSetCandidate;
            break;
        }
    }
}

$helper = $helperSet->get('db');

if (!$helper instanceof ConnectionHelper) {
    ConsoleRunner::printCliConfigTemplate();
    exit(1);
}

$connection = $helper->getConnection();

$config = new Config();
$config->addConnection('connection', $connection);

$locator = new Locator($config);

$runner = new SpotTools($locator);
$runner->run();