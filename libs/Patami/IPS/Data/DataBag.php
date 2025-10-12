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


namespace Patami\IPS\Data;


use Patami\IPS\Objects\ArrayVariable;
use Patami\IPS\Objects\Exceptions\ObjectNotFoundException;
use Patami\IPS\Objects\Exceptions\VariableInvalidDataException;
use Patami\IPS\System\Semaphore;


/**
 * Provides a data bag stored in an IPS variable object.
 * The data bag stores data as a JSON-encoded string in an IPS string variable.
 * @package IPSPATAMI
 */
class DataBag extends ArrayVariable
{

    /**
     * DataBag constructor.
     * Creates or loads a data bag.
     * When creating a data bag, it automatically sets a default name based on the IPS object ID.
     * @param int|null $objectId IPS object ID of the object.
     * @throws ObjectNotFoundException if there is no IPS object with that $objectId.
     */
    public function __construct($objectId = null)
    {
        // Call the parent constructor
        parent::__construct($objectId);

        // Set the name for new data bags
        if (is_null($objectId)) {
            $name = sprintf('Data Bag %d', $this->objectId);
            $this->SetName($name);
        }
    }

    /**
     * Returns the decoded array from the data bag.
     * If the data bag does not contain valid data, it returns an empty array.
     * The method locks the IPS variable object for exclusive use. You must either call DataBag::LeaveSemaphore() or
     * DataBag::Set() to release the semaphore again.
     * @param int $waitTime Number of milliseconds to wait for another thread to release the semaphore.
     * @return array Data from the data bag.
     * @see DataBag::LeaveSemaphore()
     * @see DataBag::Set()
     */
    public function Get($waitTime = Semaphore::DEFAULT_WAIT_TIME)
    {
        // Enter the semaphore
        $this->EnterSemaphore($waitTime);

        try {
            // Return the decoded JSON array
            return parent::Get();
        } catch (VariableInvalidDataException $e) {
            // Return an empty array if the data is invalid
            return array();
        }
    }

    /**
     * Saves the data in the data bag.
     * @param array $data Data to be stores in the data bag.
     * @return $this Fluent interface.
     */
    public function Set($data)
    {
        // JSON encode and save the array
        parent::Set($data);

        // Leave the semaphore
        $this->LeaveSemaphore();

        // Enable fluent interface
        return $this;
    }

}