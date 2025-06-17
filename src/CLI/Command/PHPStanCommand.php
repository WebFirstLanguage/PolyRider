<?php

namespace LogbieCLI\Command;

use LogbieCLI\BaseCommand;

/**
 * PHPStan Command
 * 
 * Runs PHPStan static analysis on the codebase.
 * 
 * @package LogbieCLI\Command
 * @since 1.0.0
 */
class PHPStanCommand extends BaseCommand
{
    /**
     * Get the command name
     * 
     * @return string The command name
     */
    public function getName(): string
    {
        return 'phpstan';
    }
    
    /**
     * Get the command description
     * 
     * @return string The command description
     */
    public function getDescription(): string
    {
        return 'Run PHPStan static analysis on the codebase';
    }
    
    /**
     * Get the command help text
     * 
     * @return string The command help text
     */
    public function getHelp(): string
    {
        return <<<HELP
Usage: logbie phpstan [options]

Run PHPStan static analysis on the codebase to identify potential issues.

Options:
  --level=N       Set the PHPStan analysis level (0-9, default: from phpstan.neon)
  --memory=VALUE  Set the memory limit (e.g., 256M, 1G)
  --config=FILE   Specify a custom PHPStan configuration file
  --fix           Attempt to fix issues automatically (when possible)
  --help, -h      Display this help message
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
        [$options, $remainingArgs] = $this->parseOptions($args);
        
        // Show help if requested
        if (isset($options['help']) || isset($options['h'])) {
            $this->logger->info($this->getHelp());
            return 0;
        }
        
        // Check if PHPStan is installed
        if (!$this->isPHPStanInstalled()) {
            $this->logger->error("PHPStan is not installed. Please run 'composer require --dev phpstan/phpstan'");
            return 1;
        }
        
        // Build the PHPStan command
        $command = $this->buildPHPStanCommand($options);
        
        // Run PHPStan
        $this->logger->info("Running PHPStan...");
        $exitCode = $this->executeShellCommand($command);
        
        if ($exitCode === 0) {
            $this->logger->success("PHPStan analysis completed successfully with no errors!");
        } else {
            $this->logger->error("PHPStan analysis completed with errors. Exit code: $exitCode");
        }
        
        return $exitCode;
    }
    
    /**
     * Check if PHPStan is installed
     * 
     * @return bool True if PHPStan is installed, false otherwise
     */
    private function isPHPStanInstalled(): bool
    {
        $ds = DIRECTORY_SEPARATOR;
        $phpstanPath = $this->getProjectRoot() . "{$ds}vendor{$ds}bin{$ds}phpstan";
        $phpstanBatPath = $this->getProjectRoot() . "{$ds}vendor{$ds}bin{$ds}phpstan.bat";
        
        return file_exists($phpstanPath) || file_exists($phpstanBatPath);
    }
    
    /**
     * Build the PHPStan command
     * 
     * @param array $options Command options
     * @return string The PHPStan command
     */
    private function buildPHPStanCommand(array $options): string
    {
        // Use the correct path separator for the current OS
        $ds = DIRECTORY_SEPARATOR;
        $phpstanPath = "vendor{$ds}bin{$ds}phpstan";
        
        // On Windows, check if we need to use the .bat file
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if (file_exists($this->getProjectRoot() . "{$ds}vendor{$ds}bin{$ds}phpstan.bat")) {
                $phpstanPath = "vendor{$ds}bin{$ds}phpstan.bat";
            }
        }
        
        $command = "$phpstanPath analyse";
        
        // Add configuration file
        if (isset($options['config'])) {
            $configPath = $options['config'];
            $command .= " -c $configPath";
        } else {
            // Use default config if it exists
            $ds = DIRECTORY_SEPARATOR;
            $defaultConfig = $this->getProjectRoot() . "{$ds}phpstan.neon";
            if (file_exists($defaultConfig)) {
                $command .= " -c phpstan.neon";
            }
        }
        
        // Add analysis level
        if (isset($options['level'])) {
            $level = (int) $options['level'];
            $command .= " --level=$level";
        }
        
        // Add memory limit
        if (isset($options['memory'])) {
            $memory = $options['memory'];
            $command .= " --memory-limit=$memory";
        } else {
            // Default to 256M to avoid memory issues
            $command .= " --memory-limit=256M";
        }
        
        // Add paths to analyze (default to src directory)
        $command .= " src";
        
        return $command;
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