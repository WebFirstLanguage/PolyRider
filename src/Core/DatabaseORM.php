<?php

namespace LogbieCore;

use LogbieCore\Database\DatabaseDriverInterface;
use LogbieCore\Database\DatabaseDriverFactory;

/**
 * DatabaseORM Class
 * 
 * A secure and efficient database abstraction layer for the Logbie Framework.
 * Provides prepared statement caching, SQL injection protection, transaction support,
 * and relationship handling. Supports multiple database backends through a driver system.
 * 
 * @package LogbieCore
 * @since 1.0.0
 */
class DatabaseORM
{
    /**
     * PDO instance
     * 
     * @var \PDO
     */
    private \PDO $pdo;
    
    /**
     * Database driver
     * 
     * @var DatabaseDriverInterface
     */
    private DatabaseDriverInterface $driver;
    
    /**
     * Prepared statement cache
     * 
     * @var array<string, \PDOStatement>
     */
    private array $statementCache = [];
    
    /**
     * Schema information cache
     * 
     * @var array<string, array>
     */
    private array $schemaCache = [];
    
    /**
     * Transaction nesting level
     * 
     * @var int
     */
    private int $transactionLevel = 0;
    
    /**
     * Constructor
     * 
     * @param array $config Database configuration
     * @throws \PDOException If connection fails
     */
    public function __construct(array $config)
    {
        // Get the driver name from config or use default
        $driverName = $config['driver'] ?? 'mysql';
        
        // Create the driver instance
        $this->driver = DatabaseDriverFactory::create($driverName);
        
        // Connect to the database
        try {
            $this->pdo = $this->driver->connect($config);
        } catch (\PDOException $e) {
            throw new \PDOException('Database connection failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * Alternative constructor using an existing driver instance
     * 
     * @param DatabaseDriverInterface $driver The database driver
     * @param array $config Database configuration
     * @return DatabaseORM The DatabaseORM instance
     * @throws \PDOException If connection fails
     */
    public static function withDriver(DatabaseDriverInterface $driver, array $config): self
    {
        // Create instance without calling constructor to avoid real database connections
        $instance = new class extends DatabaseORM {
            public function __construct() {
                // Empty constructor to bypass parent constructor
            }
        };
        
        $instance->driver = $driver;
        
        try {
            $instance->pdo = $driver->connect($config);
        } catch (\PDOException $e) {
            throw new \PDOException('Database connection failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
        
        return $instance;
    }
    
    /**
     * Create a new record
     * 
     * @param string $table Table name
     * @param array $data Data to insert
     * @return int|string The ID of the inserted record
     * @throws \RuntimeException If the insert fails
     */
    public function create(string $table, array $data): int|string
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Data cannot be empty');
        }
        
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        try {
            $statement = $this->prepare($sql);
            $statement->execute(array_values($data));
            
            return (int) $this->driver->lastInsertId($this->pdo);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Create operation failed: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Read records from a table
     * 
     * @param string $table Table name
     * @param array $conditions Conditions for the WHERE clause
     * @param array $columns Columns to select
     * @param array $options Additional options (orderBy, orderDirection, limit, offset)
     * @return array The matching records
     * @throws \RuntimeException If the read fails
     */
    public function read(
        string $table,
        array $conditions = [],
        array $columns = ['*'],
        array $options = []
    ): array {
        $sql = sprintf(
            'SELECT %s FROM %s',
            implode(', ', $columns),
            $table
        );
        
        $params = [];
        
        // Add WHERE clause if conditions are provided
        if (!empty($conditions)) {
            $whereConditions = [];
            
            foreach ($conditions as $column => $value) {
                $whereConditions[] = "{$column} = ?";
                $params[] = $value;
            }
            
            $sql .= ' WHERE ' . implode(' AND ', $whereConditions);
        }
        
        // Add ORDER BY clause if specified
        if (isset($options['orderBy'])) {
            $direction = $options['orderDirection'] ?? 'ASC';
            $sql .= " ORDER BY {$options['orderBy']} {$direction}";
        }
        
        // Add LIMIT clause if specified
        if (isset($options['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int) $options['limit'];
            
            // Add OFFSET clause if specified
            if (isset($options['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = (int) $options['offset'];
            }
        }
        
        try {
            $statement = $this->prepare($sql);
            $statement->execute($params);
            
            return $statement->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Read operation failed: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Update records in a table
     * 
     * @param string $table Table name
     * @param array $data Data to update
     * @param array $conditions Conditions for the WHERE clause
     * @return int The number of affected rows
     * @throws \RuntimeException If the update fails
     */
    public function update(string $table, array $data, array $conditions): int
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Data cannot be empty');
        }
        
        if (empty($conditions)) {
            throw new \InvalidArgumentException('Conditions cannot be empty for update operations');
        }
        
        $setStatements = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $setStatements[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $whereConditions = [];
        
        foreach ($conditions as $column => $value) {
            $whereConditions[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $setStatements),
            implode(' AND ', $whereConditions)
        );
        
        try {
            $statement = $this->prepare($sql);
            $statement->execute($params);
            
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Update operation failed: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Delete records from a table
     * 
     * @param string $table Table name
     * @param array $conditions Conditions for the WHERE clause
     * @return int The number of affected rows
     * @throws \RuntimeException If the delete fails
     * @throws \InvalidArgumentException If conditions are empty
     */
    public function delete(string $table, array $conditions): int
    {
        if (empty($conditions)) {
            throw new \InvalidArgumentException('Conditions cannot be empty for delete operations');
        }
        
        $whereConditions = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $whereConditions[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $table,
            implode(' AND ', $whereConditions)
        );
        
        try {
            $statement = $this->prepare($sql);
            $statement->execute($params);
            
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Delete operation failed: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Execute a custom query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|int The query results or affected row count
     * @throws \RuntimeException If the query fails
     */
    public function query(string $sql, array $params = []): array|int
    {
        try {
            $statement = $this->prepare($sql);
            $statement->execute($params);
            
            // If the query is a SELECT, return the results
            if (stripos(trim($sql), 'SELECT') === 0) {
                return $statement->fetchAll();
            }
            
            // Otherwise, return the number of affected rows
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Query execution failed: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Get many-to-many relationship data
     * 
     * @param string $sourceTable Source table name
     * @param string $targetTable Target table name
     * @param string $pivotTable Pivot table name
     * @param array $conditions Conditions for the WHERE clause
     * @return array The related records
     * @throws \RuntimeException If the query fails
     */
    public function getManyToMany(
        string $sourceTable,
        string $targetTable,
        string $pivotTable,
        array $conditions
    ): array {
        $sourceId = rtrim($sourceTable, 's') . '_id';
        $targetId = rtrim($targetTable, 's') . '_id';
        
        $whereConditions = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $whereConditions[] = "p.{$column} = ?";
            $params[] = $value;
        }
        
        $sql = sprintf(
            'SELECT t.* FROM %s t
            JOIN %s p ON t.id = p.%s
            WHERE %s',
            $targetTable,
            $pivotTable,
            $targetId,
            implode(' AND ', $whereConditions)
        );
        
        try {
            $statement = $this->prepare($sql);
            $statement->execute($params);
            
            return $statement->fetchAll();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Many-to-many query failed: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Get table schema information
     * 
     * @param string $table Table name
     * @return array The table schema
     * @throws \RuntimeException If the schema query fails
     */
    public function getTableSchema(string $table): array
    {
        // Return cached schema if available
        if (isset($this->schemaCache[$table])) {
            return $this->schemaCache[$table];
        }
        
        try {
            $schema = $this->driver->getTableSchema($this->pdo, $table);
            
            // Cache the schema
            $this->schemaCache[$table] = $schema;
            
            return $schema;
        } catch (\PDOException $e) {
            throw new \RuntimeException('Failed to get table schema: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool True on success
     * @throws \RuntimeException If the transaction fails to start
     */
    public function beginTransaction(): bool
    {
        try {
            // If this is the first transaction level, start a PDO transaction
            if ($this->transactionLevel === 0) {
                $this->driver->beginTransaction($this->pdo);
            }
            
            $this->transactionLevel++;
            
            return true;
        } catch (\PDOException $e) {
            throw new \RuntimeException('Failed to begin transaction: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool True on success
     * @throws \RuntimeException If the transaction fails to commit
     */
    public function commit(): bool
    {
        if ($this->transactionLevel === 0) {
            throw new \RuntimeException('No active transaction to commit');
        }
        
        try {
            $this->transactionLevel--;
            
            // Only commit if this is the outermost transaction
            if ($this->transactionLevel === 0) {
                return $this->driver->commit($this->pdo);
            }
            
            return true;
        } catch (\PDOException $e) {
            throw new \RuntimeException('Failed to commit transaction: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool True on success
     * @throws \RuntimeException If the transaction fails to rollback
     */
    public function rollback(): bool
    {
        if ($this->transactionLevel === 0) {
            throw new \RuntimeException('No active transaction to rollback');
        }
        
        try {
            $this->transactionLevel = 0;
            return $this->driver->rollback($this->pdo);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Failed to rollback transaction: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Prepare a statement with caching
     * 
     * @param string $sql SQL query
     * @return \PDOStatement The prepared statement
     * @throws \PDOException If the statement preparation fails
     */
    private function prepare(string $sql): \PDOStatement
    {
        // Return cached statement if available
        if (isset($this->statementCache[$sql])) {
            return $this->statementCache[$sql];
        }
        
        // Prepare and cache the statement
        $statement = $this->driver->prepare($this->pdo, $sql);
        $this->statementCache[$sql] = $statement;
        
        return $statement;
    }
    
    /**
     * Clear the statement cache
     * 
     * @return void
     */
    public function clearStatementCache(): void
    {
        $this->statementCache = [];
    }
    
    /**
     * Clear the schema cache
     * 
     * @return void
     */
    public function clearSchemaCache(): void
    {
        $this->schemaCache = [];
    }
    
    /**
     * Get the PDO instance
     * 
     * @return \PDO The PDO instance
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }
    
    /**
     * Get the database driver
     * 
     * @return DatabaseDriverInterface The database driver
     */
    public function getDriver(): DatabaseDriverInterface
    {
        return $this->driver;
    }
    
    /**
     * Execute a batch operation within a transaction
     * 
     * @param callable $operations Callback function that performs the operations
     * @return mixed The result of the callback
     * @throws \Exception If an error occurs during the operations
     */
    public function batchOperation(callable $operations)
    {
        $this->beginTransaction();
        
        try {
            // Execute the batch operations
            $result = $operations($this);
            
            $this->commit();
            
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Batch insert multiple rows
     * 
     * @param string $table Table name
     * @param array $columns Column names
     * @param array $rows Array of row data
     * @return int Number of inserted rows
     * @throws \RuntimeException If the batch insert fails
     */
    public function batchCreate(string $table, array $columns, array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }
        
        $placeholders = [];
        $values = [];
        
        foreach ($rows as $row) {
            $rowPlaceholders = [];
            
            foreach ($columns as $column) {
                $rowPlaceholders[] = '?';
                $values[] = $row[$column] ?? null;
            }
            
            $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES %s',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        try {
            $statement = $this->prepare($sql);
            $statement->execute($values);
            
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw new \RuntimeException('Batch create operation failed: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Optimize the database (driver-specific)
     * 
     * @return void
     */
    public function optimize(): void
    {
        // SQLite-specific optimization
        if ($this->driver->getName() === 'sqlite') {
            $this->query('ANALYZE;');
            $this->query('VACUUM;');
        }
        // MySQL-specific optimization
        else if ($this->driver->getName() === 'mysql') {
            // Get all tables in the database
            $tables = $this->getTables();
            foreach ($tables as $table) {
                $this->query('OPTIMIZE TABLE ?', [$table]);
            }
        }
    }
    
    /**
     * Get all tables in the current database
     *
     * @return array List of table names
     * @throws \RuntimeException If the operation fails
     */
    public function getTables(): array
    {
        try {
            // Different query based on database driver
            if ($this->driver->getName() === 'mysql') {
                $sql = "SHOW TABLES";
                $statement = $this->prepare($sql);
                $statement->execute();
                $tables = [];
                
                while ($row = $statement->fetch(\PDO::FETCH_NUM)) {
                    $tables[] = $row[0];
                }
                
                return $tables;
            } else if ($this->driver->getName() === 'sqlite') {
                $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'";
                $statement = $this->prepare($sql);
                $statement->execute();
                $tables = [];
                
                while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                    $tables[] = $row['name'];
                }
                
                return $tables;
            }
            
            // Default empty array for unsupported drivers
            return [];
        } catch (\PDOException $e) {
            throw new \RuntimeException('Failed to get tables: ' . $e->getMessage(), 0, $e);
        }
    }
}
