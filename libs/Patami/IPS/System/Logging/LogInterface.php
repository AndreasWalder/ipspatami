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


/**
 * Interface used to indicate that a class supports logging.
 * @package IPSPATAMI
 * @see Logger
 */
interface LogInterface extends DebugInterface
{

    /**
     * Sends an emergency log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @return $this Fluent interface.
     * @see Logger::Log()
     * @see Log::LEVEL_EMERGENCY
     */
    public function Emergency($tag, $message, array $data = null);

    /**
     * Sends an alert log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @return $this Fluent interface.
     * @see Logger::Log()
     * @see Log::LEVEL_ALERT
     */
    public function Alert($tag, $message, array $data = null);

    /**
     * Sends a critical log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @return $this Fluent interface.
     * @see Logger::Log()
     * @see Log::LEVEL_CRITICAL
     */
    public function Critical($tag, $message, array $data = null);

    /**
     * Sends an error log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @return $this Fluent interface.
     * @see Logger::Log()
     * @see Log::LEVEL_ERROR
     */
    public function Error($tag, $message, array $data = null);

    /**
     * Sends a warning log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @return $this Fluent interface.
     * @see Logger::Log()
     * @see Log::LEVEL_WARNING
     */
    public function Warning($tag, $message, array $data = null);

    /**
     * Sends a notice log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @return $this Fluent interface.
     * @see Logger::Log()
     * @see Log::LEVEL_NOTICE
     */
    public function Notice($tag, $message, array $data = null);

    /**
     * Sends an info log entry to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @return $this Fluent interface.
     * @see Logger::Log()
     * @see Log::LEVEL_INFO
     */
    public function Info($tag, $message, array $data = null);

}