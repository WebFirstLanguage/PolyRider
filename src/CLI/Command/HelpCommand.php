<?php

namespace LogbieCLI\Command;

use LogbieCLI\BaseCommand;
use LogbieCLI\CommandRegistry;

/**
 * Help Command
 * 
 * Displays help information for available commands.
 * 
 * @package LogbieCLI\Command
 * @since 1.0.0
 */
class HelpCommand extends BaseCommand
{
    /**
     * Get the command name
     * 
     * @return string The command name
     */
    public function getName(): string
    {
        return 'help';
    }
    
    /**
     * Get the command description
     * 
     * @return string The command description
     */
    public function getDescription(): string
    {
        return 'Display help information for available commands';
    }
    
    /**
     * Get the command help text
     * 
     * @return string The command help text
     */
    public function getHelp(): string
    {
        return <<<HELP
Usage: logbie help [command]

Display help information for available commands.

Arguments:
  command    The command to display help for

Options:
  --help, -h     Display this help message
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
        // Get the command registry
        $registry = $this->getCommandRegistry();
        
        // If a command name is provided, display help for that command
        if (!empty($args) && $registry->has($args[0])) {
            $commandName = $args[0];
            $command = $registry->get($commandName);
            
            if ($command instanceof BaseCommand) {
                $this->logger->info($command->getHelp());
            } else {
                $this->logger->info("Command: $commandName");
                $this->logger->info($registry->getDescription($commandName) ?? 'No description available');
            }
            
            return 0;
        }
        
        // Otherwise, display general help
        $this->displayGeneralHelp($registry);
        
        return 0;
    }
    
    /**
     * Display general help information
     * 
     * @param CommandRegistry $registry The command registry
     * @return void
     */
    private function displayGeneralHelp(CommandRegistry $registry): void
    {
        $this->logger->info("Logbie CLI - A command-line tool for the Logbie Framework");
        $this->logger->info("");
        $this->logger->info("Usage: logbie [command] [options] [arguments]");
        $this->logger->info("");
        $this->logger->info("Available commands:");
        
        // Get all command descriptions
        $descriptions = $registry->getAllDescriptions();
        
        // Calculate the maximum command name length for proper alignment
        $maxLength = 0;
        foreach (array_keys($descriptions) as $name) {
            $maxLength = max($maxLength, strlen($name));
        }
        
        // Sort commands alphabetically
        ksort($descriptions);
        
        // Display each command with its description
        foreach ($descriptions as $name => $description) {
            $padding = str_repeat(' ', $maxLength - strlen($name) + 2);
            $this->logger->info("  $name$padding$description");
        }
        
        $this->logger->info("");
        $this->logger->info("For more information about a specific command, run:");
        $this->logger->info("  logbie help [command]");
        $this->logger->info("  logbie [command] --help");
    }
    
    /**
     * Get the command registry
     *
     * @return CommandRegistry
     */
    private function getCommandRegistry(): CommandRegistry
    {
        // If we're running from the Application class, we can get the registry from there
        global $app;
        
        if (isset($app) && method_exists($app, 'getCommandRegistry')) {
            return $app->getCommandRegistry();
        }
        
        // If we have a container, try to get it from there
        if ($this->hasContainer() && $this->container->has('command_registry')) {
            return $this->container->get('command_registry');
        }
        
        // If we can't get the registry, throw an exception
        throw new \RuntimeException("Command registry not available");
    }
}