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


namespace Patami\IPS\Objects\Drivers\KNX;


use Patami\IPS\Objects\Drivers\Exceptions\DriverSetFailedException;
use Patami\IPS\Objects\Drivers\Exceptions\StateInvalidDataException;
use Patami\IPS\Objects\Drivers\GetDimLevelInterface;
use Patami\IPS\Objects\Drivers\GetNumberInterface;
use Patami\IPS\Objects\Drivers\GetSwitchInterface;
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\Drivers\SetDimLevelInterface;
use Patami\IPS\Objects\Drivers\SetNumberInterface;
use Patami\IPS\Objects\Drivers\SetSwitchInterface;
use Patami\IPS\Objects\Drivers\StateInterface;
use Patami\IPS\Objects\IntegerVariable;
use Patami\IPS\Objects\VariableProfiles;
use Patami\IPS\Services\KNX\KNX;


/**
 * IPS Driver for KNX dimmers.
 * @package IPSPATAMI
 */
class KNXDimmerDriver extends KNXDriver implements
    GetSwitchInterface,
    SetSwitchInterface,
    GetDimLevelInterface,
    SetDimLevelInterface,
    GetNumberInterface,
    SetNumberInterface,
    StateInterface
{

    /**
     * @var IntegerVariable IPS child variable object.
     */
    protected $value;

    /**
     * @var int Maximum value of the dimmer.
     */
    protected $maxValue;

    /**
     * Determines whether the driver supports the object.
     * The object must satisfy the parent object requirements and:
     * - have a child IPS variable object, that must:
     *     - have the identifier 'Value'.
     *     - be an integer variable.
     *     - have the variable profile '~Intensity.100' or '~Intensity.255'.
     * @throws InvalidObjectException if the object is not supported by the driver.
     */
    protected function ValidateObject()
    {
        // Make sure the parent requirements are satisfied
        parent::ValidateObject();

        // Find the value variable
        $value = $this->object->GetChildByIdent('Value');

        // Throw an exception if the object is not a variable
        if (! $value instanceof IntegerVariable) {
            throw new InvalidObjectException();
        }

        // Remember the maximum variable value or throw an exception if the variable profile is incorrect
        if ($value->HasProfileName(VariableProfiles::TYPE_INTENSITY_100)) {
            $this->maxValue = 100;
        } elseif ($value->HasProfileName(VariableProfiles::TYPE_INTENSITY_255)) {
            $this->maxValue = 255;
        } else {
            throw new InvalidObjectException();
        }

        // Remember the value object
        $this->value = $value;
    }

    public function GetMonitoredObjects()
    {
        return array(
            $this->value
        );
    }

    public function GetSwitch()
    {
        // Return the value (on if level > 0)
        return $this->GetDimLevel() > 0;
    }

    public function SetSwitch($value)
    {
        // Set the value
        $this->SetDimLevel($value? 1: 0);

        // Enable fluent interface
        return $this;
    }

    public function GetDimLevel()
    {
        // Return the dim level
        return $this->value->Get() / $this->maxValue;
    }

    public function SetDimLevel($value)
    {
        // Set the dim level
        $result = KNX::SendDimValue($this->object->GetId(), $value * $this->maxValue);

        // Throw an exception if the dim level could not be set
        if ($result === false) {
            throw new DriverSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    public function GetNumber()
    {
        // Return the dim level
        return $this->GetDimLevel();
    }

    public function SetNumber($value)
    {
        // Set the dim level and enable fluent interface
        return $this->SetDimLevel($value);
    }

    public function GetState()
    {
        return array(
            'value' => $this->GetDimLevel()
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
        $this->SetDimLevel($value);

        // Enable fluent interface
        return $this;
    }

}