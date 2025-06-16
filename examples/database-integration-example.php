<?php

/**
 * Logbie Framework Database Integration Example
 * 
 * This example demonstrates how to use the Logbie Framework's database integration
 * with both MySQL and SQLite databases. It shows equivalent operations in both
 * database systems and how to switch between them using configuration.
 */

// Include the autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use LogbieCore\Application;
use LogbieCore\DatabaseORM;
use LogbieCore\Database\DatabaseDriverFactory;
use LogbieCore\Database\MySQLDriver;
use LogbieCore\Database\SQLiteDriver;

// Set up the application
$basePath = dirname(__DIR__);
$app = new Application($basePath);

// Bootstrap the application
$app->bootstrap();

// Get the container
$container = $app->getContainer();

// Example 1: Using the default database connection from configuration
echo "Example 1: Using the default database connection\n";
$defaultDb = $container->get('db');
exampleOperations($defaultDb, 'Default');

// Example 2: Creating a MySQL database connection manually
echo "\nExample 2: Using a MySQL database connection\n";
$mysqlDriver = new MySQLDriver();
$mysqlConfig = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'logbie',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];
$mysqlDb = DatabaseORM::withDriver($mysqlDriver, $mysqlConfig);
exampleOperations($mysqlDb, 'MySQL');

// Example 3: Creating a SQLite database connection manually
echo "\nExample 3: Using a SQLite database connection\n";
$sqliteDriver = new SQLiteDriver();
$sqliteConfig = [
    'driver' => 'sqlite',
    'database' => $basePath . '/storage/database/logbie.sqlite',
    'foreignKeys' => true,
    'journalMode' => 'WAL'
];
$sqliteDb = DatabaseORM::withDriver($sqliteDriver, $sqliteConfig);
exampleOperations($sqliteDb, 'SQLite');

// Example 4: Using the factory to create database connections
echo "\nExample 4: Using the DatabaseDriverFactory\n";
$factory = new DatabaseDriverFactory();

// Create a MySQL driver
$mysqlDriver = $factory->create('mysql');
$mysqlDb = DatabaseORM::withDriver($mysqlDriver, $mysqlConfig);
exampleOperations($mysqlDb, 'MySQL (Factory)');

// Create a SQLite driver
$sqliteDriver = $factory->create('sqlite');
$sqliteDb = DatabaseORM::withDriver($sqliteDriver, $sqliteConfig);
exampleOperations($sqliteDb, 'SQLite (Factory)');

// Example 5: Switching database connections using configuration
echo "\nExample 5: Switching database connections using configuration\n";

// Load MySQL configuration
$configLoader = $app->getConfigLoader();
$mysqlConfig = $configLoader->getDatabaseConnectionConfig('mysql');
$mysqlDriver = $factory->create('mysql');
$mysqlDb = DatabaseORM::withDriver($mysqlDriver, $mysqlConfig);
exampleOperations($mysqlDb, 'MySQL (Config)');

// Load SQLite configuration
$sqliteConfig = $configLoader->getDatabaseConnectionConfig('sqlite');
$sqliteDriver = $factory->create('sqlite');
$sqliteDb = DatabaseORM::withDriver($sqliteDriver, $sqliteConfig);
exampleOperations($sqliteDb, 'SQLite (Config)');

/**
 * Example database operations
 * 
 * @param DatabaseORM $db The database connection
 * @param string $label Label for the output
 */
function exampleOperations(DatabaseORM $db, string $label): void
{
    echo "[$label] Database driver: " . $db->getDriver()->getName() . "\n";
    
    try {
        // Example 1: Create a table
        echo "[$label] Creating users table...\n";
        createUsersTable($db);
        
        // Example 2: Insert data
        echo "[$label] Inserting user data...\n";
        $userId = insertUser($db, [
            'username' => 'john_doe',
            'email' => 'john@example.com',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        echo "[$label] Inserted user with ID: $userId\n";
        
        // Example 3: Read data
        echo "[$label] Reading user data...\n";
        $user = $db->read('users', ['id' => $userId]);
        echo "[$label] User data: " . json_encode($user[0]) . "\n";
        
        // Example 4: Update data
        echo "[$label] Updating user data...\n";
        $affected = $db->update('users', ['email' => 'john.doe@example.com'], ['id' => $userId]);
        echo "[$label] Updated $affected row(s)\n";
        
        // Example 5: Read updated data
        echo "[$label] Reading updated user data...\n";
        $user = $db->read('users', ['id' => $userId]);
        echo "[$label] Updated user data: " . json_encode($user[0]) . "\n";
        
        // Example 6: Transaction handling
        echo "[$label] Testing transaction handling...\n";
        $db->beginTransaction();
        try {
            $db->update('users', ['username' => 'john_updated'], ['id' => $userId]);
            $db->commit();
            echo "[$label] Transaction committed\n";
        } catch (\Exception $e) {
            $db->rollback();
            echo "[$label] Transaction rolled back: " . $e->getMessage() . "\n";
        }
        
        // Example 7: Read after transaction
        echo "[$label] Reading user data after transaction...\n";
        $user = $db->read('users', ['id' => $userId]);
        echo "[$label] User data after transaction: " . json_encode($user[0]) . "\n";
        
        // Example 8: Batch operations
        echo "[$label] Testing batch operations...\n";
        $result = $db->batchOperation(function($db) use ($userId) {
            // Insert multiple users in a single transaction
            $users = [
                ['username' => 'user1', 'email' => 'user1@example.com', 'created_at' => date('Y-m-d H:i:s')],
                ['username' => 'user2', 'email' => 'user2@example.com', 'created_at' => date('Y-m-d H:i:s')],
                ['username' => 'user3', 'email' => 'user3@example.com', 'created_at' => date('Y-m-d H:i:s')]
            ];
            
            $ids = [];
            foreach ($users as $user) {
                $ids[] = $db->create('users', $user);
            }
            
            return $ids;
        });
        echo "[$label] Batch operation inserted users with IDs: " . implode(', ', $result) . "\n";
        
        // Example 9: Count users
        echo "[$label] Counting users...\n";
        $count = count($db->read('users'));
        echo "[$label] Total users: $count\n";
        
        // Example 10: Delete data
        echo "[$label] Deleting user data...\n";
        $affected = $db->delete('users', ['id' => $userId]);
        echo "[$label] Deleted $affected row(s)\n";
        
        // Example 11: Drop table
        echo "[$label] Dropping users table...\n";
        dropUsersTable($db);
        
    } catch (\Exception $e) {
        echo "[$label] Error: " . $e->getMessage() . "\n";
    }
}

/**
 * Create the users table
 * 
 * @param DatabaseORM $db The database connection
 */
function createUsersTable(DatabaseORM $db): void
{
    $driver = $db->getDriver()->getName();
    
    if ($driver === 'mysql') {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            created_at DATETIME NOT NULL
        )";
    } else { // sqlite
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            email TEXT NOT NULL,
            created_at TEXT NOT NULL
        )";
    }
    
    $db->query($sql);
}

/**
 * Drop the users table
 * 
 * @param DatabaseORM $db The database connection
 */
function dropUsersTable(DatabaseORM $db): void
{
    $sql = "DROP TABLE IF EXISTS users";
    $db->query($sql);
}

/**
 * Insert a user
 * 
 * @param DatabaseORM $db The database connection
 * @param array $userData User data
 * @return int|string The ID of the inserted user
 */
function insertUser(DatabaseORM $db, array $userData): int|string
{
    return $db->create('users', $userData);
}