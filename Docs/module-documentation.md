# Module Development Guide for Logbie Framework

## Table of Contents
1. [Basic Module Structure](#basic-module-structure)
2. [Module Naming and Placement](#module-naming-and-placement)
3. [Core Components](#core-components)
4. [Request Handling](#request-handling)
5. [Database Operations](#database-operations)
6. [Response Handling](#response-handling)
7. [Error Handling](#error-handling)
8. [Best Practices](#best-practices)

## Basic Module Structure

Every module must extend the `BaseModule` class and implement the `run` method. Here's the basic structure:

```php
namespace Logbie;

use logbieCore\BaseModule;
use R;

class YourModule extends BaseModule
{
    public function run(array $arguments = [])
    {
        try {
            // Your module logic here
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

## Module Naming and Placement

1. **File Location**: Place your module file in the `modules/` directory
2. **File Naming**: The file name must match the class name (e.g., `UserManager.php` for class `UserManager`)
3. **Namespace**: Use `namespace Logbie;` for all modules
4. **Class Name**: Must match the URL segment that will access it:
   - URL `/usermanager/list` → Class name `UserManager`
   - File location: `modules/UserManager.php`

## Core Components

### Available Properties
All modules inherit these properties from `BaseModule`:

```php
protected $db;        // RedBeanPHP database instance
protected $container; // Service container
protected $response;  // Response handler
protected $logger;    // Logging service
```

### Using the Logger
```php
$this->logger->log("Operation completed successfully");
```

Log levels:
- 0: No logging
- 1: Database only (default)
- 2: Database and response
- 3: Response only

## Request Handling

### Argument Processing
The `run` method receives URL segments as arguments:
```php
public function run(array $arguments = [])
{
    $action = $arguments[0] ?? 'default';
    $id = $arguments[1] ?? null;
    
    switch ($action) {
        case 'list':
            $this->listItems();
            break;
        case 'view':
            $this->viewItem($id);
            break;
        default:
            $this->defaultAction();
    }
}
```

### POST Data Handling
```php
public function handlePost()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->response->setStatus(400)
            ->setJson([
                'error' => true,
                'message' => 'Method not allowed'
            ])
            ->send();
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    // Process $data
}
```

## Database Operations

### Basic CRUD Operations
Using RedBeanPHP (R):

```php
// Create
$item = R::dispense('tablename');
$item->property = 'value';
$id = R::store($item);

// Read
$item = R::load('tablename', $id);
$allItems = R::findAll('tablename');

// Update
$item = R::load('tablename', $id);
$item->property = 'new value';
R::store($item);

// Delete
$item = R::load('tablename', $id);
R::trash($item);
```

## Response Handling

### JSON Responses
```php
// Success response
$this->response->setJson([
    'error' => false,
    'data' => $data
])->send();

// Error response
$this->response->setStatus(400)
    ->setJson([
        'error' => true,
        'message' => 'Error message'
    ])
    ->send();
```

### Status Codes
Available status codes:
- 200: OK
- 201: Created
- 400: Bad Request
- 404: Not Found
- 500: Internal Server Error

## Error Handling

### Try-Catch Pattern
```php
try {
    // Risky operation
} catch (\Exception $e) {
    $this->logger->log("Error: " . $e->getMessage());
    $this->response->setStatus(500)
        ->setJson([
            'error' => true,
            'message' => 'User-friendly error message'
        ])
        ->send();
}
```

## Best Practices

1. **Method Organization**
   - Keep methods focused and single-purpose
   - Use private methods for internal logic
   - Group related functionality

```php
class UserManager extends BaseModule
{
    public function run(array $arguments = [])
    {
        // Main routing logic
    }

    private function listUsers()
    {
        // List users logic
    }

    private function validateUserData($data)
    {
        // Validation logic
    }
}
```

2. **Input Validation**
   - Always validate input data
   - Create dedicated validation methods

```php
private function validateItemData($data)
{
    return is_array($data) &&
        isset($data['required_field']) &&
        is_string($data['required_field']) &&
        strlen($data['required_field']) > 0;
}
```

3. **Response Structure**
   - Be consistent with response formats
   - Always include error status
   - Provide meaningful messages

```php
private function standardResponse($data = null, $message = '', $error = false)
{
    $response = [
        'error' => $error,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    $this->response->setJson($response)->send();
}
```

4. **Security Considerations**
   - Sanitize all input
   - Validate user permissions
   - Use prepared statements (handled by RedBeanPHP)
   - Log sensitive operations

```php
private function checkPermissions($action)
{
    // Example permission check
    if (!$this->userHasPermission($action)) {
        $this->logger->log("Permission denied for action: $action");
        $this->response->setStatus(403)
            ->setJson([
                'error' => true,
                'message' => 'Permission denied'
            ])
            ->send();
        return false;
    }
    return true;
}
```

5. **Documentation**
   - Use PHPDoc comments for methods
   - Document expected inputs and outputs
   - Explain complex logic

```php
/**
 * Processes user data and creates a new user
 * 
 * @param array $userData Associative array containing user details
 * @return int|false Returns user ID on success, false on failure
 * @throws \InvalidArgumentException If required fields are missing
 */
private function createUser(array $userData)
{
    // Method implementation
}
```
