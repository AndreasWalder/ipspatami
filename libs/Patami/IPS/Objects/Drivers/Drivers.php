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


namespace Patami\IPS\Objects\Drivers;


use Patami\IPS\Exceptions\Exception;
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\IPSObject;


/**
 * Provides functions to manage and detect IPS drivers.
 *
 * In order to add your own drivers to the driver model, call Drivers::AddDriverClassNames() in your module's bootstrap
 * file, which will add the class names of your driver classes to the collection of drivers being used for detecting
 * the matching driver when Drivers::AutoDetect() is called.
 *
 * @package IPSPATAMI
 */
class Drivers
{

    /**
     * @var array FQCNs of all driver classes.
     * @see Drivers::AddDriverClassNames()
     */
    protected static $classNames = array();

    /**
     * Returns a list of all driver class names.
     * @return array FQCNs of all driver classes.
     */
    public static function GetDriverClassNames()
    {
        // Get the name of the called class
        /** @var Drivers $className */
        $className = get_called_class();

        // Return the driver class names
        return $className::$classNames;
    }

    /**
     * Adds a list of driver class names to the collection of drivers.
     * Drivers must be child classes of Driver.
     * @param array $classNames FQCNs of the driver classes.
     * @see Driver
     * @see Drivers::$classNames
     */
    public static function AddDriverClassNames(array $classNames)
    {
        // Get the name of the called class
        /** @var Drivers $className */
        $className = get_called_class();

        // Merge the class names
        $className::$classNames = array_unique(array_merge($className::$classNames, $classNames));
    }

    /**
     * Searches for a driver that is compatible to the given IPS object and returns a new instance of the driver.
     * By specifying the FQIN of an interface, candidate drivers used for the search can be filtered for drivers
     * offering specific functionality.
     * @param IPSObject|int $object IPS object instance or ID of the object handled by the driver.
     * @param string|array|null $interfaceNames FQIN of an interface or an array of interface FQINs or null to use all
     * drivers when probing the object. If an array is given, the driver must implement all interfaces.
     * @return Driver Instance of the detected driver.
     * @throws InvalidObjectException if no driver supports the interface with the object.
     */
    public static function AutoDetect($object, $interfaceNames = null)
    {
        // Get the name of the called class
        /** @var Drivers $className */
        $className = get_called_class();

        // Get the list of drivers
        $driverClassNames = $className::GetDriverClassNames();

        // Convert the interface name to an array if a single FCIN is given
        if (! is_null($interfaceNames) && ! is_array($interfaceNames)) {
            $interfaceNames = array($interfaceNames);
        }

        // Loop through the list of drivers and filter for an interface name
        /** @var Driver $driverClassName */
        foreach ($driverClassNames as $driverClassName) {
            // Check if the driver implements a specific interface (if requested)
            if (! is_null($interfaceNames)) {
                $driverInterfaceNames = @class_implements($driverClassName);
                if ($driverInterfaceNames) {
                    foreach ($interfaceNames as $interfaceName) {
                        if (! in_array($interfaceName, $driverInterfaceNames)) {
                            // Skip the driver if it doesn't implement the desired interface
                            continue 2;
                        }
                    }
                }
            }
            try {
                // Create a new driver instance to determine whether the driver supports the object
                $driver = new $driverClassName($object);
            } catch (Exception $e) {
                // Skip the driver, because it did not support the object
                continue;
            }
            // We found the right driver as it did not throw an exception
            return $driver;
        }

        // No driver matched, throw an exception
        throw new InvalidObjectException();
    }

}