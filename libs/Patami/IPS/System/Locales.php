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


use Patami\IPS\I18N\Translator;
use Patami\IPS\Objects\VariableProfiles;
use Patami\IPS\Objects\VariableProfile;
use Patami\IPS\Objects\VariableProfileAssociation;


/**
 * Helper class to handle locales and languages.
 * @package IPSPATAMI
 */
class Locales
{

    /**
     * German German.
     */
    const DE_DE = 'de-DE';

    /**
     * United States English.
     */
    const EN_US = 'en-US';

    /**
     * British English.
     */
    const EN_GB = 'en-GB';

    /**
     * Returns the mapping of the internal locale ID to the POSIX locale and its display name.
     * @return array Locale mappings.
     */
    protected static function GetLocales()
    {
        return  array(
            0 => array(
                'code' => self::DE_DE,
                'name' => Translator::Get('patami.framework.system.locales.select_options.de-de')
            ),
            1 => array(
                'code' => self::EN_US,
                'name' => Translator::Get('patami.framework.system.locales.select_options.en-us')
            ),
            2 => array(
                'code' => self::EN_GB,
                'name' => Translator::Get('patami.framework.system.locales.select_options.en-gb')
            )
        );
    }

    /**
     * Returns the display name associated to a POSIX locale code.
     * @param string $code POSIX code of the locale.
     * @return string|bool Display name of the locale or false if the POSIX code is unknown.
     */
    public static function GetNameByCode($code)
    {
        $locales = self::GetLocales();
        foreach ($locales as $locale) {
            if ($locale['code'] == $code) {
                return $locale['name'];
            }
        }

        return false;
    }

    /**
     * Returns the internal locale code associated to a POSIX locale code.
     * @param string $code POSIX code of the locale.
     * @return int|bool Internal locale ID or false if the POSIX code is unknown.
     */
    public static function GetIndexByCode($code)
    {
        $locales = self::GetLocales();
        foreach ($locales as $index => $locale) {
            if ($locale['code'] == $code) {
                return $index;
            }
        }

        return false;
    }

    /**
     * Returns the POSIX locale code associated to an internal locale ID.
     * @param int $index Internal locale ID.
     * @return string|bool POSIX locale code or false if the internal ID is unknown.
     */
    public static function GetCodeByIndex($index)
    {
        $locales = self::GetLocales();
        $locale = @$locales[$index];
        if ($locale) {
            return $locale['code'];
        }
        return false;
    }

    /**
     * Returns an array used to populate a drop down configuration form field.
     * @return array Drop down configuration form field data.
     */
    public static function GetSelectOptions()
    {
        $locales = self::GetLocales();
        $options = array();
        foreach ($locales as $index => $locale) {
            $options[] = array(
                'label' => $locale['name'],
                'value' => $index
            );
        }
        return $options;
    }

    /**
     * Returns the default drop down entry based on the language of the IP Symcon installation.
     * @return int Index of the default drop down option.
     */
    public static function GetDefaultSelectOption()
    {
        // Return the drop down index corresponding to the IPS locale
        return self::GetIndexByCode(self::GetIPSLocale());
    }

    /**
     * Returns the language of the IP Symcon installation as a POSIX language code.
     * @return string POSIX locale code of the IP Symcon installation.
     */
    public static function GetIPSLocale()
    {
        // Get the translated name of a built-in variable profile association
        /** @var VariableProfile $profile */
        $profile = VariableProfiles::GetByName(VariableProfiles::TYPE_SWITCH);
        /** @var VariableProfileAssociation $association */
        $association = $profile->GetAssociationByValue(0);
        $name = $association->GetName();

        // Check if the name is German, otherwise English
        $locale = ($name == 'Aus')? 'de-DE': 'en-US';

        // Return the value
        return $locale;
    }

}