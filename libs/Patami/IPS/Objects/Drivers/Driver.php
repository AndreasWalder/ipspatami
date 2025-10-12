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


use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\IPSObject;
use Patami\IPS\Objects\Objects;


/**
 * Abstract base class for IPS drivers.
 *
 * Drivers provide an abstraction layer on top of IPS instances and variables that allows developers to use the same
 * methods for performing actions and retrieving information regardless of which type of device they deal with.
 *
 * When a driver is constructed, it checks if the given IPS object is supported. If not, an InvalidObjectException is
 * thrown, indicating that another driver should be used to handle the IPS object.
 * Drivers must implement interfaces that are used to indicate the capabilities of the driver, eg. to control a switch
 * or to read a temperature.
 *
 * @see Drivers::AutoDetect() for the method used to find the matching driver for an IPS object.

 * @package IPSPATAMI
 */
abstract class Driver
{

    /**
     * @var IPSObject IPS object instance.
     */
    protected $object;

    /**
     * Driver constructor.
     * @param IPSObject|int $object IPS object instance or ID of the object handled by the driver.
     * @throws InvalidObjectException if the object is not supported by the driver.
     */
    public function __construct($object)
    {
        // Check if the given object is already an IPSObject instance
        if ($object instanceof IPSObject) {
            // Yes, remember it
            $this->object = $object;
        } else {
            // No, create and remember the it
            $this->object = Objects::GetByID($object);
        }

        // Determine whether the driver supports the object
        $this->ValidateObject();
    }

    /**
     * Determines whether the driver supports the object.
     * The concrete implementation of the method in the child class needs to probe the object and verify if the driver
     * can support the object or not. If the object is not supported by the driver, the method should thow an exception.
     * @return void
     * @throws InvalidObjectException if the object is not supported by the driver.
     */
    abstract protected function ValidateObject();

    /**
     * Returns the class name of the driver.
     * @return string FQCN of the driver class.
     */
    public function GetName()
    {
        // Return the name of the called class
        return get_called_class();
    }

    /**
     * Returns a list of objects that should be monitored for changes to the object.
     * @return array Objects that should be monitored for changes.
     */
    public function GetMonitoredObjects()
    {
        // Return an empty list
        return array();
    }

    /**
     * Returns the object of the driver.
     * @return IPSObject The object of the driver.
     */
    public function GetObject()
    {
        // Return the object
        return $this->object;
    }

}