<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = new Doctrine\DBAL\Configuration();

$connectionParams = [
    'dbname'   => getenv('MYSQL_DATABASE'),
    'user'     => getenv('MYSQL_USER'),
    'password' => getenv('MYSQL_PASSWORD'),
    'host'     => getenv('MYSQL_HOST'),
    'driver'   => 'pdo_mysql'
];

$connection = Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

return Doctrine\DBAL\Tools\Console\ConsoleRunner::createHelperSet($connection);