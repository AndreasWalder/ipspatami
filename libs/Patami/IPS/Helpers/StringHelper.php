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


namespace Patami\IPS\Helpers;


use Patami\IPS\I18N\Translator;
use Patami\IPS\System\Locales;


/**
 * Provides string helper functions.
 * @package IPSPATAMI
 */
class StringHelper
{

    /**
     * Returns a translated text (yes/no) based on the given boolean value.
     * @param bool $value Boolean value.
     * @param string|null $locale Locale to be used for the conversion or null to use the IPS system locale.
     * @return string Translated text.
     * @see Locales
     */
    public static function GetBooleanAsString($value, $locale = null)
    {
        if ($value) {
            // Translate "Yes" and return the result
            return Translator::Get('patami.framework.helpers.stringhelper.yes', $locale);
        } else {
            // Translate "No" and return the result
            return Translator::Get('patami.framework.helpers.stringhelper.no', $locale);
        }
    }

    /**
     * Returns a translated text (yes/no) based on the given boolean value.
     * @param bool $value Boolean value.
     * @param string|null $locale Locale to be used for the conversion or null to use the IPS system locale.
     * @return string Translated text.
     * @see Locales
     */
    public static function GetBooleanAsYesNo($value, $locale = null)
    {
        // Return the result
        return self::GetBooleanAsString($value, $locale);
    }

    /**
     * Returns a translated text (on/off) based on the given boolean value.
     * @param bool $value Boolean value.
     * @param string|null $locale Locale to be used for the conversion or null to use the IPS system locale.
     * @return string Translated text.
     * @see Locales
     */
    public static function GetBooleanAsOnOff($value, $locale = null)
    {
        if ($value) {
            // Translate "On" and return the result
            return Translator::Get('patami.framework.helpers.stringhelper.on', $locale);
        } else {
            // Translate "Off" and return the result
            return Translator::Get('patami.framework.helpers.stringhelper.off', $locale);
        }
    }

}
