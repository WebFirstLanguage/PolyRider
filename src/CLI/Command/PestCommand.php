<?php

namespace LogbieCLI\Command;

use LogbieCLI\BaseCommand;

/**
 * Pest Command
 * 
 * Runs Pest tests for the Logbie Framework.
 * 
 * @package LogbieCLI\Command
 * @since 1.0.0
 */
class PestCommand extends BaseCommand
{
    /**
     * Get the command name
     * 
     * @return string The command name
     */
    public function getName(): string
    {
        return 'pest';
    }
    
    /**
     * Get the command description
     * 
     * @return string The command description
     */
    public function getDescription(): string
    {
        return 'Run Pest tests for the codebase';
    }
    
    /**
     * Get the command help text
     * 
     * @return string The command help text
     */
    public function getHelp(): string
    {
        return <<<HELP
Usage: logbie pest [options] [test-path]

Run Pest tests for the Logbie Framework.

Arguments:
  test-path       Path to specific test file or directory (optional)

Options:
  --filter=NAME   Filter which tests to run based on name pattern
  --group=NAME    Only run tests from the specified group(s)
  --coverage      Generate code coverage report
  --parallel      Run tests in parallel
  --stop-on-fail  Stop execution upon first test failure
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
        
        // Check if Pest is installed
        if (!$this->isPestInstalled()) {
            $this->logger->error("Pest is not installed. Please run 'composer require --dev pestphp/pest'");
            return 1;
        }
        
        // Build the Pest command
        $command = $this->buildPestCommand($options, $remainingArgs);
        
        // Run Pest
        $this->logger->info("Running Pest tests...");
        $exitCode = $this->executeShellCommand($command);
        
        if ($exitCode === 0) {
            $this->logger->success("All tests passed successfully!");
        } else {
            $this->logger->error("Tests completed with failures. Exit code: $exitCode");
        }
        
        return $exitCode;
    }
    
    /**
     * Check if Pest is installed
     * 
     * @return bool True if Pest is installed, false otherwise
     */
    private function isPestInstalled(): bool
    {
        $ds = DIRECTORY_SEPARATOR;
        $pestPath = $this->getProjectRoot() . "{$ds}vendor{$ds}bin{$ds}pest";
        $pestBatPath = $this->getProjectRoot() . "{$ds}vendor{$ds}bin{$ds}pest.bat";
        $composerJson = $this->getProjectRoot() . "{$ds}composer.json";
        
        // Check if the pest executable exists
        $executableExists = file_exists($pestPath) || file_exists($pestBatPath);
        
        // If the executable doesn't exist, check if it's in composer.json
        if (!$executableExists && file_exists($composerJson)) {
            $composerContent = file_get_contents($composerJson);
            if ($composerContent !== false) {
                $composerData = json_decode($composerContent, true);
                if (is_array($composerData)) {
                    // Check if pest is in require or require-dev
                    $requireDev = $composerData['require-dev'] ?? [];
                    $require = $composerData['require'] ?? [];
                    
                    foreach (array_merge($require, $requireDev) as $package => $version) {
                        if (strpos($package, 'pest') !== false) {
                            $this->logger->warning("Pest is in composer.json but not installed. Run 'composer install' first.");
                            return false;
                        }
                    }
                }
            }
        }
        
        return $executableExists;
    }
    
    /**
     * Build the Pest command
     * 
     * @param array $options Command options
     * @param array $remainingArgs Remaining command arguments
     * @return string The Pest command
     */
    private function buildPestCommand(array $options, array $remainingArgs): string
    {
        // Use the correct path separator for the current OS
        $ds = DIRECTORY_SEPARATOR;
        
        // Determine the PHP executable
        $phpExecutable = PHP_BINARY;
        
        // Get the pest executable path
        $pestPath = "vendor{$ds}bin{$ds}pest";
        
        // On Windows, check if we need to use the .bat file
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if (file_exists($this->getProjectRoot() . "{$ds}vendor{$ds}bin{$ds}pest.bat")) {
                $pestPath = "vendor{$ds}bin{$ds}pest.bat";
            }
        }
        
        // Build the command with PHP executable for non-Windows systems
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $command = "{$phpExecutable} {$pestPath}";
        } else {
            $command = $pestPath;
        }
        
        // Add filter option
        if (isset($options['filter'])) {
            $filter = $options['filter'];
            $command .= " --filter=\"$filter\"";
        }
        
        // Add group option
        if (isset($options['group'])) {
            $group = $options['group'];
            $command .= " --group=\"$group\"";
        }
        
        // Add coverage option
        if (isset($options['coverage'])) {
            $command .= " --coverage";
        }
        
        // Add parallel option
        if (isset($options['parallel'])) {
            $command .= " --parallel";
        }
        
        // Add stop-on-fail option
        if (isset($options['stop-on-fail'])) {
            $command .= " --stop-on-failure";
        }
        
        // Add test path if provided
        if (!empty($remainingArgs)) {
            $testPath = $remainingArgs[0];
            $command .= " $testPath";
        } else {
            // Default to the tests directory if it exists
            $testsDir = $this->getProjectRoot() . "{$ds}tests";
            if (is_dir($testsDir)) {
                $command .= " tests";
            }
        }
        
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