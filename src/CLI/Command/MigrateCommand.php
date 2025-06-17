<?php

namespace LogbieCLI\Command;

use LogbieCLI\BaseCommand;
use LogbieCore\DatabaseORM;
use LogbieCore\Database\MigrationManager;

/**
 * Migrate Command
 * 
 * Executes all pending migrations that have not yet been run.
 * 
 * @package LogbieCLI\Command
 * @since 1.0.0
 */
class MigrateCommand extends BaseCommand
{
    /**
     * Get the command name
     * 
     * @return string
     */
    public function getName(): string
    {
        return 'migrate';
    }
    
    /**
     * Get the command description
     * 
     * @return string
     */
    public function getDescription(): string
    {
        return 'Run pending database migrations';
    }
    
    /**
     * Get the command help text
     * 
     * @return string
     */
    public function getHelp(): string
    {
        return <<<HELP
Usage: migrate

Executes all pending migrations that have not yet been run.
Migrations are run in chronological order based on their timestamps.

All migrations in a single run are grouped together in a "batch"
for rollback purposes.

Examples:
  migrate

This command will:
1. Check for pending migrations in database/migrations/
2. Execute each pending migration's up() method
3. Record the migration as executed in the migrations table
4. Group all migrations from this run in the same batch number
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
            $db = $this->getService('db');
            $migrationManager = new MigrationManager($db);
            
            $pendingMigrations = $this->getPendingMigrations($migrationManager);
            
            if (empty($pendingMigrations)) {
                $this->logger->info('No pending migrations found');
                return 0;
            }
            
            $this->logger->info('Found ' . count($pendingMigrations) . ' pending migration(s)');
            
            $batchNumber = $migrationManager->getNextBatchNumber();
            $executed = 0;
            
            foreach ($pendingMigrations as $migrationFile) {
                try {
                    $this->logger->info("Migrating: {$migrationFile}");
                    
                    $db->beginTransaction();
                    
                    $this->executeMigration($migrationFile, $db);
                    
                    $migrationManager->recordMigration($migrationFile, $batchNumber);
                    
                    $db->commit();
                    
                    $this->logger->success("Migrated: {$migrationFile}");
                    $executed++;
                } catch (\Exception $e) {
                    $db->rollback();
                    $this->logger->error("Failed to migrate {$migrationFile}: " . $e->getMessage());
                    
                    if ($executed > 0) {
                        $this->logger->info("Successfully executed {$executed} migration(s) before failure");
                    }
                    
                    return 1;
                }
            }
            
            $this->logger->success("Successfully executed {$executed} migration(s)");
            
            return 0;
        } catch (\Exception $e) {
            $this->logger->error("Migration failed: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Get pending migrations
     * 
     * @param MigrationManager $migrationManager The migration manager
     * @return array Array of pending migration filenames
     */
    private function getPendingMigrations(MigrationManager $migrationManager): array
    {
        $migrationsDirectory = $this->getMigrationsDirectory();
        
        if (!is_dir($migrationsDirectory)) {
            return [];
        }
        
        $allMigrations = [];
        $files = scandir($migrationsDirectory);
        
        foreach ($files as $file) {
            if (preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_.*\.php$/', $file)) {
                $allMigrations[] = $file;
            }
        }
        
        sort($allMigrations);
        
        $executedMigrations = $migrationManager->getExecutedMigrationNames();
        
        return array_diff($allMigrations, $executedMigrations);
    }
    
    /**
     * Execute a single migration
     * 
     * @param string $migrationFile The migration filename
     * @param DatabaseORM $db The database ORM
     * @return void
     * @throws \Exception If migration execution fails
     */
    private function executeMigration(string $migrationFile, DatabaseORM $db): void
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
        
        if (!method_exists($migration, 'up')) {
            throw new \RuntimeException("Migration {$className} does not have an up() method");
        }
        
        $migration->up();
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
}
