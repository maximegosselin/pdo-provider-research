<?php

declare(strict_types=1);

use Psr\SimpleCache\CacheInterface;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Driver\Pdo\AbstractPdoDriver;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;
use Yiisoft\Db\Sqlite\Dsn;

require __DIR__ . '/vendor/autoload.php';

$file = __DIR__ . '/test.sqlite';
if (file_exists($file)) {
    unlink($file);
}

// SchemaCache requires a PSR-16 cache; use a no-op in-memory stub for this demo.
$cache = new class implements CacheInterface {
    private array $data = [];
    public function get(string $key, mixed $default = null): mixed { return $this->data[$key] ?? $default; }
    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool { $this->data[$key] = $value; return true; }
    public function delete(string $key): bool { unset($this->data[$key]); return true; }
    public function clear(): bool { $this->data = []; return true; }
    public function getMultiple(iterable $keys, mixed $default = null): iterable { foreach ($keys as $key) { yield $key => $this->get($key, $default); } }
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool { foreach ($values as $key => $value) { $this->set($key, $value); } return true; }
    public function deleteMultiple(iterable $keys): bool { foreach ($keys as $key) { $this->delete($key); } return true; }
    public function has(string $key): bool { return array_key_exists($key, $this->data); }
};
$schemaCache = new SchemaCache($cache);

/********** Connection with configuration (lazy-connect) **********/
$dsn = new Dsn('sqlite', $file);
$driver = new Driver($dsn->asString());
$conn = new Connection($driver, $schemaCache);
// Connection is not opened until the first database operation

/********** Get underlying PDO **********/
$pdo = $conn->getActivePDO(); // Opens the connection and returns the PDO

/********** Connection from existing PDO **********/
// Connection and Driver are both final; there is no native API to inject an existing PDO.
// The only workaround is a custom driver that returns the existing PDO from createConnection().
$pdo = new PDO('sqlite:' . $file);
$driver = new class($pdo, 'sqlite:' . $file) extends AbstractPdoDriver {
    public function __construct(private readonly PDO $existingPdo, string $dsn)
    {
        parent::__construct($dsn);
    }

    public function createConnection(): PDO
    {
        return $this->existingPdo;
    }

    public function getDriverName(): string
    {
        return 'sqlite';
    }
};
$conn = new Connection($driver, $schemaCache);
