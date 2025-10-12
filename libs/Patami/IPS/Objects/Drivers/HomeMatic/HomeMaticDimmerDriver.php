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
use Patami\IPS\Objects\FloatVariable;
use Patami\IPS\Objects\VariableProfiles;


/**
 * IPS Driver for HomeMatic dimmers.
 * @package IPSPATAMI
 */
class HomeMaticDimmerDriver extends HomeMaticDriver implements
    GetSwitchInterface,
    SetSwitchInterface,
    GetDimLevelInterface,
    SetDimLevelInterface,
    GetNumberInterface,
    SetNumberInterface,
    StateInterface
{

    /**
     * @var FloatVariable IPS child variable object.
     */
    protected $value;

    /**
     * Determines whether the driver supports the object.
     * The object must satisfy the parent object requirements and:
     * - have a child IPS variable object, that must:
     *     - have the identifier 'LEVEL'.
     *     - be a float variable.
     *     - have the variable profile '~Intensity.1'.
     * @throws InvalidObjectException if the object is not supported by the driver.
     */
    protected function ValidateObject()
    {
        // Make sure the parent requirements are satisfied
        parent::ValidateObject();

        // Find the value variable
        $value = $this->object->GetChildByIdent('LEVEL');

        // Throw an exception if the object is not a variable
        if (! $value instanceof FloatVariable) {
            throw new InvalidObjectException();
        }

        // Throw an exception if the variable profile is incorrect
        if (! $value->HasProfileName(VariableProfiles::TYPE_INTENSITY_1)) {
            throw new InvalidObjectException();
        }

        // Remember the value object
        $this->value = $value;
    }

    public function GetMonitoredObjects()
    {
        return array($this->value);
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
        return $this->value->Get();
    }

    public function SetDimLevel($value)
    {
        // Set the dim level
        /** @noinspection PhpUndefinedFunctionInspection */
        $result = @HM_WriteValueFloat($this->object->GetId(), 'LEVEL', $value);

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