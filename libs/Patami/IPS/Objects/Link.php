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


use Patami\IPS\Objects\Exceptions\LinkTargetNotSetException;
use Patami\IPS\Objects\Exceptions\ObjectCreateFailedException;
use Patami\IPS\Objects\Exceptions\ObjectDeleteFailedException;
use Patami\IPS\Objects\Exceptions\ObjectSetFailedException;
use Patami\IPS\Objects\Exceptions\ScriptInvalidContentException;
use Patami\IPS\Objects\Exceptions\ScriptRunFailedException;
use Patami\IPS\Objects\Exceptions\ScriptSetFailedException;
use Patami\IPS\System\IPS;


/**
 * Provides an OO implementation of an IPS link object.
 * @package IPSPATAMI
 */
class Link extends IPSObject
{

    /**
     * Creates a new IPS link object.
     * @return int IPS object ID of the new link.
     * @throws ObjectCreateFailedException if the object could not be created.
     * @see IPS::CreateLink()
     */
    protected function Create()
    {
        // Create the link
        $objectId = IPS::CreateLink();

        // Throw an exception if the link could not be created
        if (! $objectId) {
            throw new ObjectCreateFailedException();
        }

        // Return the object ID
        return $objectId;
    }

    /**
     * Deletes the IPS link object.
     * @throws ObjectDeleteFailedException if the object could not be deleted.
     * @see IPS::DeleteLink()
     */
    public function Delete()
    {
        // Delete the object
        $result = IPS::DeleteLink($this->objectId);

        // Throw an exception if the event could not be deleted
        if ($result === false) {
            throw new ObjectDeleteFailedException();
        }

        // Invalidate the object ID
        $this->objectId = null;
    }

    /**
     * Returns the link target object.
     * @return IPSObject Link target object.
     * @throws LinkTargetNotSetException if the link target is not set.
     */
    public function GetTarget()
    {
        // Get the target object ID
        $objectId = @IPS::GetLink($this->objectId)['TargetID'];

        // Throw an exception if the link target is not set
        if (! $objectId) {
            throw new LinkTargetNotSetException();
        }

        // Create and return a new object
        return Objects::GetByID($objectId);
    }

    /**
     * Sets the target object of the link.
     * @param int|IPSObject $objectId ID or instance of the object to which the link should point.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the link target could not be set.
     */
    public function SetTarget($objectId)
    {
        // Get the object ID if an object was given
        if ($objectId instanceof IPSObject) {
            // An object was given
            $objectId = $objectId->GetId();
        }

        // Set the link
        $result = IPS::SetLinkTargetId($this->objectId, $objectId);

        // Throw an exception if the link target could not be set
        if ($result === false) {
            throw new ObjectSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

}