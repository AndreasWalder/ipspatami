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


use Patami\IPS\Framework;
use Patami\IPS\Modules\Module;
use Patami\IPS\System\Network\cURL;
use Patami\IPS\System\Network\Exceptions\cURLException;


/**
 * IP Symcon Connect Class.
 * Provides functions to control or to get information about the IP Symcon Connect service.
 * @package IPSPATAMI
 * @link https://www.symcon.de/service/dokumentation/modulreferenz/connect-control/
 */
class IPSConnect
{

    /** URL of the IPS Connect status web page. */
    const STATUS_URL = 'http://status.symcon.de/2127775';

    /**
     * IPS module GUID of the IPS Connect Control instance.
     */
    const MODULE_ID = '{9486D575-BE8C-4ED8-B5B5-20930E26DE6F}';

    /**
     * Returns the IPS object ID of the IPS Connect Control instance.
     * @return int IPS obejct ID of the IPS Connect Control instance or null if none was found.
     */
    public static function GetInstanceId()
    {
        // Get the list of Symcon Connect instances
        $ids = IPS::GetInstanceListByModuleId(self::MODULE_ID);

        // Return false if no instance can be found
        if (count($ids) < 1) {
            return null;
        }

        // Return if the instance is connected
        return $ids[0];
    }

    /**
     * Checks if IP Symcon Connect is connected.
     * @return bool True if IP Symcon Connect is connected to the cloud service.
     */
    public static function IsConnected()
    {
        // Get the instance
        $id = self::GetInstanceId();

        // Return false if no instance can be found
        if (is_null($id)) {
            return false;
        }

        // Return if the instance is connected
        return IPS::GetInstance($id)['InstanceStatus'] == Module::STATUS_INSTANCE_ACTIVE;
    }

    /**
     * Checks if the IP Symcon Connect service is healthy according to its status page.
     * @return bool|null True if the IP Symcon Connect service is healthy or null if the status could not be retrieved.
     * @link http://status.symcon.de/2127775
     */
    public static function IsHealthy()
    {
        try {
            // Create a new cURL object
            $curl = new cURL(self::STATUS_URL);

            // Execute the request
            $text = $curl->Execute();

            // Check if the service is up and return the result
            return @preg_match('/Current status: Up/', $text);
        } catch (cURLException $e) {
            // Log the error
            Framework::Debug('IPSConnect::IsHealthy()', utf8_decode($e->GetCustomMessage()));
            // Return null if the request failed
            return null;
        }
    }

    /**
     * Returns the IP Symcon Connect host name of the IP Symcon installation.
     * @return string|null FQDN of the IP Symcon installation or null if it is not configured.
     */
    public static function GetURL()
    {
        // Get the instance
        $id = self::GetInstanceId();

        // Return null if no instance can be found
        if (is_null($id)) {
            return null;
        }

        // Return null if the instance is still initializing
        if (IPS::IsInstanceInitializing($id)) {
            return null;
        }

        // Return if the instance is connected
        /** @noinspection PhpUndefinedFunctionInspection */
        return @CC_GetURL($id);
    }

}