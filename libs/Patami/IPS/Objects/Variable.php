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
use Patami\IPS\Objects\Exceptions\VariableSetActionScriptFailedException;
use Patami\IPS\Objects\Exceptions\VariableSetCustomVariableProfileFailedException;
use Patami\IPS\System\IPS;


/**
 * Provides an abstract base class for an IPS variable object.
 * @package IPSPATAMI
 */
abstract class Variable extends IPSObject
{

    /**
     * Boolean variable.
     * @see BooleanVariable
     */
    const VARIABLE_TYPE_BOOLEAN = 0;

    /**
     * Integer variable.
     * @see IntegerVariable
     */
    const VARIABLE_TYPE_INTEGER = 1;

    /**
     * Float variable.
     * @see FloatVariable
     */
    const VARIABLE_TYPE_FLOAT = 2;

    /**
     * String variable.
     * @see StringVariable
     */
    const VARIABLE_TYPE_STRING = 3;

    /**
     * Returns the type of the variable.
     * This method must be implemented by the concrete child class.
     * @return int Type of the variable.
     * @see Variable::VARIABLE_TYPE_BOOLEAN
     * @see Variable::VARIABLE_TYPE_INTEGER
     * @see Variable::VARIABLE_TYPE_FLOAT
     * @see Variable::VARIABLE_TYPE_STRING
     */
    abstract public function GetVariableType();

    /**
     * Creates a new IPS variable object.
     * @return int IPS object ID of the new category.
     * @throws ObjectCreateFailedException if the object could not be created.
     * @see IPS::CreateVariable()
     * @see Variable::GetVariableType()
     */
    protected function Create()
    {
        // Create the instance
        $objectId = IPS::CreateVariable($this->GetVariableType());

        // Throw an exception if the variable could not be created
        if (! $objectId) {
            throw new ObjectCreateFailedException();
        }

        // Return the object ID
        return $objectId;
    }

    /**
     * Deletes the IPS variable object.
     * @throws ObjectDeleteFailedException if the object could not be deleted.
     * @see IPS::DeleteVariable()
     */
    public function Delete()
    {
        // Delete the object
        $result = IPS::DeleteVariable($this->objectId);

        // Throw an exception if the instance could not be deleted
        if ($result === false) {
            throw new ObjectDeleteFailedException();
        }

        // Invalidate the object ID
        $this->objectId = null;
    }

    /**
     * Returns the value of the IPS variable object.
     * This method must be implemented by the concrete child class.
     * @return mixed Value of the variable.
     */
    abstract public function Get();

    /**
     * Sets the value of the IPS variable object.
     * This method must be implemented by the concrete child class.
     * @param mixed $value New value of the variable.
     * @return $this Fluent interface.
     */
    abstract public function Set($value);

    /**
     * Returns the name of the variable profile associated with the IPS variable object.
     * @return string Variable profile name.
     * @see IPS::GetVariable()
     */
    public function GetProfileName()
    {
        // Get the variable info
        $info = IPS::GetVariable($this->objectId);

        // Get the variable profile name
        $name = @$info['VariableProfile'];

        // Return the profile name
        return $name;
    }

    /**
     * Checks if the IPS variable object is associated to the given variable profile.
     * @param string $name Variable profile name.
     * @return bool True if the variable profiles match.
     * @see Variable::GetProfileName()
     */
    public function HasProfileName($name)
    {
        return $name == $this->GetProfileName();
    }

    /**
     * Returns a variable profile instance associated with the IPS variable object.
     * @return VariableProfile|null Variable profile instance or null if none is associated to the variable object.
     * @see Variable::GetProfileName()
     * @see VariableProfile
     */
    public function GetProfile()
    {
        // Get the variable profile name
        $name = $this->GetProfileName();

        // Return null if no profile is set
        if (is_null($name)) {
            return null;
        }

        // Create and return the profile object
        return VariableProfiles::GetByName($name);
    }

    /**
     * Returns the name of the custom variable profile associated with the IPS variable object.
     * @return string Custom variable profile name.
     * @see IPS::GetVariable()
     */
    public function GetCustomProfileName()
    {
        // Get the variable info
        $info = IPS::GetVariable($this->objectId);

        // Get the variable profile name
        $name = @$info['VariableCustomProfile'];

        // Return the profile name
        return $name;
    }

    /**
     * Checks if the IPS variable object is associated to the given variable profile.
     * @param string $name Custom variable profile name.
     * @return bool True if the variable profiles match.
     * @see Variable::GetCustomProfileName()
     */
    public function HasCustomProfileName($name)
    {
        return $name == $this->GetCustomProfileName();
    }

    /**
     * Returns a custom variable profile instance associated with the IPS variable object.
     * @return VariableProfile|null Custom variable profile instance or null if none is associated to the variable object.
     * @see Variable::GetCustomProfileName()
     * @see VariableProfile
     */
    public function GetCustomProfile()
    {
        // Get the variable profile name
        $name = $this->GetCustomProfileName();

        // Return null if no profile is set
        if (is_null($name)) {
            return null;
        }

        // Create and return the profile object
        return VariableProfiles::GetByName($name);
    }

    /**
     * Sets the custom variable profile of the IPS variable object.
     * @param VariableProfile $profile New custom variable profile instance.
     * @return $this Fluent interface.
     * @throws VariableSetCustomVariableProfileFailedException if the custom variable profile could not be set.
     * @see IPS::SetVariableCustomProfile()
     * @see VariableProfile
     */
    public function SetCustomProfile(VariableProfile $profile)
    {
        // Set the custom variable profile
        $result = IPS::SetVariableCustomProfile($this->objectId, $profile->GetName());

        // Throw an exception if an error occurred
        if ($result === false) {
            throw new VariableSetCustomVariableProfileFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the IPS instance object that is called when the IPS variable object is updated.
     * @return IPSObject|null IPS instance object or null if none is set.
     * @see IPS::GetVariable()
     * @see Instance
     */
    public function GetActionInstance()
    {
        // Get the instance ID
        $info = IPS::GetVariable($this->objectId);
        $instanceId = @$info['VariableAction'];

        // Return null if no instance is set
        if (! $instanceId) {
            return null;
        }

        // Return the object
        return Objects::GetByID($instanceId);
    }

    /**
     * Returns the IPS script object that is called when the IPS variable object is updated.
     * @return IPSObject|null IPS script object or null if none is set.
     * @see IPS::GetVariable()
     * @see Script
     */
    public function GetActionScript()
    {
        // Get the script ID
        $info = IPS::GetVariable($this->objectId);
        $scriptId = @$info['VariableCustomAction'];

        // Return null if no script is set
        if (! $scriptId) {
            return null;
        }

        // Return the object
        return Objects::GetByID($scriptId);
    }

    /**
     * Checks if calling an instance or script is disabled when the IPS variable object is updated.
     * @return bool True if the action is disabled.
     * @see IPS::GetVariable()
     * @see Instance
     * @see Script
     */
    public function IsActionDisabled()
    {
        // Get the script ID
        $info = IPS::GetVariable($this->objectId);
        $scriptId = @$info['VariableCustomAction'];

        // Return if the action is disabled
        return $scriptId == 1;
    }

    /**
     * Sets the IPS script object that is called when the IPS variable object is updated.
     * @param Script $script IPS script object.
     * @return $this Fluent interface.
     * @throws VariableSetActionScriptFailedException if the action script could not be associated with the variable object.
     * @see IPS::SetVariableCustomAction()
     */
    public function SetActionScript(Script $script)
    {
        // Get the script ID
        $scriptId = $script->GetId();

        // Set the custom variable action
        $result = IPS::SetVariableCustomAction($this->objectId, $scriptId);

        // Throw an exception if an error occurred
        if ($result === false) {
            throw new VariableSetActionScriptFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Disables the update action when the IPS variable object is updated.
     * @return $this Fluent interface.
     * @throws VariableSetActionScriptFailedException if the action could not be disabled.
     * @see IPS::SetVariableCustomAction()
     */
    public function DisableAction()
    {
        // Disable the variable action
        $result = IPS::SetVariableCustomAction($this->objectId, 1);

        // Throw an exception if an error occurred
        if ($result === false) {
            throw new VariableSetActionScriptFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Checks if the IPS variable object value is binary encoded (Base64).
     * @return bool True if the value is binary encoded.
     * @see IPS::GetVariable()
     */
    public function IsBinary()
    {
        // Get the variable info
        $info = IPS::GetVariable($this->objectId);

        // Return if the variable is binary encoded
        return @$info['VariableIsBinary'] == true;
    }

    /**
     * Checks if the variable is read only because the license variable or global object limit is exceeded.
     * @return bool True if the variable limit is exceeded.
     * @see IPS::GetVariable()
     */
    public function IsLocked()
    {
        // Get the variable info
        $info = IPS::GetVariable($this->objectId);

        // Return if the variable is locked
        return @$info['VariableIsLocked'] == true;
    }

    /**
     * Returns the timestamp when the IPS variable object was last changed (a new value was set).
     * @return int Timestamp when the IPS variable object was last changed.
     * @see IPS::GetVariable()
     */
    public function GetChangedTimestamp()
    {
        // Get and return the timestamp
        return IPS::GetVariableChangedTimestamp($this->objectId);
    }

    /**
     * Returns the timestamp when the IPS variable object was last updated (a new or an existing value was set).
     * @return int Timestamp when the IPS variable object was last udpated.
     * @see IPS::GetVariable()
     */
    public function GetUpdatedTimestamp()
    {
        // Get and return the timestamp
        return IPS::GetVariableUpdatedTimestamp($this->objectId);
    }

    /**
     * Checks if the variable was ever updated.
     * @return bool True if the variable was ever updated.
     */
    public function IsUpdated()
    {
        // Check if the variable was ever updated and return the result
        return $this->GetUpdatedTimestamp() > 0;
    }

}