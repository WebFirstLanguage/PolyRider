<?php

namespace LogbieCore\Database;

/**
 * MySQLDriver
 * 
 * MySQL/MariaDB implementation of the DatabaseDriverInterface.
 * Handles MySQL-specific operations and configuration.
 * 
 * @package LogbieCore\Database
 * @since 1.0.0
 */
class MySQLDriver implements DatabaseDriverInterface
{
    /**
     * Connect to the database
     * 
     * @param array $config Database configuration
     * @return \PDO The PDO connection instance
     * @throws \PDOException If connection fails
     */
    public function connect(array $config): \PDO
    {
        $options = $config['options'] ?? [];
        
        // Set default PDO options
        $options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
        $options[\PDO::ATTR_DEFAULT_FETCH_MODE] = \PDO::FETCH_ASSOC;
        $options[\PDO::ATTR_EMULATE_PREPARES] = false;
        
        // Set persistent connections for better performance if configured
        $options[\PDO::ATTR_PERSISTENT] = $config['persistent'] ?? true;
        
        // Use buffered queries by default
        $options[\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = $config['buffered'] ?? true;
        
        // Set connection timeout
        $options[\PDO::ATTR_TIMEOUT] = $config['timeout'] ?? 5;
        
        // Create PDO instance
        $pdo = new \PDO(
            $this->buildDsn($config),
            $config['username'] ?? '',
            $config['password'] ?? '',
            $options
        );
        
        // Configure connection
        $this->configureConnection($pdo, $config);
        
        return $pdo;
    }
    
    /**
     * Build a DSN string for the database connection
     * 
     * @param array $config Database configuration
     * @return string The DSN string
     */
    public function buildDsn(array $config): string
    {
        return sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'] ?? 'localhost',
            $config['port'] ?? '3306',
            $config['database'],
            $config['charset'] ?? 'utf8mb4'
        );
    }
    
    /**
     * Prepare a statement
     * 
     * @param \PDO $pdo PDO instance
     * @param string $sql SQL query
     * @return \PDOStatement The prepared statement
     * @throws \PDOException If preparation fails
     */
    public function prepare(\PDO $pdo, string $sql): \PDOStatement
    {
        return $pdo->prepare($sql);
    }
    
    /**
     * Get the last inserted ID
     * 
     * @param \PDO $pdo PDO instance
     * @param string|null $name Name of the sequence object (not used in MySQL)
     * @return string The last inserted ID
     */
    public function lastInsertId(\PDO $pdo, ?string $name = null): string
    {
        return $pdo->lastInsertId();
    }
    
    /**
     * Begin a transaction
     * 
     * @param \PDO $pdo PDO instance
     * @return bool True on success
     * @throws \PDOException If transaction start fails
     */
    public function beginTransaction(\PDO $pdo): bool
    {
        return $pdo->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @param \PDO $pdo PDO instance
     * @return bool True on success
     * @throws \PDOException If commit fails
     */
    public function commit(\PDO $pdo): bool
    {
        return $pdo->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @param \PDO $pdo PDO instance
     * @return bool True on success
     * @throws \PDOException If rollback fails
     */
    public function rollback(\PDO $pdo): bool
    {
        return $pdo->rollBack();
    }
    
    /**
     * Get table schema information
     * 
     * @param \PDO $pdo PDO instance
     * @param string $table Table name
     * @return array The table schema
     * @throws \RuntimeException If schema retrieval fails
     */
    public function getTableSchema(\PDO $pdo, string $table): array
    {
        try {
            $sql = "DESCRIBE {$table}";
            $statement = $pdo->prepare($sql);
            $statement->execute();
            
            return $statement->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Failed to get table schema: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Get the name of the driver
     * 
     * @return string The driver name
     */
    public function getName(): string
    {
        return 'mysql';
    }
    
    /**
     * Configure database-specific settings after connection
     * 
     * @param \PDO $pdo PDO instance
     * @param array $config Database configuration
     * @return void
     */
    public function configureConnection(\PDO $pdo, array $config): void
    {
        // Set SQL mode if specified
        if (isset($config['sqlMode'])) {
            $pdo->exec("SET SESSION sql_mode = '{$config['sqlMode']}'");
        }
        
        // Set timezone if specified
        if (isset($config['timezone'])) {
            $pdo->exec("SET time_zone = '{$config['timezone']}'");
        }
        
        // Set other MySQL-specific configuration options
        if (isset($config['mysqlConfig']) && is_array($config['mysqlConfig'])) {
            foreach ($config['mysqlConfig'] as $key => $value) {
                $pdo->exec("SET {$key} = {$value}");
            }
        }
    }
}