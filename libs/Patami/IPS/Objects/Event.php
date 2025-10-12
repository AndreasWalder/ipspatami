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
 * Provides an OO implementation of an IPS event object.
 * TODO: Complete dummy implementation.
 * @package IPSPATAMI
 */
class Event extends IPSObject
{

    /**
     * Creates a new IPS event object.
     * @return int IPS object ID of the new event.
     * @throws ObjectCreateFailedException if the object could not be created.
     * @see IPS::CreateEvent()
     */
    protected function Create()
    {
        // Create the event
        $objectId = IPS::CreateEvent(0);

        // Throw an exception if the event could not be created
        if (! $objectId) {
            throw new ObjectCreateFailedException();
        }

        // Return the object ID
        return $objectId;
    }

    /**
     * Deletes the IPS event object.
     * @throws ObjectDeleteFailedException if the object could not be deleted.
     * @see IPS::DeleteEvent()
     */
    public function Delete()
    {
        // Delete the object
        $result = IPS::DeleteEvent($this->objectId);

        // Throw an exception if the event could not be deleted
        if ($result === false) {
            throw new ObjectDeleteFailedException();
        }

        // Invalidate the object ID
        $this->objectId = null;
    }

}