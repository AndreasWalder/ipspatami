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


use Patami\IPS\Modules\Module;


/**
 * Logger implementation that sends log entries to the debug log of a module instance.
 * @package IPSPATAMI
 * @see Logger
 */
class ModuleDebugLogger extends Logger
{

    /**
     * @var Module Module instance that will receive the log events.
     */
    protected $module = null;

    /**
     * ModuleDebugLogger constructor.
     * @param Module $module Module instance that will receive the log events.
     */
    public function __construct(Module $module = null)
    {
        // Create and remember the module instance
        $this->module = $module;
    }

    /**
     * Returns the Module instance that will receive the log events.
     * @return Module Module instance that will receive the log events.
     */
    public function GetModule()
    {
        // Return the module instance
        return $this->module;
    }

    /**
     * Sets the module instance that will receive the log events.
     * @param Module $module Module instance that will receive the log events.
     * @return $this Fluent interface.
     */
    public function SetModule(Module $module)
    {
        // Remember the module instance
        $this->module = $module;

        // Enable fluent interface
        return $this;
    }

    /**
     * Sends a log entry to the module instance's debug log.
     * This method is called internally after formatting the message.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param string $formattedMessage The formatted log entry.
     * @param int $level Log level.
     * @param array|null $data Optional data to be added to the log entry.
     */
    protected function SendLog($tag, $message, $formattedMessage, $level, $data)
    {
        // If a module is set, send the log to it
        if (! is_null($this->module)) {

            // Format the message
            $text = $this->FormatAsString($message);

            // Send the text to the module's debug log
            $this->module->Debug($tag, $text, $data);

        }
    }

}