# Database ORM Documentation for Logbie Framework
Version: 1.0
Last Updated: 2024-10-26

## Table of Contents
1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Basic Usage](#basic-usage)
5. [CRUD Operations](#crud-operations)
6. [Advanced Features](#advanced-features)
7. [Transaction Management](#transaction-management)
8. [Best Practices](#best-practices)
9. [Security Considerations](#security-considerations)

## Introduction

The Logbie DatabaseORM provides a secure and efficient database abstraction layer for PHP 8.2+ applications. It offers:
- Prepared statement caching
- SQL injection protection
- Transaction support
- Schema information caching
- Relationship handling

## Installation

The DatabaseORM is part of the Logbie Framework core. Place the `DatabaseORM.php` file in:
```
src/Core/DatabaseORM.php
```

## Configuration

Initialize the ORM with your database configuration:

```php
use LogbieCore\DatabaseORM;

$config = [
    'driver'   => 'mysql',
    'host'     => 'localhost',
    'port'     => '3306',
    'database' => 'your_database',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset'  => 'utf8mb4'
];

$db = new DatabaseORM($config);
```

## Basic Usage

### Creating Records

```php
// Single record insertion
$userData = [
    'username' => 'john_doe',
    'email'    => 'john@example.com',
    'status'   => 'active'
];

$userId = $db->create('users', $userData);
```

### Reading Records

```php
// Fetch all users
$allUsers = $db->read('users');

// Fetch with conditions
$activeUsers = $db->read('users', [
    'status' => 'active'
]);

// Select specific columns with options
$users = $db->read(
    'users',
    ['status' => 'active'],
    ['id', 'username', 'email'],
    [
        'orderBy' => 'username',
        'orderDirection' => 'ASC',
        'limit' => 10,
        'offset' => 0
    ]
);
```

### Updating Records

```php
// Update user status
$data = ['status' => 'inactive'];
$conditions = ['id' => 123];

$affectedRows = $db->update('users', $data, $conditions);
```

### Deleting Records

```php
// Delete user
$conditions = ['id' => 123];
$affectedRows = $db->delete('users', $conditions);
```

## Advanced Features

### Raw Queries

```php
// Execute custom SELECT query
$results = $db->query(
    "SELECT * FROM users WHERE created_at > ? AND status = ?",
    ['2024-01-01', 'active']
);

// Execute custom UPDATE query
$affected = $db->query(
    "UPDATE users SET last_login = NOW() WHERE id = ?",
    [123]
);
```

### Many-to-Many Relationships

```php
// Get all roles for a user
$userRoles = $db->getManyToMany(
    'users',
    'roles',
    'user_roles',
    ['user_id' => 123]
);
```

### Schema Information

```php
// Get table structure
$schema = $db->getTableSchema('users');

// Example response:
// [
//     ['Field' => 'id', 'Type' => 'int', 'Null' => 'NO', 'Key' => 'PRI', ...],
//     ['Field' => 'username', 'Type' => 'varchar(255)', 'Null' => 'NO', ...],
//     ...
// ]
```

## Transaction Management

```php
try {
    $db->beginTransaction();

    // Perform multiple operations
    $userId = $db->create('users', $userData);
    $db->create('user_profiles', ['user_id' => $userId, ...]);
    
    $db->commit();
} catch (\Exception $e) {
    $db->rollback();
    throw $e;
}
```

## Best Practices

### 1. Use Prepared Statements
Always use the built-in methods instead of raw queries when possible:
```php
// Good
$user = $db->read('users', ['id' => $userId]);

// Avoid
$user = $db->query("SELECT * FROM users WHERE id = $userId");
```

### 2. Transaction Wrapping
Wrap related operations in transactions:
```php
$db->beginTransaction();
try {
    // Multiple operations
    $db->commit();
} catch (\Exception $e) {
    $db->rollback();
    throw $e;
}
```

### 3. Error Handling
Always catch and handle database exceptions:
```php
try {
    $result = $db->create('users', $userData);
} catch (\PDOException $e) {
    // Log the error
    // Handle the failure
    throw new \RuntimeException('User creation failed', 0, $e);
}
```

## Security Considerations

### 1. Input Validation
Always validate input before passing to the ORM:
```php
$username = filter_var($input['username'], FILTER_SANITIZE_STRING);
if (!$username) {
    throw new \InvalidArgumentException('Invalid username');
}
```

### 2. Output Encoding
Encode data when displaying:
```php
echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
```

### 3. Connection Security
- Use strong passwords
- Limit database user privileges
- Use SSL/TLS for remote connections

## Example Module Integration

Here's how to use the DatabaseORM in a Logbie module:

```php
namespace Logbie;

use LogbieCore\BaseModule;
use LogbieCore\DatabaseORM;

class UserManager extends BaseModule
{
    private DatabaseORM $db;

    public function __construct($container)
    {
        parent::__construct($container);
        
        // Get database configuration from container
        $config = $container->get('dbConfig');
        $this->db = new DatabaseORM($config);
    }

    public function createUser(array $userData): int
    {
        try {
            $this->db->beginTransaction();

            // Create user
            $userId = $this->db->create('users', [
                'username' => $userData['username'],
                'email'    => $userData['email'],
                'status'   => 'active'
            ]);

            // Create related profile
            $this->db->create('user_profiles', [
                'user_id'     => $userId,
                'first_name'  => $userData['firstName'],
                'last_name'   => $userData['lastName']
            ]);

            $this->db->commit();
            return $userId;

        } catch (\Exception $e) {
            $this->db->rollback();
            $this->logger->log("Error creating user: " . $e->getMessage());
            throw $e;
        }
    }
}
```

## Performance Tips

1. **Use Specific Columns**
```php
// Better performance - only fetches needed columns
$users = $db->read('users', [], ['id', 'username']);

// Less efficient - fetches all columns
$users = $db->read('users');
```

2. **Leverage Prepared Statement Caching**
```php
// The prepared statement will be cached and reused
for ($i = 0; $i < 100; $i++) {
    $db->read('users', ['id' => $i]);
}
```

3. **Batch Operations**
```php
$db->beginTransaction();
try {
    foreach ($users as $user) {
        $db->create('users', $user);
    }
    $db->commit();
} catch (\Exception $e) {
    $db->rollback();
    throw $e;
}
```

## Troubleshooting

### Common Issues and Solutions

1. **Connection Failed**
   ```
   Problem: Connection failed: SQLSTATE[HY000] [2002] Connection refused
   Solution: Verify database credentials and host availability
   ```

2. **Duplicate Entry**
   ```
   Problem: Create operation failed: SQLSTATE[23000]: Duplicate entry
   Solution: Check unique constraints and handle accordingly
   ```

3. **Table Schema Not Found**
   ```
   Problem: Failed to get table schema: Table 'database.table' doesn't exist
   Solution: Verify table name and database permissions
   ```

## Support

For additional assistance:
1. Check framework documentation
2. Review DatabaseORM source code
3. Submit issues via the framework's issue tracker
