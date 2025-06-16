<?php

namespace LogbieCLI\Command;

use LogbieCLI\BaseCommand;

/**
 * Generate Module Command
 * 
 * Creates a new module skeleton for the Logbie Framework.
 * 
 * @package LogbieCLI\Command
 * @since 1.0.0
 */
class GenerateModuleCommand extends BaseCommand
{
    /**
     * Get the command name
     * 
     * @return string The command name
     */
    public function getName(): string
    {
        return 'generate-module';
    }
    
    /**
     * Get the command description
     * 
     * @return string The command description
     */
    public function getDescription(): string
    {
        return 'Generate a new module skeleton';
    }
    
    /**
     * Get the command help text
     * 
     * @return string The command help text
     */
    public function getHelp(): string
    {
        return <<<HELP
Usage: logbie generate-module [options] <module-name>

Generate a new module skeleton for the Logbie Framework.
The module name should be in PascalCase (e.g., UserManager, ContentHandler).

Arguments:
  module-name    The name of the module to generate (required)

Options:
  --force        Overwrite existing module if it exists
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
        // Parse options
        [$options, $remainingArgs] = $this->parseOptions($args);
        
        // Check if module name is provided
        if (empty($remainingArgs)) {
            $this->logger->error("Module name is required");
            $this->logger->info($this->getHelp());
            return 1;
        }
        
        // Get the module name
        $moduleName = $remainingArgs[0];
        
        // Validate module name (PascalCase)
        if (!$this->validateModuleName($moduleName)) {
            $this->logger->error("Invalid module name: $moduleName");
            $this->logger->info("Module name must be in PascalCase (e.g., UserManager, ContentHandler)");
            return 1;
        }
        
        // Check if module already exists
        $modulePath = $this->getModulePath($moduleName);
        if (file_exists($modulePath) && !isset($options['force'])) {
            $this->logger->error("Module already exists: $moduleName");
            $this->logger->info("Use --force to overwrite");
            return 1;
        }
        
        // Create the module
        if (!$this->createModule($moduleName)) {
            $this->logger->error("Failed to create module: $moduleName");
            return 1;
        }
        
        $this->logger->success("Module created successfully: $moduleName");
        $this->logger->info("Module file: $modulePath");
        return 0;
    }
    
    /**
     * Validate module name
     * 
     * @param string $name The module name to validate
     * @return bool True if valid, false otherwise
     */
    private function validateModuleName(string $name): bool
    {
        // Check if name is in PascalCase (starts with uppercase letter, no spaces or special chars)
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name) === 1;
    }
    
    /**
     * Get the module file path
     * 
     * @param string $name The module name
     * @return string The module file path
     */
    private function getModulePath(string $name): string
    {
        return $this->getProjectRoot() . '/src/Modules/' . $name . '.php';
    }
    
    /**
     * Create a new module
     * 
     * @param string $name The module name
     * @return bool True if successful, false otherwise
     */
    private function createModule(string $name): bool
    {
        try {
            // Ensure the Modules directory exists
            $modulesDir = $this->getProjectRoot() . '/src/Modules';
            if (!$this->ensureDirectoryExists($modulesDir)) {
                $this->logger->error("Failed to create Modules directory");
                return false;
            }
            
            // Create the module file
            $modulePath = $this->getModulePath($name);
            $moduleContent = $this->generateModuleContent($name);
            
            if (file_put_contents($modulePath, $moduleContent) === false) {
                $this->logger->error("Failed to write module file: $modulePath");
                return false;
            }
            
            $this->logger->debug("Module file created: $modulePath");
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Error creating module: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate module file content
     * 
     * @param string $name The module name
     * @return string The module file content
     */
    private function generateModuleContent(string $name): string
    {
        $date = date('Y-m-d');
        
        return <<<PHP
<?php

namespace Logbie;

use LogbieCore\BaseModule;

/**
 * $name Module
 * 
 * Description of the $name module.
 * 
 * @package Logbie
 * @since 1.0.0
 * @created $date
 */
class {$name} extends BaseModule
{
    /**
     * Run the module
     * 
     * @param array \$arguments Arguments passed to the module
     * @return mixed The result of the module execution
     */
    public function run(array \$arguments = []): mixed
    {
        try {
            // Get the action from the arguments
            \$action = \$arguments[0] ?? 'default';
            
            // Log the action
            \$this->logger->log("{$name}: Running action '{\$action}'");
            
            // Route to the appropriate method based on the action
            match (\$action) {
                'default' => \$this->defaultAction(),
                // Add more actions here
                default => \$this->defaultAction(),
            };
            
            return null;
        } catch (\Exception \$e) {
            // Log the error
            \$this->logger->log("Error in {$name}: " . \$e->getMessage());
            
            // Send an error response
            \$this->sendError(\$e->getMessage(), 500);
            return null;
        }
    }
    
    /**
     * Default action
     * 
     * @return void
     */
    private function defaultAction(): void
    {
        \$this->response->setContent(
            '<h1>{$name} Module</h1>' .
            '<p>This is the default action for the {$name} module.</p>'
        )->send();
    }
    
    // Add more action methods here
}
PHP;
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