<?php

declare(strict_types=1);

use Aura\Sql\DecoratedPdo;
use Aura\Sql\ExtendedPdo;

require __DIR__ . '/vendor/autoload.php';

$file = __DIR__ . '/test.sqlite';
if (file_exists($file)) {
    unlink($file);
}

/********** Connection with configuration (lazy-connect) **********/
$extendedPdo = new ExtendedPdo('sqlite:' . $file);

/********** Get underlying PDO **********/
// ExtendedPdo extends PDO, so it IS the PDO.

/********** Connection from existing PDO **********/
$pdo = new PDO('sqlite:' . $file);
$decoratedPdo = new DecoratedPdo($pdo);
// Not possible to get the original PDO with DecoratedPdo.
