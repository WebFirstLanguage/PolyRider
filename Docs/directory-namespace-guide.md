# Logbie Framework Directory and Namespace Guide
Version: 1.0
Last Updated: 2024-10-25

## Table of Contents
1. [Introduction](#introduction)
2. [Directory Structure](#directory-structure)
3. [Namespace Mappings](#namespace-mappings)
4. [Maintenance Tools](#maintenance-tools)
5. [Implementation Guidelines](#implementation-guidelines)
6. [Best Practices](#best-practices)

## Introduction

This document outlines the standardized directory structure and namespace organization for the Logbie Framework. Following these guidelines ensures consistency across all framework components and facilitates automated tooling and maintenance.

## Directory Structure

The Logbie Framework uses a PSR-4 compliant directory structure organized under the `/src` directory:

```
/src
├── Core/           # Core framework components
├── Classes/        # Shared class files
└── Modules/        # User-facing modules
```

### Primary Directories

#### /src/Core/
- Contains core framework components
- Houses fundamental services and base classes
- Examples: Application.php, BaseModule.php, Container.php
- Restricted to essential framework functionality

#### /src/Classes/
- Contains shared class files
- Available to all Logbie components
- Used for common utilities and shared resources
- Examples: CustomUUID.php, database models, shared services

#### /src/Modules/
- Contains user-facing modules
- Implements specific application functionality
- Directly handles user interactions
- Examples: UserManager.php, ContentManager.php

## Namespace Mappings

The framework uses three primary namespaces, each mapped to a specific directory:

| Namespace | Directory | Purpose |
|-----------|-----------|---------|
| `LogbieCore` | `/src/Core` | Core framework components and services |
| `LogbieClasses` | `/src/Classes` | Shared classes and utilities |
| `Logbie` | `/src/Modules` | User-facing modules |

### Namespace Usage Rules

#### LogbieCore
- Reserved for core framework components
- Must extend or implement core interfaces
- Cannot depend on Classes or Modules
- Example:
```php
namespace LogbieCore;

class Logger {
    // Core logging implementation
}
```

#### LogbieClasses
- Used for shared functionality
- Available to all framework components
- Should be generic and reusable
- Example:
```php
namespace LogbieClasses;

class CustomUUID {
    // Shared UUID implementation
}
```

#### Logbie
- Used for module implementation
- Must extend BaseModule
- Handles specific application features
- Example:
```php
namespace Logbie;

use LogbieCore\BaseModule;

class UserManager extends BaseModule {
    // Module implementation
}
```

## Maintenance Tools

The framework provides two maintenance tools that should be placed in the root directory to run:

### pathfinder.py
- Updates directory_structure.md automatically
- Generates current directory tree
- Creates statistics about project structure
- Usage:
```bash
python3 pathfinder.py
```

### cc.py (PSR-4 Compliance Checker)
- Validates PSR-4 compliance
- Can automatically fix namespace issues
- Checks class naming conventions
- Usage:
```bash
python3 cc.py [options]
```

Options:
- `--yes`: Automatically apply all corrections
- `--dry-run`: Show changes without applying them
- `--force`: Skip Git status check
- `--backup`: Create backups before making changes
- `--log-level`: Set logging level (DEBUG/INFO/WARNING/ERROR)

## Implementation Guidelines

### File Naming
1. Files must match their class names exactly
2. Use StudlyCaps for all class names
3. One class per file
4. `.php` extension required

### Class Organization
1. Core Services:
```php
namespace LogbieCore;

class ServiceName {
    // Core service implementation
}
```

2. Shared Classes:
```php
namespace LogbieClasses;

class SharedUtility {
    // Shared functionality
}
```

3. Modules:
```php
namespace Logbie;

use LogbieCore\BaseModule;

class ModuleName extends BaseModule {
    // Module implementation
}
```

## Best Practices

### 1. Namespace Usage
- Use fully qualified class names in docblocks
- Group use statements by namespace
- Avoid using global namespace

```php
use LogbieCore\BaseModule;
use LogbieCore\Container;

use LogbieClasses\CustomUUID;
```

### 2. Directory Organization
- Keep related files together
- Maintain shallow directory structure
- Use meaningful subdirectory names

### 3. Module Development
- One module per feature
- Clear separation of concerns
- Proper extension of BaseModule

### 4. Maintenance
- Run pathfinder.py after structural changes
- Use cc.py before committing changes
- Keep documentation updated
- Follow PSR-4 standards strictly

### 5. Testing
- Mirror directory structure in /tests
- Match namespace structure
- Maintain test coverage

## Migration Checklist

When adding new components:

1. Determine appropriate namespace
2. Create file in correct directory
3. Verify PSR-4 compliance with cc.py
4. Update directory structure with pathfinder.py
5. Add appropriate tests
6. Update documentation if needed

