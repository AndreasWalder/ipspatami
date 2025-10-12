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
use Patami\IPS\System\IPS;


/**
 * Provides an OO implementation of an IPS category object.
 * @package IPSPATAMI
 */
class Category extends IPSObject
{

    /**
     * Creates a new IPS category object.
     * @return int IPS object ID of the new category.
     * @throws ObjectCreateFailedException if the object could not be created.
     * @see IPS::CreateCategory()
     */
    protected function Create()
    {
        // Create the category
        $objectId = IPS::CreateCategory();

        // Throw an exception if the module could not be created
        if (! $objectId) {
            throw new ObjectCreateFailedException();
        }

        // Return the object ID
        return $objectId;
    }

    /**
     * Deletes the IPS category object.
     * @throws ObjectDeleteFailedException if the object could not be deleted.
     * @see IPS::DeleteCategory()
     */
    public function Delete()
    {
        // Delete the object
        $result = IPS::DeleteCategory($this->objectId);

        // Throw an exception if the category could not be deleted
        if ($result === false) {
            throw new ObjectDeleteFailedException();
        }

        // Invalidate the object ID
        $this->objectId = null;
    }

}