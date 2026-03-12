<?php

declare(strict_types=1);

use ParagonIE\EasyDB\EasyDB;
use ParagonIE\EasyDB\Factory;

require __DIR__ . '/vendor/autoload.php';

$file = __DIR__ . '/test.sqlite';
if (file_exists($file)) {
    unlink($file);
}

/********** Connection with configuration (NO lazy-connect) **********/
$db = Factory::create('sqlite:' . $file); // This opens a connection

/********** Get underlying PDO **********/
$pdo = $db->getPdo(); // Connection is already open

/********** Connection from existing PDO **********/
$pdo = new PDO('sqlite:' . $file);
$db = new EasyDB($pdo);
