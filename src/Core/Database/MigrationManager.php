<?php

namespace LogbieCore\Database;

use LogbieCore\DatabaseORM;

/**
 * Migration Manager
 * 
 * Manages database migration tracking and execution.
 * Handles the migrations table and migration state management.
 * 
 * @package LogbieCore\Database
 * @since 1.0.0
 */
class MigrationManager
{
    /**
     * The database ORM instance
     * 
     * @var DatabaseORM
     */
    private DatabaseORM $db;
    
    /**
     * The migrations table name
     * 
     * @var string
     */
    private string $migrationsTable = 'migrations';
    
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
     * Ensure the migrations table exists
     * 
     * @return void
     * @throws \RuntimeException If table creation fails
     */
    public function ensureMigrationsTableExists(): void
    {
        if ($this->hasTable($this->migrationsTable)) {
            return;
        }
        
        $this->createMigrationsTable();
    }
    
    /**
     * Get all executed migrations
     * 
     * @return array Array of migration records
     */
    public function getExecutedMigrations(): array
    {
        $this->ensureMigrationsTableExists();
        
        return $this->db->read($this->migrationsTable, [], [
            'order' => 'batch ASC, migration ASC'
        ]);
    }
    
    /**
     * Get migrations from a specific batch
     * 
     * @param int $batch The batch number
     * @return array Array of migration records
     */
    public function getMigrationsByBatch(int $batch): array
    {
        $this->ensureMigrationsTableExists();
        
        return $this->db->query(
            "SELECT * FROM {$this->migrationsTable} WHERE batch = ? ORDER BY migration DESC",
            [$batch]
        );
    }
    
    /**
     * Get the next batch number
     * 
     * @return int The next batch number
     */
    public function getNextBatchNumber(): int
    {
        $this->ensureMigrationsTableExists();
        
        $result = $this->db->query(
            "SELECT MAX(batch) as max_batch FROM {$this->migrationsTable}"
        );
        
        $maxBatch = $result[0]['max_batch'] ?? 0;
        
        return (int)$maxBatch + 1;
    }
    
    /**
     * Get the latest batch number
     * 
     * @return int|null The latest batch number or null if no migrations
     */
    public function getLatestBatchNumber(): ?int
    {
        $this->ensureMigrationsTableExists();
        
        $result = $this->db->query(
            "SELECT MAX(batch) as max_batch FROM {$this->migrationsTable}"
        );
        
        $maxBatch = $result[0]['max_batch'] ?? null;
        
        return $maxBatch ? (int)$maxBatch : null;
    }
    
    /**
     * Record a migration as executed
     * 
     * @param string $migration The migration filename
     * @param int $batch The batch number
     * @return void
     */
    public function recordMigration(string $migration, int $batch): void
    {
        $this->ensureMigrationsTableExists();
        
        $this->db->create($this->migrationsTable, [
            'migration' => $migration,
            'batch' => $batch,
            'executed_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Remove a migration record
     * 
     * @param string $migration The migration filename
     * @return void
     */
    public function removeMigration(string $migration): void
    {
        $this->ensureMigrationsTableExists();
        
        $this->db->delete($this->migrationsTable, ['migration' => $migration]);
    }
    
    /**
     * Check if a migration has been executed
     * 
     * @param string $migration The migration filename
     * @return bool True if executed, false otherwise
     */
    public function hasBeenExecuted(string $migration): bool
    {
        $this->ensureMigrationsTableExists();
        
        $result = $this->db->read($this->migrationsTable, ['migration' => $migration]);
        
        return !empty($result);
    }
    
    /**
     * Get all migration filenames that have been executed
     * 
     * @return array Array of migration filenames
     */
    public function getExecutedMigrationNames(): array
    {
        $migrations = $this->getExecutedMigrations();
        
        return array_column($migrations, 'migration');
    }
    
    /**
     * Check if a table exists in the database
     * 
     * @param string $tableName The table name to check
     * @return bool True if table exists, false otherwise
     */
    public function hasTable(string $tableName): bool
    {
        try {
            $driver = $this->db->getDriver()->getName();
            
            if ($driver === 'mysql') {
                $result = $this->db->query("SHOW TABLES LIKE ?", [$tableName]);
                return !empty($result);
            } elseif ($driver === 'sqlite') {
                $result = $this->db->query(
                    "SELECT name FROM sqlite_master WHERE type='table' AND name=?", 
                    [$tableName]
                );
                return !empty($result);
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Create the migrations table
     * 
     * @return void
     * @throws \RuntimeException If table creation fails
     */
    private function createMigrationsTable(): void
    {
        $driver = $this->db->getDriver()->getName();
        
        if ($driver === 'mysql') {
            $sql = "CREATE TABLE {$this->migrationsTable} (
                migration VARCHAR(255) PRIMARY KEY,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } elseif ($driver === 'sqlite') {
            $sql = "CREATE TABLE {$this->migrationsTable} (
                migration VARCHAR(255) PRIMARY KEY,
                batch INTEGER NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            throw new \RuntimeException("Unsupported database driver: {$driver}");
        }
        
        try {
            $this->db->query($sql);
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Failed to create migrations table: " . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
