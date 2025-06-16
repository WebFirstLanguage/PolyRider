<?php

namespace LogbieCLI;

use LogbieCore\Container;

/**
 * CLI Application
 * 
 * Main entry point for the CLI application.
 * 
 * @package LogbieCLI
 * @since 1.0.0
 */
class Application
{
    /**
     * The command registry
     * 
     * @var CommandRegistry
     */
    private CommandRegistry $registry;
    
    /**
     * The console logger
     * 
     * @var ConsoleLogger
     */
    private ConsoleLogger $logger;
    
    /**
     * The dependency injection container
     * 
     * @var Container|null
     */
    private ?Container $container;
    
    /**
     * The application name
     * 
     * @var string
     */
    private string $name;
    
    /**
     * The application version
     * 
     * @var string
     */
    private string $version;
    
    /**
     * Constructor
     * 
     * @param string $name The application name
     * @param string $version The application version
     * @param Container|null $container The dependency injection container (optional)
     */
    public function __construct(string $name = 'Logbie CLI', string $version = '1.0.0', ?Container $container = null)
    {
        $this->name = $name;
        $this->version = $version;
        $this->container = $container;
        
        // Create the console logger
        $this->logger = new ConsoleLogger();
        
        // Create the command registry
        $this->registry = new CommandRegistry($this->logger);
        
        // Register built-in commands
        $this->registerBuiltInCommands();
    }
    
    /**
     * Register built-in commands
     * 
     * @return void
     */
    private function registerBuiltInCommands(): void
    {
        // Register the help command
        $this->registry->register(Command\HelpCommand::class);
        
        // Register the build command
        $this->registry->register(Command\BuildCommand::class);
        
        // Register the clean command
        $this->registry->register(Command\CleanCommand::class);
    }
    
    /**
     * Run the application
     * 
     * @param array $args Command line arguments
     * @return int Exit code
     */
    public function run(array $args = []): int
    {
        // If no arguments provided, use the global $argv
        if (empty($args) && isset($_SERVER['argv'])) {
            $args = $_SERVER['argv'];
            // Remove the script name
            array_shift($args);
        }
        
        try {
            // Display help if no command specified
            if (empty($args)) {
                return $this->registry->execute('help');
            }
            
            // Get the command name
            $commandName = array_shift($args);
            
            // Check for --help or -h option
            if (in_array('--help', $args) || in_array('-h', $args)) {
                return $this->registry->execute('help', [$commandName]);
            }
            
            // Check for --version or -v option
            if ($commandName === '--version' || $commandName === '-v') {
                $this->displayVersion();
                return 0;
            }
            
            // Execute the command
            if ($this->registry->has($commandName)) {
                return $this->registry->execute($commandName, $args);
            }
            
            // Command not found
            $this->logger->error("Command '$commandName' not found.");
            $this->logger->info("Run 'logbie help' to see available commands.");
            return 1;
        } catch (\Exception $e) {
            $this->logger->error("Error: " . $e->getMessage());
            
            if ($this->logger->getVerbosity() >= ConsoleLogger::VERBOSITY_DEBUG) {
                $this->logger->error($e->getTraceAsString());
            }
            
            return 1;
        }
    }
    
    /**
     * Display the application version
     * 
     * @return void
     */
    private function displayVersion(): void
    {
        $this->logger->info("{$this->name} version {$this->version}");
    }
    
    /**
     * Get the command registry
     * 
     * @return CommandRegistry
     */
    public function getCommandRegistry(): CommandRegistry
    {
        return $this->registry;
    }
    
    /**
     * Get the console logger
     * 
     * @return ConsoleLogger
     */
    public function getLogger(): ConsoleLogger
    {
        return $this->logger;
    }
    
    /**
     * Get the dependency injection container
     * 
     * @return Container|null
     */
    public function getContainer(): ?Container
    {
        return $this->container;
    }
    
    /**
     * Set the dependency injection container
     * 
     * @param Container $container The dependency injection container
     * @return self
     */
    public function setContainer(Container $container): self
    {
        $this->container = $container;
        return $this;
    }
    
    /**
     * Get the application name
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Get the application version
     * 
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}