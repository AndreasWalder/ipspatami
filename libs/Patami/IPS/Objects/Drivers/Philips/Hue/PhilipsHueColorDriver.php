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


namespace Patami\IPS\Objects\Drivers\Philips\Hue;


use Patami\IPS\Objects\Drivers\Exceptions\DriverSetFailedException;
use Patami\IPS\Objects\Drivers\Exceptions\StateInvalidDataException;
use Patami\IPS\Objects\Drivers\GetColorInterface;
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\Drivers\GetDimLevelInterface;
use Patami\IPS\Objects\Drivers\GetSwitchInterface;
use Patami\IPS\Objects\Drivers\SetColorInterface;
use Patami\IPS\Objects\Drivers\SetDimLevelInterface;
use Patami\IPS\Objects\Drivers\SetSwitchInterface;
use Patami\IPS\Objects\Drivers\StateInterface;


/**
 * IPS Driver for a Philips Hue light with color support.
 * @package IPSPATAMI
 */
class PhilipsHueColorDriver extends PhilipsHueDriver implements
    GetSwitchInterface,
    SetSwitchInterface,
    GetColorInterface,
    SetColorInterface,
    GetDimLevelInterface,
    SetDimLevelInterface,
    StateInterface
{

    /**
     * Include common code.
     */
    use PhilipsHueColorTrait;
    use PhilipsHueDimLevelTrait;

    /**
     * Determines whether the driver supports the object.
     * The object must satisfy the parent object requirements and:
     * - the lamp must support the color feature.
     * - have a child IPS variable object, that must:
     *     - have the identifier 'COLOR'.
     *     - be an integer variable.
     * @throws InvalidObjectException if the object is not supported by the driver.
     */
    protected function ValidateObject()
    {
        // Make sure the parent requirements are satisfied
        parent::ValidateObject();

        // Throw an exception if the lamp features don't match
        if ($this->features != self::FEATURE_COLOR) {
            throw new InvalidObjectException();
        }

        // Validate the values
        $this->ValidateObjectColor();
        $this->ValidateObjectDimLevel();
    }

    public function GetMonitoredObjects()
    {
        return array(
            $this->state,
            $this->color
        );
    }

    public function GetState()
    {
        return array(
            'state' => $this->GetSwitch(),
            'color' => $this->color->Get()
        );
    }

    public function SetState(array $state)
    {
        // Get the values
        $color = @$state['color'];
        $state = @$state['state'];

        // Throw an exception if the values are invalid
        if (is_null($state) || is_null($color)) {
            throw new StateInvalidDataException();
        }

        // Set the values
        /** @noinspection PhpUndefinedFunctionInspection */
        $result = @HUE_SetValues(
            $this->object->GetId(),
            array(
                'COLOR_MODE' => 0,
                'COLOR' => $color,
                'STATE' => $state
            )
        );

        // Throw an exception if the value could not be set
        if ($result === false) {
            throw new DriverSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

}