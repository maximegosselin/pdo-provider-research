This repository demonstrates how popular PHP database abstraction libraries handle three common scenarios:

- **Connection with configuration**: creating a connection from configuration (DSN, credentials, etc.), sometimes with
  lazy-connect support
- **Connection from existing PDO**: accepting an already-instantiated `PDO` object
- **Get underlying PDO**: exposing the internal `PDO` instance

Each library is used standalone, outside of its respective framework. This simulates a real-world scenario where an application tries to integrate library A from framework B alongside library X from framework Y.

This is related to the proposed PSR for `PdoProviderInterface`:
https://github.com/php-fig/fig-standards/pull/1348

## Libraries

- [atlas/orm](#atlasorm)
- [aura/sql](#aurasql)
- [cakephp/database](#cakephpdatabase)
- [clancats/hydrahon](#clancatshydrahon)
- [doctrine/dbal](#doctrinedbal)
- [envms/fluentpdo](#envmsfluentpdo)
- [illuminate/database](#illuminatedatabase)
- [nette/database](#nettedatabase)
- [paragonie/easydb](#paragonieeasydb)
- [php-db/phpdb-sqlite](#php-dbphpdb-sqlite)

---

## atlas/orm

**Type:** ORM

**File:** [atlas-orm.php](atlas-orm.php)

| Scenario                      | Supported           |
|-------------------------------|---------------------|
| Connection with configuration | ✅ (no lazy-connect) |
| Connection from existing PDO  | ✅                   |
| Get underlying PDO            | ✅                   |

`Atlas\Pdo\Connection` supports all three scenarios. Connection is created eagerly (no lazy-connect). It accepts a raw
`\PDO` directly via `Atlas::new($pdo)`, and exposes the underlying `\PDO` via `getPdo()`.

### Supporting `PdoProviderInterface`

The `Connection` class already exposes `getPdo()`, so implementing `PdoProviderInterface` would require adding a
`getConnection()` method that delegates to `getPdo()`, a trivial one-line change. As a consumer, `Atlas::new()` currently accepts a `\PDO`; it could transitionally accept `\PDO|PdoProviderInterface`,
calling `getConnection()` on the provider and storing the result, or calling it lazily on first use.

---

## aura/sql

**Type:** PDO extension

**File:** [aura-sql.php](aura-sql.php)

| Scenario                      | Supported        |
|-------------------------------|------------------|
| Connection with configuration | ✅ (lazy-connect) |
| Connection from existing PDO  | ✅                |
| Get underlying PDO            | ✅                |

`ExtendedPdo` supports lazy-connect and extends `\PDO` directly, so it _is_ the PDO. `DecoratedPdo` wraps an existing
`\PDO` but does not expose it; `getPdo()` is not available on `DecoratedPdo`, making the **Get underlying PDO**
scenario unavailable when using the decorator approach.

### Supporting `PdoProviderInterface`

Since `ExtendedPdo` extends `\PDO`, implementing `PdoProviderInterface` with `getConnection(): \PDO { return $this; }`
would be trivial. `DecoratedPdo` cannot implement it without first exposing its internal `\PDO` via a getter, a small upstream change. As a consumer, `DecoratedPdo` could
transitionally accept `\PDO|PdoProviderInterface` in its constructor, calling `getConnection()` when a provider is
passed, a backwards-compatible change requiring no API break.

---

## cakephp/database

**Type:** DBAL

**File:** [cakephp-database.php](cakephp-database.php)

| Scenario                      | Supported        |
|-------------------------------|------------------|
| Connection with configuration | ✅ (lazy-connect) |
| Connection from existing PDO  | ❌                |
| Get underlying PDO            | ❌                |

CakePHP's `Driver::setPdo()` and `Driver::getPdo()` are both `protected` in CakePHP 5. There is no public API to inject
an existing `\PDO` or to retrieve the one used internally. Subclassing the driver to override these methods is the only
workaround, but it is fragile and not a supported pattern.

### Supporting `PdoProviderInterface`

Currently not possible without upstream changes. CakePHP would need to make `getPdo()` and `setPdo()` public, or add a
dedicated method to accept a `PdoProviderInterface`. Until then, the library is fully self-contained with respect to
connection management and cannot participate in a shared connection model.

---

## clancats/hydrahon

**Type:** Query builder

**File:** [clancats-hydrahon.php](clancats-hydrahon.php)

| Scenario                      | Supported |
|-------------------------------|-----------|
| Connection with configuration | ❌         |
| Connection from existing PDO  | ✅         |
| Get underlying PDO            | N/A       |

Hydrahon does not manage a connection at all; it delegates query execution entirely to a user-provided callback
closure. There is no built-in way to create a connection from configuration. The PDO is always managed externally, so it
is inherently available to the caller.

### Supporting `PdoProviderInterface`

This is the most naturally compatible library. Since the executor callback is fully user-controlled, a
`PdoProviderInterface` can be used directly inside it (`$provider->getConnection()->prepare($sql)`) with no library
changes required. Every query invocation would call `getConnection()`, giving the provider full control over connection management.

---

## doctrine/dbal

**Type:** DBAL

**File:** [doctrine-dbal.php](doctrine-dbal.php)

| Scenario                      | Supported        |
|-------------------------------|------------------|
| Connection with configuration | ✅ (lazy-connect) |
| Connection from existing PDO  | ✅                |
| Get underlying PDO            | ✅                |

Doctrine DBAL supports all three scenarios. Lazy-connect is the default. An existing `\PDO` can be passed via the
`'pdo'` key in the connection params array. The underlying `\PDO` is retrievable via `getNativeConnection()`, which
triggers connection opening if not yet connected.

### Supporting `PdoProviderInterface`

The `Connection` class could implement `PdoProviderInterface` by delegating `getConnection()` to
`getNativeConnection()`, with minimal effort. As a consumer, Doctrine currently stores the passed `\PDO` directly; to
fully support a provider it would need to call `getConnection()` on each database operation rather than caching the
instance, delegating connection management to the provider. This would be a more significant internal change.

---

## envms/fluentpdo

**Type:** Query builder

**File:** [envms-fluentpdo.php](envms-fluentpdo.php)

| Scenario                      | Supported |
|-------------------------------|-----------|
| Connection with configuration | ❌         |
| Connection from existing PDO  | ✅         |
| Get underlying PDO            | ✅         |

FluentPDO requires a `\PDO` instance at construction; there is no built-in facility to create a connection from params.
The library exposes the stored PDO via `getPdo()`.

### Supporting `PdoProviderInterface`

As an implementer, `getPdo()` already exists, so adding `getConnection()` as an alias would be trivial. As a consumer,
the constructor currently stores the `\PDO` directly. A caller can pass `$provider->getConnection()` at construction,
but this is a one-time snapshot; the library would not benefit from the provider's connection management. True
support would require refactoring to call `getConnection()` before each operation instead of caching the PDO at
construction.

---

## illuminate/database

**Type:** DBAL + ORM

**File:** [illuminate-database.php](illuminate-database.php)

| Scenario                      | Supported                 |
|-------------------------------|---------------------------|
| Connection with configuration | ✅ (lazy-connect)          |
| Connection from existing PDO  | ✅ (requires a workaround) |
| Get underlying PDO            | ✅                         |

Supports all three scenarios. An existing `\PDO` can be injected via `setPdo()` after the connection is created with
params (a placeholder config is still required). The underlying PDO is retrievable via `getPdo()`. Laravel's connection
layer already includes its own connection management logic.

### Supporting `PdoProviderInterface`

`Connection` could implement `PdoProviderInterface` via `getConnection(): \PDO { return $this->getPdo(); }` with no
effort. As a consumer, `setPdo()` could transitionally accept `\PDO|PdoProviderInterface`, calling `getConnection()` when a
provider is passed. However, Laravel's internal connection management partially overlaps with the provider pattern, so
the integration would need to be designed carefully to avoid conflicts.

---

## nette/database

**Type:** DBAL

**File:** [nette-database.php](nette-database.php)

| Scenario                      | Supported        |
|-------------------------------|------------------|
| Connection with configuration | ✅ (lazy-connect) |
| Connection from existing PDO  | ❌                |
| Get underlying PDO            | ✅                |

Nette's `Connection` only accepts a DSN string in its constructor. The internal `\PDO` instance is stored in a private
property with no public setter, making it impossible to inject an existing connection. The underlying PDO is retrievable
via `getPdo()`, which opens the connection if not yet established.

### Supporting `PdoProviderInterface`

As an implementer, `getPdo()` already exists; a `getConnection()` alias would suffice. As a consumer, there is
currently no supported path: `Connection` always creates its own `\PDO` from the DSN. Supporting `PdoProviderInterface`
would require either a new constructor overload accepting a provider, or exposing a `setPdo()` method, both requiring
upstream changes.

---

## paragonie/easydb

**Type:** PDO wrapper

**File:** [paragonie-easydb.php](paragonie-easydb.php)

| Scenario                      | Supported           |
|-------------------------------|---------------------|
| Connection with configuration | ✅ (no lazy-connect) |
| Connection from existing PDO  | ✅                   |
| Get underlying PDO            | ✅                   |

EasyDB supports all three scenarios. Note that `Factory::create()` opens the connection eagerly; there is no
lazy-connect option. An existing `\PDO` can be passed directly to the `EasyDB` constructor. The underlying PDO is
accessible via `getPdo()`.

### Supporting `PdoProviderInterface`

As an implementer, delegating `getConnection()` to `getPdo()` would be a one-line addition. As a consumer, the `EasyDB` constructor could transitionally accept `\PDO|PdoProviderInterface`, calling
`getConnection()` when a provider is passed. Full support for provider-managed connections would require lazy initialization: storing the provider and calling
`getConnection()` on each use rather than caching the `\PDO` at construction.

---

## php-db/phpdb-sqlite

**Type:** DBAL

**File:** [phpdb-sqlite.php](phpdb-sqlite.php)

| Scenario                      | Supported        |
|-------------------------------|------------------|
| Connection with configuration | ✅ (lazy-connect) |
| Connection from existing PDO  | ✅                |
| Get underlying PDO            | ✅                |

Supports all three scenarios. The `Connection` class accepts either a config array (lazy-connect) or a raw `\PDO`
instance directly. The underlying PDO is accessible via `$adapter->getDriver()->getConnection()->getResource()`, though verbose due to
the layered driver architecture.

### Supporting `PdoProviderInterface`

As an implementer, a thin wrapper or extension of `Adapter` could expose `getConnection()` by delegating to
`getResource()`. As a consumer, `Connection` already accepts a `\PDO` in its constructor; it could transitionally accept
`\PDO|PdoProviderInterface`, calling `getConnection()` when a provider is passed, a contained change given the layered architecture.
