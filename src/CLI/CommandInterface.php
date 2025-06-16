<?php

namespace LogbieCLI;

/**
 * Command Interface
 * 
 * Defines the contract for all CLI commands.
 * 
 * @package LogbieCLI
 * @since 1.0.0
 */
interface CommandInterface
{
    /**
     * Get the command name
     * 
     * @return string The command name
     */
    public function getName(): string;
    
    /**
     * Get the command description
     * 
     * @return string The command description
     */
    public function getDescription(): string;
    
    /**
     * Get the command help text
     * 
     * @return string The command help text
     */
    public function getHelp(): string;
    
    /**
     * Execute the command
     * 
     * @param array $args Command arguments
     * @return int Exit code (0 for success, non-zero for failure)
     */
    public function execute(array $args = []): int;
}