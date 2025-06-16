<?php

namespace LogbieCore\Database;

/**
 * DatabaseDriverFactory
 * 
 * Factory class for creating database driver instances based on configuration.
 * 
 * @package LogbieCore\Database
 * @since 1.0.0
 */
class DatabaseDriverFactory
{
    /**
     * Available database drivers
     * 
     * @var array<string, string>
     */
    private static array $drivers = [
        'mysql' => MySQLDriver::class,
        'sqlite' => SQLiteDriver::class
    ];
    
    /**
     * Create a database driver instance
     * 
     * @param string $driver Driver name
     * @return DatabaseDriverInterface The database driver instance
     * @throws \InvalidArgumentException If the driver is not supported
     */
    public static function create(string $driver): DatabaseDriverInterface
    {
        $driver = strtolower($driver);
        
        if (!isset(self::$drivers[$driver])) {
            throw new \InvalidArgumentException(
                "Unsupported database driver: {$driver}. " .
                "Supported drivers: " . implode(', ', array_keys(self::$drivers))
            );
        }
        
        $driverClass = self::$drivers[$driver];
        return new $driverClass();
    }
    
    /**
     * Register a custom database driver
     * 
     * @param string $name Driver name
     * @param string $class Driver class name (must implement DatabaseDriverInterface)
     * @return void
     * @throws \InvalidArgumentException If the driver class does not implement DatabaseDriverInterface
     */
    public static function registerDriver(string $name, string $class): void
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Driver class does not exist: {$class}");
        }
        
        $interfaces = class_implements($class);
        if (!isset($interfaces[DatabaseDriverInterface::class])) {
            throw new \InvalidArgumentException(
                "Driver class must implement DatabaseDriverInterface: {$class}"
            );
        }
        
        self::$drivers[strtolower($name)] = $class;
    }
    
    /**
     * Get a list of supported driver names
     * 
     * @return array List of supported driver names
     */
    public static function getSupportedDrivers(): array
    {
        return array_keys(self::$drivers);
    }
}