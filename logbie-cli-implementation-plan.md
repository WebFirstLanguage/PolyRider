# Logbie CLI Tool Implementation Plan

## Overview

We'll create a cross-platform PHP CLI tool called "logbie" (without the .php extension) that implements "build" and "clean" commands with an extensible architecture for custom command creation. The tool will follow a hybrid approach where commands can be implemented as either classes or callbacks, with a registration system that supports both.

## File Structure

```
/
├── logbie                  # Main executable script (no .php extension)
├── src/
│   ├── CLI/                # CLI-specific classes
│   │   ├── Command/        # Command implementations
│   │   │   ├── BuildCommand.php
│   │   │   ├── CleanCommand.php
│   │   │   └── HelpCommand.php
│   │   ├── BaseCommand.php # Base class for commands
│   │   ├── CommandInterface.php # Interface for commands
│   │   ├── CommandRegistry.php  # Registry for commands
│   │   ├── ConsoleLogger.php    # CLI-specific logger
│   │   └── Application.php      # Main CLI application class
```

## Autoloading Configuration

We'll update the composer.json file to include the new CLI namespace:

```json
"autoload": {
    "psr-4": {
        "LogbieCore\\": "src/Core/",
        "Logbie\\": "src/Modules/",
        "LogbieCLI\\": "src/CLI/"
    }
}
```

## Component Design

### 1. Main Executable (`logbie`)

- Shebang line for cross-platform compatibility
- Autoloader inclusion
- Application instantiation and execution
- Error handling

### 2. Command Interface (`CommandInterface.php`)

- Define the contract for all commands
- Methods for name, description, help text, and execution

### 3. Base Command Class (`BaseCommand.php`)

- Abstract class implementing CommandInterface
- Common functionality for all commands
- Access to container and logger

### 4. Command Registry (`CommandRegistry.php`)

- Register and retrieve commands
- Support for both class-based and callback-based commands
- Command discovery and help generation

### 5. Console Logger (`ConsoleLogger.php`)

- CLI-specific logging implementation
- Fallback when framework Logger is not available
- Support for different output levels (info, warning, error)
- Colorized output for better readability

### 6. CLI Application (`Application.php`)

- Parse command-line arguments
- Dispatch commands
- Handle errors and display help
- Provide access to container and logger

### 7. Build Command (`BuildCommand.php`)

- Run composer install
- Compile frontend assets if present
- Create necessary directories
- Support for customization

### 8. Clean Command (`CleanCommand.php`)

- Remove Composer's vendor directory
- Remove generated assets
- Clean cache files
- Cross-platform compatibility

## Implementation Details

### Command Registration System

Commands will be registered through the CommandRegistry class, which will support:

1. Class-based registration:
```php
$registry->register(BuildCommand::class);
```

2. Callback-based registration:
```php
$registry->register('custom', function($args) {
    // Command implementation
}, 'Custom command description');
```

### Argument Parsing

The Application class will parse command-line arguments and options:

```
logbie command [--option] [argument]
```

Options will be prefixed with `--` (long form) or `-` (short form).

### Help Documentation

Help will be available through:

```
logbie help
logbie help <command>
logbie <command> --help
```

The help system will display command descriptions, usage examples, and available options.

### Error Handling

The CLI tool will implement comprehensive error handling:

1. Command not found errors
2. Invalid argument errors
3. Execution errors with appropriate exit codes
4. Detailed error messages with suggestions

### Logging

The logging system will:

1. Use the framework Logger when available
2. Fall back to ConsoleLogger when used standalone
3. Support different verbosity levels
4. Provide colorized output for better readability

### Cross-Platform Compatibility

To ensure cross-platform compatibility:

1. Use PHP's built-in functions for file operations
2. Avoid platform-specific commands
3. Use proper path handling with DIRECTORY_SEPARATOR
4. Test on multiple platforms (Windows, Linux, macOS)

## Command Implementation Examples

### Build Command

The build command will:

1. Run `composer install`
2. Create necessary directories (storage/logs, storage/cache)
3. Compile frontend assets if present
4. Support customization through hooks or configuration

### Clean Command

The clean command will:

1. Remove Composer's vendor directory
2. Remove generated assets
3. Clean cache files
4. Preserve user data and configuration

## Extension Mechanism

To add new custom commands:

1. Create a class that extends BaseCommand or implements CommandInterface
2. Register the command with the CommandRegistry
3. Alternatively, register a callback-based command

Example of adding a custom command:

```php
// In a bootstrap file or plugin
$registry = $app->getCommandRegistry();

// Class-based command
$registry->register(MyCustomCommand::class);

// Callback-based command
$registry->register('custom', function($args) {
    echo "Executing custom command with args: " . implode(', ', $args) . PHP_EOL;
    return 0;
}, 'A custom command example');
```

## Component Relationships

The main components of the CLI tool have the following relationships:

- CommandInterface is implemented by BaseCommand
- BaseCommand is extended by BuildCommand, CleanCommand, and HelpCommand
- Application uses CommandRegistry to manage commands
- Application uses ConsoleLogger for logging
- CommandRegistry manages instances of CommandInterface

## Implementation Steps

1. Create the directory structure
2. Update composer.json with the new CLI namespace
3. Implement the core classes (CommandInterface, BaseCommand, CommandRegistry, ConsoleLogger, Application)
4. Implement the built-in commands (BuildCommand, CleanCommand, HelpCommand)
5. Create the main executable script
6. Set proper permissions for the executable
7. Test the implementation
8. Document the extension mechanism