<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;

require __DIR__ . '/vendor/autoload.php';

$file = __DIR__ . '/test.sqlite';
if (file_exists($file)) {
    unlink($file);
}

/********** Connection with configuration (lazy-connect) **********/
$conn = DriverManager::getConnection([
    'path' => $file,
    'driver' => 'pdo_sqlite',
]);
$pdo = $conn->getNativeConnection();

/********** Get underlying PDO **********/
$pdo = $conn->getNativeConnection(); // This opens a connection

/********** Connection from existing PDO **********/
$pdo = new PDO('sqlite:' . $file);
$conn = DriverManager::getConnection([
    'pdo' => $pdo,
    'driver' => 'pdo_sqlite',
]);
