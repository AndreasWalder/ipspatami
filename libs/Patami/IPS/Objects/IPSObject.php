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


use Patami\IPS\System\Exceptions\SemaphoreTimeoutException;
use Patami\IPS\System\Logging\DebugInterface;
use Patami\IPS\Objects\Exceptions\ObjectNotFoundException;
use Patami\IPS\Objects\Exceptions\ObjectSetFailedException;
use Patami\IPS\System\IPS;
use Patami\IPS\System\Semaphore;


/**
 * Abstract base class for the various IPS object types.
 * @package IPSPATAMI
 */
abstract class IPSObject implements DebugInterface
{

    /**
     * Category object type.
     * @see Category
     */
    const TYPE_CATEGORY = 0;

    /**
     * Instance object type.
     * @see Instance
     */
    const TYPE_INSTANCE = 1;

    /**
     * Variable object type.
     * @see Variable
     */
    const TYPE_VARIABLE = 2;

    /**
     * Script object type.
     * @see Script
     */
    const TYPE_SCRIPT = 3;

    /**
     * Event object type.
     * @see Event
     */
    const TYPE_EVENT = 4;

    /**
     * Media object type.
     * @see Media
     */
    const TYPE_MEDIA = 5;

    /**
     * Link object type.
     * @see Link
     */
    const TYPE_LINK = 6;

    /**
     * @var int IPS object ID of the object.
     */
    protected $objectId;

    /**
     * @var string Name of the semaphore used to acquire exclusive access to the object.
     */
    protected $semaphoreName = null;

    /**
     * @var Semaphore Semaphore object used to acquire exclusive access to the object.
     */
    protected $semaphore = null;

    /**
     * @var bool True if the framework should save the object FQCN in the IPS object's info.
     * This can be used to make sure IPS objects that need specific IPSObject child classes are constructed with the
     * original class when they are instantiated again.
     */
    protected $rememberClassName = false;

    /**
     * @var DebugInterface Object used to send log entries to.
     */
    protected $debugTarget = null;

    /**
     * IPSObject constructor.
     * Creates or loads an IPS object and optionally remembers the class name.
     * @param int|null $objectId IPS object ID of the object.
     * @throws ObjectNotFoundException if there is no IPS object with that $objectId.
     * @see IPSObject::$rememberClassName
     * @see IPSObject::SaveObjectData()
     */
    public function __construct($objectId = null)
    {
        if (is_null($objectId)) {
            // Create a new object if no ID was given
            $this->objectId = $this->Create();
        } else {
            // Remember the given object ID
            $this->objectId = $objectId;
        }

        // Throw an exception if the object doesn't exist
        if (! $this->Exists()) {
            throw new ObjectNotFoundException();
        }

        // Set the default semaphore name
        $semaphoreName = sprintf('object_%d', $this->objectId);
        $this->SetSemaphoreName($semaphoreName);

        // Save the object info if the class name needs to be saved
        if ($this->rememberClassName) {
            $this->SaveObjectData();
        }
    }

    /**
     * IPSObject destructor.
     * Makes sure a semaphore that may have been locked is automatically released.
     */
    public function __destruct()
    {
        // Leave the semaphore if it is still active
        if ($this->IsSemaphoreActive()) {
            $this->LeaveSemaphore();
        }
    }

    /**
     * Loads the IPS object information as an array.
     *
     * @return array|null Object information or null if unavailable.
     */
    private function LoadObjectInfo()
    {
        $info = IPS::GetObject($this->objectId);

        if (is_object($info)) {
            $info = get_object_vars($info);
        }

        if (!is_array($info)) {
            return null;
        }

        return $info;
    }

    /**
     * Loads a field from the IPS object information.
     *
     * @param string $fieldName Field name to return.
     * @param mixed $default Default value when the field is missing.
     * @return mixed The field value or the default value.
     */
    private function LoadObjectInfoField($fieldName, $default = null)
    {
        $info = $this->LoadObjectInfo();

        if (is_null($info) || !array_key_exists($fieldName, $info)) {
            return $default;
        }

        return $info[$fieldName];
    }

    /**
     * Creates a new IPS object and returns its IPS object ID.
     * Child classes need to implement the specific code to create the respective IPS object type.
     * @return int IPS object ID of the new object.
     */
    abstract protected function Create();

    /**
     * Deletes the IPS object.
     * Child classes need to implement the specific code to delete the respective IPS object type.
     * @return void
     */
    abstract public function Delete();

    /**
     * Returns the IPS object ID of the object.
     * @return int IPS object ID.
     */
    public function GetId()
    {
        // Return the object ID
        return $this->objectId;
    }

    /**
     * Checks if the IPS object exists.
     * @return bool True if the object exists.
     * @see IPS::ObjectExists()
     */
    public function Exists()
    {
        // Ask IPS if the object exists and return the result
        return IPS::ObjectExists($this->objectId);
    }

    /**
     * Returns the type of the IPS object.
     * @return int IPS object type.
     * @see IPSObject::TYPE_CATEGORY
     * @see IPSObject::TYPE_INSTANCE
     * @see IPSObject::TYPE_VARIABLE
     * @see IPSObject::TYPE_SCRIPT
     * @see IPSObject::TYPE_EVENT
     * @see IPSObject::TYPE_MEDIA
     * @see IPSObject::TYPE_LINK
     */
    public function GetType()
    {
        return $this->LoadObjectInfoField('ObjectType');
    }

    /**
     * Returns the name of the IPS object.
     * @return string Name of the IPS object.
     * @see IPS::GetName()
     */
    public function GetName()
    {
        // Ask IPS for the object name and return it
        return IPS::GetName($this->objectId);
    }

    /**
     * Sets the name of the IPS object.
     * @param string $name Name of the IPS object.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the name could not be set.
     * @see IPS::SetName()
     */
    public function SetName($name)
    {
        // Set the object name via IPS
        $result = IPS::SetName($this->objectId, $name);

        // Throw an error if the name could not be set
        if ($result === false) {
            throw new ObjectSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the identifier of the IPS object.
     * @return string Identifier of the IPS object.
     * @see IPS::GetObject()
     */
    public function GetIdent()
    {
        return $this->LoadObjectInfoField('ObjectIdent');
    }

    /**
     * Sets the identifier of the IPS object.
     * @param string $ident Identifier of the IPS object.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the identifier could not be set.
     */
    public function SetIdent($ident)
    {
        // Set the ident via IPS
        $result = IPS::SetIdent($this->objectId, $ident);

        // Throw an error if the ident cannot not be set
        if ($result === false) {
            throw new ObjectSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the location of the IPS object.
     * The location of the object is the tree path from the root down to the object including the object name.
     * @return string Location of the IPS object.
     * @see IPS::GetLocation()
     */
    public function GetLocation()
    {
        // Ask IPS for the object location (FQON) and return it
        return IPS::GetLocation($this->objectId);
    }

    /**
     * Checks if the IPS object is disabled.
     * Disabled objects cannot be used in the web interface and are displayed with gray text in the console.
     * @return bool True if the IPS object is disabled in the web interface.
     * @see IPS::GetObject()
     */
    public function IsDisabled()
    {
        return $this->LoadObjectInfoField('ObjectIsDisabled', false) == true;
    }

    /**
     * Disables or enables the IPS object.
     * Disabled objects cannot be used in the web interface and are displayed with gray text in the console.
     * @param bool $disabled True if the object should be disabled.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the object could not be disabled or enabled.
     * @see IPS::SetDisabled()
     */
    public function SetDisabled($disabled)
    {
        // Set the disabled flag via IPS
        $result = IPS::SetDisabled($this->objectId, $disabled);

        // Throw an error if the disabled flag cannot not be set
        if ($result === false) {
            throw new ObjectSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Enables the IPS object.
     * Enabled objects can be used in the web interface.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the object could not be enabled.
     * @see IPSObject::SetDisabled()
     */
    public function Enable()
    {
        // Enable the object
        return $this->SetDisabled(false);
   }

    /**
     * Disables the IPS object.
     * Disabled objects cannot be used in the web interface and are displayed with gray text in the console.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the object could not be disabled.
     * @see IPSObject::SetDisabled()
     */
    public function Disable()
    {
        // Disable the object
        return $this->SetDisabled(true);
    }

    /**
     * Checks if the IPS object is hidden in the web interface.
     * @return bool True if the IPS object is hidden in the web interface
     * @see IPS::GetObject()
     */
    public function IsHidden()
    {
        return $this->LoadObjectInfoField('ObjectIsHidden', false) == true;
    }

    /**
     * Hides or unhides an IPS object in the web interface.
     * @param bool $hidden True if the object should be hidden in the web interface.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the object could not be hidden or unhidden.
     * @see IPS::SetHidden()
     */
    public function SetHidden($hidden)
    {
        // Set the hidden flag via IPS
        $result = IPS::SetHidden($this->objectId, $hidden);

        // Throw an error if the hidden flag cannot not be set
        if ($result === false) {
            throw new ObjectSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Hides an IPS object in the web interface.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the object could not be hidden.
     * @see IPSObject::SetHidden()
     */
    public function Hide()
    {
        // Hide the object
        return $this->SetHidden(true);
    }

    /**
     * Unhides an IPS object in the web interface.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the object could not be unhidden.
     * @see IPSObject::SetHidden()
     */
    public function Unhide()
    {
        // Unhide the object
        return $this->SetHidden(false);
    }

    /**
     * Sets the icon of the IPS object to be displayed in the web interface.
     * @param string $iconName File name of the icon without path and extension.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the icon could not be set.
     * @see IPS::SetIcon()
     */
    public function SetIcon($iconName)
    {
        // Set the icon via IPS
        $result = IPS::SetIcon($this->objectId, $iconName);

        // Throw an error if the icon cannot not be set
        if ($result === false) {
            throw new ObjectSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the icon file name of the IPS object.
     * @return string File name of the icon without path and extension.
     * @see IPS::GetObject()
     */
    public function GetIcon()
    {
        return $this->LoadObjectInfoField('ObjectIcon');
    }

    /**
     * Returns the custom object info that is persisted together with the IPS object.
     * @return string Custom object info.
     * @see IPS::GetObject()
     * @see IPSObject::SaveObjectData()
     */
    public function GetInfo()
    {
        return $this->LoadObjectInfoField('ObjectInfo');
    }

    /**
     * Sets the custom object info that is persisted together with the IPS object.
     * @param string $info Custom object info.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the object info could not be set.
     * @see IPS::SetInfo()
     * @see IPSObject::SaveObjectData()
     */
    protected function SetInfo($info)
    {
        // Set the info via IPS
        $result = IPS::SetInfo($this->objectId, $info);

        // Throw an error if the info cannot not be set
        if ($result === false) {
            throw new ObjectSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Saves custom object data.
     * Currently, it only remembers the object class name that is used to reinstantiate the same class if the IPS object is loaded again.
     * This method is automatically called from the constructor.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the object info could not be set.
     * @see IPSObject::SetInfo()
     * @see IPSObject::$rememberClassName
     */
    protected function SaveObjectData()
    {
        // Create the data structure
        $data = array();

        // Add class name if enabled
        if ($this->rememberClassName) {
            $data['className'] = get_called_class();
        }

        // Set the info if something was set
        if (count($data) > 0) {
            $this->SetInfo(json_encode($data));
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Enables remembering the object class name in the custom object info.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the object info could not be set.
     * @see IPSObject::SaveObjectData()
     * @see IPSObject::$rememberClassName
     */
    protected function RememberClassName()
    {
        // Remember the value
        $this->rememberClassName = true;

        // Save the info and enable fluent interface
        return $this->SaveObjectData();
    }

    /**
     * Disables remembering the object class name in the custom object info.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the object info could not be set.
     * @see IPSObject::SaveObjectData()
     * @see IPSObject::$rememberClassName
     */
    protected function ForgetClassName()
    {
        // Remember the value
        $this->rememberClassName = false;

        // Save the info and enable fluent interface
        return $this->SaveObjectData();
    }

    /**
     * Returns the summary text of an IPS object.
     * @return string Summary text.
     * @see IPS::GetObject()
     */
    public function GetSummary()
    {
        return $this->LoadObjectInfoField('ObjectSummary');
    }

    /**
     * Checks if the IPS object is read only.
     * @return bool True if the object is read only.
     * @see IPS::GetObject()
     */
    public function IsReadOnly()
    {
        return $this->LoadObjectInfoField('ObjectIsReadOnly', false) == true;
    }

    /**
     * Checks if the IPS object is a direct or indirect child of another IPS object.
     * @param IPSObject $object Other IPS object instance.
     * @param bool $recursive True if the other object can be more than one level above this object.
     * @return bool True if the object is a child of the other object.
     * @see IPS::IsChild()
     */
    public function IsChildOf(IPSObject $object, $recursive = true)
    {
        // Ask IPS if the object is a child of the given object and return the result
        return IPS::IsChild($this->objectId, $object->GetId(), $recursive);
    }

    /**
     * Returns the parent IPSObject instance of the IPS object.
     * @return IPSObject Parent IPS object instance.
     * @see IPS::GetParent()
     */
    public function GetParent()
    {
        // Ask IPS for the parent ID
        $parentId = IPS::GetParent($this->objectId);

        // Create and return the parent object
        return Objects::GetByID($parentId);
    }

    /**
     * Sets the new parent IPS object of the IPS object.
     * @param IPSObject $object New parent IPS object.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the parent object could not be set.
     * @see IPS::SetParent()
     */
    public function SetParent(IPSObject $object)
    {
        // Set the parent object via IPS
        $result = IPS::SetParent($this->objectId, $object->GetId());

        // Throw an error if the parent could not be set
        if ($result === false) {
            throw new ObjectSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Checks if the IPS object has child objects.
     * @return bool True if the object has child objects.
     * @see IPS::HasChildren()
     */
    public function HasChildren()
    {
        // Ask IPS if the object has children and return it
        return IPS::HasChildren($this->objectId);
    }

    /**
     * Returns the list of child IPSObject instances of the IPS object.
     * @return array IPSObject instances of the object's children.
     * @see IPS::GetChildrenIds()
     */
    public function GetChildren()
    {
        // Get the children IDs
        $childrenIds = IPS::GetChildrenIds($this->objectId);

        // Loop through the IDs and create the objects
        $children = array();
        foreach ($childrenIds as $childId) {
            $children[] = Objects::GetByID($childId);
        }

        // Return the array of objects
        return $children;
    }

    /**
     * Returns the first child object of the IPS object with the given identifier.
     * @param string $ident Identifier of the child object.
     * @return IPSObject IPS child object instance with the given identifier.
     * @throws ObjectNotFoundException if no child object with the given identifier was found.
     * @see IPS::GetObjectIdByIdent()
     */
    public function GetChildByIdent($ident)
    {
        // Get the child ID
        $childId = IPS::GetObjectIdByIdent($ident, $this->objectId);

        // Throw an exception if the child could not be found
        if ($childId === false) {
            throw new ObjectNotFoundException();
        }

        // Create and return the object
        return Objects::GetById($childId);
    }

    /**
     * Returns the first child object of the IPS object with the given name.
     * @param string $name Name of the child object.
     * @return IPSObject IPS child object instance with the given name.
     * @throws ObjectNotFoundException if no child object with the given name was found.
     * @see IPS::GetObjectIdByName()
     */
    public function GetChildByName($name)
    {
        // Get the child ID
        $childId = IPS::GetObjectIdByName($name, $this->objectId);

        // Throw an exception if the child could not be found
        if ($childId === false) {
            throw new ObjectNotFoundException();
        }

        // Create and return the object
        return Objects::GetById($childId);
    }

    /**
     * Returns the position (index) of the IPS object.
     * The position is used to sort all children of an IPS object in the web interface and the console.
     * @return int Position of the object.
     * @see IPS::GetObject()
     */
    public function GetPosition()
    {
        return $this->LoadObjectInfoField('ObjectPosition');
    }

    /**
     * Sets the position (index) of the IPS object.
     * The position is used to sort all children of an IPS object in the web interface and the console.
     * @param int $position Position of the object.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the position could not be set.
     * @see IPS::SetPosition()
     */
    public function SetPosition($position)
    {
        // Set the position via IPS
        $result = IPS::SetPosition($this->objectId, $position);

        // Throw an error if the position cannot not be set
        if ($result === false) {
            throw new ObjectSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the name of the semaphore used to acquire exclusive access to the IPS object.
     * @return string Name of the semaphore.
     */
    public function GetSemaphoreName()
    {
        // Return the semaphore name
        return $this->semaphoreName;
    }

    /**
     * Sets the name of the semaphore used to acquire exclusive access to the IPS object.
     * @param string $semaphoreName Name of the semaphore.
     * @return $this Fluent interface.
     */
    public function SetSemaphoreName($semaphoreName)
    {
        // Remember the semaphore name
        $this->semaphoreName = $semaphoreName;

        // Enable fluent interface
        return $this;
    }

    /**
     * Checks if the semaphore used to acquire exclusive access to the IPS object is currently locked.
     * @return bool True if the semaphore is locked.
     */
    public function IsSemaphoreActive()
    {
        return ! is_null($this->semaphore);
    }

    /**
     * Locks the semaphore to acquire exclusive access to the IPS object.
     * @param int $waitTime Milliseconds to wait for the semaphore if it is already locked by another thread.
     * @return $this Fluent interface.
     * @throws SemaphoreTimeoutException if a timeout occurred while waiting for the semaphore to be release by another thread.
     * @see Semaphore
     * @see IPSObject::LeaveSemaphore()
     */
    public function EnterSemaphore($waitTime = 1000)
    {
        // Do nothing if there is already a semaphore
        if ($this->IsSemaphoreActive()) {
            return $this;
        }

        // Get the object name for debugging
        $name = $this->GetName();

        // Do nothing if there is no semaphore name
        if (is_null($this->semaphoreName)) {
            return $this;
        }

        // Create the semaphore object
        $this->semaphore = new Semaphore($this->semaphoreName);

        // Try to lock the semaphore (usually works), otherwise throws an timeout exception
        $this->semaphore->Enter($waitTime);
        $this->Debug($name, sprintf('Entered semaphore %s', $name));

        // Enable fluent interface
        return $this;
    }

    /**
     * Releases the semaphore after exclusive access to the IPS object is no longer required.
     * The semaphore is automatically released when the object is destroyed.
     * @return $this Fluent interface.
     * @see IPSObject::EnterSemaphore()
     */
    public function LeaveSemaphore()
    {
        // Unlock the semaphore
        $this->semaphore->Leave();

        // Destroy the semaphore object
        $this->semaphore = null;

        // Enable fluent interface
        return $this;
    }

    /**
     * Enables debugging by setting a debug-enabled instance.
     * @param DebugInterface $target Instance that will receive the debug messages.
     * @return $this Fluent interface.
     */
    public function SetDebugTarget(DebugInterface $target)
    {
        // Remember the target object
        $this->debugTarget = $target;

        // Enable fluent interface
        return $this;
    }

    /**
     * Disables debugging.
     * @return $this Fluent interface.
     */
    public function ClearDebugTarget()
    {
        // Clear the object
        $this->debugTarget = null;

        // Enable fluent interface
        return $this;
    }

    public function Debug($tag, $message, array $data = null)
    {
        // Do nothing if the debug target is not set
        if (is_null($this->debugTarget)) {
            return $this;
        }

        // Send the debug to the target
        $this->debugTarget->Debug($tag, $message, $data);

        // Enable fluent interface
        return $this;
    }

}