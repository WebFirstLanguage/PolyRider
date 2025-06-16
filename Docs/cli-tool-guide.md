# Logbie CLI Tool Guide

The Logbie CLI tool is a command-line interface for the Logbie Framework that provides various commands to help with development, building, and maintenance tasks.

## Installation

The CLI tool is included with the Logbie Framework. To use it, simply run the `logbie` command from the project root directory:

```bash
./logbie help
```

On Windows, you may need to use:

```bash
php logbie help
```

## Available Commands

### Help

Display help information for available commands:

```bash
./logbie help
./logbie help <command>
./logbie <command> --help
```

### Build

Build the application by running composer install, creating necessary directories, and compiling frontend assets if present:

```bash
./logbie build
```

Options:
- `--no-composer`: Skip running composer install
- `--no-assets`: Skip compiling frontend assets
- `--dev`: Build in development mode
- `--prod`: Build in production mode (default)

### Clean

Clean the application by removing Composer's vendor directory, generated assets, and cache files:

```bash
./logbie clean
```

Options:
- `--vendor`: Remove vendor directory
- `--assets`: Remove generated assets
- `--cache`: Remove cache files
- `--all`: Remove all (vendor, assets, cache) (default)

## Adding Custom Commands

The Logbie CLI tool is designed to be extensible, allowing you to add your own custom commands. There are two ways to add custom commands:

### 1. Class-Based Commands

Create a new class that extends `LogbieCLI\BaseCommand`:

```php
<?php

namespace LogbieCLI\Command;

use LogbieCLI\BaseCommand;

class MyCustomCommand extends BaseCommand
{
    public function getName(): string
    {
        return 'my-command';
    }
    
    public function getDescription(): string
    {
        return 'My custom command description';
    }
    
    public function getHelp(): string
    {
        return <<<HELP
Usage: logbie my-command [options]

My custom command description.

Options:
  --option1          Option 1 description
  --option2          Option 2 description
  --help, -h         Display this help message
HELP;
    }
    
    public function execute(array $args = []): int
    {
        // Parse options
        [$options, $remainingArgs] = $this->parseOptions($args);
        
        // Command implementation
        $this->logger->info("Executing my custom command...");
        
        // Your command logic here
        
        $this->logger->success("Command completed successfully");
        return 0;
    }
}
```

Then register the command in your application bootstrap code:

```php
// Get the command registry
$registry = $app->getCommandRegistry();

// Register the command
$registry->register(MyCustomCommand::class);
```

### 2. Callback-Based Commands

For simpler commands, you can register a callback function:

```php
// Get the command registry
$registry = $app->getCommandRegistry();

// Register a callback-based command
$registry->register('simple-command', function($args, $logger) {
    $logger->info("Executing simple command...");
    
    // Your command logic here
    
    $logger->success("Command completed successfully");
    return 0;
}, 'A simple command example');
```

### Registering Commands in a Plugin or Extension

To register commands from a plugin or extension, you can hook into the application's initialization process:

```php
// In your plugin's bootstrap file
use LogbieCLI\Application;

// Check if the CLI application is available
if (class_exists(Application::class)) {
    // Get the CLI application instance
    $app = Application::getInstance();
    
    // Register your commands
    $registry = $app->getCommandRegistry();
    $registry->register(MyPluginCommand::class);
}
```

## Command Development Guidelines

When developing custom commands, follow these guidelines:

1. **Single Responsibility**: Each command should have a single, well-defined purpose.
2. **Clear Documentation**: Provide clear help text and descriptions for your commands.
3. **Error Handling**: Handle errors gracefully and provide meaningful error messages.
4. **Exit Codes**: Return appropriate exit codes (0 for success, non-zero for failure).
5. **Logging**: Use the logger for output instead of direct echo/print statements.
6. **Cross-Platform Compatibility**: Ensure your commands work on different operating systems.

## Command Structure

A well-structured command should:

1. Parse and validate arguments and options
2. Display help information when requested
3. Execute the command logic
4. Provide appropriate feedback through logging
5. Return an appropriate exit code

## Advanced Usage

### Accessing the Framework Container

If you need to access framework services, you can use the container:

```php
public function execute(array $args = []): int
{
    if ($this->hasContainer()) {
        $db = $this->getService('db');
        // Use the database service
    }
    
    // Command implementation
    return 0;
}
```

### Using the Framework Logger

You can use the framework logger if available:

```php
public function execute(array $args = []): int
{
    $frameworkLogger = $this->getFrameworkLogger();
    
    if ($frameworkLogger !== null) {
        $frameworkLogger->log("Using framework logger");
    } else {
        $this->logger->info("Using CLI logger");
    }
    
    // Command implementation
    return 0;
}
```

## Troubleshooting

### Command Not Found

If you get a "Command not found" error, make sure:

1. The command is registered correctly
2. The command class is autoloadable
3. The command name is spelled correctly

### Autoloader Issues

If you get an autoloader error, run:

```bash
composer dump-autoload
```

### Permission Issues

If you can't execute the CLI tool, make sure it has the correct permissions:

On Linux/macOS:
```bash
chmod +x logbie
```

On Windows:
```powershell
icacls logbie /grant Everyone:RX
```

## Cross-Platform Compatibility Notes

The Logbie CLI tool is designed to work on multiple platforms (Windows, Linux, macOS). Here are some platform-specific considerations:

### Windows

- Use `php logbie <command>` to run the CLI tool
- Use `icacls logbie /grant Everyone:RX` to set permissions
- Directory paths use backslashes (`\`) internally, but the tool handles this automatically

### Linux/macOS

- Use `./logbie <command>` to run the CLI tool after setting execute permissions
- Use `chmod +x logbie` to set execute permissions
- Directory paths use forward slashes (`/`)

### Common Issues and Solutions

1. **"Command not found" on Linux/macOS**
   - Make sure the file has execute permissions: `chmod +x logbie`
   
2. **"Could not find Composer autoloader" error**
   - The CLI tool will automatically attempt to run `composer install` if the autoloader is not found
   - This will only fail if Composer itself is not installed on your system
   
3. **Error recreating cache directory**
   - This has been fixed in the latest version. If you encounter this issue, update your CLI tool.
   
4. **Permission issues with directory creation**
   - Ensure you have write permissions to the project directory
   - On Linux/macOS, you may need to run with sudo for certain operations