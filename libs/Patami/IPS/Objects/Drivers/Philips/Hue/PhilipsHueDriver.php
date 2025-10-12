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


use Patami\IPS\Objects\BooleanVariable;
use Patami\IPS\Objects\Drivers\Driver;
use Patami\IPS\Objects\Drivers\Exceptions\DriverSetFailedException;
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\Drivers\GetSwitchInterface;
use Patami\IPS\Objects\Drivers\SetSwitchInterface;
use Patami\IPS\Objects\Instance;


/**
 * Abstract base class for Philips Hue drivers.
 * @package IPSPATAMI
 * @link https://github.com/traxanos/SymconHUE
 */
abstract class PhilipsHueDriver extends Driver implements
    GetSwitchInterface,
    SetSwitchInterface
{

    /** Lamp supports controlling the color and the white temperature. */
    const FEATURE_COLOR_TEMPERATURE = 0;

    /** Lamp supports controlling the color. */
    const FEATURE_COLOR = 1;

    /** Lamp supports controlling the white temperature. */
    const FEATURE_TEMPERATURE = 2;

    /** Lamp supports controlling the white brightness. */
    const FEATURE_BRIGHTNESS = 3;

    /**
     * @var int|null Features of the lamp.
     */
    protected $features = null;

    /**
     * @var BooleanVariable IPS child variable object for the lamp state.
     */
    protected $state;

    /**
     * Determines whether the driver supports the object.
     * The object must:
     * - be an IPS instance object.
     * - be a Philips Hue instance.
     * - have a child IPS variable object, that must:
     *     - have the identifier 'STATE'.
     *     - be a boolean variable.
     * @throws InvalidObjectException if the object is not supported by the driver.
     */
    protected function ValidateObject()
    {
        // Throw an exception if the object is not an instance
        if (! $this->object instanceof Instance) {
            throw new InvalidObjectException();
        }

        // Throw an exception if the module ID is incorrect
        if (! $this->object->IsModuleId('{729BE8EB-6624-4C6B-B9E5-6E09482A3E36}')) {
            throw new InvalidObjectException();
        }

        // Get and remember the light features
        $this->features = $this->object->GetProperty('LightFeatures');

        // Find the state variable
        $state = $this->object->GetChildByIdent('STATE');

        // Throw an exception if the object is not a boolean variable
        if (! $state instanceof BooleanVariable) {
            throw new InvalidObjectException();
        }

        // Remember the state variable
        $this->state = $state;
    }

    public function GetSwitch()
    {
        // Return the state
        return $this->state->Get();
    }

    public function SetSwitch($value)
    {
        // Set the state
        /** @noinspection PhpUndefinedFunctionInspection */
        $result = @HUE_SetState($this->object->GetId(), $value);

        // Throw an exception if the value could not be set
        if ($result === false) {
            throw new DriverSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

}