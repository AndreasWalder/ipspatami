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


namespace Patami\Helpers;


/**
 * Provides number helper functions.
 * @package IPSPATAMI
 */
class Number
{

    /**
     * Checks if a value is within a range of other values
     * @param int|float $value Number to check whether it is within the range or not
     * @param int|float $minValue Lower bound of the range
     * @param int|float $maxValue Upper bound of the range
     * @param bool $inclusive True if the bounds should be included in the range
     * @return bool True if $value is within the range
     */
    public static function IsInRange($value, $minValue, $maxValue, $inclusive = true)
    {
        // Check if the value is within the range and return the result
        if ($inclusive) {
            return ($value >= $minValue) && ($value <= $maxValue);
        } else {
            return ($value > $minValue) && ($value < $maxValue);
        }
    }

    /**
     * Converts a number of bytes to megabytes.
     * @param int $bytes Number of bytes.
     * @return float Number of megabytes.
     */
    public static function BytesToMegabytes($bytes)
    {
        return $bytes / (1024 * 1024);
    }

}
