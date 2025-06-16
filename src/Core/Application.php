<?php

namespace LogbieCore;

/**
 * Application Class
 * 
 * The main entry point for the Logbie Framework. Handles bootstrapping,
 * routing, and overall application lifecycle management.
 * 
 * @package LogbieCore
 * @since 1.0.0
 */
class Application
{
    /**
     * The dependency injection container
     * 
     * @var Container
     */
    private Container $container;
    
    /**
     * Application configuration
     * 
     * @var array<string, mixed>
     */
    private array $config = [];
    
    /**
     * The base path of the application
     * 
     * @var string
     */
    private string $basePath;
    
    /**
     * Flag indicating if the application has been bootstrapped
     * 
     * @var bool
     */
    private bool $bootstrapped = false;
    
    /**
     * The current module being executed
     * 
     * @var ?BaseModule
     */
    private ?object $currentModule = null;
    
    /**
     * Constructor
     * 
     * @param string $basePath The base path of the application
     * @param array<string, mixed> $config Application configuration
     */
    public function __construct(string $basePath, array $config = [])
    {
        $this->basePath = rtrim($basePath, '/\\');
        $this->config = $config;
        $this->container = new Container();
        
        // Register the application instance in the container
        $this->container->register('app', $this);
        $this->container->register(self::class, $this);
        
        // Register the container itself in the container
        $this->container->register('container', $this->container);
        $this->container->register(Container::class, $this->container);
    }
    
    /**
     * Get the dependency injection container
     * 
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
    
    /**
     * Get the base path of the application
     * 
     * @param string $path Optional path to append to the base path
     * @return string
     */
    public function getBasePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
    }
    
    /**
     * Get a configuration value
     * 
     * @param string $key Configuration key (dot notation supported)
     * @param mixed $default Default value if the key doesn't exist
     * @return mixed
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            
            $value = $value[$segment];
        }
        
        return $value;
    }
    
    /**
     * Set a configuration value
     * 
     * @param string $key Configuration key (dot notation supported)
     * @param mixed $value Configuration value
     * @return self
     */
    public function setConfig(string $key, mixed $value): self
    {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                $config[$segment] = $value;
                break;
            }
            
            if (!isset($config[$segment]) || !is_array($config[$segment])) {
                $config[$segment] = [];
            }
            
            $config = &$config[$segment];
        }
        
        return $this;
    }
    
    /**
     * Bootstrap the application
     * 
     * @return self
     * @throws \RuntimeException If the application has already been bootstrapped
     */
    public function bootstrap(): self
    {
        if ($this->bootstrapped) {
            throw new \RuntimeException('Application has already been bootstrapped');
        }
        
        // Register core services
        $this->registerCoreServices();
        
        // Load configuration
        $this->loadConfiguration();
        
        // Set up error handling
        $this->setupErrorHandling();
        
        $this->bootstrapped = true;
        
        return $this;
    }
    
    /**
     * Register core services with the container
     * 
     * @return void
     */
    protected function registerCoreServices(): void
    {
        // Register response service (needs to be first as logger depends on it)
        $this->container->register('response', function (Container $container) {
            return new Response();
        });
        
        // Register logger service
        $this->container->register('logger', function (Container $container) {
            $response = $container->get('response');
            $logMode = LogMode::FILE_ONLY;
            $logDir = $this->getBasePath('storage/logs');
            
            return new Logger($response, $logMode, $logDir);
        });
        
        // Register database service
        $this->container->register('db', function (Container $container) {
            $dbConfig = $this->getConfig('database', [
                'driver' => 'mysql',
                'host' => 'localhost',
                'port' => '3306',
                'database' => 'logbie',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4'
            ]);
            
            return new DatabaseORM($dbConfig);
        });
        
        // Register template engine service
        $this->container->register('template', function (Container $container) {
            $templateDir = $this->getBasePath('templates');
            $cacheDir = $this->getBasePath('storage/cache/templates');
            
            return new TemplateEngine($templateDir, $cacheDir);
        });
        
        // Register user management service
        $this->container->register('users', function (Container $container) {
            $db = $container->get('db');
            $logger = $container->get('logger');
            
            return new UserManagement($db, $logger);
        });
        
        // Register class aliases
        $this->container->register(Response::class, function (Container $container) {
            return $container->get('response');
        });
        
        $this->container->register(Logger::class, function (Container $container) {
            return $container->get('logger');
        });
        
        $this->container->register(DatabaseORM::class, function (Container $container) {
            return $container->get('db');
        });
        
        $this->container->register(TemplateEngine::class, function (Container $container) {
            return $container->get('template');
        });
        
        $this->container->register(UserManagement::class, function (Container $container) {
            return $container->get('users');
        });
    }
    
    /**
     * Load application configuration
     * 
     * @return void
     */
    protected function loadConfiguration(): void
    {
        // Load configuration from files
        $configPath = $this->getBasePath('config');
        
        // This is a placeholder. In a real implementation, we would
        // load configuration from files in the config directory.
    }
    
    /**
     * Set up error handling
     * 
     * @return void
     */
    protected function setupErrorHandling(): void
    {
        // Set up error handling
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                // This error code is not included in error_reporting
                return false;
            }
            
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
        
        // Set up exception handling
        set_exception_handler(function (\Throwable $exception) {
            $this->handleException($exception);
        });
    }
    
    /**
     * Handle an uncaught exception
     * 
     * @param \Throwable $exception The exception to handle
     * @return void
     */
    protected function handleException(\Throwable $exception): void
    {
        // Log the exception
        if ($this->container->has('logger')) {
            $logger = $this->container->get('logger');
            // In a real implementation, we would log the exception
            // $logger->error($exception->getMessage(), ['exception' => $exception]);
        }
        
        // Send an error response
        if ($this->container->has('response')) {
            $response = $this->container->get('response');
            // In a real implementation, we would send an error response
            // $response->setStatus(500)->setJson(['error' => $exception->getMessage()])->send();
        } else {
            // Fallback error handling
            http_response_code(500);
            echo json_encode(['error' => $exception->getMessage()]);
        }
    }
    
    /**
     * Run the application
     * 
     * @return void
     */
    public function run(): void
    {
        if (!$this->bootstrapped) {
            $this->bootstrap();
        }
        
        try {
            // Determine the module to run based on the request
            $module = $this->resolveModule();
            
            // Run the module
            $this->runModule($module);
        } catch (\Throwable $exception) {
            $this->handleException($exception);
        }
    }
    
    /**
     * Resolve the module to run based on the request
     * 
     * @return object The module instance
     * @throws \RuntimeException If no module can be resolved
     */
    protected function resolveModule(): object
    {
        // This is a placeholder. In a real implementation, we would
        // determine the module to run based on the request URL or other factors.
        
        // Get the request URI
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Parse the URI to determine the module
        $path = parse_url($uri, PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        
        // The first segment is the module name
        $moduleName = !empty($segments[0]) ? ucfirst($segments[0]) : 'Home';
        
        // Check if the module exists
        $moduleClass = "\\LogbieCore\\Modules\\{$moduleName}";
        
        if (!class_exists($moduleClass)) {
            throw new \RuntimeException("Module '{$moduleName}' not found");
        }
        
        // Create the module instance
        $module = new $moduleClass($this->container);
        
        // Store the current module
        $this->currentModule = $module;
        
        return $module;
    }
    
    /**
     * Run a module
     * 
     * @param object $module The module to run
     * @return void
     * @throws \InvalidArgumentException If the module is not a valid module
     */
    protected function runModule(object $module): void
    {
        // Check if the module is a valid module
        if (!method_exists($module, 'run')) {
            throw new \InvalidArgumentException('Invalid module: missing run method');
        }
        
        // Run the module
        $module->run();
    }
    
    /**
     * Get the current module being executed
     * 
     * @return ?object
     */
    public function getCurrentModule(): ?object
    {
        return $this->currentModule;
    }
    
    /**
     * Check if the application has been bootstrapped
     * 
     * @return bool
     */
    public function isBootstrapped(): bool
    {
        return $this->bootstrapped;
    }
}