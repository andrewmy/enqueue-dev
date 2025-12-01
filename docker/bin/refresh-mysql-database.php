<?php

use Enqueue\Dbal\DbalConnectionFactory;

require_once getcwd().'/vendor/autoload.php';

$dsn = getenv('DOCTRINE_DSN');
$database = trim(parse_url($dsn, \PHP_URL_PATH), '/');

$dbalContext = (new DbalConnectionFactory($dsn))->createContext();

$dbalContext->getDbalConnection()->createSchemaManager()->dropDatabase($database);
$dbalContext->getDbalConnection()->createSchemaManager()->createDatabase($database);
$dbalContext->getDbalConnection()->executeQuery('USE '.$database);
$dbalContext->createDataBaseTable();

echo 'MySQL Database is updated'.\PHP_EOL;
