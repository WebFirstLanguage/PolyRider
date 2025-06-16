<?php

namespace LogbieCore;

/**
 * Logger Class
 * 
 * A real-time logging system with flexible output options.
 * Supports file and response output modes.
 * 
 * @package LogbieCore
 * @since 1.0.0
 */
class Logger
{
    /**
     * The response handler
     * 
     * @var Response
     */
    private Response $response;
    
    /**
     * The logging mode
     * 
     * @var LogMode
     */
    private LogMode $logMode;
    
    /**
     * The log directory
     * 
     * @var string
     */
    private string $logDir;
    
    /**
     * The current log file
     * 
     * @var string
     */
    private string $logFile;
    
    /**
     * Constructor
     * 
     * @param Response $response The response handler
     * @param LogMode $logMode The logging mode
     * @param string|null $logDir The log directory (null for default)
     * @throws \RuntimeException If the log directory cannot be created or is not writable
     */
    public function __construct(
        Response $response,
        LogMode $logMode = LogMode::FILE_ONLY,
        ?string $logDir = null
    ) {
        $this->response = $response;
        $this->logMode = $logMode;
        
        // Set up log directory
        $this->logDir = $logDir ?? dirname(__DIR__, 2) . '/storage/logs';
        $this->ensureLogDirectory();
        
        // Set up log file
        $this->logFile = $this->logDir . '/' . date('Y-m-d') . '.log';
    }
    
    /**
     * Create a logger instance from legacy integer mode
     * 
     * @deprecated Use the constructor with LogMode enum instead
     * @param Response $response The response handler
     * @param int $legacyMode The legacy logging mode (0-3)
     * @param string|null $logDir The log directory (null for default)
     * @return self
     */
    public static function fromLegacy(Response $response, int $legacyMode, ?string $logDir = null): self
    {
        $logMode = match ($legacyMode) {
            0 => LogMode::NONE,
            1 => LogMode::FILE_ONLY,
            2 => LogMode::BOTH,
            3 => LogMode::RESPONSE_ONLY,
            default => LogMode::FILE_ONLY
        };
        
        return new self($response, $logMode, $logDir);
    }
    
    /**
     * Log a message
     * 
     * @param string $message The message to log
     * @return void
     */
    public function log(string $message): void
    {
        if ($this->logMode === LogMode::NONE) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        // Log to file if mode is FILE_ONLY or BOTH
        if ($this->logMode === LogMode::FILE_ONLY || $this->logMode === LogMode::BOTH) {
            $this->writeToFile($formattedMessage);
        }
        
        // Log to response if mode is RESPONSE_ONLY or BOTH
        if ($this->logMode === LogMode::RESPONSE_ONLY || $this->logMode === LogMode::BOTH) {
            $this->writeToResponse($formattedMessage);
        }
    }
    
    /**
     * Write a message to the log file
     * 
     * @param string $message The formatted message to write
     * @return void
     */
    private function writeToFile(string $message): void
    {
        try {
            $file = fopen($this->logFile, 'a');
            
            if ($file === false) {
                throw new \RuntimeException("Failed to open log file: {$this->logFile}");
            }
            
            // Acquire an exclusive lock
            if (flock($file, LOCK_EX)) {
                fwrite($file, $message);
                flock($file, LOCK_UN);
            } else {
                // If locking fails, try to write anyway
                fwrite($file, $message);
            }
            
            fclose($file);
        } catch (\Exception $e) {
            // If file logging fails, try to log to response as a fallback
            if ($this->logMode !== LogMode::RESPONSE_ONLY) {
                $this->writeToResponse("[Log Error: {$e->getMessage()}] {$message}");
            }
        }
    }
    
    /**
     * Write a message to the response
     * 
     * @param string $message The formatted message to write
     * @return void
     */
    private function writeToResponse(string $message): void
    {
        $this->response->appendContent($message);
    }
    
    /**
     * Ensure the log directory exists and is writable
     * 
     * @return void
     * @throws \RuntimeException If the directory cannot be created or is not writable
     */
    private function ensureLogDirectory(): void
    {
        // Create the directory if it doesn't exist
        if (!is_dir($this->logDir)) {
            if (!mkdir($this->logDir, 0755, true)) {
                throw new \RuntimeException("Failed to create log directory: {$this->logDir}");
            }
        }
        
        // Check if the directory is writable
        if (!is_writable($this->logDir)) {
            throw new \RuntimeException("Log directory is not writable: {$this->logDir}");
        }
    }
    
    /**
     * Get the current log mode
     * 
     * @return LogMode
     */
    public function getLogMode(): LogMode
    {
        return $this->logMode;
    }
    
    /**
     * Set the log mode
     * 
     * @param LogMode $logMode The new log mode
     * @return self
     */
    public function setLogMode(LogMode $logMode): self
    {
        $this->logMode = $logMode;
        return $this;
    }
    
    /**
     * Get the log directory
     * 
     * @return string
     */
    public function getLogDir(): string
    {
        return $this->logDir;
    }
    
    /**
     * Get the current log file
     * 
     * @return string
     */
    public function getLogFile(): string
    {
        return $this->logFile;
    }
}