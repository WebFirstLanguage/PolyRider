<?php

namespace LogbieCore\Database;

use LogbieCore\DatabaseORM;

/**
 * Schema Builder
 * 
 * Provides a database-agnostic interface for schema operations.
 * Translates fluent schema definitions into driver-specific SQL.
 * 
 * @package LogbieCore\Database
 * @since 1.0.0
 */
class SchemaBuilder
{
    /**
     * The database ORM instance
     * 
     * @var DatabaseORM
     */
    private DatabaseORM $db;
    
    /**
     * Constructor
     * 
     * @param DatabaseORM $db The database ORM instance
     */
    public function __construct(DatabaseORM $db)
    {
        $this->db = $db;
    }
    
    /**
     * Create a new table
     * 
     * @param string $table The table name
     * @param callable $callback The table definition callback
     * @return void
     * @throws \RuntimeException If table creation fails
     */
    public function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table, 'create');
        $callback($blueprint);
        
        $sql = $this->generateCreateTableSql($blueprint);
        
        try {
            $this->db->query($sql);
            
            $this->createIndexes($blueprint);
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Failed to create table '{$table}': " . $e->getMessage(),
                0,
                $e
            );
        }
    }
    
    /**
     * Modify an existing table
     * 
     * @param string $table The table name
     * @param callable $callback The table modification callback
     * @return void
     * @throws \RuntimeException If table modification fails
     */
    public function table(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table, 'alter');
        $callback($blueprint);
        
        try {
            $statements = $this->generateAlterTableSql($blueprint);
            
            foreach ($statements as $sql) {
                $this->db->query($sql);
            }
            
            $this->createIndexes($blueprint);
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Failed to alter table '{$table}': " . $e->getMessage(),
                0,
                $e
            );
        }
    }
    
    /**
     * Drop a table
     * 
     * @param string $table The table name
     * @return void
     * @throws \RuntimeException If table drop fails
     */
    public function drop(string $table): void
    {
        $sql = "DROP TABLE {$table}";
        
        try {
            $this->db->query($sql);
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Failed to drop table '{$table}': " . $e->getMessage(),
                0,
                $e
            );
        }
    }
    
    /**
     * Drop a table if it exists
     * 
     * @param string $table The table name
     * @return void
     * @throws \RuntimeException If table drop fails
     */
    public function dropIfExists(string $table): void
    {
        $driver = $this->db->getDriver()->getName();
        
        if ($driver === 'mysql') {
            $sql = "DROP TABLE IF EXISTS {$table}";
        } elseif ($driver === 'sqlite') {
            $sql = "DROP TABLE IF EXISTS {$table}";
        } else {
            throw new \RuntimeException("Unsupported database driver: {$driver}");
        }
        
        try {
            $this->db->query($sql);
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Failed to drop table '{$table}': " . $e->getMessage(),
                0,
                $e
            );
        }
    }
    
    /**
     * Check if a table exists
     * 
     * @param string $table The table name
     * @return bool True if table exists, false otherwise
     */
    public function hasTable(string $table): bool
    {
        try {
            $this->db->getTableSchema($table);
            return true;
        } catch (\RuntimeException $e) {
            return false;
        }
    }
    
    /**
     * Generate CREATE TABLE SQL from blueprint
     * 
     * @param Blueprint $blueprint The table blueprint
     * @return string The CREATE TABLE SQL
     */
    private function generateCreateTableSql(Blueprint $blueprint): string
    {
        $driver = $this->db->getDriver()->getName();
        $table = $blueprint->getTable();
        $columns = $blueprint->getColumns();
        
        if (empty($columns)) {
            throw new \RuntimeException("Cannot create table '{$table}' with no columns");
        }
        
        $columnDefinitions = [];
        $primaryKeys = [];
        
        foreach ($columns as $column) {
            $columnSql = $this->generateColumnSql($column, $driver);
            $columnDefinitions[] = $columnSql;
            
            if ($column['primary']) {
                $primaryKeys[] = $column['name'];
            }
        }
        
        $sql = "CREATE TABLE {$table} (\n    " . implode(",\n    ", $columnDefinitions);
        
        if (!empty($primaryKeys)) {
            $sql .= ",\n    PRIMARY KEY (" . implode(', ', $primaryKeys) . ")";
        }
        
        $sql .= "\n)";
        
        return $sql;
    }
    
    /**
     * Generate ALTER TABLE SQL from blueprint
     * 
     * @param Blueprint $blueprint The table blueprint
     * @return array Array of SQL statements
     */
    private function generateAlterTableSql(Blueprint $blueprint): array
    {
        $driver = $this->db->getDriver()->getName();
        $table = $blueprint->getTable();
        $statements = [];
        
        foreach ($blueprint->getDropColumns() as $columnName) {
            if ($driver === 'mysql') {
                $statements[] = "ALTER TABLE {$table} DROP COLUMN {$columnName}";
            } elseif ($driver === 'sqlite') {
                throw new \RuntimeException("SQLite does not support dropping columns directly");
            }
        }
        
        foreach ($blueprint->getColumns() as $column) {
            if (isset($column['action']) && $column['action'] === 'add') {
                $columnSql = $this->generateColumnSql($column, $driver);
                $statements[] = "ALTER TABLE {$table} ADD COLUMN {$columnSql}";
            }
        }
        
        return $statements;
    }
    
    /**
     * Generate column SQL definition
     * 
     * @param array $column The column definition
     * @param string $driver The database driver
     * @return string The column SQL
     */
    private function generateColumnSql(array $column, string $driver): string
    {
        $name = $column['name'];
        $type = $this->mapColumnType($column, $driver);
        
        $sql = "{$name} {$type}";
        
        if (!$column['nullable']) {
            $sql .= ' NOT NULL';
        }
        
        if ($column['default'] !== null) {
            if ($column['default'] === 'CURRENT_TIMESTAMP') {
                $sql .= ' DEFAULT CURRENT_TIMESTAMP';
            } elseif (is_string($column['default'])) {
                $sql .= " DEFAULT '" . addslashes($column['default']) . "'";
            } elseif (is_bool($column['default'])) {
                $sql .= ' DEFAULT ' . ($column['default'] ? '1' : '0');
            } else {
                $sql .= ' DEFAULT ' . $column['default'];
            }
        }
        
        if ($column['autoIncrement'] && $driver === 'mysql') {
            $sql .= ' AUTO_INCREMENT';
        }
        
        return $sql;
    }
    
    /**
     * Map column type to database-specific type
     * 
     * @param array $column The column definition
     * @param string $driver The database driver
     * @return string The database-specific type
     */
    private function mapColumnType(array $column, string $driver): string
    {
        $type = $column['type'];
        
        switch ($type) {
            case 'id':
                if ($driver === 'mysql') {
                    return 'BIGINT UNSIGNED AUTO_INCREMENT';
                } elseif ($driver === 'sqlite') {
                    return 'INTEGER PRIMARY KEY AUTOINCREMENT';
                }
                break;
                
            case 'string':
                $length = $column['length'] ?? 255;
                return "VARCHAR({$length})";
                
            case 'text':
                return 'TEXT';
                
            case 'integer':
                if ($driver === 'mysql') {
                    return 'INT';
                } elseif ($driver === 'sqlite') {
                    return 'INTEGER';
                }
                break;
                
            case 'boolean':
                if ($driver === 'mysql') {
                    return 'BOOLEAN';
                } elseif ($driver === 'sqlite') {
                    return 'INTEGER';
                }
                break;
                
            case 'timestamp':
                if ($driver === 'mysql') {
                    return 'TIMESTAMP';
                } elseif ($driver === 'sqlite') {
                    return 'TIMESTAMP';
                }
                break;
        }
        
        throw new \RuntimeException("Unsupported column type '{$type}' for driver '{$driver}'");
    }
    
    /**
     * Create indexes for the table
     * 
     * @param Blueprint $blueprint The table blueprint
     * @return void
     */
    private function createIndexes(Blueprint $blueprint): void
    {
        $table = $blueprint->getTable();
        
        foreach ($blueprint->getColumns() as $column) {
            if ($column['unique'] && !$column['primary']) {
                $indexName = "idx_{$table}_{$column['name']}_unique";
                $sql = "CREATE UNIQUE INDEX {$indexName} ON {$table} ({$column['name']})";
                
                try {
                    $this->db->query($sql);
                } catch (\Exception $e) {
                    throw new \RuntimeException(
                        "Failed to create unique index for column '{$column['name']}': " . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        }
    }
}
