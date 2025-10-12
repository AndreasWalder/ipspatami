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


namespace Patami\IPS\System;


use Patami\IPS\System\Exceptions\ColorInvalidException;


/**
 * RGB Color Class.
 * Provides functions for manipulating RGB colors.
 * @package IPSPATAMI
 */
class Color
{

    /**
     * Special color code if no color (ie. transparency) should be used.
     * Can only be used in variable profiles.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/variablenprofile/ips-setvariableprofileassociation/
     */
    const TRANSPARENT = -1;

    /** Minimum RGB value (black). */
    const MINIMUM     = 0x000000;
    /** Maximum RGB value (white). */
    const MAXIMUM     = 0xFFFFFF;

    /** Black. */
    const BLACK       = 0x000000;
    /** White */
    const WHITE       = 0xFFFFFF;

    /** Red. */
    const RED         = 0xFF0000;
    /** Green. */
    const GREEN       = 0x00FF00;
    /** Blue. */
    const BLUE        = 0x0000FF;

    /** Yellow. */
    const YELLOW      = 0xFFFF00;
    /** Magenta. */
    const MAGENTA     = 0xFF00FF;
    /** Cyan. */
    const CYAN        = 0x00FFFF;

    /** Green RGB value used by IPS. */
    const IPS_OK      = 0xC0FFC0;
    /** Red RGB value used by IPS. */
    const IPS_ERROR   = 0xFFC0C0;

    /**
     * @var int RGB value.
     */
    protected $color;

    /**
     * Color constructor.
     * @param Color|int $color RGB value of the color.
     */
    public function __construct($color = self::TRANSPARENT)
    {
        // Remember the color
        $this->Set($color);
    }

    /**
     * Factory method which creates and returns a new instance of this class.
     * @param Color|int $color RGB value of the color.
     * @return Color New instance of the class.
     */
    public static function Create($color = self::TRANSPARENT)
    {
        // Get the name of the called class
        $className = get_called_class();

        // Return a new instance of the class
        return new $className($color);
    }

    /**
     * Returns the RGB value of the color.
     * @return int RGB value of the color.
     */
    public function Get()
    {
        // Return the color
        return $this->color;
    }

    /**
     * Checks if the color is transparent.
     * @return bool True if the color is transparent.
     */
    public function IsTransparent()
    {
        return $this->color === self::TRANSPARENT;
    }

    /**
     * Sets the RGB value of the color.
     * @param int|Color $color RGB value of the color.
     * @return $this Fluent interface.
     * @throws ColorInvalidException if the RGB value is out of bounds.
     */
    public function Set($color)
    {
        // Get the color of the specified color if a Color object was given
        if ($color instanceof Color) {
            $color = $color->Get();
        }

        // Throw an exception if the color is invalid
        if ($color != self::TRANSPARENT && ($color < self::MINIMUM || $color > self::MAXIMUM)) {
            throw new ColorInvalidException();
        }

        // Remember the color
        $this->color = $color;

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the red, green and blue component values of the color.
     * @return array RGB color components.
     */
    public function GetRGB()
    {
        // Return the color
        return array(
            'red' => $this->GetRed(),
            'green' => $this->GetGreen(),
            'blue' => $this->GetBlue()
        );
    }

    /**
     * Sets the red, green and blue component values of the color.
     * @param int $red Red component value (0-255). Use null for the current value.
     * @param int $green Green component value (0-255). Use null for the current value.
     * @param int $blue Blue component value (0-255). Use null for the current value.
     * @return $this Fluent interface.
     * @throws ColorInvalidException if the values are out of bounds.
     */
    public function SetRGB($red = null, $green = null, $blue = null)
    {
        // Get the current values if not set
        if (is_null($red)) {
            $red = $this->GetRed();
        }
        if (is_null($green)) {
            $green = $this->GetGreen();
        }
        if (is_null($blue)) {
            $blue = $this->GetBlue();
        }

        // Set the color
        $this->Set(($red << 16) + ($green << 8) + $blue);

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the hex color code (for use in HTML for example).
     * @param bool $withHash True if the result should be prefixed with a # sign.
     * @return string Hexadecimal representation of the color.
     */
    public function GetHex($withHash = true)
    {
        // Return an empty string if the color is transparent
        if ($this->IsTransparent()) {
            return '';
        }

        // Generate the color string
        $color = sprintf('%02x%02x%02x', $this->GetRed(), $this->GetGreen(), $this->GetBlue());

        // Prefix the string with a hash if necessary
        if ($withHash) {
            $color = '#' . $color;
        }

        // Return the hex string
        return $color;
    }

    /**
     * Sets the color value using a hex string (as used in HTML for example).
     * Sets the color to black if the hex string is invalid.
     * @param string $hexColor Hex color code.
     * @return $this Fluent interface.
     */
    public function SetHex($hexColor)
    {
        // Strip off the hash
        $hexColor = str_replace('#', '', $hexColor);

        // Set the color
        if (strlen($hexColor) == 6) {
            $red = hexdec(substr($hexColor, 0, 2));
            $green = hexdec(substr($hexColor, 2, 2));
            $blue = hexdec(substr($hexColor, 4, 2));
            $this->Set($red * 0xFFFF + $green * 0xFF + $blue);
        } else {
            $this->color = 0;
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Sets the color transparent.
     * @return $this Fluent interface.
     */
    public function SetTransparent()
    {
        // Set the color to transparent and enable fluid interface
        return $this->Set(self::TRANSPARENT);
    }

    /**
     * Returns the red component of the color.
     * @return int Red component value or null if the color is transparent.
     */
    public function GetRed()
    {
        // Return null if the color is transparent
        if ($this->IsTransparent()) {
            return null;
        }

        // Return the value
        return ($this->color >> 16) & 0xFF;
    }

    /**
     * Sets the red component of the color.
     * @param int $red Red component value (0-255).
     * @return $this Fluent interface.
     * @throws ColorInvalidException if the value is out of bounds.
     */
    public function SetRed($red)
    {
        // Set the color and enable fluent interface
        return $this->SetRGB($red, null, null);
    }

    /**
     * Returns the green component of the color.
     * @return int Green component value or null if the color is transparent.
     */
    public function GetGreen()
    {
        // Return null if the color is transparent
        if ($this->IsTransparent()) {
            return null;
        }

        // Return the value
        return ($this->color >> 8) & 0xFF;
    }

    /**
     * Sets the green component of the color.
     * @param int $green Green component value (0-255).
     * @return $this Fluent interface.
     * @throws ColorInvalidException if the value is out of bounds.
     */
    public function SetGreen($green)
    {
        // Set the color and enable fluent interface
        return $this->SetRGB(null, $green, null);
    }

    /**
     * Returns the blue component of the color.
     * @return int Blue component value or null if the color is transparent.
     */
    public function GetBlue()
    {
        // Return null if the color is transparent
        if ($this->IsTransparent()) {
            return null;
        }

        // Return the value
        return $this->color & 0xFF;
    }

    /**
     * Sets the blue component of the color.
     * @param int $blue Blue component value (0-255).
     * @return $this Fluent interface.
     * @throws ColorInvalidException if the value is out of bounds.
     */
    public function SetBlue($blue)
    {
        // Set the color and enable fluent interface
        return $this->SetRGB(null, null, $blue);
    }

}