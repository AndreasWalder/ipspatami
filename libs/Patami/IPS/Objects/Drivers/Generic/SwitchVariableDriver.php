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


namespace Patami\IPS\Objects\Drivers\Generic;


use Patami\IPS\Objects\BooleanVariable;
use Patami\IPS\Objects\Drivers\Exceptions\StateInvalidDataException;
use Patami\IPS\Objects\Drivers\GetSwitchInterface;
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\Drivers\SetSwitchInterface;
use Patami\IPS\Objects\Drivers\StateInterface;


/**
 * IPS Driver for an IPS switch variable.
 * @package IPSPATAMI
 */
class SwitchVariableDriver extends VariableDriver implements
    GetSwitchInterface,
    SetSwitchInterface,
    StateInterface
{

    /**
     * Determines whether the driver supports the object.
     * The object must satisfy the parent object requirements and:
     * - be a boolean variable.
     * @throws InvalidObjectException if the object is not supported by the driver.
     */
    protected function ValidateObject()
    {
        // Make sure the parent requirements are satisfied
        parent::ValidateObject();

        // Throw an exception if the object is not a boolean variable
        if (! $this->object instanceof BooleanVariable) {
            throw new InvalidObjectException();
        }
    }

    public function GetSwitch()
    {
        // Return the value
        return $this->object->Get();
    }

    public function SetSwitch($value)
    {
        // Set the value
        $this->object->Set($value);

        // Enable fluent interface
        return $this;
    }

    public function GetState()
    {
        return array(
            'value' => $this->GetSwitch()
        );
    }

    public function SetState(array $state)
    {
        // Get the value
        $value = @$state['value'];

        // Throw an exception if the value is invalid
        if (is_null($value)) {
            throw new StateInvalidDataException();
        }

        // Set the value
        $this->SetSwitch($value);

        // Enable fluent interface
        return $this;
    }

}