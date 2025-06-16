
# User Management Core Service Guide
Version: 1.0
Last Updated: 2024-10-27

## Purpose
This document provides comprehensive guidance for using the Logbie Framework's User Management core service, including user creation, authentication, and account management operations.

## Scope
### Includes:
- User account creation and validation
- Authentication flows
- Account management operations
- Security considerations
- Best practices

### Prerequisites:
- PHP 8.2+
- Logbie Framework
- MySQL/MariaDB database
- Understanding of password hashing concepts

## Core Features

### Constants
```php
private const PASSWORD_ALGO = PASSWORD_BCRYPT;
private const PASSWORD_COST = 12;
private const MIN_USERNAME_LENGTH = 3;
private const MIN_PASSWORD_LENGTH = 8;
```

## Usage Guide

### 1. User Creation

```php
try {
    $userManagement = new UserManagement($db, $logger);
    $userId = $userManagement->createUser(
        'john_doe',
        'john@example.com',
        'securePassword123'
    );
} catch (InvalidArgumentException $e) {
    // Handle validation errors
} catch (RuntimeException $e) {
    // Handle creation errors
}
```

#### Validation Rules:
- Username: Minimum 3 characters
- Email: Must be valid email format
- Password: Minimum 8 characters
- Username and email must be unique

### 2. User Authentication

```php
$result = $userManagement->authenticateUser('john_doe', 'userPassword');

if ($result === null) {
    // Invalid credentials
} elseif (isset($result['error'])) {
    // Account inactive or other issue
    echo $result['error'];
} else {
    // Successful authentication
    $userId = $result['id'];
    $username = $result['username'];
    $emailVerified = $result['email_verified'];
}
```

### 3. User Retrieval

```php
$user = $userManagement->getUserById(123);

if ($user === null) {
    // User not found
} else {
    // Access user data
    $username = $user['username'];
    $email = $user['email'];
    $createdAt = $user['created_at'];
    $lastLogin = $user['last_login'];
}
```

### 4. Account Management

#### Deactivating Users
```php
try {
    $userManagement->deactivateUser(123, 'Account violation');
} catch (RuntimeException $e) {
    // Handle deactivation error
}
```

#### Updating Email
```php
try {
    $userManagement->updateEmail(123, 'newemail@example.com');
} catch (InvalidArgumentException $e) {
    // Handle invalid email
} catch (RuntimeException $e) {
    // Handle update error
}
```

#### Updating Password
```php
try {
    $userManagement->updatePassword(123, 'newSecurePassword');
} catch (InvalidArgumentException $e) {
    // Handle invalid password
} catch (RuntimeException $e) {
    // Handle update error
}
```

#### Verifying Email
```php
try {
    $userManagement->verifyEmail(123);
} catch (RuntimeException $e) {
    // Handle verification error
}
```

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    email_verified BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL
);
```

## Security Considerations

### 1. Password Handling
- Passwords are hashed using bcrypt
- Cost factor of 12 for optimal security/performance balance
- Password hashes are never exposed via API
- Minimum password length of 8 characters

### 2. Account Protection
- Users cannot be deleted (soft deletion via deactivation)
- Failed login attempts do not reveal whether username exists
- Email verification required for sensitive operations
- Account status checked during authentication

### 3. Data Validation
- Email format validated before storage
- Username uniqueness enforced
- Password complexity requirements
- Input sanitization on all fields

## Best Practices

### 1. Error Handling
```php
try {
    $userId = $userManagement->createUser($username, $email, $password);
} catch (InvalidArgumentException $e) {
    // Handle validation errors (bad input)
    logError('Validation failed: ' . $e->getMessage());
} catch (RuntimeException $e) {
    // Handle system errors (database issues, etc)
    logError('System error: ' . $e->getMessage());
} catch (\Exception $e) {
    // Handle unexpected errors
    logError('Unexpected error: ' . $e->getMessage());
}
```

### 2. Transaction Management
- All multi-step operations use transactions
- Automatic rollback on errors
- Logging of all critical operations
- Consistent state maintenance

### 3. User Feedback
- Specific error messages for validation issues
- Generic messages for security-sensitive errors
- Clear success/failure indicators
- Actionable error responses

## Example Implementation

### User Registration Module
```php
namespace Logbie;

use LogbieCore\BaseModule;
use LogbieCore\UserManagement;

final class UserRegistration extends BaseModule
{
    private readonly UserManagement $userManagement;

    public function __construct($db, $container)
    {
        parent::__construct($db, $container);
        $this->userManagement = new UserManagement($db, $this->logger);
    }

    public function run(array $arguments = []): mixed
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $userId = $this->userManagement->createUser(
                $data['username'],
                $data['email'],
                $data['password']
            );

            return $this->response
                ->setStatus(201)
                ->setJson([
                    'success' => true,
                    'userId' => $userId,
                    'message' => 'User created successfully'
                ])
                ->send();

        } catch (InvalidArgumentException $e) {
            return $this->response
                ->setStatus(400)
                ->setJson([
                    'error' => true,
                    'message' => $e->getMessage()
                ])
                ->send();
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage());
            return $this->response
                ->setStatus(500)
                ->setJson([
                    'error' => true,
                    'message' => 'Registration failed'
                ])
                ->send();
        }
    }
}
```

## Troubleshooting

### Common Issues

1. **Duplicate Username/Email**
   ```
   Problem: RuntimeException - Username/email already taken
   Solution: Verify uniqueness before submission
   ```

2. **Invalid Credentials**
   ```
   Problem: Authentication returns null
   Solution: Verify username/password combination
   ```

3. **Account Deactivation**
   ```
   Problem: Cannot authenticate - account inactive
   Solution: Check account status and contact support
   ```

## Support

For additional assistance:
1. Check framework documentation
2. Review example implementations
3. Submit issues via the framework's issue tracker
4. Contact support team for critical issues

## Version History

### Version 1.0
- Initial documentation
- Core user management functionality
- Security best practices
- Example implementations

Remember to keep this documentation updated as the UserManagement core service evolves.