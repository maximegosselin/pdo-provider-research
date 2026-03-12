<?php

declare(strict_types=1);

use ClanCats\Hydrahon\Builder;

require __DIR__ . '/vendor/autoload.php';

$file = __DIR__ . '/test.sqlite';
if (file_exists($file)) {
    unlink($file);
}

/********** Connection from existing PDO **********/
$pdo = new PDO('sqlite:' . $file);
$h = new Builder('mysql', function ($query, string $sql, array $bindings) use ($pdo) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($bindings);
    return $stmt;
});

/********** Get underlying PDO **********/
// The PDO is managed externally, so it is already available.
