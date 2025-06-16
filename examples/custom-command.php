<?php

/**
 * Example of adding a custom command to the Logbie CLI
 * 
 * This file demonstrates how to add a custom command to the Logbie CLI
 * using both class-based and callback-based approaches.
 */

// Ensure the file is being included, not executed directly
if (!defined('LOGBIE_CLI_LOADED')) {
    echo "This file should be included by the Logbie CLI, not executed directly." . PHP_EOL;
    exit(1);
}

// Example 1: Class-based command
// Create a new command class that extends BaseCommand

use LogbieCLI\BaseCommand;

/**
 * Example Command
 * 
 * A simple example command that demonstrates how to create a custom command.
 */
class ExampleCommand extends BaseCommand
{
    /**
     * Get the command name
     * 
     * @return string The command name
     */
    public function getName(): string
    {
        return 'example';
    }
    
    /**
     * Get the command description
     * 
     * @return string The command description
     */
    public function getDescription(): string
    {
        return 'An example command to demonstrate custom commands';
    }
    
    /**
     * Get the command help text
     * 
     * @return string The command help text
     */
    public function getHelp(): string
    {
        return <<<HELP
Usage: logbie example [options] [name]

An example command to demonstrate how to create custom commands.

Arguments:
  name         The name to greet (default: "World")

Options:
  --uppercase  Display the greeting in uppercase
  --help, -h   Display this help message
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
        
        // Get the name argument or use default
        $name = $remainingArgs[0] ?? 'World';
        
        // Create the greeting
        $greeting = "Hello, $name!";
        
        // Convert to uppercase if requested
        if (isset($options['uppercase'])) {
            $greeting = strtoupper($greeting);
        }
        
        // Display the greeting
        $this->logger->success($greeting);
        
        return 0;
    }
}

// Example 2: Callback-based command
// Register a command using a callback function

// Get the command registry
$registry = $app->getCommandRegistry();

// Register the class-based command
$registry->register(ExampleCommand::class);

// Register a callback-based command
$registry->register('greet', function($args, $logger) {
    // Parse arguments
    $name = $args[0] ?? 'World';
    
    // Display the greeting
    $logger->success("Greetings, $name!");
    
    return 0;
}, 'A simple greeting command');

// Output a message to confirm the commands were registered
$app->getLogger()->debug("Custom commands registered: example, greet");