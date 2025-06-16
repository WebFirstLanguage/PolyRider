<?php

namespace LogbieCore;

/**
 * LogMode Enum
 * 
 * Defines the logging modes for the Logger class.
 * 
 * @package LogbieCore
 * @since 1.0.0
 */
enum LogMode: int
{
    case NONE = 0;          // No logging
    case FILE_ONLY = 1;     // Write to log files only
    case BOTH = 2;          // Write to both files and response
    case RESPONSE_ONLY = 3; // Write to response only
}