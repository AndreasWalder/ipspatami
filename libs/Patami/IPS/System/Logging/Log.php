<?php
/**
 * Patami IPS Framework
 *
 * @package IPSPATAMI
 * @version 3.4
 * @link https://bitbucket.org/patami/ipspatami
 *
 * @author Florian Wiethoff <florian.wiethoff@patami.com>
 * @copyright 2017 Florian Wiethoff
 *
 * @license GPL
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * By intentionally submitting any modifications, corrections or derivatives to this work, or any other work intended
 * for use with this Software, to the author, you confirm that you are the copyright holder for those contributions and
 * you grant the author a nonexclusive, worldwide, irrevocable, royalty-free, perpetual, license to use, copy, create
 * derivative works based on those contributions, and sublicense and distribute those contributions and any derivatives
 * thereof.
 */


namespace Patami\IPS\System\Logging;


use Patami\IPS\System\Logging\Exceptions\LoggingInvalidLevelException;


/**
 * Static helper class to provide system-wide logging functions.
 * @package IPSPATAMI
 * @see Logger
 */
class Log
{

    /**
     * Emergency log level.
     */
    const LEVEL_EMERGENCY = 0;

    /**
     * Alert log level.
     */
    const LEVEL_ALERT     = 1;

    /**
     * Critical log level.
     */
    const LEVEL_CRITICAL  = 2;

    /**
     * Error log level.
     */
    const LEVEL_ERROR     = 3;

    /**
     * Warning log level.
     */
    const LEVEL_WARNING   = 4;

    /**
     * Notice log level.
     */
    const LEVEL_NOTICE    = 5;

    /**
     * Info log level.
     */
    const LEVEL_INFO      = 6;

    /**
     * Debug log level.
     */
    const LEVEL_DEBUG     = 7;

    /**
     * Mapping of log levels to log level names.
     * @var array Log level ID to name entries.
     */
    protected static $logLevelNames = array(
        self::LEVEL_EMERGENCY   => 'Emergency',
        self::LEVEL_ALERT       => 'Alert    ',
        self::LEVEL_CRITICAL    => 'Critical ',
        self::LEVEL_ERROR       => 'Error    ',
        self::LEVEL_WARNING     => 'Warning  ',
        self::LEVEL_NOTICE      => 'Notice   ',
        self::LEVEL_INFO        => 'Info     ',
        self::LEVEL_DEBUG       => 'Debug    ',
    );

    /**
     * @var Logger Logger instance which is used to log events.
     */
    protected static $logger = null;

    /**
     * Initializes the class and sets the default logger.
     * This method is automatically called during framework initialization.
     * @see Framework::Bootstrap()
     */
    public static function Initialize()
    {
        // Set the default logger
        self::SetDefaultLogger();
    }

    /**
     * Returns the Logger instance which is used to log events.
     * @return Logger Logger instance which is used to log events.
     */
    public static function GetLogger()
    {
        return self::$logger;
    }

    /**
     * Sets the Logger instance which is used to log events.
     * @param Logger $logger Logger instance which is used to log events.
     */
    public static function SetLogger(Logger $logger)
    {
        // Remember the value
        self::$logger = $logger;
    }

    /**
     * Sets the default Logger instance.
     * @see Log::SetLogger()
     */
    public static function SetDefaultLogger()
    {
        // Create a new instance
        self::$logger = new IPSLogger();
        //self::$logger = new FileLogger(Framework::GetLogFileName());
    }

    /**
     * Returns the name of the log level, optionally right padded with spaces.
     * @param int $level Log level.
     * @param bool $padRight True if the log level name should be right padded with spaces.
     * @return string Log level name.
     * @throws LoggingInvalidLevelException if the log level is invalid.
     */
    public static function GetLogLevelName($level, $padRight = false)
    {
        // Get the log level name
        $name = @self::$logLevelNames[$level];

        // Throw an exception if there is no mapping
        if (is_null($name)) {
            throw new LoggingInvalidLevelException();
        }

        // Strip white spaces if padding is off
        if (! $padRight) {
            $name = trim($name);
        }

        // Return the name
        return $name;
    }

    /**
     * Sends a log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @param int|null $level Log level of the message or null for the default log level.
     * @see Logger::Log()
     */
    public static function Log($tag, $message, array $data = null, $level = null)
    {
        //self::$logger->Log($tag, $message, $data, $level);
    }

    /**
     * Sends an emergency log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @see Logger::Emergency()
     */
    public static function Emergency($tag, $message, array $data = null)
    {
        //self::$logger->Emergency($tag, $message, $data);
    }

    /**
     * Sends an emergency log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @see Logger::Alert()
     */
    public static function Alert($tag, $message, array $data = null)
    {
        //self::$logger->Alert($tag, $message, $data);
    }

    /**
     * Sends a critical log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @see Logger::Critical()
     */
    public static function Critical($tag, $message, array $data = null)
    {
        //self::$logger->Critical($tag, $message, $data);
    }

    /**
     * Sends an error log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @see Logger::Error()
     */
    public static function Error($tag, $message, array $data = null)
    {
        //self::$logger->Error($tag, $message, $data);
    }

    /**
     * Sends a warning log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @see Logger::Warning()
     */
    public static function Warning($tag, $message, array $data = null)
    {
        //self::$logger->Warning($tag, $message, $data);
    }

    /**
     * Sends a notice log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @see Logger::Notice()
     */
    public static function Notice($tag, $message, array $data = null)
    {
        //self::$logger->Notice($tag, $message, $data);
    }

    /**
     * Sends an info log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @see Logger::Info()
     */
    public static function Info($tag, $message, array $data = null)
    {
        //self::$logger->Info($tag, $message, $data);
    }

    /**
     * Sends a debug log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @see Logger::Debug()
     */
    public static function Debug($tag, $message, array $data = null)
    {
        //self::$logger->Debug($tag, $message, $data);
    }

}
