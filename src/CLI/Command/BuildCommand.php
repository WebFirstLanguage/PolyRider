<?php

namespace LogbieCLI\Command;

use LogbieCLI\BaseCommand;

/**
 * Build Command
 * 
 * Builds the application by running composer install, creating necessary directories,
 * and compiling frontend assets if present.
 * 
 * @package LogbieCLI\Command
 * @since 1.0.0
 */
class BuildCommand extends BaseCommand
{
    /**
     * Get the command name
     * 
     * @return string The command name
     */
    public function getName(): string
    {
        return 'build';
    }
    
    /**
     * Get the command description
     * 
     * @return string The command description
     */
    public function getDescription(): string
    {
        return 'Build the application';
    }
    
    /**
     * Get the command help text
     * 
     * @return string The command help text
     */
    public function getHelp(): string
    {
        return <<<HELP
Usage: logbie build [options]

Build the application by running composer install, creating necessary directories,
and compiling frontend assets if present.

Options:
  --no-composer     Skip running composer install
  --no-assets       Skip compiling frontend assets
  --dev             Build in development mode
  --prod            Build in production mode (default)
  --help, -h        Display this help message
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
        // Parse options
        [$options, $remainingArgs] = $this->parseOptions($args, [
            'no-composer' => false,
            'no-assets' => false,
            'dev' => false,
            'prod' => true,
        ]);
        
        $this->logger->info("Building Logbie application...");
        
        // Create necessary directories
        if (!$this->createDirectories()) {
            $this->logger->error("Failed to create necessary directories");
            return 1;
        }
        
        // Run composer install
        if (!$options['no-composer']) {
            if (!$this->runComposerInstall($options['dev'])) {
                $this->logger->error("Failed to run composer install");
                return 1;
            }
        } else {
            $this->logger->info("Skipping composer install");
        }
        
        // Compile frontend assets
        if (!$options['no-assets']) {
            if (!$this->compileFrontendAssets($options['dev'])) {
                $this->logger->warning("Failed to compile frontend assets");
                // Don't return error code for asset compilation failure
            }
        } else {
            $this->logger->info("Skipping frontend asset compilation");
        }
        
        $this->logger->success("Build completed successfully");
        return 0;
    }
    
    /**
     * Create necessary directories
     * 
     * @return bool True if successful, false otherwise
     */
    private function createDirectories(): bool
    {
        $this->logger->info("Creating necessary directories...");
        
        $directories = [
            'storage/logs',
            'storage/cache',
            'public/assets',
        ];
        
        $success = true;
        
        foreach ($directories as $dir) {
            $fullPath = $this->getProjectRoot() . '/' . $dir;
            
            if (!$this->ensureDirectoryExists($fullPath)) {
                $this->logger->error("Failed to create directory: $fullPath");
                $success = false;
            } else {
                $this->logger->debug("Directory created or already exists: $fullPath");
            }
        }
        
        if ($success) {
            $this->logger->success("Directories created successfully");
        }
        
        return $success;
    }
    
    /**
     * Run composer install
     * 
     * @param bool $dev Whether to install dev dependencies
     * @return bool True if successful, false otherwise
     */
    private function runComposerInstall(bool $dev): bool
    {
        $this->logger->info("Running composer install...");
        
        $command = 'composer install --no-interaction';
        
        if (!$dev) {
            $command .= ' --no-dev --optimize-autoloader';
        }
        
        $exitCode = $this->executeShellCommand($command);
        
        if ($exitCode === 0) {
            $this->logger->success("Composer install completed successfully");
            return true;
        } else {
            $this->logger->error("Composer install failed with exit code: $exitCode");
            return false;
        }
    }
    
    /**
     * Compile frontend assets
     * 
     * @param bool $dev Whether to compile in development mode
     * @return bool True if successful, false otherwise
     */
    private function compileFrontendAssets(bool $dev): bool
    {
        $this->logger->info("Checking for frontend assets...");
        
        $packageJsonPath = $this->getProjectRoot() . '/package.json';
        
        // Check if package.json exists
        if (!file_exists($packageJsonPath)) {
            $this->logger->info("No package.json found, skipping frontend asset compilation");
            return true;
        }
        
        $this->logger->info("Compiling frontend assets...");
        
        // Check for npm or yarn
        $packageManager = $this->detectPackageManager();
        
        if ($packageManager === null) {
            $this->logger->warning("No package manager (npm or yarn) found, skipping frontend asset compilation");
            return false;
        }
        
        // Install dependencies
        $installCommand = $packageManager === 'yarn' ? 'yarn install' : 'npm install';
        $exitCode = $this->executeShellCommand($installCommand);
        
        if ($exitCode !== 0) {
            $this->logger->error("Failed to install frontend dependencies");
            return false;
        }
        
        // Run build script
        $buildScript = $dev ? 'dev' : 'build';
        $buildCommand = $packageManager === 'yarn' ? "yarn $buildScript" : "npm run $buildScript";
        $exitCode = $this->executeShellCommand($buildCommand);
        
        if ($exitCode === 0) {
            $this->logger->success("Frontend assets compiled successfully");
            return true;
        } else {
            $this->logger->error("Frontend asset compilation failed with exit code: $exitCode");
            return false;
        }
    }
    
    /**
     * Detect the package manager (npm or yarn)
     * 
     * @return string|null The package manager or null if not found
     */
    private function detectPackageManager(): ?string
    {
        // Check for yarn.lock
        if (file_exists($this->getProjectRoot() . '/yarn.lock')) {
            // Check if yarn is installed
            $exitCode = $this->executeShellCommand('yarn --version 2>/dev/null', false);
            if ($exitCode === 0) {
                return 'yarn';
            }
        }
        
        // Check if npm is installed
        $exitCode = $this->executeShellCommand('npm --version 2>/dev/null', false);
        if ($exitCode === 0) {
            return 'npm';
        }
        
        return null;
    }
    
    /**
     * Get the project root directory
     * 
     * @return string The project root directory
     */
    private function getProjectRoot(): string
    {
        return dirname(__DIR__, 3);
    }
}