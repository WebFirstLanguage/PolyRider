# Logbie Framework: Architecture

## System Architecture

The Logbie Framework follows a modular architecture with clear separation of concerns, adhering to SOLID principles. The architecture is designed to be maintainable, extensible, and secure.

### Core Components

1. **Module System**
   - Base class: `LogbieCore\BaseModule`
   - Entry point for all application functionality
   - Self-contained units with specific responsibilities
   - Handles routing, request processing, and response generation

2. **Database ORM**
   - Secure database abstraction layer
   - Driver-based architecture supporting multiple database systems
   - Prepared statement caching
   - Transaction support
   - Relationship handling
   - SQL injection protection
   - Support for MySQL/MariaDB and SQLite

3. **JavaScript Framework (Logbie.js)**
   - Client-side AJAX handling
   - Form submission management
   - Auto-refresh functionality
   - Error handling and user feedback

4. **Template Engine**
   - Secure variable rendering
   - Control structures
   - Custom filters
   - Template caching
   - File imports and partials

5. **Logger**
   - Real-time logging
   - Multiple output modes (file, response, both)
   - Structured message format
   - Error tracking

6. **Response Core**
   - Fluent interface for HTTP responses
   - Support for multiple content types (JSON, XML, HTML)
   - Header and cookie management
   - Status code handling

7. **User Management**
   - User creation and validation
   - Authentication
   - Account management
   - Security features

8. **CLI Component**
   - Cross-platform command-line interface
   - Extensible command registry
   - Built-in commands (build, clean, help)
   - Support for both class-based and callback-based commands
   - Console logging with colorized output
   - Dependency injection integration
   - Command-line argument parsing
   - Comprehensive error handling
   - Help documentation system
   - Cross-platform path handling

## Source Code Paths

```
/src
├── Core/           # Core framework components
│   ├── Application.php
│   ├── BaseModule.php
│   ├── Container.php
│   ├── DatabaseORM.php
│   ├── Logger.php
│   ├── LogMode.php
│   ├── Response.php
│   ├── TemplateEngine.php
│   ├── UserManagement.php
│   └── Database/    # Database driver components
│       ├── DatabaseDriverInterface.php
│       ├── DatabaseDriverFactory.php
│       ├── MySQLDriver.php
│       └── SQLiteDriver.php
├── CLI/            # Command-line interface components
│   ├── Application.php
│   ├── BaseCommand.php
│   ├── CommandInterface.php
│   ├── CommandRegistry.php
│   ├── ConsoleLogger.php
│   └── Command/    # CLI command implementations
│       ├── BuildCommand.php
│       ├── CleanCommand.php
│       └── HelpCommand.php
├── Classes/        # Shared class files
│   ├── CustomUUID.php
│   ├── Utility/
│   │   ├── EmailValidator.php
│   │   └── ...
│   └── ...
└── Modules/        # User-facing modules
    ├── UserManager.php
    ├── ContentManager.php
    └── ...
```

## Key Technical Decisions

1. **PSR-4 Compliance**
   - Standardized autoloading
   - Consistent namespace structure
   - Automated validation tools (cc.py)

2. **SOLID Principles Implementation**
   - Single Responsibility Principle: Each class has one purpose
   - Open-Closed Principle: Extensible through interfaces
   - Liskov Substitution Principle: Proper inheritance hierarchies
   - Interface Segregation Principle: Focused interfaces
   - Dependency Inversion Principle: Dependency injection

3. **Security-First Approach**
   - Prepared statements for all database operations
   - Automatic output escaping in templates
   - Secure password handling
   - Input validation
   - CSRF protection

4. **Performance Optimization**
   - Template caching
   - Prepared statement caching
   - Schema information caching
   - Efficient JavaScript operations

5. **Cross-Platform Compatibility**
   - CLI tool works on Windows, Linux, and macOS
   - Proper path handling with DIRECTORY_SEPARATOR
   - Automatic detection of environment capabilities

## Design Patterns

1. **Module Pattern**
   - Self-contained functionality units
   - Extends BaseModule
   - Handles specific application features

2. **Repository Pattern**
   - Database access abstraction
   - CRUD operations
   - Query optimization

3. **Factory Pattern**
   - Object creation abstraction
   - Dependency management

4. **Dependency Injection**
   - Container-based service management
   - Constructor injection
   - Interface-based dependencies

5. **Fluent Interface**
   - Method chaining for Response
   - Readable API design

6. **Command Pattern**
   - CLI commands encapsulate actions
   - Standardized interface
   - Extensible registry

## Component Relationships

### Module Dependencies
```
BaseModule
  ├── DatabaseORM
  │   └── DatabaseDriver (MySQLDriver or SQLiteDriver)
  ├── Container
  ├── Response
  ├── Logger
  └── TemplateEngine (optional)
```

### CLI Component Dependencies
```
LogbieCLI\Application
  ├── CommandRegistry
  ├── ConsoleLogger
  └── Container (optional)

CommandRegistry
  └── BaseCommand
      ├── ConsoleLogger
      └── Container (optional)
```

### Request Flow
1. Client request → Application
2. Application → Module (based on URL)
3. Module processes request using core services
4. Module generates response
5. Response sent to client

### CLI Command Flow
1. User input → LogbieCLI\Application
2. Application → CommandRegistry → Command
3. Command executes with provided arguments
4. Command outputs results via ConsoleLogger
5. Command returns exit code

### Data Flow
1. Client input → Module
2. Module validates input
3. Module uses DatabaseORM for data operations
4. Module processes data
5. Module uses Response to format output
6. Response sent to client

## Critical Implementation Paths

1. **Module Execution**
   ```
   Application → Module::run() → Module::processRequest() → Response::send()
   ```

2. **CLI Command Execution**
   ```
   logbie script → LogbieCLI\Application::run() → CommandRegistry::get() → Command::execute()
   ```

3. **Database Operations**
   ```
   Module → DatabaseORM::beginTransaction() → DatabaseDriver::beginTransaction() → CRUD operations → DatabaseORM::commit() → DatabaseDriver::commit()
   ```

4. **Template Rendering**
   ```
   Module → Response::render() → TemplateEngine::render() → Response::send()
   ```

5. **Error Handling**
   ```
   try/catch → Logger::log() → Response::setStatus() → Response::setJson() → Response::send()
   ```

6. **CLI Error Handling**
   ```
   try/catch → ConsoleLogger::error() → Command::return(error_code)
   ```

7. **User Authentication**
   ```
   Module → UserManagement::authenticateUser() → Session management → Response