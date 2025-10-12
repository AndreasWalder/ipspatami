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


namespace Patami\IPS\I18N;
use Patami\IPS\System\Locales;


/**
 * Translator Helper Class.
 * @package IPSPATAMI
 */
class Translator
{

    const MSGID_MESSAGE_NOT_FOUND = 'patami.framework.i18n.message_not_found';

    /**
     * @var array Directories to be searched for translation files.
     * @see Translator::AddDir()
     */
    protected static $dirs = array();

    /**
     * @var string|null Default locale.
     */
    protected static $defaultLocale = null;

    /**
     * @var array|null Cached translations.
     */
    protected static $translations = null;

    /**
     * Initializes the translator and adds the framework translations.
     */
    public static function Initialize()
    {
        self::AddDir(__DIR__ . DIRECTORY_SEPARATOR . 'translations');
    }

    /**
     * Adds a directory to the search list.
     * @param string $dir Absolute path to the directory to be added to the search list.
     */
    public static function AddDir($dir)
    {
        // Return if the directory is already in the list
        if (in_array($dir, self::$dirs)) {
            return;
        }

        // Add the directory
        self::$dirs[] = $dir;

        // Invalidate the cache
        self::$translations = null;
    }

    /**
     * Loads the translations for the specified locale.
     * @param string $locale Locale of the translations.
     */
    protected static function LoadTranslations($locale)
    {
        // Return if the translations are already loaded
        if (! is_null(self::$translations) && isset(self::$translations[$locale])) {
            return;
        }

        // Initialize the translations
        $translations = array();

        // Loop through the directories
        foreach (self::$dirs as $dir) {
            // Generate the file name
            $fileName = sprintf('%s%s%s.json', $dir, DIRECTORY_SEPARATOR, $locale);
            // Load the file
            $text = @file_get_contents($fileName);
            // Skip the directory if the file cannot be loaded
            if ($text === false) {
                continue;
            }
            // JSON-decode the text
            $data = @json_decode($text, true);
            // Skip the directory if the file cannot be decoded
            if (is_null($data)) {
                continue;
            }
            // Merge in the translations
            $translations = array_merge($translations, $data);
        }

        // Cache the translations
        self::$translations[$locale] = $translations;
    }

    /**
     * Returns either the specified locale or the default locale (which is the IPS locale).
     * The method caches the default locale to make sure it is not determined again on every call.
     * @param string|null $locale User-specified locale.
     * @return string Locale string.
     */
    protected static function GetLocale($locale)
    {
        if (is_null($locale)) {
            // No locale was given
            if (is_null(self::$defaultLocale)) {
                // The default locale is unknown, use the IPS locale
                self::$defaultLocale = Locales::GetIPSLocale();
            }
            // Return the default locale
            return self::$defaultLocale;
        }

        // Return the specified locale
        return $locale;
    }

    /**
     * Returns the translated message for the given key and locale.
     * @param string $key Key of the message.
     * @param string|null $locale Locale of the message or null to use the IPS system locale.
     * @return string Translated message.
     */
    public static function Get($key, $locale = null)
    {
        // Get the locale
        $locale = self::GetLocale($locale);

        // Load the translations
        self::LoadTranslations($locale);

        // Return an error message if the key does not exist
        if (! isset(self::$translations[$locale][$key])) {
            if ($key == self::MSGID_MESSAGE_NOT_FOUND) {
                // Return a static error message if not even the error message was found
                return 'Unable to load translations';
            } else {
                // Return the error message
                return self::FormatWithLocale(self::MSGID_MESSAGE_NOT_FOUND, $locale, $key);
            }
        }

        // Return the message
        return self::$translations[$locale][$key];
    }

    /**
     * Returns the formatted translated message for the given key.
     * @param string $key Key of the message.
     * @param array ...$args Arguments for sprintf()
     * @return string Formatted and translated message.
     * @see sprintf()
     */
    public static function Format($key, ...$args)
    {
        // Get the translated message, then format and return it
        return vsprintf(self::Get($key), $args);
    }

    /**
     * Returns the formatted translated message for the given key.
     * @param string $key Key of the message.
     * @param string $locale Locale of the message.
     * @param array ...$args Arguments for sprintf()
     * @return string Formatted and translated message.
     * @see sprintf()
     */
    public static function FormatWithLocale($key, $locale, ...$args)
    {
        // Get the translated message, then format and return it
        return vsprintf(self::Get($key, $locale), $args);
    }

}