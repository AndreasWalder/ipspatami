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


namespace Patami\IPS\Objects;


use Patami\IPS\Objects\Exceptions\ObjectCreateFailedException;
use Patami\IPS\Objects\Exceptions\ObjectDeleteFailedException;
use Patami\IPS\Objects\Exceptions\ScriptInvalidContentException;
use Patami\IPS\Objects\Exceptions\ScriptRunFailedException;
use Patami\IPS\Objects\Exceptions\ScriptSetFailedException;
use Patami\IPS\System\IPS;


/**
 * Provides an OO implementation of an IPS script object.
 * @package IPSPATAMI
 */
class Script extends IPSObject
{

    /**
     * PHP script type.
     * This is currently the only script type supported by IPS.
     */
    const SCRIPT_TYPE_PHP = 0;

    /**
     * Creates a new IPS script object.
     * @return int IPS object ID of the new script.
     * @throws ObjectCreateFailedException if the object could not be created.
     * @see IPS::CreateScript()
     */
    protected function Create()
    {
        // Create the script
        $objectId = IPS::CreateScript(self::SCRIPT_TYPE_PHP);

        // Throw an exception if the variable could not be created
        if (! $objectId) {
            throw new ObjectCreateFailedException();
        }

        // Return the object ID
        return $objectId;
    }

    /**
     * Returns the type of the IPS script object.
     * Currently only PHP scripts are supported by IPS.
     * @return int Script type.
     */
    public function GetScriptType()
    {
        return self::SCRIPT_TYPE_PHP;
    }

    /**
     * Deletes the IPS script object.
     * @param bool $deleteFile True if the script file should also be deleted.
     * @throws ObjectDeleteFailedException if the object could not be deleted.
     * @see IPS::DeleteScript()
     */
    public function Delete($deleteFile = true)
    {
        // Delete the object
        $result = IPS::DeleteScript($this->objectId, $deleteFile);

        // Throw an exception if the instance could not be deleted
        if ($result === false) {
            throw new ObjectDeleteFailedException();
        }

        // Invalidate the object ID
        $this->objectId = null;
    }

    /**
     * Checks if the IPS script object encountered an error during the last execution.
     * @return bool True if the script encountered an error during the last execution.
     * @see IPS::GetScript()
     */
    public function IsBroken()
    {
        // Get the variable info
        $info = IPS::GetScript($this->objectId);

        // Return if the script had an error on the last execution
        return @$info['ScriptIsBroken'] == true;
    }

    /**
     * Returns the file name of the script associated with the IPS script object.
     * @return string File name of the script.
     * @see IPS::GetScriptFile()
     */
    public function GetFileName()
    {
        // Return the script file name
        return IPS::GetScriptFile($this->objectId);
    }

    /**
     * Set the file name of the script associated with the IPS script object.
     * @param string $fileName File name of the script.
     * @return $this Fluent interface.
     * @throws ScriptSetFailedException if the script file could not be associated with the script object.
     * @see IPS::SetScriptFile()
     */
    public function SetFileName($fileName)
    {
        // Set the script file name
        $result = IPS::SetScriptFile($this->objectId, $fileName);

        // Throw an exception if the file name could not be set
        if ($result === false) {
            throw new ScriptSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the content of the script associated with the IPS script object.
     * @return string Contents of the script file.
     * @throws ScriptInvalidContentException if the contents of the script could not be retrieved.
     */
    public function GetContent()
    {
        // Get the script content
        $content = IPS::GetScriptContent($this->objectId);

        // Throw an error if the script content is invalid
        if (! $content) {
            throw new ScriptInvalidContentException();
        }

        // Return the script content
        return $content;
    }

    /**
     * Sets the contents of the script associated with the IPS script object.
     * @param string $content PHP code.
     * @return $this Fluent interface.
     * @throws ScriptSetFailedException if the contents of the script could not be updated.
     */
    public function SetContent($content)
    {
        // Set the script content via IPS
        $result = IPS::SetScriptContent($this->objectId, $content);

        // Throw an exception if the content could not be set
        if ($result === false) {
            throw new ScriptSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the time in seconds of the timer set for the IPS script object.
     * @return int Number of seconds configured as script timer for the script (not the remaining time) or 0 if no script timer was set.
     * @see IPS::GetScriptTimer()
     */
    public function GetTimer()
    {
        // Return the script timer interval
        return IPS::GetScriptTimer($this->objectId);
    }

    /**
     * Sets the time in seconds when the IPS script object is executed by the script timer.
     * @param int $interval Number of seconds between script invocations by the script timer or 0 to delete the timer.
     * @return $this Fluent interface.
     * @throws ScriptSetFailedException if the timer of the script could not be set.
     * @see IPS::SetScriptTimer()
     */
    public function SetTimer($interval)
    {
        // Set the timer via IPS
        $result = IPS::SetScriptTimer($this->objectId, $interval);

        // Throw an exception if the timer could not be set
        if ($result === false) {
            throw new ScriptSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Clears the IPS script object's timer.
     * @return $this Fluent interface.
     * @throws ScriptSetFailedException if the timer of the script could not be set.
     * @see Script::SetTimer()
     */
    public function ClearTimer()
    {
        // Disable the timer and enable fluent interface
        return $this->SetTimer(0);
    }

    /**
     * Executes the script associated with the IPS script object in a separate thread.
     * @param array|null $parameters Parameters to be passed to the script or null for no parameters.
     * @return $this Fluent interface.
     * @throws ScriptRunFailedException if the script could not be executed.
     * @see IPS::RunScript()
     * @see IPS::RunScriptEx()
     */
    public function Run(array $parameters = null)
    {
        // Execute the script via IPS
        if (is_null($parameters)) {
            $result = IPS::RunScript($this->objectId);
        } else {
            $result = IPS::RunScriptEx($this->objectId, $parameters);
        }

        // Throw an exception if the script could not be executed
        if ($result === false) {
            throw new ScriptRunFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Executes the script associated with the IPS script object, waits for it to end and returns the script output.
     * @param array|null $parameters Parameters to be passed to the script or null for no parameters.
     * @return string Output of the script.
     * @throws ScriptRunFailedException if the script could not be executed.
     * @see IPS::RunScriptWait()
     * @see IPS::RunScriptWaitEx()
     */
    public function RunWait(array $parameters = null)
    {
        // Execute the script via IPS
        if (is_null($parameters)) {
            $result = IPS::RunScriptWait($this->objectId);
        } else {
            $result = IPS::RunScriptWaitEx($this->objectId, $parameters);
        }

        // Throw an exception if the script could not be executed
        if ($result === false) {
            throw new ScriptRunFailedException();
        }

        // Return the text returned by the script
        return $result;
    }

    /**
     * Includes the script file associated to the IPS script object.
     * @return bool True if the script was successfully included.
     */
    public function Load()
    {
        // Get the script file name
        $fileName = $this->GetFileName();

        // Do nothing if the script does not exist
        if (! file_exists($fileName)) {
            return false;
        }

        // Include the script
        /** @noinspection PhpIncludeInspection */
        @require_once($fileName);

        return true;
    }

    /**
     * Returns the timestamp of the last execution of the IPS script object.
     * @return int Timestamp of the last execution of the script.
     */
    public function GetExecutedTimestamp()
    {
        // Get the variable info
        $info = IPS::GetScript($this->objectId);

        // Return the timestamp when the script was last executed
        return @$info['ScriptExecuted'];
    }

}