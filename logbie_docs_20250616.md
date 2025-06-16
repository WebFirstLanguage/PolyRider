# Logbie Framework - Combined Documentation

*Generated on: 2025-06-16 08:49:19*

## Table of Contents

1. [JavaScript Module User Guide](#javascript-module-user-guide)
2. [Javascript Framwork Specs](#javascript-framwork-specs)
3. [Logbie.js Framework User Manual](#logbiejs-framework-user-manual)
4. [Template and Debug Specification for Logbie Framework](#template-and-debug-specification-for-logbie-framework)
5. [Logbie CLI Tool Guide](#logbie-cli-tool-guide)
6. [Complete Logbie Framework Module Development Guide](#complete-logbie-framework-module-development-guide)
7. [Database ORM Documentation for Logbie Framework](#database-orm-documentation-for-logbie-framework)
8. [Logbie Framework Directory and Namespace Guide](#logbie-framework-directory-and-namespace-guide)
9. [Logbie Documentation Writing Guide](#logbie-documentation-writing-guide)
10. [Logger Documentation for Logbie Framework](#logger-documentation-for-logbie-framework)
11. [Module Development Guide for Logbie Framework](#module-development-guide-for-logbie-framework)
12. [PSR-4 Compliance Script Specification for the Logbie Framework](#psr-4-compliance-script-specification-for-the-logbie-framework)
13. [PSR-4 Compliant Namespace Guidelines for Logbie Framework](#psr-4-compliant-namespace-guidelines-for-logbie-framework)
14. [Logbie Response Core User Guide](#logbie-response-core-user-guide)
15. [SOLID Principles Guide for Logbie Framework](#solid-principles-guide-for-logbie-framework)
16. [Template Engine Documentation for Logbie Framework](#template-engine-documentation-for-logbie-framework)
17. [User Management Core Service Guide](#user-management-core-service-guide)

---

<!-- BEGIN: JavaScript Module User Guide.md -->

# JavaScript Module User Guide

*Source: `JavaScript Module User Guide.md`*

Version: 1.0
Last Updated: 2024-10-27

## Overview

The JS module provides secure JavaScript file serving functionality for the Logbie Framework. It allows you to serve JavaScript files directly through clean URLs without exposing your file system structure.

## URL Pattern

All JavaScript files are accessible through the following URL pattern:
```
http://[your-domain]/js/[filename].js
```

For example:
- `http://example.com/js/main.js`
- `http://example.com/js/utils.js`
- `http://localhost/js/app.js`

## File Organization

### Directory Structure
JavaScript files should be placed in:
```
/templates/js/
├── main.js
├── utils.js
└── components/
    └── slider.js
```

## Security Features

The module implements several security measures:
- Only `.js` files are allowed
- Directory traversal prevention
- Content-Type enforcement
- File path validation
- `nosniff` header protection
- Files must be within the `/templates/js` directory
- Read-only access to JavaScript files

## Usage Examples

### 1. Direct Script Tags
```html
<script src="/js/main.js"></script>
<script src="/js/utils.js"></script>
```

### 2. Dynamic Loading
```javascript
const script = document.createElement('script');
script.src = '/js/components/slider.js';
document.head.appendChild(script);
```

### 3. Module Import
```javascript
import { utils } from '/js/utils.js';
```

## Best Practices

1. **File Organization**
   - Keep related scripts together
   - Use clear, descriptive filenames
   - Consider using subdirectories for organization

2. **Performance**
   - Minify production JavaScript files
   - Consider using module bundling
   - Implement proper caching strategies

3. **Security**
   - Don't store sensitive information in JavaScript files
   - Keep files within the designated directory
   - Use appropriate file permissions

## Troubleshooting

### Common Issues and Solutions

1. **File Not Found (404)**
   - Verify file exists in `/templates/js/`
   - Check file permissions
   - Ensure correct filename and case

2. **Invalid File Extension**
   - Only `.js` files are served
   - Verify file extension is lowercase
   - Rename files if necessary

3. **Access Denied**
   - Check directory permissions
   - Verify file ownership
   - Ensure file is within `/templates/js/`

## Setup Requirements

1. **Directory Structure**
   ```
   /templates/
   └── js/
       └── [your JavaScript files]
   ```

2. **File Permissions**
   - Directory: readable
   - Files: readable
   - Recommended: 644 for files, 755 for directories

## Examples

### Directory Structure Example
```
/templates/js/
├── main.js              # Core application logic
├── utils.js            # Utility functions
├── components/         # Component-specific scripts
│   ├── modal.js
│   ├── slider.js
│   └── forms.js
└── vendor/            # Third-party scripts
    └── jquery.min.js
```

### HTML Integration Example
```html
<!DOCTYPE html>
<html>
<head>
    <!-- Core scripts -->
    <script src="/js/main.js" defer></script>
    <script src="/js/utils.js" defer></script>

    <!-- Component scripts -->
    <script src="/js/components/modal.js" defer></script>
    <script src="/js/components/slider.js" defer></script>
</head>
<body>
    <!-- Your content here -->
</body>
</html>
```

## Support

If you encounter issues:
1. Check file permissions and location
2. Verify URL formatting
3. Check server logs for errors
4. Ensure JavaScript file exists

Remember that this module is designed for serving JavaScript files only and includes security measures to prevent unauthorized access or abuse.

<!-- END: {filename} -->

---

<!-- BEGIN: Javascript framwork specs.md -->

# Javascript Framwork Specs

*Source: `Javascript framwork specs.md`*

**JavaScript Framework Specification for Client-Server Interactions**

### 1. Overview

This document specifies a JavaScript framework for interacting with a server using AJAX, designed to handle form submissions, data retrieval, and auto-refresh tasks. The aim is to provide a simple interface for developers to send and receive data, display it dynamically in specified elements, and hide the underlying complexity of the client-server communication.

### 2. Features Overview

- **Send Data to Server**: Support form submissions and other data transfers to a server endpoint.
- **Receive Data from Server**: Provide seamless updates of server-side changes in specified div elements.
- **Display Server Data in Div**: Allow data retrieved from the server to be automatically displayed in specified container elements.
- **AJAX Integration**: Use AJAX for all interactions, ensuring the page does not refresh unnecessarily.
- **Send and Receive JSON**: Support the sending and receiving of JSON data to and from the server.
- **Success/Failure Handling**: Provide intuitive visual feedback on server responses, with optional redirects on success.
- **Auto-Refresh Mechanism**: Automatically update specific data (e.g., votes) without requiring a page reload.
- **Refresh Interception**: Provide a method to stop the auto-refresh action when required.

### 3. Requirements

1. **Form Submission**

   - Ability to send data to a specified server endpoint.
   - Data can be submitted via form or triggered by events like button clicks.
   - Use AJAX to handle form submissions to avoid full-page refreshes.
   - If successful, display a success message and optionally redirect to a new URL.
   - If failure occurs, display a failure message in a designated container.
   - **Support JSON**: Form data should be able to be converted and sent as JSON, and server responses should also be parsed as JSON.

2. **Data Retrieval and Display**

   - Ability to request data from a server (GET request).
   - Display the data in a specified div tag on the page.
   - Allow the developer to define a div ID for where the data should be displayed.
   - **Support JSON**: Data fetched from the server should be in JSON format and appropriately parsed before being displayed.

3. **Auto-Refresh Updates**

   - Support for recurring data requests from the server, useful for vote counts or real-time updates.
   - Default refresh interval (e.g., every 5 seconds) which can be customized.
   - Should provide a mechanism to stop/pause the refresh process, giving more flexibility to the user.
   - **Support JSON**: JSON responses from the server should be parsed and updated in the target elements.

4. **Success and Failure Handling**

   - When data submission is successful, provide an in-page success message.
   - Support the option for a redirect to a defined URL after successful submission.
   - When data submission fails, display an in-page error message in a designated container.

5. **User-Friendly Interface**

   - Hide all AJAX complexity from end-users, with the API abstracting all the underlying operations.
   - Developers should be able to trigger these actions with simple function calls.

6. **Interception Mechanism**

   - Allow the ability to intercept or cancel AJAX requests if a specific condition is met (e.g., if a user navigates away or presses a 'Stop' button).
   - Provide an easily accessible function for developers to cancel ongoing refresh/update requests.

### 4. Framework Architecture

1. **Core Components**

   - **DataSender**: Handles form submissions and sending of custom data to server endpoints.
     - Takes parameters such as `formElement` or `dataObject` and `endpointURL`.
     - **Supports JSON**: Converts data to JSON format before sending it to the server.
   - **DataFetcher**: Handles server data requests and automatic display in a specified div.
     - Accepts `endpointURL` and `divElement` as parameters for customization.
     - **Supports JSON**: Parses JSON responses and injects them into the specified div.
   - **AutoUpdater**: Provides recurring data fetching for real-time updates.
     - Customizable refresh intervals, and can be paused or stopped programmatically.
     - **Supports JSON**: Automatically parses JSON data received from the server.
   - **FeedbackHandler**: Displays messages in the appropriate div tags to indicate submission status (success or failure).
     - Options to configure whether a redirect is triggered after a success.

2. **Functional Flow**

   - **Form Submission Flow**:
     - User submits form data.
     - `DataSender` sends data via AJAX to server endpoint in JSON format.
     - `FeedbackHandler` handles server response.
     - Success message shown or optional redirect initiated.
   - **Auto-Refresh Flow**:
     - `AutoUpdater` makes periodic requests to the server endpoint.
     - JSON data fetched is parsed and displayed dynamically in the defined div tag.
     - An option to stop/pause refresh based on user interaction is provided.

3. **Technical Requirements**

   - AJAX requests should be based on the `XMLHttpRequest` object or `fetch()` API.
   - JSON should be used for both sending and receiving data to ensure format consistency.
   - Functions should provide clear options for callbacks (e.g., onSuccess, onFailure).

### 5. User Interface Elements

- **Form Elements**: The framework should work seamlessly with standard HTML forms.
- **Message Divs**: Configurable divs to display the outcome of submissions (success or failure).
- **Auto-Update Control**: Provide user interaction (e.g., buttons or links) to start or stop the auto-update feature.

### 6. Example Use Cases

1. **Form Submission and Response**

   - **Scenario**: User submits a form with feedback.
   - **Behavior**: Data is sent to the server in JSON format. A success message is shown upon a successful response, or an error message if the submission fails.

2. **Vote Count Auto-Refresh**

   - **Scenario**: Vote count displayed on a page needs to update every 5 seconds.
   - **Behavior**: `AutoUpdater` periodically fetches the latest vote count in JSON format and displays it in a div without page reload. Users can stop auto-refresh if needed.

### 7. Extensibility

- **Custom Intervals**: Allow developers to define custom auto-refresh intervals.
- **Custom Success/Failure Callbacks**: Developers can provide their own callbacks for more specific success or failure handling, like animations or additional processing.

### 8. Security Considerations

- **Validation**: Ensure form data is validated before sending it to the server.
- **Error Handling**: Graceful error handling should be in place to manage network issues or invalid responses from the server. The framework should wait about 10 seconds between retries and retry up to 3 times before giving up.

### 9. Sample Framework Usage

- **Send Data**: `Logbie.send(formElement, endpointURL, onSuccess, onFailure)`
- **Fetch Data**: `Logbie.get(endpointURL, divElement, onSuccess, onFailure)`
- **Start Auto-Refresh**: `Logbie.startAutoRefresh(endpointURL, divElement, interval)`
- **Stop Auto-Refresh**: `Logbie.stopAutoRefresh()`

### 10. Dependencies

- **jQuery (Optional)**: If preferred, the framework could optionally integrate with jQuery to simplify AJAX operations.
- **JavaScript ES6 Compatibility**: The framework should be compatible with modern JavaScript standards, ensuring cross-browser support.

### 11. Conclusion

This framework aims to provide a robust yet simple method for handling client-server interactions using JavaScript and AJAX. The focus is on minimizing complexity for the developer while providing a flexible interface for common use cases such as form submissions and dynamic updates.

Would you like to dive deeper into any specific feature or adjust the scope of this spec?

<!-- END: {filename} -->

---

<!-- BEGIN: Logbie JS framework.md -->

# Logbie.js Framework User Manual

*Source: `Logbie JS framework.md`*

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

<!-- END: {filename} -->

---

<!-- BEGIN: Template Specs.md -->

# Template and Debug Specification for Logbie Framework

*Source: `Template Specs.md`*

Version: 2.0
Last Updated: 2024-11-17

## Required Dependencies

### CSS Libraries
```html
<!-- Bootstrap 5.3.2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
```

### JavaScript Libraries
```html
<!-- jQuery 3.7.1 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<!-- Bootstrap 5.3.2 Bundle (includes Popper) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
```

## Debug Configuration

Place at the start of your main JavaScript file:

```javascript
const LogbieDebug = {
    enabled: true,
    logLevel: 'debug', // 'debug', 'info', 'warn', 'error'
    
    log: function(message, data = {}) {
        if (!this.enabled) return;
        
        const debugInfo = {
            ...data,
            timestamp: new Date().toISOString(),
            url: window.location.href
        };
        
        switch(this.logLevel) {
            case 'debug':
                console.debug(message, debugInfo);
                break;
            case 'info':
                console.log(message, debugInfo);
                break;
            case 'warn':
                console.warn(message, debugInfo);
                break;
            case 'error':
                console.error(message, debugInfo);
                break;
        }
    }
};

// Global error handling
window.onerror = function(msg, url, line, col, error) {
    LogbieDebug.log('Global error:', {
        message: msg,
        url: url,
        line: line,
        column: col,
        error: error,
        stack: error?.stack
    });
    return false;
};
```

## Base Template Structure

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{{ page.title }}}</title>
    
    <!-- Required CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    {% import("styles/main.css") %}
    
    <!-- Page-specific CSS -->
    {% if($page.styles) %}
        {% import($page.styles) %}
    {% endif %}
</head>
<body>
    <!-- Navigation -->
    {% import("partials/nav.html") %}

    <!-- Main Content -->
    <main class="container py-4">
        {% import($page.content) %}
    </main>

    <!-- Footer -->
    {% import("partials/footer.html") %}

    <!-- Required JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    {% import("JS/logbie.js") %}

    <!-- Debug Initialization -->
    <script>
        $(document).ready(function() {
            LogbieDebug.log('Document ready - initializing handlers');
        });
    </script>

    <!-- Page-specific JavaScript -->
    {% if($page.scripts) %}
        {% import($page.scripts) %}
    {% endif %}
</body>
</html>
```

## AJAX Request Tracking

```javascript
$(document).ajaxSend(function(event, xhr, settings) {
    LogbieDebug.log('Request started:', {
        url: settings.url,
        type: settings.type,
        data: settings.data
    });
    console.time(`Request-${settings.url}`);
});

$(document).ajaxComplete(function(event, xhr, settings) {
    console.timeEnd(`Request-${settings.url}`);
    LogbieDebug.log('Request completed:', {
        url: settings.url,
        status: xhr.status,
        responseText: xhr.responseText
    });
});

$(document).ajaxError(function(event, jqXHR, settings, error) {
    LogbieDebug.log('AJAX error:', {
        status: jqXHR.status,
        statusText: jqXHR.statusText,
        responseText: jqXHR.responseText,
        url: settings.url,
        type: settings.type,
        error: error
    });
});
```

## Form Implementation

### HTML Structure
```html
<form id="myForm" class="needs-validation" novalidate>
    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" 
               class="form-control" 
               id="username" 
               name="username" 
               required>
        <div class="invalid-feedback">
            Please enter a username
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>

<div id="successMessage" class="alert alert-success d-none"></div>
<div id="errorMessage" class="alert alert-danger d-none"></div>
```

### Form JavaScript
```javascript
$(document).ready(function() {
    $('#myForm').on('submit', function(e) {
        e.preventDefault();
        
        LogbieDebug.log('Form submission started:', {
            formId: this.id,
            elements: $(this).serializeArray()
        });
        
        if (this.checkValidity()) {
            LogbieDebug.log('Form validation passed');
            
            Logbie.send(this, '/api/endpoint', {
                successDivId: 'successMessage',
                errorDivId: 'errorMessage',
                onSuccess: function(response) {
                    LogbieDebug.log('Form submission successful:', {
                        response: response
                    });
                },
                onError: function(error) {
                    LogbieDebug.log('Form submission failed:', {
                        error: error
                    });
                }
            });
        } else {
            LogbieDebug.log('Form validation failed:', {
                invalidElements: $(this).find(':invalid').toArray().map(el => ({
                    id: el.id,
                    name: el.name,
                    value: el.value
                }))
            });
        }
        
        $(this).addClass('was-validated');
    });
});
```

## Dynamic Content Loading

```javascript
Logbie.get('/api/data', 'contentDiv', {
    errorDivId: 'errorMessage',
    onSuccess: function(response) {
        LogbieDebug.log('Content loaded successfully:', {
            targetDiv: 'contentDiv',
            response: response
        });
    },
    onError: function(error) {
        LogbieDebug.log('Content loading failed:', {
            targetDiv: 'contentDiv',
            error: error
        });
    }
});
```

## Auto-Refresh Implementation

```javascript
Logbie.startAutoRefresh('/api/status', 'statusDiv', 5000, {
    errorDivId: 'errorMessage',
    onSuccess: function(response) {
        LogbieDebug.log('Auto-refresh update:', {
            targetDiv: 'statusDiv',
            response: response
        });
    },
    onError: function(error) {
        LogbieDebug.log('Auto-refresh failed:', {
            targetDiv: 'statusDiv',
            error: error
        });
    }
});
```

## Event Tracking

```javascript
// DOM Event Tracking
$('body').on('click', '[data-action]', function(e) {
    LogbieDebug.log('Action triggered:', {
        action: $(this).data('action'),
        element: this,
        event: e.type
    });
});

// Bootstrap Event Tracking
$('.modal').on('show.bs.modal', function(e) {
    LogbieDebug.log('Modal opening:', {
        modalId: this.id,
        trigger: e.relatedTarget
    });
});
```

## Performance Monitoring

```javascript
// DOM Update Monitoring
const observer = new MutationObserver(function(mutations) {
    LogbieDebug.log('DOM Updated:', {
        mutations: mutations.map(m => ({
            type: m.type,
            target: m.target.id || m.target.tagName
        }))
    });
});

observer.observe(document.body, {
    childList: true,
    subtree: true
});
```

## Bootstrap Component Integration

### Modals
```html
<div class="modal fade" id="exampleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modal Title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Modal content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>
```

### Alerts
```html
<div class="alert alert-success alert-dismissible fade show" role="alert">
    Success message
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
```

## Best Practices

1. **Debug Output**
   - Use LogbieDebug.log() for all debug messages
   - Include relevant context in debug data
   - Use appropriate log levels
   - Monitor performance with console.time()

2. **Performance**
   - Load JavaScript at end of body
   - Use minified library versions
   - Monitor DOM updates
   - Track AJAX timing

3. **Error Handling**
   - Implement global error handler
   - Track AJAX errors
   - Log validation failures
   - Monitor Bootstrap component errors

4. **Security**
   - Validate inputs client-side and server-side
   - Escape user-generated content
   - Use HTTPS for external resources
   - Implement CSRF protection

## Testing Requirements

1. **Console Output**
   - Verify debug messages appear
   - Check timing measurements
   - Validate error tracking
   - Review AJAX monitoring

2. **Browser Compatibility**
   - Test on major browsers
   - Verify console output works
   - Check responsive layouts
   - Validate JavaScript functionality

## Documentation

Templates must include:
- Clear debug message points
- Performance monitoring spots
- Error handling documentation
- Console output expectations

Remember to update this specification as requirements evolve.

<!-- END: {filename} -->

---

<!-- BEGIN: cli-tool-guide.md -->

# Logbie CLI Tool Guide

*Source: `cli-tool-guide.md`*

The Logbie CLI tool is a command-line interface for the Logbie Framework that provides various commands to help with development, building, and maintenance tasks.

## Installation

The CLI tool is included with the Logbie Framework. To use it, simply run the `logbie` command from the project root directory:

```bash
php logbie help
```

On Windows, you may need to use:

```bash
php logbie help
```

## Available Commands

### Help

Display help information for available commands:

```bash
php logbie help
php logbie help <command>
php logbie <command> --help
```

### Build

Build the application by running composer install, creating necessary directories, and compiling frontend assets if present:

```bash
php logbie build
```

Options:
- `--no-composer`: Skip running composer install
- `--no-assets`: Skip compiling frontend assets
- `--dev`: Build in development mode
- `--prod`: Build in production mode (default)

### Clean

Clean the application by removing Composer's vendor directory, generated assets, and cache files:

```bash
php logbie clean
```

Options:
- `--vendor`: Remove vendor directory
- `--assets`: Remove generated assets
- `--cache`: Remove cache files
- `--all`: Remove all (vendor, assets, cache) (default)

## Adding Custom Commands

The Logbie CLI tool is designed to be extensible, allowing you to add your own custom commands. There are two ways to add custom commands:

### 1. Class-Based Commands

Create a new class that extends `LogbieCLI\BaseCommand`:

```php
<?php

namespace LogbieCLI\Command;

use LogbieCLI\BaseCommand;

class MyCustomCommand extends BaseCommand
{
    public function getName(): string
    {
        return 'my-command';
    }
    
    public function getDescription(): string
    {
        return 'My custom command description';
    }
    
    public function getHelp(): string
    {
        return <<<HELP
Usage: logbie my-command [options]

My custom command description.

Options:
  --option1          Option 1 description
  --option2          Option 2 description
  --help, -h         Display this help message
HELP;
    }
    
    public function execute(array $args = []): int
    {
        // Parse options
        [$options, $remainingArgs] = $this->parseOptions($args);
        
        // Command implementation
        $this->logger->info("Executing my custom command...");
        
        // Your command logic here
        
        $this->logger->success("Command completed successfully");
        return 0;
    }
}
```

Then register the command in your application bootstrap code:

```php
// Get the command registry
$registry = $app->getCommandRegistry();

// Register the command
$registry->register(MyCustomCommand::class);
```

### 2. Callback-Based Commands

For simpler commands, you can register a callback function:

```php
// Get the command registry
$registry = $app->getCommandRegistry();

// Register a callback-based command
$registry->register('simple-command', function($args, $logger) {
    $logger->info("Executing simple command...");
    
    // Your command logic here
    
    $logger->success("Command completed successfully");
    return 0;
}, 'A simple command example');
```

### Registering Commands in a Plugin or Extension

To register commands from a plugin or extension, you can hook into the application's initialization process:

```php
// In your plugin's bootstrap file
use LogbieCLI\Application;

// Check if the CLI application is available
if (class_exists(Application::class)) {
    // Get the CLI application instance
    $app = Application::getInstance();
    
    // Register your commands
    $registry = $app->getCommandRegistry();
    $registry->register(MyPluginCommand::class);
}
```

## Command Development Guidelines

When developing custom commands, follow these guidelines:

1. **Single Responsibility**: Each command should have a single, well-defined purpose.
2. **Clear Documentation**: Provide clear help text and descriptions for your commands.
3. **Error Handling**: Handle errors gracefully and provide meaningful error messages.
4. **Exit Codes**: Return appropriate exit codes (0 for success, non-zero for failure).
5. **Logging**: Use the logger for output instead of direct echo/print statements.
6. **Cross-Platform Compatibility**: Ensure your commands work on different operating systems.

## Command Structure

A well-structured command should:

1. Parse and validate arguments and options
2. Display help information when requested
3. Execute the command logic
4. Provide appropriate feedback through logging
5. Return an appropriate exit code

## Advanced Usage

### Accessing the Framework Container

If you need to access framework services, you can use the container:

```php
public function execute(array $args = []): int
{
    if ($this->hasContainer()) {
        $db = $this->getService('db');
        // Use the database service
    }
    
    // Command implementation
    return 0;
}
```

### Using the Framework Logger

You can use the framework logger if available:

```php
public function execute(array $args = []): int
{
    $frameworkLogger = $this->getFrameworkLogger();
    
    if ($frameworkLogger !== null) {
        $frameworkLogger->log("Using framework logger");
    } else {
        $this->logger->info("Using CLI logger");
    }
    
    // Command implementation
    return 0;
}
```

## Troubleshooting

### Command Not Found

If you get a "Command not found" error, make sure:

1. The command is registered correctly
2. The command class is autoloadable
3. The command name is spelled correctly

### Autoloader Issues

If you get an autoloader error, run:

```bash
composer dump-autoload
```

### Permission Issues

If you can't execute the CLI tool, make sure it has the correct permissions:

On Linux/macOS:
```bash
chmod +x logbie
```

On Windows:
```powershell
icacls logbie /grant Everyone:RX
```

## Cross-Platform Compatibility Notes

The Logbie CLI tool is designed to work on multiple platforms (Windows, Linux, macOS). Here are some platform-specific considerations:

### Universal Method (All Platforms)

- Use `php logbie <command>` to run the CLI tool
- This method works consistently across all platforms

### Windows

- Use `icacls logbie /grant Everyone:RX` to set permissions if needed
- Directory paths use backslashes (`\`) internally, but the tool handles this automatically

### Linux/macOS

- Alternatively, after setting execute permissions with `chmod +x logbie`, you can also use `./logbie <command>`
- Directory paths use forward slashes (`/`)

### Common Issues and Solutions

1. **"Command not found" on Linux/macOS**
   - Make sure the file has execute permissions: `chmod +x logbie`
   
2. **"Could not find Composer autoloader" error**
   - The CLI tool will automatically attempt to run `composer install` if the autoloader is not found
   - This will only fail if Composer itself is not installed on your system
   
3. **Error recreating cache directory**
   - This has been fixed in the latest version. If you encounter this issue, update your CLI tool.
   
4. **Permission issues with directory creation**
   - Ensure you have write permissions to the project directory
   - On Linux/macOS, you may need to run with sudo for certain operations

<!-- END: {filename} -->

---

<!-- BEGIN: complete-module-guide.md -->

# Complete Logbie Framework Module Development Guide

*Source: `complete-module-guide.md`*

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

<!-- END: {filename} -->

---

<!-- BEGIN: database-orm-docs.md -->

# Database ORM Documentation for Logbie Framework

*Source: `database-orm-docs.md`*

Version: 1.0
Last Updated: 2024-10-26

## Table of Contents
1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Basic Usage](#basic-usage)
5. [CRUD Operations](#crud-operations)
6. [Advanced Features](#advanced-features)
7. [Transaction Management](#transaction-management)
8. [Best Practices](#best-practices)
9. [Security Considerations](#security-considerations)

## Introduction

The Logbie DatabaseORM provides a secure and efficient database abstraction layer for PHP 8.2+ applications. It offers:
- Prepared statement caching
- SQL injection protection
- Transaction support
- Schema information caching
- Relationship handling

## Installation

The DatabaseORM is part of the Logbie Framework core. Place the `DatabaseORM.php` file in:
```
src/Core/DatabaseORM.php
```

## Configuration

Initialize the ORM with your database configuration:

```php
use LogbieCore\DatabaseORM;

$config = [
    'driver'   => 'mysql',
    'host'     => 'localhost',
    'port'     => '3306',
    'database' => 'your_database',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset'  => 'utf8mb4'
];

$db = new DatabaseORM($config);
```

## Basic Usage

### Creating Records

```php
// Single record insertion
$userData = [
    'username' => 'john_doe',
    'email'    => 'john@example.com',
    'status'   => 'active'
];

$userId = $db->create('users', $userData);
```

### Reading Records

```php
// Fetch all users
$allUsers = $db->read('users');

// Fetch with conditions
$activeUsers = $db->read('users', [
    'status' => 'active'
]);

// Select specific columns with options
$users = $db->read(
    'users',
    ['status' => 'active'],
    ['id', 'username', 'email'],
    [
        'orderBy' => 'username',
        'orderDirection' => 'ASC',
        'limit' => 10,
        'offset' => 0
    ]
);
```

### Updating Records

```php
// Update user status
$data = ['status' => 'inactive'];
$conditions = ['id' => 123];

$affectedRows = $db->update('users', $data, $conditions);
```

### Deleting Records

```php
// Delete user
$conditions = ['id' => 123];
$affectedRows = $db->delete('users', $conditions);
```

## Advanced Features

### Raw Queries

```php
// Execute custom SELECT query
$results = $db->query(
    "SELECT * FROM users WHERE created_at > ? AND status = ?",
    ['2024-01-01', 'active']
);

// Execute custom UPDATE query
$affected = $db->query(
    "UPDATE users SET last_login = NOW() WHERE id = ?",
    [123]
);
```

### Many-to-Many Relationships

```php
// Get all roles for a user
$userRoles = $db->getManyToMany(
    'users',
    'roles',
    'user_roles',
    ['user_id' => 123]
);
```

### Schema Information

```php
// Get table structure
$schema = $db->getTableSchema('users');

// Example response:
// [
//     ['Field' => 'id', 'Type' => 'int', 'Null' => 'NO', 'Key' => 'PRI', ...],
//     ['Field' => 'username', 'Type' => 'varchar(255)', 'Null' => 'NO', ...],
//     ...
// ]
```

## Transaction Management

```php
try {
    $db->beginTransaction();

    // Perform multiple operations
    $userId = $db->create('users', $userData);
    $db->create('user_profiles', ['user_id' => $userId, ...]);
    
    $db->commit();
} catch (\Exception $e) {
    $db->rollback();
    throw $e;
}
```

## Best Practices

### 1. Use Prepared Statements
Always use the built-in methods instead of raw queries when possible:
```php
// Good
$user = $db->read('users', ['id' => $userId]);

// Avoid
$user = $db->query("SELECT * FROM users WHERE id = $userId");
```

### 2. Transaction Wrapping
Wrap related operations in transactions:
```php
$db->beginTransaction();
try {
    // Multiple operations
    $db->commit();
} catch (\Exception $e) {
    $db->rollback();
    throw $e;
}
```

### 3. Error Handling
Always catch and handle database exceptions:
```php
try {
    $result = $db->create('users', $userData);
} catch (\PDOException $e) {
    // Log the error
    // Handle the failure
    throw new \RuntimeException('User creation failed', 0, $e);
}
```

## Security Considerations

### 1. Input Validation
Always validate input before passing to the ORM:
```php
$username = filter_var($input['username'], FILTER_SANITIZE_STRING);
if (!$username) {
    throw new \InvalidArgumentException('Invalid username');
}
```

### 2. Output Encoding
Encode data when displaying:
```php
echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
```

### 3. Connection Security
- Use strong passwords
- Limit database user privileges
- Use SSL/TLS for remote connections

## Example Module Integration

Here's how to use the DatabaseORM in a Logbie module:

```php
namespace Logbie;

use LogbieCore\BaseModule;
use LogbieCore\DatabaseORM;

class UserManager extends BaseModule
{
    private DatabaseORM $db;

    public function __construct($container)
    {
        parent::__construct($container);
        
        // Get database configuration from container
        $config = $container->get('dbConfig');
        $this->db = new DatabaseORM($config);
    }

    public function createUser(array $userData): int
    {
        try {
            $this->db->beginTransaction();

            // Create user
            $userId = $this->db->create('users', [
                'username' => $userData['username'],
                'email'    => $userData['email'],
                'status'   => 'active'
            ]);

            // Create related profile
            $this->db->create('user_profiles', [
                'user_id'     => $userId,
                'first_name'  => $userData['firstName'],
                'last_name'   => $userData['lastName']
            ]);

            $this->db->commit();
            return $userId;

        } catch (\Exception $e) {
            $this->db->rollback();
            $this->logger->log("Error creating user: " . $e->getMessage());
            throw $e;
        }
    }
}
```

## Performance Tips

1. **Use Specific Columns**
```php
// Better performance - only fetches needed columns
$users = $db->read('users', [], ['id', 'username']);

// Less efficient - fetches all columns
$users = $db->read('users');
```

2. **Leverage Prepared Statement Caching**
```php
// The prepared statement will be cached and reused
for ($i = 0; $i < 100; $i++) {
    $db->read('users', ['id' => $i]);
}
```

3. **Batch Operations**
```php
$db->beginTransaction();
try {
    foreach ($users as $user) {
        $db->create('users', $user);
    }
    $db->commit();
} catch (\Exception $e) {
    $db->rollback();
    throw $e;
}
```

## Troubleshooting

### Common Issues and Solutions

1. **Connection Failed**
   ```
   Problem: Connection failed: SQLSTATE[HY000] [2002] Connection refused
   Solution: Verify database credentials and host availability
   ```

2. **Duplicate Entry**
   ```
   Problem: Create operation failed: SQLSTATE[23000]: Duplicate entry
   Solution: Check unique constraints and handle accordingly
   ```

3. **Table Schema Not Found**
   ```
   Problem: Failed to get table schema: Table 'database.table' doesn't exist
   Solution: Verify table name and database permissions
   ```

## Support

For additional assistance:
1. Check framework documentation
2. Review DatabaseORM source code
3. Submit issues via the framework's issue tracker

<!-- END: {filename} -->

---

<!-- BEGIN: directory-namespace-guide.md -->

# Logbie Framework Directory and Namespace Guide

*Source: `directory-namespace-guide.md`*

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

<!-- END: {filename} -->

---

<!-- BEGIN: logbie-documentation-guide.md -->

# Logbie Documentation Writing Guide

*Source: `logbie-documentation-guide.md`*

## Table of Contents
1. [Introduction](#introduction)
2. [Document Structure](#document-structure)
3. [Components](#components)
4. [Style Guide](#style-guide)
5. [Examples](#examples)

## Introduction

This guide outlines the standardized approach for writing documentation within the Logbie Framework. Following these guidelines ensures consistency across all documentation and makes it easier for developers to find and understand information.

## Document Structure

### 1. Header Section
Every document must start with:
```markdown
# [Document Title]
Version: [X.Y]
Last Updated: [YYYY-MM-DD]
```

### 2. Purpose and Scope Section
```markdown
## Purpose
[Clear statement of what this document is for]

## Scope
- What is included
- What is not included
- Any prerequisites or assumptions
```

### 3. Function/Method Documentation Table
If the document describes functional components, include a table:

| Function/Method | Description | Parameters | Return Value |
|----------------|-------------|------------|--------------|
| `methodName()` | What it does | `$param (type)`: description | `type`: description |

### 4. Examples Section
```markdown
## Examples

### Example 1: [Brief Description]
**Purpose:**  
[What this example demonstrates]

**Code:**
```[language]
[code example]
```

**Explanation:**  
[Detailed explanation of what the code does]
```

## Components

### 1. Purpose Statement
- Must be clear and concise
- Should answer "Why does this document exist?"
- Include target audience
- State main objectives

Example:
```markdown
## Purpose
This document provides developers with guidelines for implementing custom modules in the Logbie Framework. It covers module structure, best practices, and common patterns to ensure consistency across module development.
```

### 2. Scope Definition
- List included topics
- Specify exclusions
- State any prerequisites
- Define boundaries

Example:
```markdown
## Scope
### Includes:
- Module class structure
- Required methods
- Database interactions
- Response handling

### Does Not Include:
- Framework core modifications
- Third-party integrations
- Frontend development

### Prerequisites:
- Basic PHP knowledge
- Understanding of OOP principles
- Familiarity with MVC pattern
```

### 3. Function/Method Documentation
Each function or method should be documented using this template:

```markdown
### functionName()

**Description:**  
Detailed explanation of what the function does.

**Parameters:**
- `$param1 (type)`: Description of first parameter
- `$param2 (type)`: Description of second parameter

**Returns:**  
`type`: Description of return value

**Throws:**  
- `ExceptionType`: When/why this exception might be thrown

**Example Usage:**
```php
// Code example
```

### 4. Examples Section
Each example should follow this structure:

```markdown
### Example: [Descriptive Title]

**Scenario:**  
Description of the use case or situation.

**Code:**
```[language]
// Code implementation
```

**Expected Output:**
```
What the code produces
```

**Key Points:**
- Important aspect 1
- Important aspect 2
```

## Style Guide

### 1. Markdown Formatting
- Use ATX-style headers (`#` for headers)
- Code blocks should specify language
- Use tables for structured data
- Use bullet points for lists
- Use numbered lists for sequences

### 2. Code Examples
- Must be complete and runnable
- Include comments explaining crucial parts
- Use meaningful variable names
- Follow Logbie coding standards

### 3. Writing Style
- Use active voice
- Be concise but thorough
- Break complex concepts into steps
- Include warnings/notes where appropriate

## Examples

### 1. Module Documentation Example

```markdown
# User Authentication Module
Version: 1.0
Last Updated: 2024-10-24

## Purpose
Provides user authentication functionality for the Logbie Framework.

## Scope
### Includes:
- User login/logout
- Password handling
- Session management
- Authentication middleware

### Does Not Include:
- User registration
- Password recovery
- Role management

## Functions

| Function | Description | Parameters | Return Value |
|----------|-------------|------------|--------------|
| `login()` | Authenticates user | `$credentials (array)` | `bool` |
| `logout()` | Terminates session | none | `void` |

### login()

**Description:**  
Authenticates a user using provided credentials.

**Parameters:**
- `$credentials (array)`:
  - `username (string)`: User's username
  - `password (string)`: User's password

**Returns:**  
`bool`: True if authentication successful, false otherwise

**Example:**
```php
$credentials = [
    'username' => 'john_doe',
    'password' => 'secure_password'
];

if ($auth->login($credentials)) {
    // Authentication successful
    redirect('/dashboard');
} else {
    // Authentication failed
    showError('Invalid credentials');
}
```
```

### 2. Configuration Documentation Example

```markdown
# Database Configuration Guide
Version: 1.1
Last Updated: 2024-10-24

## Purpose
Explains how to configure database connections in Logbie Framework.

## Scope
### Includes:
- Database configuration file structure
- Connection parameters
- Multiple database support
- Connection pooling

## Configuration Structure

```php
return [
    'default' => 'mysql',
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'database' => env('DB_NAME'),
            'username' => env('DB_USER'),
            'password' => env('DB_PASS')
        ]
    ]
];
```

**Explanation:**
- `default`: Specifies the default connection
- `connections`: Defines available connections
```

## Advanced Usage

### Document Templates
Create templates in the following directories:
```
/docs/
  ├── templates/
  │   ├── module-doc-template.md
  │   ├── api-doc-template.md
  │   └── configuration-doc-template.md
  └── examples/
      ├── module-example.md
      └── configuration-example.md
```

### Version Control
- Store documentation in version control
- Update version numbers when making changes
- Maintain a changelog
- Include last updated date

Remember to follow these guidelines when creating new documentation or updating existing ones to maintain consistency across the Logbie Framework documentation.

<!-- END: {filename} -->

---

<!-- BEGIN: logging-documentation.md -->

# Logger Documentation for Logbie Framework

*Source: `logging-documentation.md`*

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

<!-- END: {filename} -->

---

<!-- BEGIN: module-documentation.md -->

# Module Development Guide for Logbie Framework

*Source: `module-documentation.md`*

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

<!-- END: {filename} -->

---

<!-- BEGIN: psr-4 compliance.md -->

# PSR-4 Compliance Script Specification for the Logbie Framework

*Source: `psr-4 compliance.md`*

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

<!-- END: {filename} -->

---

<!-- BEGIN: psr4-namespace-guidelines.md -->

# PSR-4 Compliant Namespace Guidelines for Logbie Framework

*Source: `psr4-namespace-guidelines.md`*

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

<!-- END: {filename} -->

---

<!-- BEGIN: response-guide.md -->

# Logbie Response Core User Guide

*Source: `response-guide.md`*

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

<!-- END: {filename} -->

---

<!-- BEGIN: solid-principles-guide.md -->

# SOLID Principles Guide for Logbie Framework

*Source: `solid-principles-guide.md`*

## Overview of SOLID Principles

SOLID is an acronym for five design principles that help make software maintainable and scalable:
- **S**ingle Responsibility Principle (SRP)
- **O**pen-Closed Principle (OCP)
- **L**iskov Substitution Principle (LSP)
- **I**nterface Segregation Principle (ISP)
- **D**ependency Inversion Principle (DIP)

## 1. Single Responsibility Principle (SRP)
"A class should have one, and only one, reason to change."

### Bad Example:
```php
// classes/User/User.php
namespace Classes\User;

class User {
    private $db;
    
    public function getUserData($id) {
        return $this->db->query("SELECT * FROM users WHERE id = ?", [$id]);
    }
    
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public function sendPasswordResetEmail($email) {
        $mailer = new \PHPMailer();
        // Email sending logic here
    }
}
```

### Good Example:
```php
// classes/User/UserRepository.php
namespace Classes\User;

class UserRepository {
    private $db;
    
    public function findById($id): ?User {
        return $this->db->query("SELECT * FROM users WHERE id = ?", [$id]);
    }
}

// classes/Utility/EmailValidator.php
namespace Classes\Utility;

class EmailValidator {
    public function isValid(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

// classes/Services/PasswordResetService.php
namespace Classes\Services;

class PasswordResetService {
    private $mailer;
    
    public function sendResetEmail(string $email): void {
        // Email sending logic here
    }
}
```

## 2. Open-Closed Principle (OCP)
"Software entities should be open for extension but closed for modification."

### Bad Example:
```php
// classes/Payment/PaymentProcessor.php
namespace Classes\Payment;

class PaymentProcessor {
    public function processPayment($type, $amount) {
        if ($type === 'credit') {
            // Process credit card payment
        } else if ($type === 'paypal') {
            // Process PayPal payment
        }
        // Adding new payment types requires modifying this class
    }
}
```

### Good Example:
```php
// classes/Payment/PaymentMethodInterface.php
namespace Classes\Payment;

interface PaymentMethodInterface {
    public function process(float $amount): bool;
}

// classes/Payment/CreditCardPayment.php
class CreditCardPayment implements PaymentMethodInterface {
    public function process(float $amount): bool {
        // Process credit card payment
        return true;
    }
}

// classes/Payment/PayPalPayment.php
class PayPalPayment implements PaymentMethodInterface {
    public function process(float $amount): bool {
        // Process PayPal payment
        return true;
    }
}

// classes/Payment/PaymentProcessor.php
class PaymentProcessor {
    public function processPayment(PaymentMethodInterface $paymentMethod, float $amount): bool {
        return $paymentMethod->process($amount);
    }
}
```

## 3. Liskov Substitution Principle (LSP)
"Derived classes must be substitutable for their base classes."

### Bad Example:
```php
// classes/Storage/FileStorage.php
namespace Classes\Storage;

class FileStorage {
    public function save(string $data): bool {
        // Save to file
        return true;
    }
}

class ReadOnlyStorage extends FileStorage {
    public function save(string $data): bool {
        throw new \Exception('Cannot save to read-only storage');
    }
}
```

### Good Example:
```php
// classes/Storage/StorageInterface.php
namespace Classes\Storage;

interface StorageInterface {
    public function read(string $key): string;
}

interface WritableStorageInterface extends StorageInterface {
    public function save(string $key, string $data): bool;
}

// classes/Storage/FileStorage.php
class FileStorage implements WritableStorageInterface {
    public function read(string $key): string {
        // Read from file
        return $data;
    }
    
    public function save(string $key, string $data): bool {
        // Save to file
        return true;
    }
}

// classes/Storage/ReadOnlyStorage.php
class ReadOnlyStorage implements StorageInterface {
    public function read(string $key): string {
        // Read from storage
        return $data;
    }
}
```

## 4. Interface Segregation Principle (ISP)
"Clients should not be forced to depend on interfaces they do not use."

### Bad Example:
```php
// classes/Repository/UserRepositoryInterface.php
namespace Classes\Repository;

interface UserRepositoryInterface {
    public function find($id);
    public function save(User $user);
    public function delete($id);
    public function sendEmail(User $user);
    public function generateReport(User $user);
}
```

### Good Example:
```php
// classes/Repository/UserRepositoryInterface.php
namespace Classes\Repository;

interface UserRepositoryInterface {
    public function find($id);
    public function save(User $user);
    public function delete($id);
}

// classes/Services/UserNotificationInterface.php
interface UserNotificationInterface {
    public function sendEmail(User $user);
}

// classes/Reporting/UserReportInterface.php
interface UserReportInterface {
    public function generateReport(User $user);
}

// classes/Repository/UserRepository.php
class UserRepository implements UserRepositoryInterface {
    // Implements only repository methods
}

// classes/Services/UserNotificationService.php
class UserNotificationService implements UserNotificationInterface {
    // Implements only notification methods
}
```

## 5. Dependency Inversion Principle (DIP)
"High-level modules should not depend on low-level modules. Both should depend on abstractions."

### Bad Example:
```php
// classes/Order/OrderProcessor.php
namespace Classes\Order;

class OrderProcessor {
    private $mysqlDatabase;
    
    public function __construct() {
        $this->mysqlDatabase = new MySQLDatabase();
    }
    
    public function process(Order $order) {
        $this->mysqlDatabase->save($order);
    }
}
```

### Good Example:
```php
// classes/Database/DatabaseInterface.php
namespace Classes\Database;

interface DatabaseInterface {
    public function save($data): bool;
}

// classes/Order/OrderProcessor.php
namespace Classes\Order;

class OrderProcessor {
    private $database;
    
    public function __construct(DatabaseInterface $database) {
        $this->database = $database;
    }
    
    public function process(Order $order) {
        return $this->database->save($order);
    }
}
```

## Implementing SOLID in Modules

### Example Module Following SOLID:
```php
// modules/OrderManager.php
namespace Logbie;

use core\BaseModule;
use Classes\Order\OrderProcessor;
use Classes\Order\OrderValidator;
use Classes\Payment\PaymentMethodInterface;

class OrderManager extends BaseModule {
    private $orderProcessor;
    private $orderValidator;
    private $paymentMethod;
    
    public function __construct($db, $container = null) {
        parent::__construct($db, $container);
        
        // Dependencies are injected and follow interfaces
        $this->orderProcessor = $container->get('orderProcessor');
        $this->orderValidator = $container->get('orderValidator');
        $this->paymentMethod = $container->get('paymentMethod');
    }
    
    public function run(array $arguments = []) {
        $action = $arguments[0] ?? 'default';
        
        switch ($action) {
            case 'process':
                $this->processOrder();
                break;
            default:
                $this->defaultAction();
        }
    }
    
    private function processOrder() {
        try {
            $orderData = json_decode(file_get_contents('php://input'), true);
            
            if (!$this->orderValidator->validate($orderData)) {
                throw new \InvalidArgumentException('Invalid order data');
            }
            
            $order = $this->orderProcessor->process($orderData);
            $payment = $this->paymentMethod->process($order->getTotal());
            
            $this->response->setJson([
                'error' => false,
                'message' => 'Order processed successfully',
                'orderId' => $order->getId()
            ])->send();
            
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage());
            $this->response->setStatus(400)
                ->setJson([
                    'error' => true,
                    'message' => $e->getMessage()
                ])->send();
        }
    }
}
```

## Best Practices for SOLID Implementation

1. **Dependency Injection**
   - Use the Container class for managing dependencies
   - Inject dependencies through constructors
   - Type-hint interfaces rather than concrete classes

2. **Interface Design**
   - Create small, focused interfaces
   - Use interface inheritance for related functionality
   - Place interfaces in appropriate namespace directories

3. **Module Organization**
   - Keep modules focused on specific business domains
   - Use services for complex business logic
   - Implement validation in separate classes

4. **Testing**
   - Write unit tests for each class
   - Mock dependencies using interfaces
   - Test each SOLID principle separately

5. **Error Handling**
   - Create specific exception classes
   - Handle errors at appropriate levels
   - Log errors consistently

<!-- END: {filename} -->

---

<!-- BEGIN: template-engine-spec.md -->

# Template Engine Documentation for Logbie Framework

*Source: `template-engine-spec.md`*

Version: 1.0
Last Updated: 2024-12-04

## Purpose
This document details the Template Engine component of the Logbie Framework, which provides secure, efficient, and flexible template rendering with caching support. It serves as a comprehensive guide for developers implementing templates in their Logbie applications.

## Scope
### Includes:
- Template syntax and patterns
- Variable handling and filters
- Control structures
- Caching mechanism
- Security features
- Best practices

### Prerequisites:
- PHP 8.2+
- Write access to cache directory
- Understanding of PHP template concepts

## Core Features

### Template Syntax

| Pattern | Description | Example |
|---------|-------------|---------|
| `{{{variable}}}` | Escaped variable output | `{{{user.name}}}` |
| `{{[variable]}}` | Raw variable output | `{{[html_content]}}` |
| `{% if condition %}` | Conditional block | `{% if user.isAdmin %}` |
| `{% while condition %}` | Loop block | `{% while items.hasNext %}` |
| `{% try %}` | Error handling block | `{% try %}...{% catch %}` |
| `{% import('file.html') %}` | File import | `{% import('header.html') %}` |

## Basic Usage

### Initialization
```php
$templateEngine = new TemplateEngine(
    templateDir: '/path/to/templates',
    cacheDir: '/path/to/cache'
);
```

### Rendering Templates
```php
// Simple template rendering
$html = $templateEngine->render('page.html', [
    'title' => 'Welcome',
    'user' => $user
]);
```

### Variable Output
```html
<!-- Escaped output (recommended) -->
<h1>{{{page.title}}}</h1>

<!-- Raw output (use with caution) -->
<div>{{[content.html]}}</div>
```

### Control Structures

#### Conditionals
```html
{% if user.isLoggedIn %}
    Welcome, {{{user.name}}}!
{% else %}
    Please log in.
{% endif %}
```

#### Loops
```html
{% while items %}
    <li>{{{item.name}}}</li>
{% end while %}
```

#### Error Handling
```html
{% try %}
    {{{api.getData}}}
{% catch %}
    Error loading data
{% end catch %}
```

## Advanced Features

### Custom Filters
The engine includes built-in filters and supports custom implementations:

#### Built-in Filters
- `escape`: HTML escaping (default)
- `raw`: No escaping
- `upper`: Convert to uppercase
- `lower`: Convert to lowercase
- `trim`: Remove whitespace

#### Usage
```html
{{{user.name|upper}}}
{{{content|raw}}}
```

### Partials and Includes

#### Defining Partials
```html
{% partial 'header' %}
    <header>
        <h1>{{{site.title}}}</h1>
    </header>
{% endpartial %}
```

#### Including Templates
```html
{% include 'components/navbar.html' %}
```

### File Imports
```html
{% import('styles/main.css') %}
{% import('js/app.js') %}
```

## Security Considerations

### 1. Variable Escaping
Always use triple braces for user-supplied data:
```html
<!-- Correct (escaped) -->
{{{user.input}}}

<!-- Dangerous (unescaped) -->
{{[user.input]}}
```

### 2. Path Validation
The engine validates all template paths against the configured template directory to prevent directory traversal attacks.

### 3. Cache Security
- Cache files are stored with appropriate permissions
- Cache invalidation on template changes
- Automatic cache directory validation

## Best Practices

### 1. Template Organization
```
templates/
├── layouts/
│   └── default.html
├── partials/
│   ├── header.html
│   └── footer.html
└── pages/
    └── home.html
```

### 2. Variable Handling
```html
<!-- Recommended -->
<meta name="description" content="{{{page.description}}}">

<!-- Avoid -->
<meta name="description" content="{{[page.description]}}">
```

### 3. Cache Management
- Use appropriate cache directory permissions
- Implement cache cleanup strategy
- Monitor cache size

### 4. Error Handling
```html
{% try %}
    {{{api.getData}}}
{% catch %}
    <div class="error-message">
        Failed to load data. Please try again.
    </div>
{% end catch %}
```

## Troubleshooting

### Common Issues

1. **Cache Write Errors**
   ```
   Problem: "Failed to write cache file"
   Solution: Check directory permissions
   ```

2. **Template Not Found**
   ```
   Problem: "Invalid template path"
   Solution: Verify template exists in configured directory
   ```

3. **Variable Access**
   ```
   Problem: Undefined variable errors
   Solution: Check variable passing and dot notation syntax
   ```

## Support

For additional assistance:
1. Check framework documentation
2. Review example implementations
3. Submit issues via the framework's issue tracker

## Version History

### Version 1.0
- Initial documentation
- Core template functionality
- Security features
- Caching system

Remember to keep this documentation updated as the Template Engine evolves.

<!-- END: {filename} -->

---

<!-- BEGIN: user-management-docs.md -->

# User Management Core Service Guide

*Source: `user-management-docs.md`*

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

<!-- END: {filename} -->

---

