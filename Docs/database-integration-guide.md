# Logbie Framework: Database Integration Guide

This guide explains how to use the Logbie Framework's database integration system, which supports both MySQL and SQLite databases.

## Overview

The Logbie Framework now supports multiple database systems through a driver-based architecture. This allows you to:

- Use either MySQL or SQLite databases with the same API
- Switch between database systems without changing your application code
- Configure database-specific settings for optimal performance
- Extend the system with additional database drivers if needed

## Architecture

The database integration is built on the following components:

1. **DatabaseDriverInterface**: Defines the contract for all database drivers
2. **MySQLDriver**: Implements the interface for MySQL/MariaDB databases
3. **SQLiteDriver**: Implements the interface for SQLite databases
4. **DatabaseDriverFactory**: Creates driver instances based on configuration
5. **DatabaseORM**: Uses the appropriate driver to perform database operations
6. **ConfigLoader**: Loads and manages database configuration

This architecture follows the Dependency Inversion Principle, allowing the DatabaseORM to depend on abstractions rather than concrete implementations.

## Configuration

Database configuration is stored in `config/database.php`. This file defines connections for both MySQL and SQLite:

```php
return [
    // Default database connection
    'default' => env('DB_DRIVER', 'mysql'),
    
    // Database connections
    'connections' => [
        // MySQL connection
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('MYSQL_HOST', 'localhost'),
            'port' => env('MYSQL_PORT', '3306'),
            'database' => env('MYSQL_DATABASE', 'logbie'),
            'username' => env('MYSQL_USERNAME', 'root'),
            'password' => env('MYSQL_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            // Additional MySQL-specific settings...
        ],
        
        // SQLite connection
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('SQLITE_DATABASE', storage_path('database/logbie.sqlite')),
            'foreignKeys' => env('SQLITE_FOREIGN_KEYS', true),
            'journalMode' => env('SQLITE_JOURNAL_MODE', 'WAL'),
            // Additional SQLite-specific settings...
        ]
    ]
];
```

You can configure the default database system using environment variables:

```
# Use SQLite as the default database
DB_DRIVER=sqlite
SQLITE_DATABASE=/path/to/database.sqlite
```

## Basic Usage

### Using the Default Database Connection

The simplest way to use the database integration is through the dependency injection container:

```php
// Get the database instance from the container
$db = $container->get('db');

// Perform database operations
$users = $db->read('users', ['active' => true]);
```

The framework will automatically use the default database connection specified in the configuration.

### Creating Database Connections Manually

You can also create database connections manually:

```php
// Create a MySQL connection
$mysqlDriver = new MySQLDriver();
$mysqlConfig = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'logbie',
    'username' => 'root',
    'password' => ''
];
$mysqlDb = DatabaseORM::withDriver($mysqlDriver, $mysqlConfig);

// Create a SQLite connection
$sqliteDriver = new SQLiteDriver();
$sqliteConfig = [
    'driver' => 'sqlite',
    'database' => '/path/to/database.sqlite'
];
$sqliteDb = DatabaseORM::withDriver($sqliteDriver, $sqliteConfig);
```

### Using the DatabaseDriverFactory

The `DatabaseDriverFactory` provides a convenient way to create database drivers:

```php
$factory = new DatabaseDriverFactory();

// Create a MySQL driver
$mysqlDriver = $factory->create('mysql');
$mysqlDb = DatabaseORM::withDriver($mysqlDriver, $mysqlConfig);

// Create a SQLite driver
$sqliteDriver = $factory->create('sqlite');
$sqliteDb = DatabaseORM::withDriver($sqliteDriver, $sqliteConfig);
```

## Database Operations

The DatabaseORM provides a consistent API for both database systems:

### CRUD Operations

```php
// Create a record
$userId = $db->create('users', [
    'username' => 'john_doe',
    'email' => 'john@example.com'
]);

// Read records
$users = $db->read('users', ['active' => true], ['id', 'username', 'email']);

// Update records
$affected = $db->update('users', ['active' => false], ['last_login_at' => null]);

// Delete records
$affected = $db->delete('users', ['id' => $userId]);
```

### Transactions

```php
// Begin a transaction
$db->beginTransaction();

try {
    // Perform multiple operations
    $userId = $db->create('users', ['username' => 'john_doe']);
    $db->create('profiles', ['user_id' => $userId, 'bio' => 'Lorem ipsum']);
    
    // Commit the transaction
    $db->commit();
} catch (\Exception $e) {
    // Rollback on error
    $db->rollback();
    throw $e;
}
```

### Batch Operations

```php
// Perform multiple operations in a transaction
$result = $db->batchOperation(function($db) {
    // Operations here will be wrapped in a transaction
    $ids = [];
    
    for ($i = 0; $i < 10; $i++) {
        $ids[] = $db->create('items', [
            'name' => "Item {$i}",
            'value' => rand(1, 100)
        ]);
    }
    
    return $ids;
});
```

### Raw Queries

```php
// Execute a raw SQL query
$results = $db->query("SELECT * FROM users WHERE created_at > ?", ['2025-01-01']);
```

## Database-Specific Considerations

### MySQL-Specific Features

The MySQL driver supports:

- Connection pooling through persistent connections
- Query caching
- SSL/TLS secure connections
- Custom SQL mode configuration
- Server-side timezone settings

Example configuration:

```php
$mysqlConfig = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'logbie',
    'username' => 'root',
    'password' => '',
    'persistent' => true,
    'sqlMode' => 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION',
    'timezone' => '+00:00'
];
```

### SQLite-Specific Features

The SQLite driver supports:

- In-memory databases (`:memory:`)
- Write-Ahead Logging (WAL) for better concurrency
- Foreign key constraint enforcement
- Custom cache size configuration
- Memory-mapped I/O

Example configuration:

```php
$sqliteConfig = [
    'driver' => 'sqlite',
    'database' => '/path/to/database.sqlite',
    'foreignKeys' => true,
    'journalMode' => 'WAL',
    'synchronous' => 'NORMAL',
    'cacheSize' => 2000,
    'tempStore' => 'MEMORY'
];
```

## Schema Differences

When working with both database systems, be aware of these schema differences:

| Feature | MySQL | SQLite |
|---------|-------|--------|
| Auto-increment | `AUTO_INCREMENT` | `AUTOINCREMENT` |
| Data types | `INT`, `VARCHAR`, etc. | `INTEGER`, `TEXT`, etc. |
| Date/time | Native date types | Stored as TEXT in ISO format |
| Enum | `ENUM` type | Use `CHECK` constraints |
| Indexes | Many index types | Basic indexes only |

Example of creating a compatible table:

```php
// MySQL version
$db->query("CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL
)");

// SQLite version
$db->query("CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    email TEXT NOT NULL,
    created_at TEXT NOT NULL
)");
```

## Performance Optimization

### MySQL Optimization

```php
// Configure MySQL for performance
$mysqlConfig = [
    // ... basic configuration ...
    'persistent' => true,
    'buffered' => true,
    'mysqlConfig' => [
        'innodb_buffer_pool_size' => '256M',
        'innodb_flush_log_at_trx_commit' => 2,
        'query_cache_size' => '50M'
    ]
];
```

### SQLite Optimization

```php
// Configure SQLite for performance
$sqliteConfig = [
    // ... basic configuration ...
    'journalMode' => 'WAL',
    'synchronous' => 'NORMAL',
    'cacheSize' => 10000,
    'tempStore' => 'MEMORY',
    'mmapSize' => '1G'
];
```

## Extending with Custom Drivers

You can add support for additional database systems by:

1. Creating a new driver class that implements `DatabaseDriverInterface`
2. Registering the driver with the `DatabaseDriverFactory`

Example:

```php
// Create a PostgreSQL driver
class PostgreSQLDriver implements DatabaseDriverInterface
{
    // Implement interface methods
}

// Register the driver
DatabaseDriverFactory::registerDriver('pgsql', PostgreSQLDriver::class);

// Use the driver
$pgsqlDriver = DatabaseDriverFactory::create('pgsql');
```

## Example Code

See `examples/database-integration-example.php` for a complete example of using both MySQL and SQLite databases with the same code.

## Best Practices

1. **Use Configuration**: Store database settings in configuration files rather than hardcoding them
2. **Handle Differences**: Be aware of SQL syntax differences between database systems
3. **Use Transactions**: Wrap related operations in transactions for data integrity
4. **Optimize for Each Database**: Apply database-specific optimizations when needed
5. **Test Both Systems**: Ensure your code works correctly with both MySQL and SQLite