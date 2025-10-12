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


namespace Patami\IPS\Objects\Drivers\Patami\ObjectGroup;


use Patami\IPS\Objects\Drivers\GetSwitchInterface;
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\BooleanVariable;
use Patami\IPS\System\IPS;


/**
 * IPS Driver for Patami Object Groups in Status mode.
 * @package IPSPATAMI
 */
class PatamiObjectGroupStatusDriver extends PatamiObjectGroupDriver implements
    GetSwitchInterface
{

    /**
     * @var BooleanVariable IPS child variable object.
     */
    protected $value;

    /**
     * Determines whether the driver supports the object.
     * The object must satisfy the parent object requirements and:
     * - have the mode property set to 0 (status).
     * - have a child IPS variable object, that must:
     *     - have the identifier 'Value'.
     *     - be a boolean variable.
     * @throws InvalidObjectException if the object is not supported by the driver.
     */
    protected function ValidateObject()
    {
        // Make sure the object is a PatamiObjectGroup instance
        parent::ValidateObject();

        // Throw an exception if the mode is not "status"
        $mode = $this->object->GetProperty('Mode');
        if ($mode != 0) {
            throw new InvalidObjectException();
        }

        // Find the value variable
        $value = $this->object->GetChildByIdent('Value');

        // Throw an exception if the object is not a variable
        if (! $value instanceof BooleanVariable) {
            throw new InvalidObjectException();
        }

        // Remember the value object
        $this->value = $value;
    }

    public function GetMonitoredObjects()
    {
        return array(
            $this->value
        );
    }

    public function GetSwitch()
    {
        // Return the value
        return $this->value->Get();
    }

}