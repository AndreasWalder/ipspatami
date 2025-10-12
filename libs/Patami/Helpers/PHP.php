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
 * Provides PHP helper functions.
 * @package IPSPATAMI
 */
class PHP
{

    /**
     * Returns the version of the PHP interpreter.
     * @return string PHP version.
     */
    public static function GetVersion()
    {
        return phpversion();
    }

    /**
     * Returns the information from phpinfo() as an array.
     * @return array PHP info atrributes.
     * @link http://php.net/manual/de/function.phpinfo.php#117961
     */
    public static function GetPhpInfoArray()
    {
        $entitiesToUtf8 = function($input) {
            // http://php.net/manual/en/function.html-entity-decode.php#104617
            return preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $input);
        };

        $plainText = function($input) use ($entitiesToUtf8) {
            return trim(html_entity_decode($entitiesToUtf8(strip_tags($input))));
        };

        $titlePlainText = function($input) use ($plainText) {
            return '# '.$plainText($input);
        };

        ob_start();
        phpinfo(-1);

        $phpinfo = array('phpinfo' => array());

        // Strip everything after the <h1>Configuration</h1> tag (other h1's)
        if (! preg_match('#(.*<h1[^>]*>\s*Configuration.*)<h1#s', ob_get_clean(), $matches)) {
            return array();
        }

        $input = $matches[1];
        $matches = array();

        if (preg_match_all(
            '#(?:<h2.*?>(?:<a.*?>)?(.*?)(?:<\/a>)?<\/h2>)|'.
            '(?:<tr.*?><t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>)?)?</tr>)#s',
            $input,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $match) {
                $fn = strpos($match[0], '<th') === false ? $plainText : $titlePlainText;
                if (strlen($match[1])) {
                    $phpinfo[$match[1]] = array();
                } elseif (isset($match[3])) {
                    $keys1 = array_keys($phpinfo);
                    $phpinfo[end($keys1)][$fn($match[2])] = isset($match[4]) ? array($fn($match[3]), $fn($match[4])) : $fn($match[3]);
                } else {
                    $keys1 = array_keys($phpinfo);
                    $phpinfo[end($keys1)][] = $fn($match[2]);
                }

            }
        }

        return $phpinfo;
    }

    /**
     * Returns the name and the version of the operating system.
     * @return string Operating system name and version.
     */
    public static function GetOSName()
    {
        return @self::GetPhpInfoArray()['phpinfo']['System'];
    }

    /**
     * Returns the architecture for which the PHP interpreter was compiled.
     * @return string Architecture name.
     */
    public static function GetArchitecture()
    {
        return @self::GetPhpInfoArray()['phpinfo']['Architecture'];
    }

    /**
     * Checks if the PHP interpreter uses 64-bit.
     * @return bool True if the PHP interpreter uses 64-bit.
     */
    public static function Is64Bit()
    {
        return self::GetArchitecture() == 'x64';
    }

}
