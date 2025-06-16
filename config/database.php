<?php

/**
 * Database Configuration
 * 
 * This file contains the configuration for database connections.
 * It supports both MySQL and SQLite database systems.
 */

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
            'persistent' => env('MYSQL_PERSISTENT', true),
            'buffered' => env('MYSQL_BUFFERED', true),
            'timeout' => env('MYSQL_TIMEOUT', 5),
            'sqlMode' => env('MYSQL_SQL_MODE', 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'),
            'timezone' => env('MYSQL_TIMEZONE', '+00:00'),
            'options' => [
                // PDO options specific to MySQL
            ],
            'mysqlConfig' => [
                // Additional MySQL configuration options
                // Example: 'innodb_buffer_pool_size' => '256M'
            ]
        ],
        
        // SQLite connection
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('SQLITE_DATABASE', storage_path('database/logbie.sqlite')),
            'foreignKeys' => env('SQLITE_FOREIGN_KEYS', true),
            'journalMode' => env('SQLITE_JOURNAL_MODE', 'WAL'),
            'synchronous' => env('SQLITE_SYNCHRONOUS', 'NORMAL'),
            'cacheSize' => env('SQLITE_CACHE_SIZE', 2000),
            'tempStore' => env('SQLITE_TEMP_STORE', 'MEMORY'),
            'mmapSize' => env('SQLITE_MMAP_SIZE', null),
            'options' => [
                // PDO options specific to SQLite
            ],
            'sqliteConfig' => [
                // Additional SQLite PRAGMA settings
                // Example: 'auto_vacuum' => 'INCREMENTAL'
            ]
        ]
    ]
];

/**
 * Helper function to get environment variables with default values
 * 
 * @param string $key Environment variable name
 * @param mixed $default Default value if not found
 * @return mixed The environment variable value or default
 */
function env(string $key, $default = null)
{
    $value = getenv($key);
    
    if ($value === false) {
        return $default;
    }
    
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'null':
        case '(null)':
            return null;
        case 'empty':
        case '(empty)':
            return '';
    }
    
    return $value;
}

/**
 * Helper function to get storage path
 * 
 * @param string $path Path relative to storage directory
 * @return string The absolute path
 */
function storage_path(string $path = ''): string
{
    $basePath = dirname(__DIR__);
    $storagePath = $basePath . DIRECTORY_SEPARATOR . 'storage';
    
    if (empty($path)) {
        return $storagePath;
    }
    
    return $storagePath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
}