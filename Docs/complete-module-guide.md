# Complete Logbie Framework Module Development Guide
Version: 1.0
Last Updated: 2024-10-27

## Table of Contents
1. [Introduction](#introduction)
2. [Module Fundamentals](#module-fundamentals)
3. [Core Components](#core-components)
4. [Request Handling](#request-handling)
5. [Database Operations](#database-operations)
6. [Response Handling](#response-handling)
7. [Error Management](#error-management)
8. [Security Considerations](#security-considerations)
9. [Best Practices](#best-practices)
10. [Example Implementations](#example-implementations)

## Introduction

### What is a Logbie Module?
Modules are standalone components that encapsulate specific functionality in the Logbie Framework. Each module is responsible for handling its own routes, processing requests, managing data, and generating responses.

### Key Characteristics
- Self-contained units of functionality
- Follows PSR-4 autoloading standards
- Extends `LogbieCore\BaseModule`
- Located in `src/Modules/` directory
- Uses `Logbie` namespace

## Module Fundamentals

### Basic Structure
```php
<?php

declare(strict_types=1);

namespace Logbie;

use LogbieCore\BaseModule;

final class UserManager extends BaseModule
{
    public function run(array $arguments = []): mixed
    {
        try {
            return $this->processRequest($arguments);
        } catch (\Exception $e) {
            $this->logger->log("Error: " . $e->getMessage());
            return $this->handleError($e);
        }
    }
}
```

### Naming Conventions
- Class names must use StudlyCaps (e.g., `UserManager`)
- File names must match class names exactly (e.g., `UserManager.php`)
- Class names should be descriptive of functionality
- All modules must be in the `Logbie` namespace

### File Organization
```
src/
└── Modules/
    ├── UserManager.php
    ├── ContentManager.php
    └── SystemTest.php
```

### The Run Method
The `run()` method is the entry point for all module execution:
```php
public function run(array $arguments = []): mixed
{
    try {
        $action = $arguments[0] ?? 'default';
        $id = $arguments[1] ?? null;
        
        return match($action) {
            'create' => $this->createResource(),
            'read' => $this->readResource($id),
            'update' => $this->updateResource($id),
            'delete' => $this->deleteResource($id),
            default => $this->listResources(),
        };
    } catch (\Exception $e) {
        return $this->handleError($e);
    }
}
```

## Core Components

### Inherited Properties
```php
protected readonly DatabaseORM $db;            // Database operations
protected readonly Container $container;       // Service container
protected readonly Response $response;         // Response handling
protected readonly Logger $logger;             // System logging
protected readonly ?TemplateEngine $templateEngine; // Template rendering
```

### Constructor
```php
public function __construct(DatabaseORM $db, ?Container $container = null)
{
    parent::__construct($db, $container);
    $this->initialize();
}

private function initialize(): void
{
    // Module-specific initialization
}
```

## Request Handling

### URL Routing
URLs are mapped to module methods through the `run()` method arguments:
```
URL: /usermanager/edit/123
↓
$arguments = ['edit', '123']
```

### Request Methods
```php
private function handleRequest(string $method, array $arguments): mixed
{
    return match($method) {
        'GET' => $this->handleGet($arguments),
        'POST' => $this->handlePost($arguments),
        'PUT' => $this->handlePut($arguments),
        'DELETE' => $this->handleDelete($arguments),
        default => throw new \RuntimeException('Method not allowed')
    };
}
```

### Input Processing
```php
private function getRequestData(): array
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \InvalidArgumentException('Invalid JSON payload');
    }
    
    return $data;
}
```

## Database Operations

### Create
```php
private function createUser(array $userData): int
{
    return $this->transaction(function() use ($userData) {
        return $this->create('users', [
            'username' => $userData['username'],
            'email' => $userData['email'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
    });
}
```

### Read
```php
private function getUser(int $id): ?array
{
    $result = $this->read(
        'users',
        ['id' => $id],
        ['id', 'username', 'email'],
        ['limit' => 1]
    );
    
    return $result[0] ?? null;
}
```

### Update
```php
private function updateUser(int $id, array $data): int
{
    return $this->update(
        'users',
        $data,
        ['id' => $id]
    );
}
```

### Delete
```php
private function deleteUser(int $id): int
{
    return $this->delete('users', ['id' => $id]);
}
```

### Transactions
```php
private function complexOperation(): mixed
{
    return $this->transaction(function() {
        // Multiple database operations
        $userId = $this->create('users', [...]);
        $this->create('profiles', ['user_id' => $userId, ...]);
        return $userId;
    });
}
```

## Response Handling

### JSON Responses
```php
private function sendJsonResponse(
    mixed $data,
    int $status = 200,
    bool $error = false
): never {
    $this->response
        ->setStatus($status)
        ->setJson([
            'error' => $error,
            'data' => $data
        ])
        ->send();
}
```

### HTML Responses
```php
private function renderTemplate(
    string $template,
    array $data = []
): never {
    $this->response
        ->render($template, $data)
        ->send();
}
```

### Error Responses
```php
private function sendError(
    string $message,
    int $status = 400
): never {
    $this->response
        ->setStatus($status)
        ->setJson([
            'error' => true,
            'message' => $message
        ])
        ->send();
}
```

## Error Management

### Exception Handling
```php
private function handleError(\Exception $e): never
{
    $statusCode = match(true) {
        $e instanceof \InvalidArgumentException => 400,
        $e instanceof \RuntimeException => 500,
        default => 500
    };
    
    $this->logger->log("Error: " . $e->getMessage());
    $this->sendError($e->getMessage(), $statusCode);
}
```

### Validation
```php
private function validateData(array $data, array $rules): void
{
    foreach ($rules as $field => $rule) {
        if (!isset($data[$field])) {
            throw new \InvalidArgumentException("Missing required field: $field");
        }
        
        if (!$rule($data[$field])) {
            throw new \InvalidArgumentException("Invalid value for field: $field");
        }
    }
}
```

## Security Considerations

### Input Validation
```php
private function sanitizeInput(array $data): array
{
    return array_map(function($value) {
        return is_string($value) ? 
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : 
            $value;
    }, $data);
}
```

### Access Control
```php
private function checkPermissions(string $action): void
{
    if (!$this->hasPermission($action)) {
        throw new \RuntimeException('Permission denied');
    }
}
```

## Best Practices

### 1. Method Organization
- Keep methods focused and single-purpose
- Use descriptive names
- Group related functionality
- Implement proper access modifiers

### 2. Error Handling
- Always wrap operations in try-catch blocks
- Log meaningful error messages
- Return appropriate HTTP status codes
- Provide user-friendly error messages

### 3. Database Operations
- Use transactions for multiple operations
- Validate data before database operations
- Handle database errors gracefully
- Use proper indexing and optimization

### 4. Security
- Validate all input
- Sanitize output
- Implement proper access control
- Use secure database operations

## Example Implementations

### 1. Complete CRUD Module
```php
<?php

declare(strict_types=1);

namespace Logbie;

use LogbieCore\BaseModule;

final class ResourceManager extends BaseModule
{
    public function run(array $arguments = []): mixed
    {
        try {
            $this->checkPermissions('access');
            
            $action = $arguments[0] ?? 'list';
            $id = $arguments[1] ?? null;
            
            return match($action) {
                'create' => $this->createResource(),
                'read' => $this->readResource($id),
                'update' => $this->updateResource($id),
                'delete' => $this->deleteResource($id),
                default => $this->listResources(),
            };
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }
    
    private function createResource(): never
    {
        $data = $this->getRequestData();
        $this->validateData($data, [
            'name' => fn($v) => is_string($v) && strlen($v) >= 3,
            'type' => fn($v) => in_array($v, ['type1', 'type2'])
        ]);
        
        $id = $this->create('resources', $data);
        $this->sendJsonResponse(['id' => $id], 201);
    }
    
    private function readResource(?string $id): never
    {
        if (!$id) {
            throw new \InvalidArgumentException('Resource ID required');
        }
        
        $resource = $this->read('resources', ['id' => $id]);
        
        if (!$resource) {
            $this->sendError('Resource not found', 404);
        }
        
        $this->sendJsonResponse($resource);
    }
}
```

### 2. API Module
```php
<?php

declare(strict_types=1);

namespace Logbie;

use LogbieCore\BaseModule;

final class ApiEndpoint extends BaseModule
{
    public function run(array $arguments = []): mixed
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $endpoint = $arguments[0] ?? '';
            
            $this->validateApiKey();
            
            return match($method) {
                'GET' => $this->handleGet($endpoint, $arguments),
                'POST' => $this->handlePost($endpoint),
                'PUT' => $this->handlePut($endpoint),
                'DELETE' => $this->handleDelete($endpoint),
                default => throw new \RuntimeException('Method not allowed')
            };
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }
    
    private function validateApiKey(): void
    {
        $key = $_SERVER['HTTP_X_API_KEY'] ?? null;
        
        if (!$key || !$this->isValidApiKey($key)) {
            throw new \RuntimeException('Invalid API key');
        }
    }
}
```

### 3. Template Module
```php
<?php

declare(strict_types=1);

namespace Logbie;

use LogbieCore\BaseModule;

final class PageRenderer extends BaseModule
{
    public function run(array $arguments = []): mixed
    {
        try {
            $page = $arguments[0] ?? 'home';
            $this->validatePage($page);
            
            $data = $this->getPageData($page);
            $template = $this->getTemplate($page);
            
            return $this->response
                ->render($template, $data)
                ->send();
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }
    
    private function getPageData(string $page): array
    {
        // Fetch and prepare page data
        $data = $this->read('pages', ['slug' => $page]);
        
        return [
            'title' => $data['title'],
            'content' => $data['content'],
            'metadata' => json_decode($data['metadata'], true)
        ];
    }
}
```

## Conclusion

This guide covers the essential aspects of module development in the Logbie Framework. Remember to:
- Follow the framework's conventions and standards
- Implement proper error handling and logging
- Validate all input and sanitize output
- Use transactions for complex database operations
- Keep security in mind at all times
- Write clear, maintainable code
- Make sure you decalre your functions as mixed or never but make sure to use the correct return type.

For additional assistance, consult the framework documentation or submit issues via the framework's issue tracker.
