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


namespace Patami\IPS\Libraries;


use Patami\IPS\Libraries\Exceptions\LibraryUnloadFailedException;
use Patami\IPS\Libraries\Exceptions\ModuleControlNotFoundException;
use Patami\IPS\Libraries\Exceptions\LibraryLoadFailedException;
use Patami\IPS\System\IPS;


/**
 * Provides functions to manage IPS libraries.
 * @package IPSPATAMI
 */
class Libraries
{

    /**
     * IPS module GUID of the IPS Module Control
     * @link https://www.symcon.de/service/dokumentation/modulreferenz/module-control/
     */
    const MODULE_CONTROL_MODULE_ID = '{B8A5067A-AFC2-3798-FEDC-BCD02A45615E}';

    /**
     * Returns the IPS object ID of the IPS Module Control instance.
     * @return int IPS object ID of the IPS Module Control instance.
     * @throws ModuleControlNotFoundException if the IPS Module Control instance was not found.
     * @link https://www.symcon.de/service/dokumentation/modulreferenz/module-control/
     */
    public static function GetModuleControlInstanceId()
    {
        // Get the list of module control instances
        $ids = IPS::GetInstanceListByModuleId(self::MODULE_CONTROL_MODULE_ID);

        // Throw an exception if not exactly one entry was found
        if (count($ids) != 1) {
            throw new ModuleControlNotFoundException();
        }

        // Return the instance ID
        return $ids[0];
    }

    /**
     * Returns a list of all IPS library keys.
     * @return array|bool Keys of all IPS libraries or false if the list of libraries could not be retrieved.
     * @throws ModuleControlNotFoundException if the IPS Module Control instance was not found.
     */
    public static function GetLibraries()
    {
        // Return the list of library names
        /** @noinspection PhpUndefinedFunctionInspection */
        return @MC_GetModuleList(self::GetModuleControlInstanceId());
    }

    /**
     * Checks if an IPS library is loaded.
     * @param string $name Key of the library.
     * @return bool True if the library is loaded.
     * @throws ModuleControlNotFoundException if the IPS Module Control instance was not found.
     */
    public static function IsLibraryLoaded($name)
    {
        // Get the list of libraries
        $libraries = self::GetLibraries();

        // Return false if the list of libraries could not be retrieved.
        if ($libraries === false) {
            return false;
        }

        // Return if the library is loaded
        return in_array($name, $libraries);
    }

    /**
     * Adds an IPS library to IPS.
     * The library will be added with the default branch of the Git repository.
     * @param string $url URL of the library's Git repository.
     * @throws ModuleControlNotFoundException if the IPS Module Control instance was not found.
     * @throws LibraryLoadFailedException if the library could not be loaded.
     */
    public static function LoadLibrary($url)
    {
        // Create/load the module
        /** @noinspection PhpUndefinedFunctionInspection */
        $result = @MC_CreateModule(Libraries::GetModuleControlInstanceId(), $url);

        // Throw an exception if loading the module was not successful
        if (! $result) {
            throw new LibraryLoadFailedException();
        }
    }

    /**
     * Removed an IPS library from IPS.
     * @param string $name Key of the library.
     * @throws ModuleControlNotFoundException if the IPS Module Control instance was not found.
     * @throws LibraryUnloadFailedException if the library could not be removed.
     */
    public static function UnloadLibrary($name)
    {
        // Delete the module
        /** @noinspection PhpUndefinedFunctionInspection */
        $result = @MC_DeleteModule(Libraries::GetModuleControlInstanceId(), $name);

        // Throw an exception if the module could not be removed
        if (! $result) {
            throw new LibraryUnloadFailedException();
        }
    }

}