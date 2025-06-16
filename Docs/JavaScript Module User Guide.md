# JavaScript Module User Guide
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