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


use Patami\IPS\Objects\Drivers\Exceptions\DriverSetFailedException;
use Patami\IPS\Objects\Drivers\Exceptions\StateInvalidDataException;
use Patami\IPS\Objects\Drivers\GetSwitchInterface;
use Patami\IPS\Objects\Drivers\SetSwitchInterface;
use Patami\IPS\Objects\Drivers\StateInterface;


/**
 * IPS Driver for Patami Object Groups in Scene mode with switching enabled.
 * @package IPSPATAMI
 */
class PatamiObjectGroupSceneSwitchDriver extends PatamiObjectGroupSceneDriver implements
    GetSwitchInterface,
    SetSwitchInterface,
    StateInterface
{

    public function SetSwitch($value)
    {
        // Get the object ID
        $id = $this->object->GetId();

        // Switch
        /** @noinspection PhpUndefinedFunctionInspection */
        $result = POG_SetSwitch($id, $value);

        // Throw an exception if the value could not be set
        if ($result === false) {
            throw new DriverSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    public function GetState()
    {
        // Get the object ID
        $id = $this->object->GetId();

        // Get the state
        /** @noinspection PhpUndefinedFunctionInspection */
        $state = POG_GetState($id);

        // Return the state
        return array(
            'state' => $state
        );
    }

    public function SetState(array $state)
    {
        // Get the state
        $state = @$state['state'];

        // Throw an exception if the value is invalid
        if (is_null($state) || ! is_array($state)) {
            throw new StateInvalidDataException();
        }

        // Get the object ID
        $id = $this->object->GetId();

        // Set the state
        /** @noinspection PhpUndefinedFunctionInspection */
        POG_SetState($id, $state);

        // Enable fluent interface
        return $this;
    }

}