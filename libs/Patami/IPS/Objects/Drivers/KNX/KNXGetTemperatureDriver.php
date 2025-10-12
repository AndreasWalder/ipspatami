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


use Patami\IPS\Objects\Drivers\GetNumberInterface;
use Patami\IPS\Objects\Drivers\GetTemperatureInterface;
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\FloatVariable;
use Patami\IPS\Objects\VariableProfiles;


/**
 * IPS Driver for KNX temperature sensors.
 * @package IPSPATAMI
 */
class KNXGetTemperatureDriver extends KNXDriver implements
    GetTemperatureInterface,
    GetNumberInterface
{

    /**
     * @var FloatVariable IPS child variable object.
     */
    protected $value;

    /**
     * Determines whether the driver supports the object.
     * The object must satisfy the parent object requirements and:
     * - have a child IPS variable object, that must:
     *     - have the identifier 'Value'.
     *     - be a float variable.
     *     - have the variable profile '~Temperature'.
     * @throws InvalidObjectException if the object is not supported by the driver.
     */
    protected function ValidateObject()
    {
        // Make sure the parent requirements are satisfied
        parent::ValidateObject();

        // Find the value variable
        $value = $this->object->GetChildByIdent('Value');

        // Throw an exception if the object is not a variable
        if (! $value instanceof FloatVariable) {
            throw new InvalidObjectException();
        }

        // Throw an exception if the variable profile is incorrect
        if (! $value->HasCustomProfileName(VariableProfiles::TYPE_TEMPERATURE)) {
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

    public function GetTemperature()
    {
        // Return the value
        return $this->value->Get();
    }

    public function GetNumber()
    {
        // Return the temperature
        return $this->GetTemperature();
    }

}