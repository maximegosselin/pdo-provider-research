<?php

declare(strict_types=1);

use Atlas\Orm\Atlas;
use Atlas\Pdo\Connection;

require __DIR__ . '/vendor/autoload.php';

$file = __DIR__ . '/test.sqlite';
if (file_exists($file)) {
    unlink($file);
}

/********** Connection with params **********/
$connection = Connection::new('sqlite:' . $file); // This opens a connection
$orm = Atlas::new($connection);

/********** Get underlying PDO **********/
$pdo = $connection->getPdo(); // Connection is already open

/********** Connection from existing PDO **********/
$pdo = new PDO('sqlite:' . $file);
$orm = Atlas::new($pdo);
