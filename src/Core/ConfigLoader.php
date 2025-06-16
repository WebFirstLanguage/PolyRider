<?php

namespace LogbieCore;

/**
 * ConfigLoader
 * 
 * Loads configuration files and provides access to configuration values.
 * 
 * @package LogbieCore
 * @since 1.0.0
 */
class ConfigLoader
{
    /**
     * Configuration data
     * 
     * @var array
     */
    private array $config = [];
    
    /**
     * Base path for configuration files
     * 
     * @var string
     */
    private string $basePath;
    
    /**
     * Constructor
     * 
     * @param string $basePath Base path for configuration files
     */
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\');
    }
    
    /**
     * Load a configuration file
     * 
     * @param string $name Configuration file name (without extension)
     * @return array The configuration data
     * @throws \RuntimeException If the configuration file cannot be loaded
     */
    public function load(string $name): array
    {
        $filePath = $this->basePath . DIRECTORY_SEPARATOR . $name . '.php';
        
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Configuration file not found: {$name}.php");
        }
        
        $config = require $filePath;
        
        if (!is_array($config)) {
            throw new \RuntimeException("Configuration file must return an array: {$name}.php");
        }
        
        $this->config[$name] = $config;
        
        return $config;
    }
    
    /**
     * Get a configuration value
     * 
     * @param string $key Configuration key in dot notation (e.g., 'database.connections.mysql')
     * @param mixed $default Default value if the key is not found
     * @return mixed The configuration value
     */
    public function get(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $configName = array_shift($parts);
        
        // Load the configuration file if not already loaded
        if (!isset($this->config[$configName])) {
            try {
                $this->load($configName);
            } catch (\RuntimeException $e) {
                return $default;
            }
        }
        
        $value = $this->config[$configName];
        
        // Traverse the configuration array
        foreach ($parts as $part) {
            if (!is_array($value) || !isset($value[$part])) {
                return $default;
            }
            
            $value = $value[$part];
        }
        
        return $value;
    }
    
    /**
     * Get the default database configuration
     * 
     * @return array The default database configuration
     */
    public function getDatabaseConfig(): array
    {
        $defaultConnection = $this->get('database.default', 'mysql');
        return $this->get("database.connections.{$defaultConnection}", []);
    }
    
    /**
     * Get a specific database connection configuration
     * 
     * @param string $connection Connection name
     * @return array The database connection configuration
     */
    public function getDatabaseConnectionConfig(string $connection): array
    {
        return $this->get("database.connections.{$connection}", []);
    }
    
    /**
     * Check if a configuration key exists
     * 
     * @param string $key Configuration key in dot notation
     * @return bool True if the key exists
     */
    public function has(string $key): bool
    {
        return $this->get($key, $this) !== $this;
    }
    
    /**
     * Set a configuration value
     * 
     * @param string $key Configuration key in dot notation
     * @param mixed $value Configuration value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $parts = explode('.', $key);
        $configName = array_shift($parts);
        
        // Load the configuration file if not already loaded
        if (!isset($this->config[$configName])) {
            try {
                $this->load($configName);
            } catch (\RuntimeException $e) {
                $this->config[$configName] = [];
            }
        }
        
        $reference = &$this->config[$configName];
        
        // Traverse the configuration array
        foreach ($parts as $i => $part) {
            // If this is the last part, set the value
            if ($i === count($parts) - 1) {
                $reference[$part] = $value;
                break;
            }
            
            // Create the array if it doesn't exist
            if (!isset($reference[$part]) || !is_array($reference[$part])) {
                $reference[$part] = [];
            }
            
            $reference = &$reference[$part];
        }
    }
}