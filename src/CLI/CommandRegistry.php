<?php

namespace LogbieCLI;

/**
 * Command Registry
 * 
 * Manages the registration and retrieval of CLI commands.
 * 
 * @package LogbieCLI
 * @since 1.0.0
 */
class CommandRegistry
{
    /**
     * Registered commands
     * 
     * @var array<string, CommandInterface|callable>
     */
    private array $commands = [];
    
    /**
     * Command descriptions
     * 
     * @var array<string, string>
     */
    private array $descriptions = [];
    
    /**
     * The console logger
     * 
     * @var ConsoleLogger
     */
    private ConsoleLogger $logger;
    
    /**
     * Constructor
     * 
     * @param ConsoleLogger $logger The console logger
     */
    public function __construct(ConsoleLogger $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * Register a command
     * 
     * @param string|CommandInterface $command The command class name, instance, or name
     * @param callable|null $callback The command callback (if $command is a string)
     * @param string|null $description The command description (if $command is a string)
     * @return self
     * @throws \InvalidArgumentException If the command is invalid
     */
    public function register(string|CommandInterface $command, ?callable $callback = null, ?string $description = null): self
    {
        // If $command is a class name
        if (is_string($command) && class_exists($command) && is_subclass_of($command, CommandInterface::class)) {
            $instance = new $command($this->logger);
            $name = $instance->getName();
            $this->commands[$name] = $instance;
            $this->descriptions[$name] = $instance->getDescription();
            return $this;
        }
        
        // If $command is a CommandInterface instance
        if ($command instanceof CommandInterface) {
            $name = $command->getName();
            $this->commands[$name] = $command;
            $this->descriptions[$name] = $command->getDescription();
            return $this;
        }
        
        // If $command is a string and $callback is provided
        if (is_string($command) && is_callable($callback)) {
            $this->commands[$command] = $callback;
            $this->descriptions[$command] = $description ?? "Command '$command'";
            return $this;
        }
        
        throw new \InvalidArgumentException(
            "Invalid command. Must be a CommandInterface instance, a class name that implements CommandInterface, " .
            "or a string name with a callable callback."
        );
    }
    
    /**
     * Check if a command is registered
     * 
     * @param string $name The command name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->commands[$name]);
    }
    
    /**
     * Get a command
     * 
     * @param string $name The command name
     * @return CommandInterface|callable|null The command or null if not found
     */
    public function get(string $name): CommandInterface|callable|null
    {
        return $this->commands[$name] ?? null;
    }
    
    /**
     * Get all registered command names
     * 
     * @return array<string>
     */
    public function getNames(): array
    {
        return array_keys($this->commands);
    }
    
    /**
     * Get all registered commands
     * 
     * @return array<string, CommandInterface|callable>
     */
    public function getAll(): array
    {
        return $this->commands;
    }
    
    /**
     * Get a command description
     * 
     * @param string $name The command name
     * @return string|null The command description or null if not found
     */
    public function getDescription(string $name): ?string
    {
        return $this->descriptions[$name] ?? null;
    }
    
    /**
     * Get all command descriptions
     * 
     * @return array<string, string>
     */
    public function getAllDescriptions(): array
    {
        return $this->descriptions;
    }
    
    /**
     * Execute a command
     * 
     * @param string $name The command name
     * @param array $args The command arguments
     * @return int The command exit code
     * @throws \InvalidArgumentException If the command is not found
     */
    public function execute(string $name, array $args = []): int
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException("Command '$name' not found");
        }
        
        $command = $this->get($name);
        
        // If it's a CommandInterface instance
        if ($command instanceof CommandInterface) {
            return $command->execute($args);
        }
        
        // If it's a callable
        if (is_callable($command)) {
            return $command($args, $this->logger) ?? 0;
        }
        
        throw new \InvalidArgumentException("Invalid command type for '$name'");
    }
}