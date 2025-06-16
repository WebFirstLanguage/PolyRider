<?php

namespace LogbieCore;

/**
 * TemplateEngine Class
 * 
 * A secure and efficient template rendering system with variable handling,
 * control structures, custom filters, and caching.
 * 
 * @package LogbieCore
 * @since 1.0.0
 */
class TemplateEngine
{
    /**
     * Template directory
     * 
     * @var string
     */
    private string $templateDir;
    
    /**
     * Cache directory
     * 
     * @var string
     */
    private string $cacheDir;
    
    /**
     * Whether to use caching
     * 
     * @var bool
     */
    private bool $useCache = true;
    
    /**
     * Custom filters
     * 
     * @var array<string, callable>
     */
    private array $filters = [];
    
    /**
     * Constructor
     * 
     * @param string $templateDir The template directory
     * @param string|null $cacheDir The cache directory (null to disable caching)
     * @throws \RuntimeException If the directories cannot be created or are not writable
     */
    public function __construct(string $templateDir, ?string $cacheDir = null)
    {
        $this->templateDir = rtrim($templateDir, '/\\');
        
        if ($cacheDir === null) {
            $this->useCache = false;
        } else {
            $this->cacheDir = rtrim($cacheDir, '/\\');
            $this->ensureCacheDirectory();
        }
        
        // Register built-in filters
        $this->registerBuiltInFilters();
    }
    
    /**
     * Render a template
     * 
     * @param string $template The template path
     * @param array $data The template data
     * @return string The rendered template
     * @throws \RuntimeException If the template cannot be rendered
     */
    public function render(string $template, array $data = []): string
    {
        $templatePath = $this->validateTemplatePath($template);
        
        // Check if we can use a cached version
        if ($this->useCache) {
            $cachedPath = $this->getCachedPath($templatePath);
            
            // If the cached file doesn't exist or the template has been modified
            if (!file_exists($cachedPath) || filemtime($templatePath) > filemtime($cachedPath)) {
                $this->compileTemplate($templatePath, $cachedPath);
            }
            
            $content = $this->renderCachedTemplate($cachedPath, $data);
        } else {
            // Compile and render directly
            $content = $this->compileAndRenderTemplate($templatePath, $data);
        }
        
        return $content;
    }
    
    /**
     * Register a custom filter
     * 
     * @param string $name The filter name
     * @param callable $callback The filter callback
     * @return self
     */
    public function registerFilter(string $name, callable $callback): self
    {
        $this->filters[$name] = $callback;
        return $this;
    }
    
    /**
     * Register built-in filters
     * 
     * @return void
     */
    private function registerBuiltInFilters(): void
    {
        // Escape filter (default)
        $this->registerFilter('escape', function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        });
        
        // Raw filter (no escaping)
        $this->registerFilter('raw', function ($value) {
            return $value;
        });
        
        // Upper filter
        $this->registerFilter('upper', function ($value) {
            return strtoupper((string) $value);
        });
        
        // Lower filter
        $this->registerFilter('lower', function ($value) {
            return strtolower((string) $value);
        });
        
        // Trim filter
        $this->registerFilter('trim', function ($value) {
            return trim((string) $value);
        });
    }
    
    /**
     * Validate a template path
     * 
     * @param string $template The template path
     * @return string The full template path
     * @throws \RuntimeException If the template is invalid or not found
     */
    private function validateTemplatePath(string $template): string
    {
        // Normalize the template path
        $template = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $template);
        
        // Prevent directory traversal
        if (strpos($template, '..') !== false) {
            throw new \RuntimeException('Invalid template path: ' . $template);
        }
        
        $templatePath = $this->templateDir . DIRECTORY_SEPARATOR . $template;
        
        if (!file_exists($templatePath)) {
            throw new \RuntimeException('Template not found: ' . $template);
        }
        
        return $templatePath;
    }
    
    /**
     * Get the cached path for a template
     * 
     * @param string $templatePath The template path
     * @return string The cached path
     */
    private function getCachedPath(string $templatePath): string
    {
        $relativePath = str_replace($this->templateDir, '', $templatePath);
        $cacheKey = md5($relativePath);
        
        return $this->cacheDir . DIRECTORY_SEPARATOR . $cacheKey . '.php';
    }
    
    /**
     * Compile a template to PHP code
     * 
     * @param string $templatePath The template path
     * @param string $cachedPath The cached path
     * @return void
     * @throws \RuntimeException If the template cannot be compiled
     */
    private function compileTemplate(string $templatePath, string $cachedPath): void
    {
        $content = file_get_contents($templatePath);
        
        if ($content === false) {
            throw new \RuntimeException('Failed to read template: ' . $templatePath);
        }
        
        // Compile the template
        $compiled = $this->compileContent($content);
        
        // Write the compiled template to the cache
        if (file_put_contents($cachedPath, $compiled) === false) {
            throw new \RuntimeException('Failed to write cache file: ' . $cachedPath);
        }
    }
    
    /**
     * Compile template content to PHP code
     * 
     * @param string $content The template content
     * @return string The compiled PHP code
     */
    private function compileContent(string $content): string
    {
        // Start with a security check
        $compiled = '<?php if(!defined("TEMPLATE_SECURITY")) exit("Direct access not allowed"); ?>' . PHP_EOL;
        
        // Replace escaped variables
        $content = preg_replace_callback('/\{\{\{([^}]+)\}\}\}/', function ($matches) {
            return '<?php echo $this->applyFilter(' . $this->compileVariable($matches[1]) . ', "escape"); ?>';
        }, $content);
        
        // Replace raw variables
        $content = preg_replace_callback('/\{\{\[([^}]+)\]\}\}/', function ($matches) {
            return '<?php echo $this->applyFilter(' . $this->compileVariable($matches[1]) . ', "raw"); ?>';
        }, $content);
        
        // Compile if statements
        $content = preg_replace_callback('/\{%\s*if\s+(.+?)\s*%\}/', function ($matches) {
            return '<?php if(' . $this->compileCondition($matches[1]) . '): ?>';
        }, $content);
        
        $content = preg_replace('/\{%\s*else\s*%\}/', '<?php else: ?>', $content);
        $content = preg_replace('/\{%\s*endif\s*%\}/', '<?php endif; ?>', $content);
        
        // Compile while loops
        $content = preg_replace_callback('/\{%\s*while\s+(.+?)\s*%\}/', function ($matches) {
            return '<?php while(' . $this->compileCondition($matches[1]) . '): ?>';
        }, $content);
        
        $content = preg_replace('/\{%\s*end\s+while\s*%\}/', '<?php endwhile; ?>', $content);
        
        // Compile try/catch blocks
        $content = preg_replace('/\{%\s*try\s*%\}/', '<?php try { ?>', $content);
        $content = preg_replace('/\{%\s*catch\s*%\}/', '<?php } catch (\Exception $e) { ?>', $content);
        $content = preg_replace('/\{%\s*end\s+catch\s*%\}/', '<?php } ?>', $content);
        
        // Compile imports
        $content = preg_replace_callback('/\{%\s*import\([\'"](.+?)[\'"]\)\s*%\}/', function ($matches) {
            $importPath = $this->validateTemplatePath($matches[1]);
            $importContent = file_get_contents($importPath);
            
            if ($importContent === false) {
                return '<!-- Import failed: ' . $matches[1] . ' -->';
            }
            
            return $importContent;
        }, $content);
        
        // Compile includes
        $content = preg_replace_callback('/\{%\s*include\s+[\'"](.+?)[\'"]\s*%\}/', function ($matches) {
            return '<?php echo $this->render("' . $matches[1] . '", $data); ?>';
        }, $content);
        
        // Add the compiled content
        $compiled .= $content;
        
        return $compiled;
    }
    
    /**
     * Compile a variable expression
     * 
     * @param string $expression The variable expression
     * @return string The compiled PHP code
     */
    private function compileVariable(string $expression): string
    {
        // Handle filters
        if (strpos($expression, '|') !== false) {
            list($variable, $filter) = explode('|', $expression, 2);
            return '$this->applyFilter(' . $this->compileVariablePath(trim($variable)) . ', "' . trim($filter) . '")';
        }
        
        return $this->compileVariablePath(trim($expression));
    }
    
    /**
     * Compile a variable path (e.g., user.name)
     * 
     * @param string $path The variable path
     * @return string The compiled PHP code
     */
    private function compileVariablePath(string $path): string
    {
        $segments = explode('.', $path);
        $compiled = '$data';
        
        foreach ($segments as $segment) {
            $compiled .= '["' . $segment . '"]';
        }
        
        return $compiled;
    }
    
    /**
     * Compile a condition
     * 
     * @param string $condition The condition
     * @return string The compiled PHP code
     */
    private function compileCondition(string $condition): string
    {
        // Replace variable paths in the condition
        return preg_replace_callback('/([a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)*)/', function ($matches) {
            return $this->compileVariablePath($matches[1]);
        }, $condition);
    }
    
    /**
     * Render a cached template
     * 
     * @param string $cachedPath The cached template path
     * @param array $data The template data
     * @return string The rendered template
     */
    private function renderCachedTemplate(string $cachedPath, array $data): string
    {
        // Define a security constant to prevent direct access
        define('TEMPLATE_SECURITY', true);
        
        // Start output buffering
        ob_start();
        
        // Include the cached template
        include $cachedPath;
        
        // Get the buffer contents and clean the buffer
        return ob_get_clean();
    }
    
    /**
     * Compile and render a template directly (without caching)
     * 
     * @param string $templatePath The template path
     * @param array $data The template data
     * @return string The rendered template
     */
    private function compileAndRenderTemplate(string $templatePath, array $data): string
    {
        $content = file_get_contents($templatePath);
        
        if ($content === false) {
            throw new \RuntimeException('Failed to read template: ' . $templatePath);
        }
        
        // Compile the template
        $compiled = $this->compileContent($content);
        
        // Create a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'template_');
        
        if ($tempFile === false) {
            throw new \RuntimeException('Failed to create temporary file');
        }
        
        // Write the compiled template to the temporary file
        if (file_put_contents($tempFile, $compiled) === false) {
            throw new \RuntimeException('Failed to write temporary file');
        }
        
        // Render the template
        $result = $this->renderCachedTemplate($tempFile, $data);
        
        // Remove the temporary file
        unlink($tempFile);
        
        return $result;
    }
    
    /**
     * Apply a filter to a value
     * 
     * @param mixed $value The value to filter
     * @param string $filter The filter name
     * @return mixed The filtered value
     */
    public function applyFilter(mixed $value, string $filter): mixed
    {
        if (!isset($this->filters[$filter])) {
            return $value;
        }
        
        return $this->filters[$filter]($value);
    }
    
    /**
     * Ensure the cache directory exists and is writable
     * 
     * @return void
     * @throws \RuntimeException If the directory cannot be created or is not writable
     */
    private function ensureCacheDirectory(): void
    {
        // Create the directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            if (!mkdir($this->cacheDir, 0755, true)) {
                throw new \RuntimeException('Failed to create cache directory: ' . $this->cacheDir);
            }
        }
        
        // Check if the directory is writable
        if (!is_writable($this->cacheDir)) {
            throw new \RuntimeException('Cache directory is not writable: ' . $this->cacheDir);
        }
    }
    
    /**
     * Clear the template cache
     * 
     * @return void
     */
    public function clearCache(): void
    {
        if (!$this->useCache) {
            return;
        }
        
        $files = glob($this->cacheDir . DIRECTORY_SEPARATOR . '*.php');
        
        if ($files === false) {
            return;
        }
        
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    /**
     * Get the template directory
     * 
     * @return string
     */
    public function getTemplateDir(): string
    {
        return $this->templateDir;
    }
    
    /**
     * Get the cache directory
     * 
     * @return string|null
     */
    public function getCacheDir(): ?string
    {
        return $this->useCache ? $this->cacheDir : null;
    }
    
    /**
     * Check if caching is enabled
     * 
     * @return bool
     */
    public function isCacheEnabled(): bool
    {
        return $this->useCache;
    }
    
    /**
     * Enable or disable caching
     * 
     * @param bool $useCache Whether to use caching
     * @return self
     */
    public function setCacheEnabled(bool $useCache): self
    {
        $this->useCache = $useCache;
        return $this;
    }
}