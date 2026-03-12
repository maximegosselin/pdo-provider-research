<?php

declare(strict_types=1);

use Cake\Database\Connection;
use Cake\Database\Driver\Sqlite;

require __DIR__ . '/vendor/autoload.php';

$file = __DIR__ . '/test.sqlite';
if (file_exists($file)) {
    unlink($file);
}

/********** Connection with configuration (lazy-connect) **********/
$connection = new Connection([
    'driver' => Sqlite::class,
    'database' => $file,
]);

/********** Get underlying PDO **********/
// Not possible: Driver::getPdo() is protected in CakePHP 5

/********** Connection from existing PDO **********/
// Not possible: Driver::setPdo() is protected in CakePHP 5
