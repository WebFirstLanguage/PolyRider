# Logbie.js Framework User Manual
Version: 1.0
Last Updated: 2024-10-27

## Table of Contents
1. [Introduction](#introduction)
2. [Core Features](#core-features)
3. [Installation](#installation)
4. [Basic Usage](#basic-usage)
5. [Advanced Features](#advanced-features)
6. [Error Handling](#error-handling)
7. [Best Practices](#best-practices)

## Introduction

Logbie.js is a lightweight JavaScript framework for handling AJAX requests, form submissions, and auto-refresh functionality with built-in error handling and feedback display.

## Core Features

- AJAX request handling with automatic retries
- Form data submission
- Server data fetching
- Auto-refresh management
- User feedback display
- Error handling and logging

## Installation

Include the Logbie.js file in your HTML:

```html
<script src="path/to/logbie.js"></script>
```

## Basic Usage

### Sending Form Data

```javascript
// HTML Form
<form id="myForm">
    <input type="text" name="username">
    <input type="password" name="password">
</form>
<div id="successMessage"></div>
<div id="errorMessage"></div>

// JavaScript
const form = document.getElementById('myForm');
form.onsubmit = async (e) => {
    e.preventDefault();
    try {
        await Logbie.send(form, '/api/login', {
            successDivId: 'successMessage',
            errorDivId: 'errorMessage',
            redirectUrl: '/dashboard',
            onSuccess: (response) => console.log('Login successful'),
            onError: (error) => console.error('Login failed')
        });
    } catch (error) {
        // Handle error
    }
};
```

### Fetching Data

```javascript
// HTML
<div id="content"></div>
<div id="error"></div>

// JavaScript
await Logbie.get('/api/data', 'content', {
    errorDivId: 'error',
    onSuccess: (data) => console.log('Data loaded'),
    onError: (error) => console.error('Failed to load data')
});
```

### Auto-Refresh

```javascript
// Start auto-refresh
const intervalId = Logbie.startAutoRefresh(
    '/api/status',
    'statusDisplay',
    5000, // Refresh every 5 seconds
    {
        errorDivId: 'refreshError',
        onSuccess: (data) => console.log('Status updated'),
        onError: (error) => console.error('Refresh failed')
    }
);

// Stop auto-refresh
Logbie.stopAutoRefresh('statusDisplay');

// Stop all auto-refresh intervals
Logbie.stopAllAutoRefresh();
```

## Advanced Features

### Retry Configuration

The framework automatically retries failed requests:
- Maximum retries: 3
- Retry delay: 10 seconds

### Custom Request Options

```javascript
await Logbie.send(form, '/api/endpoint', {
    successDivId: 'success',
    errorDivId: 'error',
    onSuccess: (response) => {
        // Custom success handling
    },
    onError: (error) => {
        // Custom error handling
    },
    redirectUrl: '/success-page' // Optional redirect after success
});
```

### Response Handling

Responses are automatically parsed as JSON and handled based on their structure:

```javascript
// Success response structure
{
    "message": "Operation successful",
    "data": { ... }
}

// Error response structure
{
    "message": "Operation failed",
    "details": "Detailed error information"
}
```

## Error Handling

### Automatic Error Management

The framework provides:
- Automatic retry for failed requests
- Error message display in specified elements
- Console logging of detailed error information
- Custom error handling through callbacks

### Error Display

```javascript
// HTML
<div id="errorDisplay"></div>

// JavaScript
try {
    await Logbie.get('/api/data', 'content', {
        errorDivId: 'errorDisplay'
    });
} catch (error) {
    // Additional error handling if needed
}
```

## Best Practices

### 1. Form Submission
- Always prevent default form submission
- Provide both success and error message containers
- Use appropriate error handling

```javascript
form.onsubmit = async (e) => {
    e.preventDefault();
    try {
        await Logbie.send(form, '/api/endpoint', {
            successDivId: 'success',
            errorDivId: 'error'
        });
    } catch (error) {
        // Handle critical errors
    }
};
```

### 2. Auto-Refresh Management
- Store interval IDs if you need to stop specific refreshes
- Clean up intervals when they're no longer needed
- Use appropriate refresh intervals (not too frequent)

```javascript
// Start on page load
const intervalId = Logbie.startAutoRefresh('/api/status', 'status', 5000);

// Clean up on page unload or component unmount
window.addEventListener('unload', () => {
    Logbie.stopAutoRefresh('status');
});
```

### 3. Error Handling
- Provide meaningful error messages
- Log errors appropriately
- Handle both network and application errors

```javascript
await Logbie.get('/api/data', 'content', {
    errorDivId: 'error',
    onError: (error) => {
        console.error('Detailed error:', error);
        notifyAdmin(error); // Custom error notification
    }
});
```

## Security Considerations

1. **Data Validation**
   - Validate form data before submission
   - Sanitize received data before display
   - Use HTTPS for all requests

2. **Error Messages**
   - Don't expose sensitive information in error messages
   - Log detailed errors server-side
   - Show user-friendly messages to users

3. **CSRF Protection**
   - Include CSRF tokens in forms
   - Use appropriate headers for requests
   - Follow security best practices

## Troubleshooting

### Common Issues

1. **Request Failures**
   ```
   Problem: Requests failing without retries
   Solution: Check network connectivity and server status
   ```

2. **Auto-Refresh Issues**
   ```
   Problem: Multiple refreshes running simultaneously
   Solution: Stop existing refresh before starting new one
   ```

3. **Form Submission Errors**
   ```
   Problem: Form data not being sent correctly
   Solution: Verify form structure and field names
   ```

## Support

For additional assistance:
1. Check console logs for detailed error information
2. Review network requests in browser developer tools
3. Verify server endpoints and response formats
4. Contact support team for unresolved issues

## Version History

### Version 1.0
- Initial release
- Core AJAX functionality
- Auto-refresh support
- Error handling system
- Form submission handling

Remember to keep this documentation updated as the Logbie.js framework evolves.