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


namespace Patami\IPS\Objects\Drivers\HomeMatic;


use Patami\IPS\Objects\Drivers\Driver;
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\Instance;


/**
 * Abstract base class for HomeMatic IPS drivers.
 * @package IPSPATAMI
 */
abstract class HomeMaticDriver extends Driver
{

    /**
     * Determines whether the driver supports the object.
     * The object must:
     * - be an IPS instance object.
     * - be a HomeMatic instance.
     * @throws InvalidObjectException if the object is not supported by the driver.
     */
    protected function ValidateObject()
    {
        // Throw an exception if the object is not an instance
        if (! $this->object instanceof Instance) {
            throw new InvalidObjectException();
        }

        // Throw an exception if the module ID is incorrect
        if (! $this->object->IsModuleId('{EE4A81C6-5C90-4DB7-AD2F-F6BBD521412E}')) {
            throw new InvalidObjectException();
        }
    }

}