<?php

namespace LogbieCLI\Command;

use LogbieCLI\BaseCommand;
use LogbieCore\DatabaseORM;
use LogbieCore\Database\MigrationManager;

/**
 * Migrate Rollback Command
 * 
 * Reverts the last "batch" of migrations that were run.
 * 
 * @package LogbieCLI\Command
 * @since 1.0.0
 */
class MigrateRollbackCommand extends BaseCommand
{
    /**
     * Get the command name
     * 
     * @return string
     */
    public function getName(): string
    {
        return 'migrate:rollback';
    }
    
    /**
     * Get the command description
     * 
     * @return string
     */
    public function getDescription(): string
    {
        return 'Rollback the last batch of migrations';
    }
    
    /**
     * Get the command help text
     * 
     * @return string
     */
    public function getHelp(): string
    {
        return <<<HELP
Usage: migrate:rollback

Reverts the last "batch" of migrations that were run.
A batch is considered all migrations that were run during 
the last migrate command execution.

The migrations are rolled back in reverse order (newest first).

Examples:
  migrate:rollback

This command will:
1. Find the latest batch of migrations
2. Execute each migration's down() method in reverse order
3. Remove the migration records from the migrations table

Note: If any migration in the batch fails to rollback,
the entire rollback operation will be aborted.
HELP;
    }
    
    /**
     * Execute the command
     * 
     * @param array $args Command arguments
     * @return int Exit code (0 for success, non-zero for failure)
     */
    public function execute(array $args = []): int
    {
        try {
            $db = $this->getDatabaseConnection();
            $migrationManager = new MigrationManager($db);
            
            $latestBatch = $migrationManager->getLatestBatchNumber();
            
            if ($latestBatch === null) {
                $this->logger->info('No migrations to rollback');
                return 0;
            }
            
            $migrationsToRollback = $migrationManager->getMigrationsByBatch($latestBatch);
            
            if (empty($migrationsToRollback)) {
                $this->logger->info('No migrations found in the latest batch');
                return 0;
            }
            
            $this->logger->info('Rolling back ' . count($migrationsToRollback) . ' migration(s) from batch ' . $latestBatch);
            
            $rolledBack = 0;
            
            foreach ($migrationsToRollback as $migrationRecord) {
                $migrationFile = $migrationRecord['migration'];
                
                try {
                    $this->logger->info("Rolling back: {$migrationFile}");
                    
                    $db->beginTransaction();
                    
                    $this->rollbackMigration($migrationFile, $db);
                    
                    $migrationManager->removeMigration($migrationFile);
                    
                    $db->commit();
                    
                    $this->logger->success("Rolled back: {$migrationFile}");
                    $rolledBack++;
                } catch (\Exception $e) {
                    $db->rollback();
                    $this->logger->error("Failed to rollback {$migrationFile}: " . $e->getMessage());
                    
                    if ($rolledBack > 0) {
                        $this->logger->info("Successfully rolled back {$rolledBack} migration(s) before failure");
                    }
                    
                    return 1;
                }
            }
            
            $this->logger->success("Successfully rolled back {$rolledBack} migration(s)");
            
            return 0;
        } catch (\Exception $e) {
            $this->logger->error("Rollback failed: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Rollback a single migration
     * 
     * @param string $migrationFile The migration filename
     * @param DatabaseORM $db The database ORM
     * @return void
     * @throws \Exception If migration rollback fails
     */
    private function rollbackMigration(string $migrationFile, DatabaseORM $db): void
    {
        $migrationPath = $this->getMigrationsDirectory() . '/' . $migrationFile;
        
        if (!file_exists($migrationPath)) {
            throw new \RuntimeException("Migration file not found: {$migrationPath}");
        }
        
        require_once $migrationPath;
        
        $className = $this->extractClassNameFromFile($migrationFile);
        
        if (!class_exists($className)) {
            throw new \RuntimeException("Migration class not found: {$className}");
        }
        
        $migration = new $className($db);
        
        if (!method_exists($migration, 'down')) {
            throw new \RuntimeException("Migration {$className} does not have a down() method");
        }
        
        $migration->down();
    }
    
    /**
     * Extract class name from migration filename
     * 
     * @param string $filename The migration filename
     * @return string The class name
     */
    private function extractClassNameFromFile(string $filename): string
    {
        $parts = explode('_', pathinfo($filename, PATHINFO_FILENAME));
        
        if (count($parts) < 5) {
            throw new \RuntimeException("Invalid migration filename format: {$filename}");
        }
        
        $nameParts = array_slice($parts, 4);
        $className = '';
        
        foreach ($nameParts as $part) {
            $className .= ucfirst($part);
        }
        
        return $className;
    }
    
    /**
     * Get the migrations directory path
     * 
     * @return string The directory path
     */
    private function getMigrationsDirectory(): string
    {
        return getcwd() . '/database/migrations';
    }
    
    /**
     * Get database connection
     * 
     * @return \LogbieCore\DatabaseORM
     * @throws \RuntimeException If database connection fails
     */
    private function getDatabaseConnection(): \LogbieCore\DatabaseORM
    {
        if ($this->hasContainer()) {
            try {
                return $this->getService('db');
            } catch (\Exception $e) {
            }
        }
        
        $configPath = getcwd() . '/config/database.php';
        if (!file_exists($configPath)) {
            throw new \RuntimeException("Database configuration file not found at: {$configPath}");
        }
        
        $config = require $configPath;
        $defaultConnection = $config['default'] ?? 'sqlite';
        $connectionConfig = $config['connections'][$defaultConnection] ?? null;
        
        if (!$connectionConfig) {
            throw new \RuntimeException("Database connection '{$defaultConnection}' not configured");
        }
        
        $driverFactory = new \LogbieCore\Database\DatabaseDriverFactory();
        $driver = $driverFactory->create($connectionConfig['driver'], $connectionConfig);
        
        return \LogbieCore\DatabaseORM::withDriver($driver, $connectionConfig);
    }
}
