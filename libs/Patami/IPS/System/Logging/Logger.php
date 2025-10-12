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
 * Abstract base class for the various logger implementations.
 * @package IPSPATAMI
 */
abstract class Logger implements LogInterface
{

    /**
     * @var int Default log level.
     * @see Log
     */
    protected $defaultLevel = Log::LEVEL_DEBUG;

    /**
     * Sends a log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @param int $level Log level.
     * @return $this Fluent interface.
     * @see Log
     * @throws LoggingInvalidLevelException if the log level is invalid.
     */
    public function Log($tag, $message, array $data = null, $level = null)
    {
        // Use the default level if none specified
        if (is_null($level)) {
            $level = $this->defaultLevel;
        }

        // Throw an exception if the level is invalid
        if ($level < Log::LEVEL_EMERGENCY  || $level > Log::LEVEL_DEBUG) {
            throw new LoggingInvalidLevelException();
        }

        // Format the message
        $formattedMessage = $this->FormatMessage($tag, $message, $level, $data);

        // Send the message
        $this->SendLog($tag, $message, $formattedMessage, $level, $data);

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the log entry formatted as a string.
     * This implementation simply returns the message, which can be overridden by child classes.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param int $level Log level.
     * @param array|null $data Optional data to be added to the log entry.
     * @return string Formatted log message.
     */

    protected function FormatMessage(/** @noinspection PhpUnusedParameterInspection */ $tag, $message, $level, $data)
    {

        // Return the message
        return $message;
    }

    /**
     * Returns the message if it is a string or uses var_export to format and return it otherwise.
     * @param mixed $message Log message.
     * @return string Formatted log message.
     */
    protected function FormatAsString($message)
    {
        // Format the message if the message if not a string
        if (! is_string($message)) {
            $message = @var_export($message, true);
        }

        // Return the formatted message
        return $message;
    }

    /**
     * Returns the message and data formatted as a string.
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @return string Formatted log message.
     */
    protected function FormatMessageAsString($message, $data)
    {
        // Format the message
        $text = $this->FormatAsString($message);

        // Format and add the data if necessary
        if (! is_null($data)) {
            $text .= "\n" . $this->FormatAsString($data);
        }

        // Return the message
        return $text;
    }

    /**
     * Sends a log entry to the log.
     * This method is called internally after formatting the message.
     * Child classes need to implement the specific code to send a log entry to the respective logging facility.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param string $formattedMessage The formatted log entry.
     * @param int $level Log level.
     * @param array|null $data Optional data to be added to the log entry.
     * @return void
     * @see Logger::Log()
     */
    abstract protected function SendLog($tag, $message, $formattedMessage, $level, $data);

    /**
     * Returns the default log level.
     * @return int Default log level.
     * @see Log
     */
    public function GetDefaultLevel()
    {
        // Return the value
        return $this->defaultLevel;
    }

    /**
     * Sets the default log level.
     * @param int $level Default log level.
     * @return $this Fluent interface.
     * @throws LoggingInvalidLevelException if the log level is invalid.
     * @see Log
     */
    public function SetDefaultLevel($level)
    {
        // Throw an exception if the level is invalid
        if ($level < Log::LEVEL_EMERGENCY  || $level > Log::LEVEL_DEBUG) {
            throw new LoggingInvalidLevelException();
        }

        // Remember the value
        $this->defaultLevel = $level;

        // Enable fluent interface
        return $this;
    }

    public function Emergency($tag, $message, array $data = null)
    {
        return $this->Log($tag, $message, $data, Log::LEVEL_EMERGENCY);
    }

    public function Alert($tag, $message, array $data = null)
    {
        return $this->Log($tag, $message, $data, Log::LEVEL_ALERT);
    }

    public function Critical($tag, $message, array $data = null)
    {
        return $this->Log($tag, $message, $data, Log::LEVEL_CRITICAL);
    }

    public function Error($tag, $message, array $data = null)
    {
        return $this->Log($tag, $message, $data, Log::LEVEL_ERROR);
    }

    public function Warning($tag, $message, array $data = null)
    {
        return $this->Log($tag, $message, $data, Log::LEVEL_WARNING);
    }

    public function Notice($tag, $message, array $data = null)
    {
        return $this->Log($tag, $message, $data, Log::LEVEL_NOTICE);
    }

    public function Info($tag, $message, array $data = null)
    {
        return $this->Log($tag, $message, $data, Log::LEVEL_INFO);
    }

    public function Debug($tag, $message, array $data = null)
    {
        return $this->Log($tag, $message, $data, Log::LEVEL_DEBUG);
    }

}