# PSR-4 Compliance Script Specification for the Logbie Framework

## 1. Objective

This document specifies a script designed to:

- **Validate PHP code compliance** with PSR-4 autoloading standards, specifically tailored for the Logbie Framework.
- **Ensure adherence to the Logbie Framework's namespace and directory structure guidelines**.
- **Automatically correct** any detected non-compliance when possible.
- **Introduce mechanisms** for automated confirmation and dry-run options.
- **Ensure safe operation** with version control checks and configuration flexibility.

## 2. Background

PSR-4 is a widely adopted standard for autoloading classes in PHP, defining how namespaces map to filesystem paths. The Logbie Framework implements PSR-4 autoloading specifications with specific conventions and guidelines for namespace usage.

This script aims to validate and enforce PSR-4 compliance within the Logbie Framework projects to ensure codebases are structured for:

- **Scalability**
- **Maintainability**
- **Interoperability**

By incorporating the Logbie Framework's specific namespace guidelines, the script will help developers maintain consistency and adherence to framework standards.

## 3. Features

### 3.1 PSR-4 Compliance Checker

#### Recursive Scanning

- Recursively scan PHP project directories for all `.php` files within the `src/` directory, as per the Logbie Framework's structure.

#### Validation Checks

- **Namespace Alignment**: Verify that each class's namespace matches the directory structure exactly, following the Logbie Framework's namespace mappings.
- **Class and File Name Consistency**: Ensure class names match file names exactly, using StudlyCaps, as required by the Logbie Framework.
- **Composer Configuration**: Check that the `composer.json` file includes the correct PSR-4 autoloading mappings, specifically `"Logbie\\": "src/"`.
- **Use Statement Order**: Confirm that `use` statements follow PSR-4 and Logbie Framework guidelines:
  - Immediately after the namespace declaration.
  - Alphabetically ordered.
  - Grouped by namespace depth.
  - Use fully qualified class names.
- **File Header and Structure**: Validate that each file:
  - Contains exactly one class/interface/trait.
  - Has a name exactly matching the class name.
  - Uses the `.php` extension.
  - Is in a directory matching its namespace.
  - Includes the file header as per Logbie's requirements.
- **Interface and Trait Naming**: Ensure interfaces end with `Interface` and traits end with `Trait`, placed in the appropriate directories (`Interfaces`, `Traits`).

### 3.2 Correction Mechanism

#### Automatic Corrections

- **Namespace Mismatches**: Adjust namespaces to align with directory structures according to the Logbie Framework's rules.
- **File Renaming**: Rename files to match class names exactly, ensuring they use StudlyCaps.
- **Composer Configuration Updates**: Add or correct missing PSR-4 mappings in `composer.json`.
- **Reordering `use` Statements**: Alphabetically reorder `use` statements, grouped by namespace depth.
- **File Header Standardization**: Ensure each file includes the appropriate file header, including the `declare(strict_types=1);` statement.
- **Interface and Trait Handling**: Rename interfaces and traits to end with `Interface` and `Trait` respectively, and place them in the correct directories.

#### User Interaction

- Prompt for ignoring specific errors.
- Option to add namespaces or paths to an ignore list in the configuration file.

### 3.3 Automated Confirmation Option

#### Command-Line Argument

```bash
python psr4_compliance.py --yes
```

#### Behavior in `--yes` Mode

- Automatically proceed with all corrections.
- Respect the ignore list from the configuration.
- Return appropriate exit codes.
- Log unfixable issues.

### 3.4 Dry Run Option

#### Command-Line Argument

```bash
python psr4_compliance.py --dry-run
```

#### Behavior in `--dry-run` Mode

- Perform validation checks.
- Display potential corrections.
- Make no file modifications.

### 3.5 Configuration File Support

#### Loading Options

- Auto-load `config.json` from the current directory.
- Support custom config via `--config` argument.

```bash
python psr4_compliance.py --config custom_config.json
```

#### Configuration Structure

```json
{
  "custom_namespace_mappings": {
    "Logbie\\Classes\\Models": "src/Classes/Models",
    "Logbie\\Classes\\Services": "src/Classes/Services"
  },
  "exemptions": [
    "src/Legacy",
    "tests"
  ],
  "ignored_namespaces": [
    "Logbie\\Ignore"
  ]
}
```

### 3.6 Git Uncommitted Changes Check

#### Safety Check

- Verify no uncommitted changes via `git status --porcelain`.
- Refuse to proceed if changes are detected.

#### Override Option

```bash
python psr4_compliance.py --force
```

- 10-second confirmation delay.
- `--yes` cannot override Git check.

## 4. Implementation Details

### 4.1 Namespace Checker

- **Directory Traversal**: Use the `os` module to traverse directories under `src/`.
- **Namespace Extraction**: Extract namespace declarations using regular expressions.
- **Validation Against Directory Paths**: Compare extracted namespaces with the directory paths to ensure they match exactly, following the Logbie Framework's namespace mappings.
- **Composer.json Mapping Verification**: Ensure that the `composer.json` file has the correct PSR-4 mappings, especially `"Logbie\\": "src/"`.

### 4.2 Correction Logic

- **Namespace Declaration Modifications**: Update namespace declarations in files to match their directory structures and the Logbie Framework's guidelines.
- **File Renaming**: Use `os` and `shutil` to rename files to match class names, ensuring they use StudlyCaps.
- **Composer Configuration Updates**: Modify `composer.json` to include any missing PSR-4 mappings relevant to the Logbie Framework.
- **Use Statement Reordering**: Reorder `use` statements alphabetically, grouped by namespace depth, and ensure they follow Logbie's import guidelines.
- **File Header Standardization**: Ensure each file includes the appropriate file header, including the `declare(strict_types=1);` statement.
- **Interface and Trait Handling**: Rename interfaces and traits to end with `Interface` and `Trait` respectively, and place them in the correct directories.

### 4.3 User Interaction

Example prompts:

```
Detected mismatch in namespace for file: src/Modules/UserManager.php
Correct namespace from `Logbie` to `Logbie\Modules`? (y/n)
Ignore this error in future checks? (y/n)
```

```
File name 'usermanager.php' does not match class name 'UserManager'.
Rename file to 'UserManager.php'? (y/n)
```

## 5. Error Handling

- **Exception Management**: Catch exceptions during file operations and provide meaningful error messages.
- **Descriptive Error Messages**: Output errors to stderr with detailed descriptions.
- **Logging with Severity Levels**: Implement logging that differentiates between warnings, errors, and info messages.
- **Standardized Exit Codes**: Use standard exit codes to indicate success or specific types of failure.

## 6. Example User Flows

### Manual Confirmation

```bash
python psr4_compliance.py
```

- The script will prompt the user for each correction.

### Automatic Correction

```bash
python psr4_compliance.py --yes
```

- The script will automatically apply all corrections.

### Dry Run

```bash
python psr4_compliance.py --dry-run
```

- The script will display potential corrections without making changes.

### Custom Configuration

```bash
python psr4_compliance.py --config custom_config.json
```

- The script will use the specified configuration file.

### Force Mode

```bash
python psr4_compliance.py --force
```

- The script will proceed despite uncommitted Git changes, after a confirmation delay.

## 7. Deliverables

### Script Capabilities

- PSR-4 compliance validation tailored to the Logbie Framework's guidelines.
- Automatic correction of namespace and file structure issues.
- Automated confirmation option.
- Dry run mode.
- Configuration flexibility.
- Version control safety.
- Comprehensive logging.
- Standard exit codes.
- Cross-platform compatibility.
- PEP8 compliance for the Python script.

### Documentation

- **User Guide**: Instructions on how to use the script, including command-line options and configuration.
- **Configuration Examples**: Sample `config.json` files showing how to customize the script.
- **Developer Guide**: Details on the script's implementation, for contributors and maintainers.

## 8. Testing Requirements

### Unit Tests

- **Function/Method Coverage**: Each function and method should have associated unit tests.
- **Namespace Validation**: Tests for correct and incorrect namespace and directory combinations.
- **File Operations**: Tests for renaming files and updating file contents.
- **Configuration Parsing**: Tests for loading and interpreting configuration files.
- **Git Status Checking**: Tests for detecting uncommitted changes.

### Integration Tests

- **Sample Project Testing**: Use a sample Logbie Framework project to test the script.
- **Command-Line Argument Combinations**: Test various combinations of command-line arguments.
- **Validation of Corrections**: Ensure that the script correctly applies corrections.
- **Configuration Behavior**: Test the script with different configurations.

## 9. Additional Features

### Help System

```bash
python psr4_compliance.py --help
```

- Provides detailed usage instructions and options.

### Logging Levels

```bash
python psr4_compliance.py --log-level DEBUG
```

- Allows users to set the verbosity of the logs.

### Backup Option

```bash
python psr4_compliance.py --backup
```

- Creates backups of files before making changes.

## 10. Acceptance Criteria

- **Feature Completeness**: All specified features are implemented.
- **Code Quality Standards**: The script follows best practices and is PEP8 compliant.
- **Documentation Completeness**: All documentation is complete and clear.
- **User Experience Requirements**: The script is user-friendly and behaves as expected.

# PSR-4 Compliant Namespace Guidelines for the Logbie Framework

## 11. Namespace Structure

### 11.1 Base Namespace Rules

All namespaces **MUST** follow these rules:

- **MUST** start with the vendor namespace `Logbie`.
- **MUST** use StudlyCaps for namespace names.
- **MUST** use StudlyCaps for class names.
- **MUST** match the file system structure exactly.

### 11.2 Directory Structure

The script should enforce that the project adheres to the following directory structure:

```
src/
├── Core/               # Core framework components
│   ├── Application.php
│   ├── BaseModule.php
│   ├── Container.php
│   ├── Logger.php
│   └── Response.php
├── Modules/            # Application modules
│   ├── UserManager.php
│   └── ContentManager.php
└── Classes/            # Shared classes
    ├── Models/
    ├── Services/
    ├── Database/
    └── Utility/
```

### 11.3 Namespace Mappings

The script should ensure that namespaces map to directories as follows:

```php
\Logbie\Core\          => src/Core/
\Logbie\Modules\       => src/Modules/
\Logbie\Classes\       => src/Classes/
```

## 12. Implementation Examples

### 12.1 Core Components

```php
<?php

declare(strict_types=1);

namespace Logbie\Core;

class Application
{
    // Implementation
}
```

### 12.2 Modules

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

### 12.3 Shared Classes

```php
<?php

declare(strict_types=1);

namespace Logbie\Classes\Models;

class User
{
    // Implementation
}
```

## 13. Composer Configuration

The script should verify that `composer.json` includes the following:

```json
{
    "autoload": {
        "psr-4": {
            "Logbie\\": "src/"
        }
    }
}
```

## 14. Import Guidelines

### 14.1 Use Statements

- **MUST** be immediately after the namespace declaration.
- **MUST** be alphabetically ordered.
- **MUST** be grouped by namespace depth.
- **MUST** use fully qualified class names.

Example:

```php
<?php

declare(strict_types=1);

namespace Logbie\Modules;

use Logbie\Core\BaseModule;
use Logbie\Core\Container;

use Logbie\Classes\Models\User;
use Logbie\Classes\Services\AuthService;
```

### 14.2 Class Resolution

When referencing classes:

- **MUST** use fully qualified class names in docblocks.
- **SHOULD** use import statements for classes from other namespaces.
- **MAY** use fully qualified class names in code for clarity.

Example:

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

## 15. File Structure Requirements

### 15.1 File Organization

Each file **MUST**:

- Contain exactly one class/interface/trait.
- Have a name exactly matching the class name.
- Use the `.php` extension.
- Be in a directory matching its namespace.

### 15.2 File Header

The script should ensure each file includes the following header:

```php
<?php

declare(strict_types=1);

namespace Logbie\{Category};

// Use statements here
```

## 16. Interfaces and Traits

### 16.1 Interface Naming

- **MUST** end with `Interface`.
- **MUST** be in the same namespace as the primary implementation.

Example:

```php
<?php

declare(strict_types=1);

namespace Logbie\Classes\Services;

interface AuthServiceInterface
{
    // Interface definition
}
```

### 16.2 Trait Naming

- **MUST** end with `Trait`.
- **MUST** be in a `Traits` subdirectory.

Example:

```php
<?php

declare(strict_types=1);

namespace Logbie\Classes\Traits;

trait LoggableTrait
{
    // Trait implementation
}
```

## 17. Best Practices

### 17.1 Class Names

- **MUST** use StudlyCaps.
- **MUST** match filename exactly.
- **MUST** be descriptive.
- **SHOULD** be nouns for models.
- **SHOULD** end in `Service` for services.
- **SHOULD** end in `Controller` for controllers.

### 17.2 Namespace Organization

- **MUST** reflect logical domain separation.
- **MUST** maintain single responsibility principle.
- **SHOULD** group related functionality.
- **SHOULD** limit namespace depth to 3-4 levels.

### 17.3 File Placement

New files **MUST** be placed according to their namespace:

- `\Logbie\Core\NewClass` → `src/Core/NewClass.php`
- `\Logbie\Modules\NewModule` → `src/Modules/NewModule.php`
- `\Logbie\Classes\Models\NewModel` → `src/Classes/Models/NewModel.php`

## 18. Migration Guide

For existing projects, the script can assist in migrating to the Logbie Framework's namespace guidelines by:

1. **Renaming Directories**:

   - `core/` → `src/Core/`
   - `modules/` → `src/Modules/`
   - `classes/` → `src/Classes/`

2. **Updating Namespaces**:

   - `logbieCore` → `Logbie\Core`
   - `Logbie` → `Logbie\Modules`
   - `Classes` → `Logbie\Classes`

3. **Updating Composer Configuration**:

   ```json
   {
       "autoload": {
           "psr-4": {
               "Logbie\\": "src/"
           }
       }
   }
   ```

4. **Running Composer Autoload Dump**:

   ```bash
   composer dump-autoload
   ```

## 19. Testing

Test namespaces **MUST** follow the same rules:

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

## 20. Common Issues and Script Handling

The script should detect and correct common issues, including:

### 20.1 Invalid Namespace/Directory Mapping

**Incorrect**:

```php
// File: src/core/Application.php
namespace Logbie\Core;
```

**Correct**:

```php
// File: src/Core/Application.php
namespace Logbie\Core;
```

### 20.2 Invalid Class Name Case

**Incorrect**:

```php
class userManager extends baseModule
```

**Correct**:

```php
class UserManager extends BaseModule
```

### 20.3 Missing Namespace Declaration

**Incorrect**:

```php
<?php
class SomeClass
```

**Correct**:

```php
<?php

declare(strict_types=1);

namespace Logbie\Core;

class SomeClass
```

## 21. Conclusion

By integrating the Logbie Framework's specific namespace guidelines into the PSR-4 Compliance Script, we can ensure that projects not only adhere to PSR-4 standards but also conform to the framework's conventions. This alignment will enhance code consistency, maintainability, and overall quality across all Logbie Framework projects.

---

This merged document provides a comprehensive specification for a PSR-4 compliance script tailored to the Logbie Framework, incorporating the framework's namespace guidelines and ensuring that developers have a tool to maintain compliance effectively.