# Logbie Response Core User Guide
Version: 1.0
Last Updated: 2024-10-27

## Overview

The Response core provides a fluent interface for building and sending HTTP responses in the Logbie Framework. It supports JSON, XML, HTML templates, and raw content responses with full control over headers, status codes, and cookies.

## Basic Usage

### Simple Text Response
```php
$response = new Response();
$response
    ->setContent('Hello World', 'text/plain')
    ->send();
```

### JSON Response
```php
$response
    ->setJson([
        'status' => 'success',
        'data' => ['id' => 123]
    ])
    ->send();
```

### Status Codes
```php
$response
    ->setStatus(404)
    ->setJson([
        'error' => true,
        'message' => 'Resource not found'
    ])
    ->send();
```

## Content Types

### Setting Content Type
```php
$response
    ->setContentType('application/pdf')
    ->setContent($pdfContent)
    ->send();
```

### XML Response
```php
$data = [
    'user' => [
        'id' => 123,
        'name' => 'John Doe'
    ]
];

$response
    ->setXml($data)
    ->send();
```

### Template Rendering
```php
$response
    ->render('user/profile.html', [
        'user' => $userData,
        'title' => 'User Profile'
    ])
    ->send();
```

## Headers

### Adding Custom Headers
```php
$response
    ->addHeader('X-Custom-Header', 'Value')
    ->addHeader('Cache-Control', 'no-cache')
    ->setJson($data)
    ->send();
```

## Cookie Management

### Setting Cookies
```php
$response
    ->setCookie('session', $sessionId, [
        'expires' => time() + 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ])
    ->send();
```

### Removing Cookies
```php
$response
    ->removeCookie('old_cookie')
    ->send();
```

## Error Handling

### Error Response Pattern
```php
$response
    ->setStatus(400)
    ->setJson([
        'error' => true,
        'message' => 'Invalid input',
        'details' => $validationErrors
    ])
    ->send();
```

## Content Manipulation

### Appending Content
```php
$response
    ->setContent('Initial content')
    ->appendContent(' - Additional content')
    ->send();
```

## Best Practices

### 1. Always Set Content Type
```php
// Good
$response
    ->setContentType('application/json')
    ->setContent($jsonString)
    ->send();

// Not Recommended
$response
    ->setContent($jsonString)
    ->send();
```

### 2. Use Appropriate Status Codes
```php
// Success
$response->setStatus(200);  // OK
$response->setStatus(201);  // Created

// Client Errors
$response->setStatus(400);  // Bad Request
$response->setStatus(403);  // Forbidden
$response->setStatus(404);  // Not Found

// Server Errors
$response->setStatus(500);  // Internal Server Error
```

### 3. Consistent Response Structure
```php
// Success Response
$response->setJson([
    'success' => true,
    'data' => $result
]);

// Error Response
$response->setJson([
    'error' => true,
    'message' => $errorMessage,
    'code' => $errorCode
]);
```

### 4. Secure Cookie Settings
```php
$response->setCookie('auth', $token, [
    'expires' => time() + 3600,
    'path' => '/',
    'domain' => null,
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
```

## Security Considerations

### 1. Content Security
- Always sanitize content before sending
- Use appropriate content-type headers
- Validate template variables

### 2. Cookie Security
- Use secure and httpOnly flags
- Set appropriate SameSite attribute
- Limit cookie lifetime
- Use path restrictions

### 3. Header Security
```php
$response
    ->addHeader('X-Content-Type-Options', 'nosniff')
    ->addHeader('X-Frame-Options', 'DENY')
    ->addHeader('X-XSS-Protection', '1; mode=block');
```

## Common Patterns

### 1. API Response
```php
$response
    ->setStatus($statusCode)
    ->addHeader('X-Request-ID', $requestId)
    ->setJson([
        'success' => $success,
        'data' => $data,
        'metadata' => [
            'timestamp' => time(),
            'version' => '1.0'
        ]
    ])
    ->send();
```

### 2. File Download
```php
$response
    ->setContentType('application/pdf')
    ->addHeader('Content-Disposition', 'attachment; filename="document.pdf"')
    ->setContent($fileContent)
    ->send();
```

### 3. Redirect Response
```php
$response
    ->setStatus(302)
    ->addHeader('Location', '/new-page')
    ->send();
```

## Performance Tips

1. **Minimize Response Size**
   - Only include necessary data
   - Use appropriate compression
   - Consider pagination for large datasets

2. **Efficient Template Rendering**
   - Cache templates when possible
   - Minimize template variables
   - Use efficient template engine settings

3. **Header Optimization**
   - Set appropriate cache headers
   - Use content compression when beneficial
   - Minimize custom headers

## Troubleshooting

### Common Issues

1. **Headers Already Sent**
   ```
   Problem: Cannot modify header information - headers already sent
   Solution: Ensure no output before response->send()
   ```

2. **JSON Encoding Errors**
   ```
   Problem: JSON encoding failed
   Solution: Check for UTF-8 encoding and circular references
   ```

3. **Template Engine Issues**
   ```
   Problem: Template engine not initialized
   Solution: Pass template engine in constructor
   ```

## Support

For additional assistance:
1. Check framework documentation
2. Review example implementations
3. Submit issues via the framework's issue tracker
