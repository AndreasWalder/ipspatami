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


namespace Patami\IPS\Services\KNX;


use Patami\IPS\Framework;
use Patami\IPS\System\IPS;


/**
 * KNX/EIB Helper Class.
 * @package IPSPATAMI
 */
class KNX
{

    /** IPS module GUID of the KNX/EIB splitter. */
    const KNX_SPLITTER_MODULE_ID = '{1C902193-B044-43B8-9433-419F09C641B8}';

    /**
     * Checks if KNX is supported by the IPS installation.
     * @return bool True if KNX is supported by the IPS installation.
     */
    public static function IsSupported()
    {
        // Get the list of KNX splitter instances
        $instanceIds = IPS::GetInstanceListByModuleId(self::KNX_SPLITTER_MODULE_ID);

        // Return true if there are any
        return count($instanceIds) > 0;
    }

    /**
     * Switches the KNX/EIB device on or off.
     * @param int $instanceId IPS object ID of the KNX/EIB instance.
     * @param bool $value True if the device should be switchd on.
     * @return bool True if the telegram was successfully sent.
     */
    public static function SendSwitch($instanceId, $value)
    {
        // Send the telegram via the KNX/EIB module
        /** @noinspection PhpUndefinedFunctionInspection */
        return EIB_Switch($instanceId, $value);
    }

    /**
     * Sets the KNX/EIB device to the specified dim value.
     * @param int $instanceId IPS object ID of the KNX/EIB instance.
     * @param int $value Dim value.
     * @return bool True if the telegram was successfully sent.
     */
    public static function SendDimValue($instanceId, $value)
    {
        // Send the telegram via the KNX/EIB module
        /** @noinspection PhpUndefinedFunctionInspection */
        return EIB_DimValue($instanceId, $value);
    }

    /**
     * Sets the KNX/EIB device to the specified float value.
     * @param int $instanceId IPS object ID of the KNX/EIB instance.
     * @param float $value Float value.
     * @return bool True if the telegram was successfully sent.
     */
    public static function SendFloatValue($instanceId, $value)
    {
        // Send the telegram via the KNX/EIB module
        /** @noinspection PhpUndefinedFunctionInspection */
        return EIB_FloatValue($instanceId, $value);
    }

}