<?php

declare(strict_types=1);

use Nette\Database\Connection;

require __DIR__ . '/vendor/autoload.php';

$file = __DIR__ . '/test.sqlite';
if (file_exists($file)) {
    unlink($file);
}

/********** Connection with params (lazy-connect) **********/
$connection = new Connection('sqlite:' . $file, options: ['lazy' => true]);

/********** Get underlying PDO **********/
$pdo = $connection->getPdo(); // This opens a connection

/********** Connection from existing PDO **********/
// Not possible: Connection only accepts a DSN string, $pdo is private with no setter.
