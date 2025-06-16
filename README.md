# Logbie Framework

A comprehensive PHP framework for building modular, secure, and maintainable web applications.

## Overview

The Logbie Framework provides a structured approach to client-server interactions, module development, database management, and robust error handling. It aims to minimize complexity for developers while ensuring high standards of performance, security, and maintainability.

## Key Features

- **Modular Architecture**: Self-contained modules for specific functionalities
- **Dependency Injection**: Powerful container for service management
- **Secure Database ORM**: Prepared statement caching, SQL injection protection, transaction support
- **Template Engine**: Secure variable rendering, control structures, custom filters, caching
- **Real-time Logging**: Flexible output options (file, response, or both)
- **Response Core**: Fluent interface for HTTP responses (JSON, XML, HTML)
- **User Management**: Secure user creation, authentication, account management

## Requirements

- PHP 8.2+
- MySQL/MariaDB
- Composer

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/logbie-framework.git
   ```

2. Install dependencies:
   ```
   composer install
   ```

3. Configure your web server to point to the `public` directory.

4. Create a `.env` file in the root directory with your configuration:
   ```
   APP_ENV=development
   APP_DEBUG=true
   
   DB_DRIVER=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=logbie
   DB_USER=root
   DB_PASS=
   DB_CHARSET=utf8mb4
   ```

5. Create the necessary directories:
   ```
   mkdir -p storage/logs
   mkdir -p storage/cache/templates
   mkdir -p templates
   ```

6. Set appropriate permissions:
   ```
   chmod -R 755 storage
   ```

## Directory Structure

```
/
├── public/             # Web-accessible files
│   └── index.php       # Entry point
├── src/                # Source code
│   ├── Core/           # Framework core
│   │   ├── Application.php
│   │   ├── BaseModule.php
│   │   ├── Container.php
│   │   ├── DatabaseORM.php
│   │   ├── Logger.php
│   │   ├── LogMode.php
│   │   ├── Response.php
│   │   ├── TemplateEngine.php
│   │   └── UserManagement.php
│   ├── Classes/        # Shared classes
│   └── Modules/        # Application modules
│       └── ExampleModule.php
├── storage/            # Non-public storage
│   ├── logs/           # Log files
│   └── cache/          # Cache files
│       └── templates/  # Template cache
├── templates/          # Template files
├── tests/              # Test files
│   └── Core/           # Core tests
│       └── ContainerTest.php
├── vendor/             # Composer dependencies
├── .env                # Environment configuration
└── composer.json       # Composer configuration
```

## Creating a Module

1. Create a new PHP file in the `src/Modules` directory:

```php
<?php

namespace Logbie;

use LogbieCore\BaseModule;

class YourModule extends BaseModule
{
    public function run(array $arguments = []): mixed
    {
        try {
            // Your module logic here
            $this->logger->log("YourModule: Running");
            
            $this->response->setContent('<h1>Hello from YourModule!</h1>')->send();
        } catch (\Exception $e) {
            $this->logger->log("Error in YourModule: " . $e->getMessage());
            $this->response->setStatus(500)
                ->setJson([
                    'error' => true,
                    'message' => 'An error occurred'
                ])
                ->send();
        }
    }
}
```

2. Access your module at `/yourmodule` in your browser.

## Example Usage

The framework includes an example module that demonstrates various features:

- Basic HTML response: `/example`
- Dynamic content: `/example/hello/YourName`
- JSON response: `/example/json`
- Form handling: `/example/form`
- Database operations: `/example/db`
- Template rendering: `/example/template`

## Documentation

For detailed documentation, see the `Docs` directory:

- [Module Development Guide](Docs/module-documentation.md)
- [Database ORM Documentation](Docs/database-orm-docs.md)
- [Logger Documentation](Docs/logging-documentation.md)
- [Response Guide](Docs/response-guide.md)
- [Template Engine Specification](Docs/template-engine-spec.md)
- [User Management Documentation](Docs/user-management-docs.md)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.
