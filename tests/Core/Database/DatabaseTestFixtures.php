<?php

namespace Tests\Core\Database;

/**
 * DatabaseTestFixtures
 * 
 * Provides test fixtures for database tests.
 */
class DatabaseTestFixtures
{
    /**
     * Get MySQL configuration for testing
     * 
     * @return array MySQL configuration
     */
    public static function getMySQLConfig(): array
    {
        return [
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => '3306',
            'database' => 'logbie_test',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'persistent' => false,
            'buffered' => true,
            'timeout' => 5,
            'sqlMode' => 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION',
            'timezone' => '+00:00',
            'options' => []
        ];
    }
    
    /**
     * Get SQLite configuration for testing
     * 
     * @return array SQLite configuration
     */
    public static function getSQLiteConfig(): array
    {
        return [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'foreignKeys' => true,
            'journalMode' => 'WAL',
            'synchronous' => 'NORMAL',
            'cacheSize' => 2000,
            'tempStore' => 'MEMORY',
            'options' => []
        ];
    }
    
    /**
     * Create users table SQL for MySQL
     * 
     * @return string SQL statement
     */
    public static function createUsersTableMySQL(): string
    {
        return "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            created_at DATETIME NOT NULL
        )";
    }
    
    /**
     * Create users table SQL for SQLite
     * 
     * @return string SQL statement
     */
    public static function createUsersTableSQLite(): string
    {
        return "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            email TEXT NOT NULL,
            created_at TEXT NOT NULL
        )";
    }
    
    /**
     * Get sample user data
     * 
     * @return array Sample user data
     */
    public static function getSampleUsers(): array
    {
        return [
            [
                'username' => 'john_doe',
                'email' => 'john@example.com',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'jane_doe',
                'email' => 'jane@example.com',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'bob_smith',
                'email' => 'bob@example.com',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    /**
     * Create roles table SQL for MySQL
     * 
     * @return string SQL statement
     */
    public static function createRolesTableMySQL(): string
    {
        return "CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            description VARCHAR(255) NULL
        )";
    }
    
    /**
     * Create roles table SQL for SQLite
     * 
     * @return string SQL statement
     */
    public static function createRolesTableSQLite(): string
    {
        return "CREATE TABLE IF NOT EXISTS roles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT NULL
        )";
    }
    
    /**
     * Create user_roles table SQL for MySQL
     * 
     * @return string SQL statement
     */
    public static function createUserRolesTableMySQL(): string
    {
        return "CREATE TABLE IF NOT EXISTS user_roles (
            user_id INT NOT NULL,
            role_id INT NOT NULL,
            PRIMARY KEY (user_id, role_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
        )";
    }
    
    /**
     * Create user_roles table SQL for SQLite
     * 
     * @return string SQL statement
     */
    public static function createUserRolesTableSQLite(): string
    {
        return "CREATE TABLE IF NOT EXISTS user_roles (
            user_id INTEGER NOT NULL,
            role_id INTEGER NOT NULL,
            PRIMARY KEY (user_id, role_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
        )";
    }
    
    /**
     * Get sample role data
     * 
     * @return array Sample role data
     */
    public static function getSampleRoles(): array
    {
        return [
            [
                'name' => 'admin',
                'description' => 'Administrator'
            ],
            [
                'name' => 'editor',
                'description' => 'Content Editor'
            ],
            [
                'name' => 'user',
                'description' => 'Regular User'
            ]
        ];
    }
    
    /**
     * Get sample user-role assignments
     * 
     * @return array Sample user-role assignments
     */
    public static function getSampleUserRoles(): array
    {
        return [
            ['user_id' => 1, 'role_id' => 1], // john_doe is admin
            ['user_id' => 1, 'role_id' => 2], // john_doe is editor
            ['user_id' => 2, 'role_id' => 2], // jane_doe is editor
            ['user_id' => 3, 'role_id' => 3]  // bob_smith is user
        ];
    }
    
    /**
     * Drop tables SQL
     * 
     * @return array SQL statements
     */
    public static function getDropTablesSql(): array
    {
        return [
            "DROP TABLE IF EXISTS user_roles",
            "DROP TABLE IF EXISTS roles",
            "DROP TABLE IF EXISTS users"
        ];
    }
}