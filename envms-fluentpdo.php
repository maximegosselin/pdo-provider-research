<?php

declare(strict_types=1);

use Envms\FluentPDO\Query;

require __DIR__ . '/vendor/autoload.php';

$file = __DIR__ . '/test.sqlite';
if (file_exists($file)) {
    unlink($file);
}

/********** Connection from existing PDO **********/
$pdo = new PDO('sqlite:' . $file);
$query = new Query($pdo);

/********** Get underlying PDO **********/
$pdo = $query->getPdo();
