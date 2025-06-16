<?php

/**
 * Logbie Framework Entry Point
 * 
 * This file serves as the entry point for the Logbie Framework.
 * It bootstraps the application and handles the request.
 */

// Define the base path
$basePath = dirname(__DIR__);

// Autoload dependencies
require_once $basePath . '/vendor/autoload.php';

// Load environment variables if .env file exists
if (file_exists($basePath . '/.env')) {
    $dotenv = new \Dotenv\Dotenv($basePath);
    $dotenv->load();
}

// Create the application
$app = new \LogbieCore\Application($basePath, [
    'debug' => getenv('APP_DEBUG') === 'true',
    'environment' => getenv('APP_ENV') ?: 'production',
    'database' => [
        'driver' => getenv('DB_DRIVER') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_NAME') ?: 'logbie',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'charset' => getenv('DB_CHARSET') ?: 'utf8mb4'
    ]
]);

// Bootstrap the application
$app->bootstrap();

// Run the application
$app->run();