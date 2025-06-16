# Logger Documentation for Logbie Framework
Version: 2.0
Last Updated: 2024-10-29

## Purpose
This document details the real-time logging system in the Logbie Framework, which provides immediate structured logging capabilities with file and response output options using modern PHP 8.2+ features.

## Scope
### Includes:
- Real-time Logger class implementation
- LogMode enum configuration
- File and response output handling
- Legacy support information
- Best practices and examples

### Prerequisites:
- PHP 8.2+
- Logbie Framework
- Write access to log directory

## Core Components

### LogMode Enum (Required)
```php
enum LogMode: int
{
    case NONE = 0;          // No logging
    case FILE_ONLY = 1;     // Write to log files only
    case BOTH = 2;          // Write to both files and response
    case RESPONSE_ONLY = 3; // Write to response only
}
```

### Logger Class Overview

| Method | Description | Parameters | Return |
|--------|-------------|------------|--------|
| `__construct()` | Creates new logger instance | `Response $response, LogMode $logMode = LogMode::FILE_ONLY, ?string $logDir = null` | `void` |
| `log()` | Logs a message in real-time | `string $message` | `void` |

### Legacy Support
The `fromLegacy()` static method exists solely for compatibility with existing scripts that haven't been updated. New code must use the enum-based constructor.

```php
// LEGACY ONLY - Do not use in new code
Logger::fromLegacy($response, 2); // Convert old integer levels to LogMode
```

## Standard Implementation

### Required Usage Pattern
```php
// Correct usage for all new code
$logger = new Logger(
    response: $response,
    logMode: LogMode::FILE_ONLY
);
$logger->log("Processing user request");
```

### File Structure
Log files are automatically organized by date with immediate write operations:
```
/storage/logs/
├── 2024-10-29.log  // Current active log file
├── 2024-10-28.log  // Previous day
└── 2024-10-27.log  // Older logs
```

### Real-Time Message Format
```
[2024-10-29 14:30:45] User login successful
```

## Configuration

### Standard Constructor
```php
new Logger(
    response: $response,
    logMode: LogMode::BOTH,
    logDir: '/custom/log/path'
);
```

### System Settings
- File permissions: `0755` (enforced)
- Default directory: `[project_root]/storage/logs`
- File naming: `YYYY-MM-DD.log`
- Write mode: Immediate (no buffering)

## Best Practices

### 1. Structured Message Format
```php
$logger->log(sprintf(
    "User action: %s | ID: %d | Status: %s",
    $action,
    $userId,
    $status
));
```

### 2. Error Logging
```php
try {
    // Operation code
} catch (\Exception $e) {
    $logger->log(sprintf(
        "Error: %s | File: %s:%d",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));
}
```

### 3. Context Tracking
```php
$logger->log(sprintf(
    "[RequestID: %s] Processing payment for order %d",
    $requestId,
    $orderId
));
```

## Security Considerations

1. **Sensitive Data**
   - Never log passwords or credentials
   - Mask sensitive identifiers
   - Follow data protection regulations

2. **File Permissions**
   - Log directory must be outside web root
   - File permissions strictly enforced to 0755
   - Regular security audits required

3. **Log Rotation**
   - Automatic date-based file rotation
   - Monitor disk space usage
   - Archive or delete old logs

## Error Handling

### Common Issues

1. **Directory Access**
```php
try {
    $logger = new Logger($response, LogMode::FILE_ONLY, '/path/to/logs');
} catch (\RuntimeException $e) {
    // Handle directory creation/access errors
}
```

2. **Write Failures**
```php
try {
    $logger->log("Important message");
} catch (\RuntimeException $e) {
    // Logger automatically falls back to response output
}
```

## Performance Considerations

1. **Mode Selection**
   - Use `LogMode::NONE` for production if logs aren't needed
   - Use `LogMode::FILE_ONLY` for background tasks
   - Use `LogMode::RESPONSE_ONLY` for debugging

2. **Message Optimization**
   - Keep messages concise
   - Use structured formats
   - Include only necessary context

## Module Integration

```php
final class UserManager extends BaseModule
{
    public function __construct($db, $container)
    {
        parent::__construct($db, $container);
        // Logger available through container
    }

    public function processUser(int $userId): void
    {
        $this->logger->log(sprintf(
            "Processing user: %d | Time: %s",
            $userId,
            date('Y-m-d H:i:s')
        ));
    }
}
```

## Legacy Code Migration

### Converting from Legacy Format
```php
// Old code (deprecated)
$logger = Logger::fromLegacy($response, 2);

// New required format
$logger = new Logger($response, LogMode::BOTH);
```

### Migration Steps
1. Replace all integer log levels with LogMode enum
2. Update constructor calls to use new format
3. Remove any references to legacy integer levels
4. Test log output after migration

## Version History

### Version 2.0 (Current)
- Mandatory LogMode enum usage
- Real-time logging enforcement
- Legacy support for transition only
- Enhanced error handling

### Version 1.0 (Deprecated)
- Initial implementation
- Integer-based log levels
- Non-real-time logging

## Support

For additional assistance:
1. Review example implementations
2. Check framework documentation
3. Submit issues via the framework's issue tracker

The Logger class provides thread-safe, real-time logging with automatic file locking and error handling. Legacy support exists only for transition purposes and should not be used in new code.
