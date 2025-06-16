<?php

namespace LogbieCore\Database;

/**
 * SQLiteDriver
 * 
 * SQLite implementation of the DatabaseDriverInterface.
 * Handles SQLite-specific operations and configuration.
 * 
 * @package LogbieCore\Database
 * @since 1.0.0
 */
class SQLiteDriver implements DatabaseDriverInterface
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
        // Create database directory if it doesn't exist and it's not an in-memory database
        $dbFile = $config['database'];
        if ($dbFile !== ':memory:' && !str_starts_with($dbFile, 'file::memory:')) {
            $dbDir = dirname($dbFile);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
        }
        
        $options = $config['options'] ?? [];
        
        // Set default PDO options
        $options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
        $options[\PDO::ATTR_DEFAULT_FETCH_MODE] = \PDO::FETCH_ASSOC;
        $options[\PDO::ATTR_EMULATE_PREPARES] = false;
        
        // Create PDO instance
        $pdo = new \PDO(
            $this->buildDsn($config),
            null,
            null,
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
        return 'sqlite:' . $config['database'];
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
     * @param string|null $name Name of the sequence object (not used in SQLite)
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
            // SQLite uses PRAGMA table_info instead of DESCRIBE
            $sql = "PRAGMA table_info({$table})";
            $statement = $pdo->prepare($sql);
            $statement->execute();
            $columns = $statement->fetchAll();
            
            // Transform SQLite schema format to match MySQL format for compatibility
            $schema = [];
            foreach ($columns as $column) {
                $schema[] = [
                    'Field' => $column['name'],
                    'Type' => $column['type'],
                    'Null' => $column['notnull'] ? 'NO' : 'YES',
                    'Key' => $column['pk'] ? 'PRI' : '',
                    'Default' => $column['dflt_value'],
                    'Extra' => $column['pk'] && $column['type'] === 'INTEGER' ? 'auto_increment' : ''
                ];
            }
            
            return $schema;
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
        return 'sqlite';
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
        // Enable foreign keys if requested
        if ($config['foreignKeys'] ?? true) {
            $pdo->exec('PRAGMA foreign_keys = ON;');
        }
        
        // Set journal mode (WAL recommended for performance)
        $journalMode = $config['journalMode'] ?? 'WAL';
        $pdo->exec("PRAGMA journal_mode = {$journalMode};");
        
        // Set synchronous mode
        $synchronous = $config['synchronous'] ?? 'NORMAL';
        $pdo->exec("PRAGMA synchronous = {$synchronous};");
        
        // Set cache size (in pages, default page size is 4KB)
        $cacheSize = $config['cacheSize'] ?? 2000;
        $pdo->exec("PRAGMA cache_size = {$cacheSize};");
        
        // Set temp store location
        $tempStore = $config['tempStore'] ?? 'MEMORY';
        $pdo->exec("PRAGMA temp_store = {$tempStore};");
        
        // Set mmap size if specified
        if (isset($config['mmapSize'])) {
            $pdo->exec("PRAGMA mmap_size = {$config['mmapSize']};");
        }
        
        // Set other SQLite-specific configuration options
        if (isset($config['sqliteConfig']) && is_array($config['sqliteConfig'])) {
            foreach ($config['sqliteConfig'] as $pragma => $value) {
                $pdo->exec("PRAGMA {$pragma} = {$value};");
            }
        }
    }
}