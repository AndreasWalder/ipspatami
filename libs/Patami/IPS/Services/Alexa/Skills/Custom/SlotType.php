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


namespace Patami\IPS\Services\Alexa\Skills\Custom;


use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\LocaleNotSupportedException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\SlotValueNotSetException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\SlotValueNotSupportedException;
use Patami\IPS\Services\Alexa\Skills\LocaleInterface;


/**
 * Abstract base class that provides an OO encapsulation of Amazon Alexa intent slot types.
 *
 * It provides methods to translate slot values to slot value keys, making it possible to define multiple slot values
 * for the same slot value key, eg. to defined alternate spellings of the value, plural/singular and so on.
 * It also provides methods retrieve information for the slot value keys, such as a "normalized" slot value.
 *
 * @package IPSPATAMI
 */
abstract class SlotType
{

    /**
     * Returns a list of slot value keys and slot values associated to them.
     * It must return data in that form:
     * ```
     * array(
     *     'de-DE' => array(
     *         'alias11' => 'key1',
     *         'alias12' => 'key1',
     *         'alias13' => 'key1',
     *         'alias21' => 'key2',
     *         'alias22' => 'key2',
     *         [...]
     *     ),
     *     'en-US' => array(
     *         [...]
     *     )
     * )
     * ```
     * @return array List of slot value keys and their values.
     */
    public static function GetAliasMap()
    {
        return array();
    }

    /**
     * Returns the slot value key associated to the intent slot of the given Request.
     * @param Request $request Request object of the current Custom Skill request.
     * @param string $name Intent slot name.
     * @param bool $throwExceptions True if the method should throw an exception if the specified intent slot is not
     * set in the request or false to return an empty string.
     * @return string|null Slot value key or null if the specified intent slot is not set in the request.
     * @throws LocaleNotSupportedException if the locale is not supported by the class.
     * @throws SlotValueNotSetException if the intent slot is not set in the request.
     * @throws SlotValueNotSupportedException if the value of the intent slot could not be found in the alias map.
     */
    public static function GetKey(Request $request, $name, $throwExceptions = true)
    {
        $tag = sprintf('Slot "%s"', $name);

        // Get the value
        $value = $request->slots[$name];

        // Check if the value is missing
        if (! $value || $value == '') {
            $request->Debug($tag, 'Slot is empty');
            if ($throwExceptions) {
                throw new SlotValueNotSetException();
            } else {
                return '';
            }
        }

        // Get the alias map
        /** @var SlotType $className */
        $className = get_called_class();
        $aliases = $className::GetAliasMap();

        // Check if the locale is supported
        $locale = $request->GetLocale();
        if (! isset($aliases[$locale])) {
            $request->Debug($tag, 'Locale is not supported');
            if ($throwExceptions) {
                throw new LocaleNotSupportedException();
            } else {
                return null;
            }
        }

        // Get the key
        $value = strtolower($value);
        $key = @$aliases[$locale][$value];

        // Check if the key was found
        if (is_null($key)) {
            $request->Debug($tag, 'Value is not supported');
            if ($throwExceptions) {
                throw new SlotValueNotSupportedException();
            } else {
                return null;
            }
        }

        // Return the key
        $request->Debug($tag, $key);
        return $key;
    }

    /**
     * Returns the information associated to the slot value key.
     * It must return data in that form:
     * ```
     * array(
     *     'key1' => array(
     *         'name' => array(
     *             'de-DE' => 'Deutscher Name',
     *             'en-US' => 'American english name',
     *             'en-GB' => 'British english name',
     *          ),
     *          [...]
     *      ),
     *      [...]
     * )
     * ```
     * The `name` attribute is required and is used to retrieve the translated, "normalized" name of the slot value key.
     * @return array Information about the slot value key.
     */
    public static function GetInfoMap()
    {
        return array();
    }

    /**
     * Returns information about a slot value key.
     * @param string $key Slot value key.
     * @param bool $throwExceptions True if the method should throw an exception if the slot value key could not be found.
     * @return array|null Information about the slot value key or null if the key could not be found.
     * @throws SlotValueNotSupportedException if the key could not be found.
     */
    public static function GetInfo($key, $throwExceptions = true)
    {
        // Get the info map
        /** @var SlotType $className */
        $className = get_called_class();
        $infos = $className::GetInfoMap();
        $info = @$infos[$key];

        // Check if the key was found
        if (! is_array($info)) {
            if ($throwExceptions) {
                throw new SlotValueNotSupportedException();
            } else {
                return null;
            }
        }

        // Return the info
        return $info;
    }

    /**
     * Returns the translated "normalized" name of the slot value key.
     * The locale used for the translation is retrieved from the value configured on the configuration page of the I/O
     * or the locale of the request (if available).
     * @param LocaleInterface $requestOrIO WebHook or WebOAuth I/O object which processes the Alexa Custom Skill request.
     * @param string $key Slot value key.
     * @param bool $throwExceptions True if the method should throw an exception if the slot value key could not be found.
     * @return string|null Translated and "normalized" name of the slot value key.
     * @throws LocaleNotSupportedException if the locale is not supported.
     */
    public static function GetName(LocaleInterface $requestOrIO, $key, $throwExceptions = true)
    {
        // Get the info map
        /** @var SlotType $className */
        $className = get_called_class();
        $info = $className::GetInfo($key, $throwExceptions);

        // Get the name
        $locale = $requestOrIO->GetLocale();
        $name = @$info['name'][$locale];

        // Check if the name was found
        if (is_null($name)) {
            if ($throwExceptions) {
                throw new LocaleNotSupportedException();
            } else {
                return null;
            }
        }

        // Return the name
        return $name;
    }

}