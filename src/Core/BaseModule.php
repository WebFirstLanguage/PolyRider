<?php

namespace LogbieCore;

/**
 * BaseModule Class
 * 
 * The base class for all modules in the Logbie Framework.
 * Provides access to core services and common functionality.
 * 
 * @package LogbieCore
 * @since 1.0.0
 */
abstract class BaseModule
{
    /**
     * The dependency injection container
     * 
     * @var Container
     */
    protected Container $container;
    
    /**
     * The database ORM instance
     * 
     * @var DatabaseORM
     */
    protected DatabaseORM $db;
    
    /**
     * The response handler
     * 
     * @var Response
     */
    protected Response $response;
    
    /**
     * The logger instance
     * 
     * @var Logger
     */
    protected Logger $logger;
    
    /**
     * Constructor
     * 
     * @param Container $container The dependency injection container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        
        // Get core services from the container
        $this->db = $container->get('db');
        $this->response = $container->get('response');
        $this->logger = $container->get('logger');
    }
    
    /**
     * Run the module
     * 
     * This method must be implemented by all modules.
     * It serves as the entry point for module execution.
     * 
     * @param array $arguments Arguments passed to the module
     * @return mixed The result of the module execution
     */
    abstract public function run(array $arguments = []): mixed;
    
    /**
     * Process a request
     * 
     * A helper method for handling common request processing tasks.
     * 
     * @param string $method The HTTP method to check for
     * @param callable $handler The handler to execute if the method matches
     * @return mixed The result of the handler or null
     */
    protected function processRequest(string $method, callable $handler): mixed
    {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            $this->response->setStatus(405)
                ->setJson([
                    'error' => true,
                    'message' => 'Method not allowed'
                ])
                ->send();
            return null;
        }
        
        try {
            return $handler();
        } catch (\Exception $e) {
            $this->logger->log("Error in " . static::class . ": " . $e->getMessage());
            $this->response->setStatus(500)
                ->setJson([
                    'error' => true,
                    'message' => 'An error occurred'
                ])
                ->send();
            return null;
        }
    }
    
    /**
     * Get POST data as JSON
     * 
     * A helper method for retrieving and parsing JSON data from a POST request.
     * 
     * @return array|null The parsed JSON data or null if invalid
     */
    protected function getJsonData(): ?array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->response->setStatus(400)
                ->setJson([
                    'error' => true,
                    'message' => 'Invalid JSON data'
                ])
                ->send();
            return null;
        }
        
        return $data;
    }
    
    /**
     * Validate required fields in data
     * 
     * A helper method for validating that required fields exist in the data.
     * 
     * @param array $data The data to validate
     * @param array $requiredFields The required fields
     * @return bool True if all required fields exist, false otherwise
     */
    protected function validateRequiredFields(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->response->setStatus(400)
                    ->setJson([
                        'error' => true,
                        'message' => "Missing required field: {$field}"
                    ])
                    ->send();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Send a standard success response
     * 
     * A helper method for sending a standardized success response.
     * 
     * @param mixed $data The data to include in the response
     * @param string $message A success message
     * @param int $status The HTTP status code
     * @return void
     */
    protected function sendSuccess(mixed $data = null, string $message = 'Success', int $status = 200): void
    {
        $response = [
            'error' => false,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->response->setStatus($status)
            ->setJson($response)
            ->send();
    }
    
    /**
     * Send a standard error response
     * 
     * A helper method for sending a standardized error response.
     * 
     * @param string $message An error message
     * @param int $status The HTTP status code
     * @param mixed $details Additional error details
     * @return void
     */
    protected function sendError(string $message = 'An error occurred', int $status = 400, mixed $details = null): void
    {
        $response = [
            'error' => true,
            'message' => $message
        ];
        
        if ($details !== null) {
            $response['details'] = $details;
        }
        
        $this->response->setStatus($status)
            ->setJson($response)
            ->send();
    }
}