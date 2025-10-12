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


use Patami\Helpers\PHP;
use Patami\IPS\Framework;
use Patami\IPS\System\Network\cURL;
use Patami\IPS\System\Network\Exceptions\cURLException;


/**
 * Provides functions to get information about the system.
 * @package IPSPATAMI
 */
class System
{

    /** URL of the ifconfig.co web service, which returns information about the IP performing a request. */
    const IFCONFIG_URL = 'https://ifconfig.co/json';

    /** @var array Information about the public IP of the system. */
    protected static $publicIpInfo = null;

    /**
     * Returns the name and the version of the operating system.
     * @return string Operating system name and version.
     */
    public static function GetOSName()
    {
        return PHP::GetOSName();
    }

    /**
     * Returns information about the public IP of the system.
     * The method should not be called too often, as the ifconfig.co service limits the number of requests from a single
     * IP address and returns an error if the request rate is too high. The method will detect this condition and will
     * return the old value, if possible.
     * @param bool $refresh True if the cached information should be refreshed.
     * @return array|null Attributes of the public IP or null if the public IP could not be determined.
     */
    public static function GetPublicIPInfo($refresh = false)
    {
        // Return cached information if possible and requested
        if (! is_null(self::$publicIpInfo) && ! $refresh) {
            return self::$publicIpInfo;
        }

        try {
            // Create a new cURL object
            $curl = new cURL(self::IFCONFIG_URL);

            // Execute the request
            $text = $curl->Execute();

            // Return cached information if the request was refused due to too many requests
            if (trim($text) == '429 Too Many Requests') {
                return self::$publicIpInfo;
            }

            // JSON-decode the text
            $info = json_decode($text, true);
        } catch (cURLException $e) {
            // Log the error
            Framework::Debug('System::GetPublicIPInfo()', utf8_decode($e->GetCustomMessage()));
            // Return null
            return null;
        }

        // Remember the info
        self::$publicIpInfo = $info;

        // Return the info
        return $info;
    }

    /**
     * Returns the public IP of the system.
     * @param bool $refresh True if the cached information should be refreshed.
     * @return string|null IP address or null if it could not be determined.
     */
    public static function GetPublicIP($refresh = false)
    {
        // Get the information
        $info = self::GetPublicIPInfo($refresh);

        // Return the IP address
        return @$info['ip'];
    }

    /**
     * Returns the hostname of the public IP of the system.
     * @param bool $refresh True if the cached information should be refreshed.
     * @return string|null Hostname or null if it could not be determined.
     */
    public static function GetPublicIPHostname($refresh = false)
    {
        // Get the information
        $info = self::GetPublicIPInfo($refresh);

        // Return the hostname
        return @$info['hostname'];
    }

}