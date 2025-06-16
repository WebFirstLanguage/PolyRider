<?php

namespace LogbieCLI;

/**
 * Console Logger
 * 
 * A simple logger for CLI applications with support for colorized output.
 * 
 * @package LogbieCLI
 * @since 1.0.0
 */
class ConsoleLogger
{
    /**
     * ANSI color codes
     */
    private const COLORS = [
        'reset'      => "\033[0m",
        'black'      => "\033[30m",
        'red'        => "\033[31m",
        'green'      => "\033[32m",
        'yellow'     => "\033[33m",
        'blue'       => "\033[34m",
        'magenta'    => "\033[35m",
        'cyan'       => "\033[36m",
        'white'      => "\033[37m",
        'bold'       => "\033[1m",
        'underline'  => "\033[4m",
    ];
    
    /**
     * Whether to use colors in output
     * 
     * @var bool
     */
    private bool $useColors;
    
    /**
     * The verbosity level
     * 
     * @var int
     */
    private int $verbosity;
    
    /**
     * Verbosity levels
     */
    public const VERBOSITY_QUIET = 0;
    public const VERBOSITY_NORMAL = 1;
    public const VERBOSITY_VERBOSE = 2;
    public const VERBOSITY_DEBUG = 3;
    
    /**
     * Constructor
     * 
     * @param bool $useColors Whether to use colors in output
     * @param int $verbosity The verbosity level
     */
    public function __construct(bool $useColors = true, int $verbosity = self::VERBOSITY_NORMAL)
    {
        // Disable colors if not supported
        $this->useColors = $useColors && $this->supportsColors();
        $this->verbosity = $verbosity;
    }
    
    /**
     * Log an info message
     * 
     * @param string $message The message to log
     * @param int $verbosity The minimum verbosity level required to display this message
     * @return void
     */
    public function info(string $message, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        if ($this->verbosity >= $verbosity) {
            $this->write($message);
        }
    }
    
    /**
     * Log a success message
     * 
     * @param string $message The message to log
     * @param int $verbosity The minimum verbosity level required to display this message
     * @return void
     */
    public function success(string $message, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        if ($this->verbosity >= $verbosity) {
            $this->write($message, 'green');
        }
    }
    
    /**
     * Log a warning message
     * 
     * @param string $message The message to log
     * @param int $verbosity The minimum verbosity level required to display this message
     * @return void
     */
    public function warning(string $message, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        if ($this->verbosity >= $verbosity) {
            $this->write($message, 'yellow');
        }
    }
    
    /**
     * Log an error message
     * 
     * @param string $message The message to log
     * @param int $verbosity The minimum verbosity level required to display this message
     * @return void
     */
    public function error(string $message, int $verbosity = self::VERBOSITY_QUIET): void
    {
        if ($this->verbosity >= $verbosity) {
            $this->write($message, 'red');
        }
    }
    
    /**
     * Log a debug message
     * 
     * @param string $message The message to log
     * @return void
     */
    public function debug(string $message): void
    {
        if ($this->verbosity >= self::VERBOSITY_DEBUG) {
            $this->write($message, 'cyan');
        }
    }
    
    /**
     * Write a message to the console
     * 
     * @param string $message The message to write
     * @param string|null $color The color to use
     * @return void
     */
    private function write(string $message, ?string $color = null): void
    {
        if ($color !== null && $this->useColors && isset(self::COLORS[$color])) {
            echo self::COLORS[$color] . $message . self::COLORS['reset'] . PHP_EOL;
        } else {
            echo $message . PHP_EOL;
        }
    }
    
    /**
     * Check if the current terminal supports colors
     * 
     * @return bool
     */
    private function supportsColors(): bool
    {
        // Windows 10 with ConEmu, Cmder, or Windows Terminal
        if (DIRECTORY_SEPARATOR === '\\') {
            return (
                getenv('ANSICON') !== false
                || getenv('ConEmuANSI') === 'ON'
                || getenv('TERM_PROGRAM') === 'vscode'
                || getenv('WT_SESSION') !== false
            );
        }
        
        // Linux/macOS
        return (
            stream_isatty(STDOUT)
            && getenv('NO_COLOR') === false
            && getenv('TERM') !== 'dumb'
        );
    }
    
    /**
     * Set the verbosity level
     * 
     * @param int $verbosity The verbosity level
     * @return self
     */
    public function setVerbosity(int $verbosity): self
    {
        $this->verbosity = $verbosity;
        return $this;
    }
    
    /**
     * Get the verbosity level
     * 
     * @return int
     */
    public function getVerbosity(): int
    {
        return $this->verbosity;
    }
    
    /**
     * Enable or disable colors
     * 
     * @param bool $useColors Whether to use colors
     * @return self
     */
    public function setUseColors(bool $useColors): self
    {
        $this->useColors = $useColors && $this->supportsColors();
        return $this;
    }
    
    /**
     * Check if colors are enabled
     * 
     * @return bool
     */
    public function getUseColors(): bool
    {
        return $this->useColors;
    }
}