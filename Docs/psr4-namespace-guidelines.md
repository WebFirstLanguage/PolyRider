# PSR-4 Compliant Namespace Guidelines for Logbie Framework

## Overview

The Logbie Framework implements PSR-4 autoloading specifications for class autoloading and namespace organization. This document defines the standardized approach for namespace usage across the framework.

## Namespace Structure

### 1. Base Namespace Rules

All namespaces MUST follow these rules:
- MUST start with a vendor namespace "Logbie"
- MUST use StudlyCaps for namespace names
- MUST use StudlyCaps for class names
- MUST match the file system structure exactly

### 2. Directory Structure

```
src/
├── Core/               # Core framework components
│   ├── Application.php
│   ├── BaseModule.php
│   ├── Container.php
│   ├── Logger.php
│   └── Response.php
├── Modules/           # Application modules
│   ├── UserManager.php
│   └── ContentManager.php
└── Classes/          # Shared classes
    ├── Models/
    ├── Services/
    ├── Database/
    └── Utility/
```

### 3. Namespace Mappings

```php
\LogbieCore         => src/Core/
\Logbie             => src/Modules/
\LogbieClasses      => src/Classes/
\LogbieExtensions   => src/Ext
```

## Implementation

### 1. Core Components

```php
<?php

declare(strict_types=1);

namespace Logbie\Core;

class Application
{
    // Implementation
}
```

### 2. Modules

```php
<?php

declare(strict_types=1);

namespace Logbie\Modules;

use Logbie\Core\BaseModule;

class UserManager extends BaseModule
{
    // Implementation
}
```

### 3. Shared Classes

```php
<?php

declare(strict_types=1);

namespace Logbie\Classes\Models;

class User
{
    // Implementation
}
```

## Composer Configuration

```json
{
    "autoload": {
        "psr-4": {
            "Logbie\\": "src/"
        }
    }
}
```

## Import Guidelines

### 1. Use Statements

- MUST be immediately after the namespace declaration
- MUST be alphabetically ordered
- MUST be grouped by namespace depth
- MUST use fully qualified class names

```php
<?php

declare(strict_types=1);

namespace Logbie\Modules;

use Logbie\Core\BaseModule;
use Logbie\Core\Container;

use Logbie\Classes\Models\User;
use Logbie\Classes\Services\AuthService;
```

### 2. Class Resolution

When referencing classes:
- MUST use fully qualified class names in docblocks
- SHOULD use import statements for classes from other namespaces
- MAY use fully qualified class names in code for clarity

```php
<?php

declare(strict_types=1);

namespace Logbie\Modules;

use Logbie\Core\BaseModule;
use Logbie\Classes\Models\User;

class UserManager extends BaseModule
{
    /**
     * @param \Logbie\Classes\Models\User $user
     * @return void
     */
    public function processUser(User $user): void
    {
        // Implementation
    }
}
```

## File Structure Requirements

### 1. File Organization

Each file MUST:
- Contain exactly one class/interface/trait
- Have a name exactly matching the class name
- Use `.php` extension
- Be in a directory matching its namespace

### 2. File Header

```php
<?php

declare(strict_types=1);

namespace Logbie\{Category};

// Use statements here
```

## Interfaces and Traits

### 1. Interface Naming

- MUST end with `Interface`
- MUST be in same namespace as primary implementation

```php
<?php

declare(strict_types=1);

namespace Logbie\Classes\Services;

interface AuthServiceInterface
{
    // Interface definition
}
```

### 2. Trait Naming

- MUST end with `Trait`
- MUST be in a `Traits` subdirectory

```php
<?php

declare(strict_types=1);

namespace Logbie\Classes\Traits;

trait LoggableTrait
{
    // Trait implementation
}
```

## Best Practices

### 1. Class Names

- MUST use StudlyCaps
- MUST match filename exactly
- MUST be descriptive
- SHOULD be nouns for models
- SHOULD end in `Service` for services
- SHOULD end in `Controller` for controllers

### 2. Namespace Organization

- MUST reflect logical domain separation
- MUST maintain single responsibility principle
- SHOULD group related functionality
- SHOULD limit namespace depth to 3-4 levels

### 3. File Placement

New files MUST be placed according to their namespace:
- `\Logbie\Core\NewClass` → `src/Core/NewClass.php`
- `\Logbie\Modules\NewModule` → `src/Modules/NewModule.php`
- `\Logbie\Classes\Models\NewModel` → `src/Classes/Models/NewModel.php`

## Migration Guide

To update existing code:

1. Rename Directories:
   - `core/` → `src/Core/`
   - `modules/` → `src/Modules/`
   - `classes/` → `src/Classes/`

2. Update Namespaces:
   - `logbieCore` → `Logbie\Core`
   - `Logbie` → `Logbie\Modules`
   - `Classes` → `Logbie\Classes`

3. Update Composer:
```json
{
    "autoload": {
        "psr-4": {
            "Logbie\\": "src/"
        }
    }
}
```

4. Run:
```bash
composer dump-autoload
```

## Testing

Test namespaces MUST follow the same rules:

```php
<?php

declare(strict_types=1);

namespace Logbie\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use Logbie\Classes\Models\User;

class UserTest extends TestCase
{
    // Test implementation
}
```

## Common Issues

1. Invalid Namespace/Directory Mapping:
```php
// INCORRECT
namespace Logbie\Core;
// in src/core/Application.php

// CORRECT
namespace Logbie\Core;
// in src/Core/Application.php
```

2. Invalid Class Name Case:
```php
// INCORRECT
class userManager extends baseModule

// CORRECT
class UserManager extends BaseModule
```

3. Missing Namespace Declaration:
```php
// INCORRECT
<?php
class SomeClass

// CORRECT
<?php
declare(strict_types=1);
namespace Logbie\Core;
class SomeClass
```
