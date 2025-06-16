<?php

namespace LogbieCLI;

use LogbieCore\Container;
use LogbieCore\Logger;

/**
 * Base Command
 * 
 * Abstract base class for CLI commands.
 * 
 * @package LogbieCLI
 * @since 1.0.0
 */
abstract class BaseCommand implements CommandInterface
{
    /**
     * The console logger
     * 
     * @var ConsoleLogger
     */
    protected ConsoleLogger $logger;
    
    /**
     * The dependency injection container
     * 
     * @var Container|null
     */
    protected ?Container $container;
    
    /**
     * Constructor
     * 
     * @param ConsoleLogger $logger The console logger
     * @param Container|null $container The dependency injection container (optional)
     */
    public function __construct(ConsoleLogger $logger, ?Container $container = null)
    {
        $this->logger = $logger;
        $this->container = $container;
    }
    
    /**
     * Get the command name
     * 
     * @return string The command name
     */
    abstract public function getName(): string;
    
    /**
     * Get the command description
     * 
     * @return string The command description
     */
    abstract public function getDescription(): string;
    
    /**
     * Get the command help text
     * 
     * @return string The command help text
     */
    public function getHelp(): string
    {
        return $this->getDescription();
    }
    
    /**
     * Execute the command
     * 
     * @param array $args Command arguments
     * @return int Exit code (0 for success, non-zero for failure)
     */
    abstract public function execute(array $args = []): int;
    
    /**
     * Parse command options
     * 
     * @param array $args Command arguments
     * @param array $defaultOptions Default option values
     * @return array [options, remaining_args]
     */
    protected function parseOptions(array $args, array $defaultOptions = []): array
    {
        $options = $defaultOptions;
        $remainingArgs = [];
        
        foreach ($args as $arg) {
            // Check if it's a long option (--option or --option=value)
            if (strpos($arg, '--') === 0) {
                $option = substr($arg, 2);
                $value = true;
                
                // Check if it has a value (--option=value)
                if (strpos($option, '=') !== false) {
                    [$option, $value] = explode('=', $option, 2);
                }
                
                $options[$option] = $value;
            }
            // Check if it's a short option (-o or -o value)
            elseif (strpos($arg, '-') === 0 && strlen($arg) > 1) {
                $option = substr($arg, 1);
                $options[$option] = true;
            }
            // It's a regular argument
            else {
                $remainingArgs[] = $arg;
            }
        }
        
        return [$options, $remainingArgs];
    }
    
    /**
     * Execute a shell command
     * 
     * @param string $command The command to execute
     * @param bool $showOutput Whether to show the command output
     * @return int The command exit code
     */
    protected function executeShellCommand(string $command, bool $showOutput = true): int
    {
        $this->logger->debug("Executing: $command");
        
        if ($showOutput) {
            passthru($command, $exitCode);
        } else {
            exec($command, $output, $exitCode);
        }
        
        return $exitCode;
    }
    
    /**
     * Check if a directory exists and create it if it doesn't
     * 
     * @param string $dir The directory path
     * @param int $permissions The directory permissions (octal)
     * @return bool True if the directory exists or was created, false otherwise
     */
    protected function ensureDirectoryExists(string $dir, int $permissions = 0755): bool
    {
        if (is_dir($dir)) {
            return true;
        }
        
        $this->logger->info("Creating directory: $dir");
        
        return mkdir($dir, $permissions, true);
    }
    
    /**
     * Check if the framework container is available
     * 
     * @return bool
     */
    protected function hasContainer(): bool
    {
        return $this->container !== null;
    }
    
    /**
     * Get a service from the container
     * 
     * @param string $id The service ID
     * @return mixed The service
     * @throws \RuntimeException If the container is not available
     */
    protected function getService(string $id): mixed
    {
        if (!$this->hasContainer()) {
            throw new \RuntimeException("Container is not available");
        }
        
        return $this->container->get($id);
    }
    
    /**
     * Get the framework logger if available
     * 
     * @return Logger|null The framework logger or null if not available
     */
    protected function getFrameworkLogger(): ?Logger
    {
        if (!$this->hasContainer() || !$this->container->has('logger')) {
            return null;
        }
        
        return $this->container->get('logger');
    }
}