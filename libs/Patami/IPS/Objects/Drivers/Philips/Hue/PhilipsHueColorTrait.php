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
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\IntegerVariable;
use Patami\IPS\System\Color;


/**
 * Provides common methods to control the color of Philips Hue Lamps.
 * @package IPSPATAMI
 */
trait PhilipsHueColorTrait
{

    /**
     * @var IntegerVariable IPS child variable object for the lamp color.
     */
    protected $color;

    /**
     * Determines whether the driver supports the object.
     * The object must:
     * - have a child IPS variable object, that must:
     *     - have the identifier 'COLOR'.
     *     - be an integer variable.
     * @throws InvalidObjectException if the object is not supported by the driver.
     */
    protected function ValidateObjectColor()
    {
        // Find the color variable
        /** @noinspection PhpUndefinedMethodInspection */
        $color = $this->object->GetChildByIdent('COLOR');

        // Throw an exception if the object is not an integer variable
        if (! $color instanceof IntegerVariable) {
            throw new InvalidObjectException();
        }

        // Remember the color variable
        $this->color = $color;
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
        /** @noinspection PhpUndefinedMethodInspection */
        $result = @HUE_SetColor(
            $this->object->GetId(),
            $color->Get()
        );

        // Throw an exception if the value could not be set
        if ($result === false) {
            throw new DriverSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

}