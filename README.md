This repository demonstrates how popular PHP database abstraction libraries handle three common scenarios:

- **Connection with params** — creating a connection from configuration (DSN, credentials, etc.), sometimes with lazy-connect support
- **Connection from existing PDO** — accepting an already-instantiated `PDO` object
- **Get underlying PDO** — exposing the internal `PDO` instance

Each library is used standalone, outside of its respective framework. This simulates a real-world scenario where an application tries to integrate library A from framework B alongside library X from framework Y.

This is related to the proposed PSR for `PdoProviderInterface`:
https://github.com/php-fig/fig-standards/pull/1348

## Libraries

| Package | Type | File |
|---|---|---|
| `atlas/orm` | ORM | [atlas-orm.php](atlas-orm.php) |
| `aura/sql` | PDO extension | [aura-sql.php](aura-sql.php) |
| `cakephp/database` | DBAL | [cakephp-database.php](cakephp-database.php) |
| `clancats/hydrahon` | Query builder | [clancats-hydrahon.php](clancats-hydrahon.php) |
| `doctrine/dbal` | DBAL | [doctrine-dbal.php](doctrine-dbal.php) |
| `envms/fluentpdo` | Query builder | [envms-fluentpdo.php](envms-fluentpdo.php) |
| `illuminate/database` | DBAL + ORM | [illuminate-database.php](illuminate-database.php) |
| `nette/database` | DBAL | [nette-database.php](nette-database.php) |
| `paragonie/easydb` | PDO wrapper | [paragonie-easydb.php](paragonie-easydb.php) |
| `php-db/phpdb-sqlite` | DBAL | [phpdb-sqlite.php](phpdb-sqlite.php) |
