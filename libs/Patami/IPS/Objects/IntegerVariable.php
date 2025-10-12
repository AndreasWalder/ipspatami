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


use Patami\IPS\Objects\Exceptions\ObjectSetFailedException;
use Patami\IPS\System\IPS;


/**
 * Provides an OO implementation of an integer IPS variable object.
 * @package IPSPATAMI
 */
class IntegerVariable extends Variable
{

    /**
     * Returns the type of the variable.
     * @return int Type of the variable.
     * @see Variable::VARIABLE_TYPE_INTEGER
     */
    public function GetVariableType()
    {
        return self::VARIABLE_TYPE_INTEGER;
    }

    /**
     * Returns the value of the IPS variable object.
     * @return int Value of the variable.
     * @see IPS::GetValueInteger()
     */
    public function Get()
    {
        return IPS::GetValueInteger($this->objectId);
    }

    /**
     * Sets the value of the IPS variable object.
     * @param int $value New value of the variable.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the value of the variable object could not be updated.
     * @see IPS::SetValueInteger()
     */
    public function Set($value)
    {
        // Set the value via IPS
        $result = IPS::SetValueInteger($this->objectId, $value);

        // Throw an exception if setting the value failed
        if ($result === false) {
            throw new ObjectSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

}