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


namespace Patami\IPS\Objects\Drivers\Patami\SplitRGB;


use Patami\IPS\Objects\Drivers\Driver;
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\Drivers\Exceptions\StateInvalidDataException;
use Patami\IPS\Objects\Drivers\GetColorInterface;
use Patami\IPS\Objects\Drivers\GetSwitchInterface;
use Patami\IPS\Objects\Drivers\SetColorInterface;
use Patami\IPS\Objects\Drivers\SetSwitchInterface;
use Patami\IPS\Objects\Drivers\StateInterface;
use Patami\IPS\Objects\Instance;
use Patami\IPS\Objects\IntegerVariable;
use Patami\IPS\System\Color;


/**
 * Abstract base class for Patami Split RGB instances.
 * @package IPSPATAMI
 */
class PatamiSplitRGBDriver extends Driver implements
    GetSwitchInterface,
    SetSwitchInterface,
    GetColorInterface,
    SetColorInterface,
    StateInterface
{

    /**
     * @var Instance IPS object instance.
     */
    protected $object;

    /**
     * @var IntegerVariable IPS child variable object for the lamp color.
     */
    protected $color;

    /**
     * Determines whether the driver supports the object.
     * The object must:
     * - be an IPS instance object.
     * - be a PatamiSplitRGB instance.
     * - have a child IPS variable object, that must:
     *     - have the identifier 'Color'.
     *     - be an integer variable.
     * @throws InvalidObjectException if the object is not supported by the driver.
     */
    protected function ValidateObject()
    {
        // Throw an exception if the object is not an instance
        if (! $this->object instanceof Instance) {
            throw new InvalidObjectException();
        }

        // Throw an exception if the module ID is incorrect
        if (! $this->object->IsModuleId('{42A59F8E-2797-4589-B55D-C81D93B7BB79}')) {
            throw new InvalidObjectException();
        }

        // Find the color variable
        /** @noinspection PhpUndefinedMethodInspection */
        $color = $this->object->GetChildByIdent('Color');

        // Throw an exception if the object is not an integer variable
        if (! $color instanceof IntegerVariable) {
            throw new InvalidObjectException();
        }

        // Remember the color variable
        $this->color = $color;

    }

    public function GetMonitoredObjects()
    {
        return array(
            $this->color
        );
    }

    public function GetSwitch()
    {
        // Get the color
        $color = $this->color->Get();

        // Check if the lamp is on and return the result
        return $color != Color::BLACK;
    }

    public function SetSwitch($value)
    {
        // Set the status
        /** @noinspection PhpUndefinedFunctionInspection */
        @PSRGB_SetSwitch(
            $this->object->GetId(),
            $value
        );

        // Enable fluent interface
        return $this;
    }

    public function GetColor()
    {
        // Get the color
        $color = $this->color->Get();

        // Create a new Color instance and return it
        return new Color($color);
    }

    public function SetColor(Color $color)
    {
        // Set the color
        /** @noinspection PhpUndefinedFunctionInspection */
        @PSRGB_SetColor(
            $this->object->GetId(),
            $color->Get()
        );

        // Enable fluent interface
        return $this;
    }

    public function GetState()
    {
        return array(
            'color' => $this->color->Get()
        );
    }

    public function SetState(array $state)
    {
        // Get the value
        $color = @$state['color'];

        // Throw an exception if the value is invalid
        if (is_null($color)) {
            throw new StateInvalidDataException();
        }

        // Set the color
        $this->SetColor(new Color($color));

        // Enable fluent interface
        return $this;
    }

}