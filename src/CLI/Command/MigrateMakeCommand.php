<?php

namespace LogbieCLI\Command;

use LogbieCLI\BaseCommand;

/**
 * Migrate Make Command
 * 
 * Creates a new migration file with a timestamped filename.
 * 
 * @package LogbieCLI\Command
 * @since 1.0.0
 */
class MigrateMakeCommand extends BaseCommand
{
    /**
     * Get the command name
     * 
     * @return string
     */
    public function getName(): string
    {
        return 'migrate:make';
    }
    
    /**
     * Get the command description
     * 
     * @return string
     */
    public function getDescription(): string
    {
        return 'Create a new migration file';
    }
    
    /**
     * Get the command help text
     * 
     * @return string
     */
    public function getHelp(): string
    {
        return <<<HELP
Usage: migrate:make <MigrationName>

Creates a new migration file in the database/migrations directory.
The filename will be timestamped to ensure chronological order.

Arguments:
  MigrationName    The name of the migration (e.g., CreateUsersTable)

Examples:
  migrate:make CreateUsersTable
  migrate:make AddEmailToUsersTable
  migrate:make DropOldTable

The generated file will contain a class that extends the Migration base class
with empty up() and down() methods for you to implement.
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
        if (empty($args)) {
            $this->logger->error('Migration name is required');
            $this->logger->info('Usage: migrate:make <MigrationName>');
            return 1;
        }
        
        $migrationName = $args[0];
        
        if (!$this->isValidMigrationName($migrationName)) {
            $this->logger->error('Invalid migration name. Use PascalCase (e.g., CreateUsersTable)');
            return 1;
        }
        
        try {
            $filename = $this->generateMigrationFilename($migrationName);
            $filepath = $this->getMigrationsDirectory() . '/' . $filename;
            
            if (file_exists($filepath)) {
                $this->logger->error("Migration file already exists: {$filename}");
                return 1;
            }
            
            $this->ensureMigrationsDirectoryExists();
            
            $content = $this->generateMigrationContent($migrationName);
            
            if (file_put_contents($filepath, $content) === false) {
                $this->logger->error("Failed to create migration file: {$filepath}");
                return 1;
            }
            
            $this->logger->success("Created migration: {$filename}");
            $this->logger->info("Location: {$filepath}");
            
            return 0;
        } catch (\Exception $e) {
            $this->logger->error("Failed to create migration: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Validate migration name
     * 
     * @param string $name The migration name
     * @return bool True if valid, false otherwise
     */
    private function isValidMigrationName(string $name): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name) === 1;
    }
    
    /**
     * Generate timestamped migration filename
     * 
     * @param string $name The migration name
     * @return string The filename
     */
    private function generateMigrationFilename(string $name): string
    {
        $timestamp = date('Y_m_d_His');
        $snakeCaseName = $this->convertToSnakeCase($name);
        
        return "{$timestamp}_{$snakeCaseName}.php";
    }
    
    /**
     * Convert PascalCase to snake_case
     * 
     * @param string $name The PascalCase name
     * @return string The snake_case name
     */
    private function convertToSnakeCase(string $name): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
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
     * Ensure the migrations directory exists
     * 
     * @return void
     * @throws \RuntimeException If directory creation fails
     */
    private function ensureMigrationsDirectoryExists(): void
    {
        $directory = $this->getMigrationsDirectory();
        
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new \RuntimeException("Failed to create migrations directory: {$directory}");
            }
        }
    }
    
    /**
     * Generate migration file content
     * 
     * @param string $name The migration name
     * @return string The file content
     */
    private function generateMigrationContent(string $name): string
    {
        return <<<PHP
<?php

use LogbieCore\Database\Migration;

/**
 * {$name} Migration
 * 
 * @package Database\Migrations
 */
class {$name} extends Migration
{
    /**
     * Run the migration
     * 
     * @return void
     */
    public function up(): void
    {
    }
    
    /**
     * Reverse the migration
     * 
     * @return void
     */
    public function down(): void
    {
    }
}
PHP;
    }
}
