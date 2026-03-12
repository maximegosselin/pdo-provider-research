<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/vendor/autoload.php';

function base_path($path = ''): string
{
    return __DIR__ . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

$file = __DIR__ . '/test.sqlite';
if (file_exists($file)) {
    unlink($file);
}

/********** Connection with configuration (lazy-connect) **********/
$capsule = new Capsule();
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => $file,
]);
$capsule->setAsGlobal();

/********** Get underlying PDO **********/
touch($file); // Laravel won't create the SQLite file if it doesn't exist
$pdo = $capsule->getConnection()->getPdo();

/********** Connection from existing PDO **********/
$pdo = new PDO('sqlite:' . $file);
$capsule = new Capsule();
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => '...', // this won't be used
]);
$capsule->getConnection()->setPdo($pdo);
