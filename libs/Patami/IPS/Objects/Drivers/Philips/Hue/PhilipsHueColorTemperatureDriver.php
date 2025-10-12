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


use Patami\Helpers\Number;
use Patami\IPS\Objects\Drivers\Exceptions\DriverSetFailedException;
use Patami\IPS\Objects\Drivers\Exceptions\StateInvalidDataException;
use Patami\IPS\Objects\Drivers\GetColorInterface;
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\Drivers\GetColorModeInterface;
use Patami\IPS\Objects\Drivers\GetColorTemperatureInterface;
use Patami\IPS\Objects\Drivers\GetDimLevelInterface;
use Patami\IPS\Objects\Drivers\GetSwitchInterface;
use Patami\IPS\Objects\Drivers\SetColorInterface;
use Patami\IPS\Objects\Drivers\SetColorModeInterface;
use Patami\IPS\Objects\Drivers\SetColorTemperatureInterface;
use Patami\IPS\Objects\Drivers\SetDimLevelInterface;
use Patami\IPS\Objects\Drivers\SetSwitchInterface;
use Patami\IPS\Objects\Drivers\StateInterface;
use Patami\IPS\Objects\IntegerVariable;


/**
 * IPS Driver for a Philips Hue light with color and color temperature support.
 * @package IPSPATAMI
 */
class PhilipsHueColorTemperatureDriver extends PhilipsHueDriver implements
    GetSwitchInterface,
    SetSwitchInterface,
    GetColorModeInterface,
    SetColorModeInterface,
    GetColorInterface,
    SetColorInterface,
    GetColorTemperatureInterface,
    SetColorTemperatureInterface,
    GetDimLevelInterface,
    SetDimLevelInterface,
    StateInterface
{

    /**
     * Include common code.
     */
    use PhilipsHueColorTrait;
    use PhilipsHueTemperatureTrait;
    use PhilipsHueDimLevelTrait;

    /**
     * @var IntegerVariable IPS child variable object for the lamp color mode.
     */
    protected $colorMode;

    const COLOR_MODE_COLOR = 0;
    const COLOR_MODE_TEMPERATURE = 1;

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
        if ($this->features != self::FEATURE_COLOR_TEMPERATURE) {
            throw new InvalidObjectException();
        }

        // Find the color mode variable
        /** @noinspection PhpUndefinedMethodInspection */
        $colorMode = $this->object->GetChildByIdent('COLOR_MODE');

        // Throw an exception if the object is not an integer variable
        if (! $colorMode instanceof IntegerVariable) {
            throw new InvalidObjectException();
        }

        // Remember the color mode variable
        $this->colorMode = $colorMode;

        // Validate the values
        $this->ValidateObjectColor();
        $this->ValidateObjectColorTemperature();
        $this->ValidateObjectDimLevel();
    }

    public function GetMonitoredObjects()
    {
        return array(
            $this->state,
            $this->colorMode,
            $this->color,
            $this->colorTemperature,
            $this->dimLevel
        );
    }

    public function GetColorMode()
    {
        // Get and return the color mode
        return $this->colorMode->Get();
    }

    public function SetColorMode($colorMode)
    {
        // Throw an exception if the value is out of bounds
        if ($colorMode != self::COLOR_MODE_COLOR && $colorMode != self::COLOR_MODE_TEMPERATURE) {
            throw new DriverSetFailedException();
        }

        // Set the color mode
        /** @noinspection PhpUndefinedFunctionInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $result = @HUE_SetValues(
            $this->object->GetId(),
            array(
                'COLOR_MODE' => $colorMode
            )
        );

        // Throw an exception if the value could not be set
        if ($result === false) {
            throw new DriverSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    public function GetState()
    {
        return array(
            'state' => $this->GetSwitch(),
            'colorMode' => $this->colorMode->Get(),
            'color' => $this->color->Get(),
            'colorTemperature' => $this->colorTemperature->Get(),
            'dimLevel' => $this->dimLevel->Get()
        );
    }

    public function SetState(array $state)
    {
        // Get the values
        $colorMode = @$state['colorMode'];
        $color = @$state['color'];
        $colorTemperature = @$state['colorTemperature'];
        $dimLevel = @$state['dimLevel'];
        $state = @$state['state'];

        // Throw an exception if the values are invalid
        if (is_null($state) || is_null($color) || is_null($colorTemperature) || is_null($colorMode) || is_null($dimLevel)) {
            throw new StateInvalidDataException();
        }

        // Set the values
        if ($state) {
            if ($colorMode == self::COLOR_MODE_COLOR) {
                // Color mode
                $this->colorMode->Set($colorMode);
                /** @noinspection PhpUndefinedFunctionInspection */
                $result = @HUE_SetValues(
                    $this->object->GetId(),
                    array(
                        'STATE' => true,
                        'COLOR' => $color
                    )
                );
            } elseif ($colorMode == self::COLOR_MODE_TEMPERATURE) {
                // Color temperature mode
                $this->colorMode->Set($colorMode);
                $this->colorTemperature->Set($colorTemperature);
                /** @noinspection PhpUndefinedFunctionInspection */
                $result = @HUE_SetValues(
                    $this->object->GetId(),
                    array(
                        'STATE' => true,
                        'BRIGHTNESS' => $dimLevel
                    )
                );
            } else {
                // Invalid mode
                throw new DriverSetFailedException();
            }
        } else {
            // Switch off the lamp
            /** @noinspection PhpUndefinedFunctionInspection */
            $result = @HUE_SetState($this->object->GetId(), false);
        }

        // Throw an exception if the value could not be set
        if ($result === false) {
            throw new DriverSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

}