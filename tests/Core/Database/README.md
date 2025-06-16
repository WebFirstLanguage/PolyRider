# Database Integration Test Suite

This directory contains comprehensive tests for the Logbie Framework's database integration implementation. The tests verify the functionality of the database drivers, ORM, and configuration components.

## Test Structure

The test suite is organized into the following files:

1. **DatabaseDriverFactoryTest.php**
   - Tests for the `DatabaseDriverFactory` class
   - Verifies driver creation, registration, and validation

2. **MySQLDriverTest.php**
   - Tests for the `MySQLDriver` class
   - Verifies MySQL-specific operations and configuration

3. **SQLiteDriverTest.php**
   - Tests for the `SQLiteDriver` class
   - Verifies SQLite-specific operations and configuration

4. **DatabaseORMTest.php**
   - Tests for the `DatabaseORM` class
   - Verifies CRUD operations, transaction management, and other ORM features

5. **ConfigLoaderTest.php**
   - Tests for the `ConfigLoader` class
   - Verifies database configuration loading and access

6. **DatabaseIntegrationTest.php**
   - Integration tests for the database components
   - Verifies the components working together with real database connections

7. **DatabaseTestFixtures.php**
   - Provides test fixtures and helper methods for database tests

## Test Coverage

The test suite covers the following aspects of the database integration:

### Connection Handling
- Connection establishment for both MySQL and SQLite
- Connection configuration
- Error handling for failed connections
- Driver-specific connection options

### Query Execution
- CRUD operations (Create, Read, Update, Delete)
- Custom query execution
- Prepared statement management
- Parameter binding
- Result handling

### Transaction Management
- Transaction begin/commit/rollback
- Nested transactions
- Transaction error handling
- Batch operations within transactions

### Error Handling
- Connection errors
- Query execution errors
- Transaction errors
- Configuration errors
- Invalid parameter handling

### Prepared Statement Management
- Statement preparation
- Statement caching
- Statement execution
- Parameter binding

### Configuration Loading
- Database configuration file loading
- Default configuration access
- Connection-specific configuration access
- Configuration validation

### Driver Switching
- Switching between MySQL and SQLite drivers
- Equivalent functionality across drivers
- Driver-specific optimizations

## Running the Tests

To run the tests, use PHPUnit from the project root directory:

```bash
vendor/bin/phpunit tests/Core/Database
```

To skip MySQL tests (if no MySQL server is available):

```bash
SKIP_MYSQL_TESTS=true vendor/bin/phpunit tests/Core/Database
```

## Test Dependencies

The tests require:

1. PHPUnit 9.0+
2. PHP 8.2+
3. SQLite extension
4. PDO extension
5. MySQL server (optional, for MySQL tests)

## Notes on MySQL Tests

The MySQL integration tests require a MySQL server with the following configuration:

- Host: localhost
- Port: 3306
- Database: logbie_test
- Username: root
- Password: (empty)

You can modify these settings in the `DatabaseTestFixtures.php` file if needed.

If a MySQL server is not available, the tests will automatically skip the MySQL-specific tests when the `SKIP_MYSQL_TESTS` environment variable is set to `true`.