# Template Engine Documentation for Logbie Framework
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