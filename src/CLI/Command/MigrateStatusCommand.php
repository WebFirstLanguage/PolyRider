<?php

namespace LogbieCLI\Command;

use LogbieCLI\BaseCommand;
use LogbieCore\Database\MigrationManager;

/**
 * Migrate Status Command
 * 
 * Shows a list of all migration files, indicating whether 
 * each one has been run ("up") or is pending ("down").
 * 
 * @package LogbieCLI\Command
 * @since 1.0.0
 */
class MigrateStatusCommand extends BaseCommand
{
    /**
     * Get the command name
     * 
     * @return string
     */
    public function getName(): string
    {
        return 'migrate:status';
    }
    
    /**
     * Get the command description
     * 
     * @return string
     */
    public function getDescription(): string
    {
        return 'Show the status of all migrations';
    }
    
    /**
     * Get the command help text
     * 
     * @return string
     */
    public function getHelp(): string
    {
        return <<<HELP
Usage: migrate:status

Shows a list of all migration files, indicating whether each one 
has been run ("up") or is pending ("down").

The output includes:
- Migration filename
- Status (up/down)
- Batch number (for executed migrations)
- Execution timestamp (for executed migrations)

Examples:
  migrate:status

Sample output:
+--------------------------------------+--------+-------+---------------------+
| Migration                            | Status | Batch | Executed At         |
+--------------------------------------+--------+-------+---------------------+
| 2024_01_15_120000_create_users_table | up     | 1     | 2024-01-15 12:00:00 |
| 2024_01_15_130000_add_email_column   | down   | -     | -                   |
+--------------------------------------+--------+-------+---------------------+
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
            
            $allMigrations = $this->getAllMigrations();
            $executedMigrations = $this->getExecutedMigrationsMap($migrationManager);
            
            if (empty($allMigrations)) {
                $this->logger->info('No migration files found');
                return 0;
            }
            
            $this->displayMigrationStatus($allMigrations, $executedMigrations);
            
            $pendingCount = count($allMigrations) - count($executedMigrations);
            $executedCount = count($executedMigrations);
            
            $this->logger->info('');
            $this->logger->info("Total migrations: " . count($allMigrations));
            $this->logger->info("Executed: {$executedCount}");
            $this->logger->info("Pending: {$pendingCount}");
            
            return 0;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get migration status: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Get all migration files
     * 
     * @return array Array of migration filenames
     */
    private function getAllMigrations(): array
    {
        $migrationsDirectory = $this->getMigrationsDirectory();
        
        if (!is_dir($migrationsDirectory)) {
            return [];
        }
        
        $migrations = [];
        $files = scandir($migrationsDirectory);
        
        foreach ($files as $file) {
            if (preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_.*\.php$/', $file)) {
                $migrations[] = $file;
            }
        }
        
        sort($migrations);
        
        return $migrations;
    }
    
    /**
     * Get executed migrations as a map
     * 
     * @param MigrationManager $migrationManager The migration manager
     * @return array Map of migration filename to migration record
     */
    private function getExecutedMigrationsMap(MigrationManager $migrationManager): array
    {
        $executedMigrations = $migrationManager->getExecutedMigrations();
        $map = [];
        
        foreach ($executedMigrations as $migration) {
            $map[$migration['migration']] = $migration;
        }
        
        return $map;
    }
    
    /**
     * Display migration status table
     * 
     * @param array $allMigrations All migration files
     * @param array $executedMigrations Map of executed migrations
     * @return void
     */
    private function displayMigrationStatus(array $allMigrations, array $executedMigrations): void
    {
        $maxMigrationLength = max(array_map('strlen', $allMigrations));
        $migrationColumnWidth = max($maxMigrationLength, 20);
        
        $separator = '+' . str_repeat('-', $migrationColumnWidth + 2) . '+--------+-------+---------------------+';
        
        $this->logger->info($separator);
        $this->logger->info(sprintf(
            '| %-' . $migrationColumnWidth . 's | %-6s | %-5s | %-19s |',
            'Migration',
            'Status',
            'Batch',
            'Executed At'
        ));
        $this->logger->info($separator);
        
        foreach ($allMigrations as $migration) {
            if (isset($executedMigrations[$migration])) {
                $record = $executedMigrations[$migration];
                $status = 'up';
                $batch = $record['batch'];
                $executedAt = $record['executed_at'];
            } else {
                $status = 'down';
                $batch = '-';
                $executedAt = '-';
            }
            
            $this->logger->info(sprintf(
                '| %-' . $migrationColumnWidth . 's | %-6s | %-5s | %-19s |',
                $migration,
                $status,
                $batch,
                $executedAt
            ));
        }
        
        $this->logger->info($separator);
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
