# Template and Debug Specification for Logbie Framework
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