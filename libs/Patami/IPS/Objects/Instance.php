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


use Patami\IPS\Objects\Exceptions\InstanceConfigNotFoundException;
use Patami\IPS\Objects\Exceptions\InstanceInvalidConfigException;
use Patami\IPS\Objects\Exceptions\ObjectCreateFailedException;
use Patami\IPS\Objects\Exceptions\ObjectInvalidModuleIdException;
use Patami\IPS\Objects\Exceptions\ObjectDeleteFailedException;
use Patami\IPS\Objects\Exceptions\ObjectNotFoundException;
use Patami\IPS\System\IPS;
use Patami\IPS\System\Logging\Log;


/**
 * Provides an OO implementation of an IPS instance object.
 * @package IPSPATAMI
 */
class Instance extends IPSObject
{

    /**
     * @var string IPS module GUID of the instance.
     */
    protected $moduleId;

    /**
     * Instance constructor.
     * @param int $objectId IPS object ID of the object.
     * @param string $moduleId IPS module GUID used when creating a new IPS instance object.
     * @throws ObjectNotFoundException if there is no IPS object with that $objectId.
     */
    public function __construct($objectId = null, $moduleId = null)
    {
        // Remember the module ID
        $this->moduleId = $moduleId;

        // Call the parent constructor
        parent::__construct($objectId);
    }

    /**
     * Creates a new IPS instance object.
     * @return int IPS object ID of the new instance.
     * @throws ObjectCreateFailedException if the object could not be created.
     * @throws ObjectInvalidModuleIdException if the IPS module GUID is invalid.
     * @see IPS::CreateInstance()
     */
    protected function Create()
    {
        // Get information about the module
        $info = IPS::GetModule($this->moduleId);

        // Throw an exception if the module doesn't exist
        if (! $info) {
            throw new ObjectInvalidModuleIdException();
        }

        // Create the instance
        $objectId = IPS::CreateInstance($this->moduleId);

        // Throw an exception if the object cannot be created
        if (! $objectId) {
            throw new ObjectCreateFailedException();
        }

        // Return the object ID
        return $objectId;
    }

    /**
     * Deletes the IPS instance object.
     * @throws ObjectDeleteFailedException if the object could not be deleted.
     * @see IPS::DeleteInstance()
     */
    public function Delete()
    {
        // Delete the object
        $result = IPS::DeleteInstance($this->objectId);

        // Throw an exception if the instance could not be deleted
        if ($result === false) {
            throw new ObjectDeleteFailedException();
        }

        // Invalidate the object ID
        $this->objectId = null;
    }

    /**
     * Returns the configuration of the IPS instance object.
     * @return array Configuration properties of the IPS instance object.
     * @throws InstanceConfigNotFoundException if the instance has no configuration.
     * @throws InstanceInvalidConfigException if the instance configuration cannot be decoded.
     * @see IPS::GetConfiguration()
     */
    public function GetConfiguration()
    {
        // Get the configuration of the instance
        $config = IPS::GetConfiguration($this->objectId);

        // Throw an exception if the configuration could not be found
        if (! $config) {
            throw new InstanceConfigNotFoundException();
        }

        // Decode the configuration
        $config = @json_decode($config, true);

        // Throw and exception if the configuration could not be decoded
        if (is_null($config)) {
            throw new InstanceInvalidConfigException();
        }

        // Return the configuration
        return $config;
    }

    /**
     * Returns a configuration property of the IPS instance object.
     * @param string $propertyName Name of the configuration property.
     * @return mixed|null Value of the configuration property or null if it does not exists.
     * @see IPS::GetProperty()
     */
    public function GetProperty($propertyName)
    {
        // Get and return the property value from IPS
        return IPS::GetProperty($this->objectId, $propertyName);
    }

    /**
     * Returns the IPS module GUID of the IPS instance object.
     * @return string IPS module GUID.
     * @see IPS::GetInstance()
     */
    public function GetModuleId()
    {
        // Get and return the module GUID
        return IPS::GetInstanceModuleId($this->objectId);
    }

    /**
     * Checks if the IPS instance object has the given IPS module GUID.
     * @param string $moduleId IPS module GUID which should be checked.
     * @return bool True if the IPS module GUIDs match.
     * @see Instance::GetModuleId()
     */
    public function IsModuleId($moduleId)
    {
        return $moduleId == $this->GetModuleId();
    }

    /**
     * Returns the name of the IPS module of the IPS instance object.
     * @return string Name of the IPS module.
     * @see IPS::GetInstance()
     */
    public function GetModuleName()
    {
        // Get and return the module name
        return IPS::GetInstanceModuleName($this->objectId);
    }

    /**
     * Checks if the IPS instance has the given IPS module name.
     * @param string $name Name of the IPS module.
     * @return bool True if the IPS module names match.
     * @see Instance::GetModuleName()
     */
    public function IsModuleName($name)
    {
        return $name == $this->GetModuleName();
    }

    /**
     * Returns the IPS library GUID of the IPS instance object.
     * @return string IPS library GUID.
     * @see Instance::GetModuleName()
     * @see IPS::GetModule()
     */
    public function GetLibraryId()
    {
        // Get and return the library GUID
        return IPS::GetModuleLibraryId($this->GetModuleId());
    }

}