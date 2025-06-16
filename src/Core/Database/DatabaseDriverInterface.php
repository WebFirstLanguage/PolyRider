<?php

namespace LogbieCore\Database;

/**
 * DatabaseDriverInterface
 * 
 * Defines the contract for database drivers in the Logbie Framework.
 * This interface abstracts database-specific operations to allow
 * for multiple database backends while maintaining a consistent API.
 * 
 * @package LogbieCore\Database
 * @since 1.0.0
 */
interface DatabaseDriverInterface
{
    /**
     * Connect to the database
     * 
     * @param array $config Database configuration
     * @return \PDO The PDO connection instance
     * @throws \PDOException If connection fails
     */
    public function connect(array $config): \PDO;
    
    /**
     * Build a DSN string for the database connection
     * 
     * @param array $config Database configuration
     * @return string The DSN string
     */
    public function buildDsn(array $config): string;
    
    /**
     * Prepare a statement
     * 
     * @param \PDO $pdo PDO instance
     * @param string $sql SQL query
     * @return \PDOStatement The prepared statement
     * @throws \PDOException If preparation fails
     */
    public function prepare(\PDO $pdo, string $sql): \PDOStatement;
    
    /**
     * Get the last inserted ID
     * 
     * @param \PDO $pdo PDO instance
     * @param string|null $name Name of the sequence object (if applicable)
     * @return string The last inserted ID
     */
    public function lastInsertId(\PDO $pdo, ?string $name = null): string;
    
    /**
     * Begin a transaction
     * 
     * @param \PDO $pdo PDO instance
     * @return bool True on success
     * @throws \PDOException If transaction start fails
     */
    public function beginTransaction(\PDO $pdo): bool;
    
    /**
     * Commit a transaction
     * 
     * @param \PDO $pdo PDO instance
     * @return bool True on success
     * @throws \PDOException If commit fails
     */
    public function commit(\PDO $pdo): bool;
    
    /**
     * Rollback a transaction
     * 
     * @param \PDO $pdo PDO instance
     * @return bool True on success
     * @throws \PDOException If rollback fails
     */
    public function rollback(\PDO $pdo): bool;
    
    /**
     * Get table schema information
     * 
     * @param \PDO $pdo PDO instance
     * @param string $table Table name
     * @return array The table schema
     * @throws \RuntimeException If schema retrieval fails
     */
    public function getTableSchema(\PDO $pdo, string $table): array;
    
    /**
     * Get the name of the driver
     * 
     * @return string The driver name
     */
    public function getName(): string;
    
    /**
     * Configure database-specific settings after connection
     * 
     * @param \PDO $pdo PDO instance
     * @param array $config Database configuration
     * @return void
     */
    public function configureConnection(\PDO $pdo, array $config): void;
}