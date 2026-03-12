<?php

declare(strict_types=1);

use PhpDb\Adapter\Adapter;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Connection;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Feature\SqliteRowCounter;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Pdo as PdoDriver;
use PhpDb\Adapter\Sqlite\Platform\Sqlite as SqlitePlatform;

require __DIR__ . '/vendor/autoload.php';

$file = __DIR__ . '/test.sqlite';
if (file_exists($file)) {
    unlink($file);
}

/********** Connection with params (lazy-connect) **********/
$connection = new Connection(['dsn' => 'sqlite:' . $file]);
$driver = new PdoDriver($connection, new Statement(), new Result(), [new SqliteRowCounter()]);
$adapter = new Adapter($driver, new SqlitePlatform($driver));

/********** Get underlying PDO **********/
$pdo = $adapter->getDriver()->getConnection()->getResource(); // This opens a connection

/********** Connection from existing PDO **********/
$pdo = new PDO('sqlite:' . $file);
$connection = new Connection($pdo);
$driver = new PdoDriver($connection, new Statement(), new Result(), [new SqliteRowCounter()]);
$adapter = new Adapter($driver, new SqlitePlatform($driver));
