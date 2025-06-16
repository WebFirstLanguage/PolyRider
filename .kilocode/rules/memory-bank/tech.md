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

### Standards
- **PSR-4**: Autoloading standard
- **SOLID Principles**: Design philosophy

### Development Tools
- **PHPStan**: Static analysis tool for PHP
- **PHPUnit**: Testing framework
- **PHP_CodeSniffer**: Code style checking
- **CLI Tool**: Custom command-line interface

## Development Setup

### Required Software
- PHP 8.2+ with extensions:
  - PDO
  - JSON
  - mbstring
  - fileinfo
- MySQL 8.0+ or MariaDB 10.5+
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
- MySQL 8.0+ / MariaDB 10.5+
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
./logbie help

# Build the application
./logbie build [--no-composer] [--no-assets] [--dev|--prod]

# Clean the application
./logbie clean [--vendor] [--assets] [--cache] [--all]

# Windows usage
php logbie <command>
```

### PHPStan Static Analysis
```bash
# Run PHPStan analysis
composer require --dev phpstan/phpstan
vendor/bin/phpstan analyse src

# Using configuration file
vendor/bin/phpstan analyse -c phpstan.neon
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
- `config/database.php`: Database configuration
- `config/app.php`: Application settings
- `phpstan.neon`: PHPStan configuration

### Environment Variables
- `APP_ENV`: Application environment (development, staging, production)
- `DB_HOST`: Database host
- `DB_NAME`: Database name
- `DB_USER`: Database username
- `DB_PASS`: Database password
- `LOG_MODE`: Logging mode (file, response, both)

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