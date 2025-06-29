# Logbie Framework: Technology Stack

## Technologies Used

### Backend
- **PHP 8.2+**: Core programming language with modern features
  - Type declarations
  - Enumerations
  - Named arguments
  - Constructor property promotion
  - Match expressions
  - Readonly properties

### Frontend
- **JavaScript**: Client-side scripting
- **jQuery**: DOM manipulation and AJAX handling
- **Bootstrap 5.3.2**: CSS framework for responsive design

### Database
- **MySQL/MariaDB**: Primary database system
  - InnoDB engine for transaction support
  - UTF-8MB4 character encoding
- **SQLite**: Alternative lightweight database system
  - File-based storage
  - WAL (Write-Ahead Logging) journal mode for better performance
  - Foreign key constraint support

### Standards
- **PSR-4**: Autoloading standard
- **SOLID Principles**: Design philosophy

### Development Tools
- **PHPStan**: Static analysis tool for PHP
  - Level-based analysis
  - Custom configuration in phpstan.neon
  - Integrated into development workflow
- **PHPUnit**: Testing framework
- **PHP_CodeSniffer**: Code style checking
- **CLI Tool**: Custom command-line interface
  - Cross-platform support
  - Extensible command registry
  - Built-in commands for common tasks

## Development Setup

### Required Software
- PHP 8.2+ with extensions:
  - PDO
  - JSON
  - mbstring
  - fileinfo
- MySQL 8.0+ or MariaDB 10.5+ (for MySQL driver)
- SQLite 3.8.8+ (for SQLite driver)
- Web server (Apache/Nginx)
- Composer for dependency management
- Git for version control

### Directory Structure
```
/
├── src/                # Source code
│   ├── Core/           # Framework core
│   ├── Classes/        # Shared classes
│   ├── Modules/        # Application modules
│   └── CLI/            # Command-line interface
│       └── Command/    # CLI commands
├── public/             # Web-accessible files
│   ├── index.php       # Entry point
│   ├── js/             # JavaScript files
│   │   └── logbie.js   # Logbie.js framework
│   └── css/            # CSS files
├── storage/            # Non-public storage
│   ├── logs/           # Log files
│   └── cache/          # Cache files
├── tests/              # Test files
├── vendor/             # Composer dependencies
├── Docs/               # Documentation
├── logbie              # CLI executable
└── tools/              # Development tools
    ├── pathfinder.py   # Directory structure tool
    └── cc.py           # PSR-4 compliance checker
```

## Technical Constraints

### Performance Requirements
- Response time < 200ms for API endpoints
- Page load time < 1s for standard pages
- Support for concurrent users: 100+

### Security Requirements
- OWASP Top 10 compliance
- Secure password hashing (bcrypt)
- Input validation and sanitization
- CSRF protection
- XSS prevention

### Compatibility
- PHP 8.2+
- MySQL 8.0+ / MariaDB 10.5+ (when using MySQL driver)
- SQLite 3.8.8+ (when using SQLite driver)
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile-responsive design
- Cross-platform CLI (Windows, Linux, macOS)

## Dependencies

### Core Dependencies
- **vlucas/phpdotenv**: Environment variable management

### Development Dependencies
- **phpstan/phpstan**: Static analysis
- **phpunit/phpunit**: Testing framework
- **squizlabs/php_codesniffer**: Code style checking
- Python 3.6+ for maintenance tools

## Tool Usage Patterns

### CLI Tool (logbie)
```bash
# Display help information
php logbie help
php logbie help <command>
php logbie <command> --help

# Build the application
php logbie build [--no-composer] [--no-assets] [--dev|--prod]

# Clean the application
php logbie clean [--vendor] [--assets] [--cache] [--all]

# Windows usage
php logbie <command>

# Linux/macOS usage (after setting execute permissions)
./logbie <command>
```

The CLI tool provides a consistent interface for common development tasks and can be extended with custom commands. It includes comprehensive error handling, colorized output, and cross-platform compatibility.

### Adding Custom CLI Commands

#### Class-Based Commands
```php
// Create a new command class
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
    
    public function execute(array $args = []): int
    {
        // Command implementation
        $this->logger->info("Executing my command...");
        return 0;
    }
}

// Register the command
$registry = $app->getCommandRegistry();
$registry->register(MyCustomCommand::class);
```

#### Callback-Based Commands
```php
// Register a callback-based command
$registry = $app->getCommandRegistry();
$registry->register('simple-command', function($args, $logger) {
    $logger->info("Executing simple command...");
    return 0;
}, 'A simple command example');
```

### PHPStan Static Analysis
```bash
# Run PHPStan analysis
composer require --dev phpstan/phpstan
vendor/bin/phpstan analyse src

# Using configuration file
vendor/bin/phpstan analyse -c phpstan.neon
```

PHPStan has been integrated into the development workflow to ensure code quality and type safety. Common issues that have been addressed include:
- Void return type handling
- Type safety in reflection operations
- Redundant condition removal
- Proper method call validation

The phpstan.neon configuration includes:
```yaml
parameters:
    level: 5
    paths:
        - src
    treatPhpDocTypesAsCertain: false
```

### PSR-4 Compliance Checker (cc.py)
```bash
# Check compliance without making changes
python3 cc.py --dry-run

# Automatically fix compliance issues
python3 cc.py --yes

# Create backups before making changes
python3 cc.py --backup
```

### Directory Structure Tool (pathfinder.py)
```bash
# Generate directory structure documentation
python3 pathfinder.py

# Output to specific file
python3 pathfinder.py --output=directory_structure.md
```

### Development Workflow
1. Create module extending BaseModule
2. Implement required methods
3. Run cc.py to verify PSR-4 compliance
4. Run PHPStan to check for type errors
5. Write unit tests
6. Document module functionality
7. Update Dev_diary with changes

### Deployment Process
1. Run all tests
2. Run PHPStan analysis
3. Verify PSR-4 compliance
4. Update documentation
5. Deploy to staging environment
6. Run integration tests
7. Deploy to production

## Environment Configuration

### Configuration Files
- `.env`: Environment variables
- `config/database.php`: Database configuration with support for multiple drivers
- `config/app.php`: Application settings
- `phpstan.neon`: PHPStan configuration

### Environment Variables
- `APP_ENV`: Application environment (development, staging, production)
- `DB_DRIVER`: Database driver to use (mysql, sqlite)
- `DB_CHARSET`: Character encoding for database connections
- `LOG_MODE`: Logging mode (file, response, both)

#### MySQL-specific
- `MYSQL_HOST`: MySQL database host
- `MYSQL_PORT`: MySQL database port
- `MYSQL_DATABASE`: MySQL database name
- `MYSQL_USERNAME`: MySQL database username
- `MYSQL_PASSWORD`: MySQL database password

#### SQLite-specific
- `SQLITE_DATABASE`: Path to SQLite database file
- `SQLITE_FOREIGN_KEYS`: Enable foreign key constraints (true/false)
- `SQLITE_JOURNAL_MODE`: Journal mode (WAL, DELETE, etc.)

## Maintenance and Monitoring

### Logging
- Daily log rotation
- Structured log format
- Error level filtering
- CLI-specific logging (ConsoleLogger)

### Performance Monitoring
- Database query logging
- Response time tracking
- Memory usage monitoring

### Security Auditing
- Regular dependency updates
- Code reviews
- Automated security scanning
- Static analysis with PHPStan