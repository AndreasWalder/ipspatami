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


namespace Patami\IPS\Objects\Drivers;


use Patami\IPS\Objects\Drivers\Exceptions\DriverSetFailedException;
use Patami\IPS\Objects\Drivers\Exceptions\StateInvalidDataException;


/**
 * Interface used to get and set the serializable state of an object.
 * This can be used to save and restore the current state of an object, eg. to implement scene controllers.
 * @package IPSPATAMI
 * @see Driver
 */
interface StateInterface
{

    /**
     * Returns a state of the object.
     * The return value can be serialized and stored in a variable and then be used to restore the state with the
     * SetState() method.
     * @return array State of the object.
     */
    public function GetState();

    /**
     * Sets the state of the object with the given state data.
     * The state date can be retrieved from an object using the GetState() method.
     * @param array $state State of the object.
     * @return $this Fluent interface.
     * @throws StateInvalidDataException if the state data is invalid.
     * @throws DriverSetFailedException if the state could not be set.
     */
    public function SetState(array $state);

}