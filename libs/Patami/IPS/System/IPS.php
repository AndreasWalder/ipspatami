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


use Patami\Helpers\Directory;
use Patami\Helpers\PHP;
use Patami\IPS\Modules\Module;


/**
 * IPS Wrapper Class.
 * Encapsulates IP Symcon's IPS_* functions.
 * @package IPSPATAMI
 * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/
 */
class IPS
{

    /**
     * Returns the runlevel of the IPS kernel.
     * @return int The IPS kernel runlevel.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/programminformationen/ips-getkernelrunlevel/
     */
    public static function GetKernelRunlevel()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetKernelRunlevel();
    }

    /**
     * Checks if the IPS kernel is ready.
     * @return bool True if the kernel is ready
     * @see IPS::GetKernelRunlevel()
     * @see KR_READY
     */
    public static function IsKernelReady()
    {
        return self::GetKernelRunlevel() == KR_READY;
    }

    /**
     * Returns the full path of the IPS program directory.
     * @return string Full path of the IPS program directory.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/programminformationen/ips-getkerneldir/
     */
    public static function GetKernelDir()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetKernelDir();
    }

    /**
     * Returns the full path of the directory where IPS stores user-defined scripts.
     * @return string Full path of the IPS scripts directory.
     * @see IPS::GetKernelDir()
     */
    public static function GetScriptDir()
    {
        return self::GetKernelDir() . 'scripts' . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the size of the directory where IPS stores user-defined scripts.
     * @return int Size of the directory in bytes.
     * @see IPS::GetScriptDir()
     */
    public static function GetScriptsDirSize()
    {
        return Directory::GetSize(self::GetScriptDir());
    }

    /**
     * Returns the full path of the directory where IPS stores the library Git repositories.
     * @return string Full path of the IPS modules directory.
     * @see IPS::GetKernelDir()
     */
    public static function GetModulesDir()
    {
        return self::GetKernelDir() . 'modules' . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the full path of the directory where the Git repository of an IPS module is stored.
     * @param string $name Key of the IPS library.
     * @return string Full path of the IPS module directory.
     * @see IPS::GetModulesDir()
     */
    public static function GetModuleDir($name)
    {
        return self::GetModulesDir() . $name . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the full path of the directory where IPS stores the database.
     * @return string Full path of the IPS database directory.
     * @see IPS::GetKernelDir()
     */
    public static function GetDatabaseDir()
    {
        return self::GetKernelDir() . 'db' . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the size of the directory where IPS stores the database.
     * @return int Size of the directory in bytes.
     * @see IPS::GetDatabaseDir()
     */
    public static function GetDatabaseDirSize()
    {
        return Directory::GetSize(self::GetDatabaseDir());
    }

    /**
     * Returns the full path of the IPS log directory.
     * @return string Full path of the IPS log directory.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/programminformationen/ips-getlogdir/
     */
    public static function GetLogDir()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetLogDir();
    }

    /**
     * Returns the size of the IPS log directory.
     * @return int Size of the directory in bytes.
     * @see IPS::GetLogDir()
     */
    public static function GetLogDirSize()
    {
        return Directory::GetSize(self::GetLogDir());
    }

    /**
     * Returns the full path of a log file in the IPS log directory.
     * @param string $fileName Name of the log file.
     * @return string Full path of the log file.
     * @see IPS::GetLogDir()
     */
    public static function GetLogFileName($fileName)
    {
        return self::GetLogDir() . $fileName;
    }

    /**
     * Returns the IPS version.
     * @return string IPS version.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/programminformationen/ips-getkernelversion/
     */
    public static function GetKernelVersion()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetKernelVersion();
    }

    /**
     * Returns the unix timestamp when IPS was started.
     * @return int Unix timestamp when IPS was started.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/programminformationen/ips-getkernelstarttime/
     */
    public static function GetKernelStartTime()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetKernelStartTime();
    }

    /**
     * Checks if the IPS binary is 32- or 64-bit.
     * @return bool True if the IPS binary is 64-bit.
     */
    public static function Is64Bit()
    {
        return PHP::Is64Bit();
    }

    /**
     * Logs a message in the IPS log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @return bool True if messages was successfully added to the IPS log.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/programminformationen/ips-logmessage/
     */
    public static function LogMessage($tag, $message)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_LogMessage($tag, $message);
    }

    /**
     * Checks if an IPS instance function exists.
     * @param string $name Name of the IPS instance function.
     * @return bool True if the instance function is registered.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/programminformationen/ips-functionexists/
     */
    public static function FunctionExists($name)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_FunctionExists($name);
    }

    /**
     * Returns a list of all functions registered by an instance.
     * @param int $instanceId IPS object ID of the instance or 0 for all instances.
     * @return array List of all registered IPS functions.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/programminformationen/ips-getfunctionlist/
     */
    public static function GetFunctionList($instanceId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetFunctionList($instanceId);
    }

    /**
     * Returns the number of all functions registered by an instance.
     * @param int $instanceId IPS object ID of the instance or 0 for all instances.
     * @return int Number of all registered IPS functions.
     * @see IPS::GetFunctionList()
     */
    public static function GetFunctionCount($instanceId)
    {
        return count(self::GetFunctionList($instanceId));
    }

    /**
     * Waits for the specified time.
     * @param int $waitTime Number of milliseconds to wait.
     * @return bool True if the function successfully waited for the specified time.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ablaufsteuerung/ips-sleep/
     */
    public static function Sleep($waitTime)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_Sleep($waitTime);
    }

    /**
     * Returns the value of an IPS variable.
     * @param int $objectId IPS object ID of the variable.
     * @return mixed Value of the variable.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenzugriff/getvalue/
     */
    public static function GetValue($objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        /** @noinspection PhpParamsInspection */
        return @GetValue($objectId);
    }

    /**
     * Sets the value of an IPS variable.
     * @param int $objectId IPS object ID of the variable.
     * @param mixed $value New value of the variable.
     * @return bool True if the variable value was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenzugriff/SetValue/
     */
    public static function SetValue($objectId, $value)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @SetValue($objectId, $value);
    }

    /**
     * Returns the formatted value of an IPS variable using the associated variable profile.
     * @param int $objectId IPS object ID of the variable.
     * @return string Formatted value of the variable or NULL if an error occurred.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenzugriff/getvalueformatted/
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/variablenprofile/
     */
    public static function GetValueFormatted($objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @GetValueFormatted($objectId);
    }

    /**
     * Returns the value of a boolean IPS variable.
     * @param int $objectId IPS object ID of the variable.
     * @return bool Value of the variable or NULL if the variable does not exist or is not a boolean variable.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenzugriff/getvalueboolean/
     */
    public static function GetValueBoolean($objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @GetValueBoolean($objectId);
    }

    /**
     * Sets the value of a boolean IPS variable.
     * @param int $objectId IPS object ID of the variable.
     * @param bool $value New value of the variable.
     * @return bool True if the variable value was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenzugriff/setvalueboolean/
     */
    public static function SetValueBoolean($objectId, $value)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @SetValueBoolean($objectId, $value);
    }

    /**
     * Returns the value of an integer IPS variable.
     * @param int $objectId IPS object ID of the variable.
     * @return int Value of the variable or NULL if the variable does not exist or is not an integer variable.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenzugriff/getvalueinteger/
     */
    public static function GetValueInteger($objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @GetValueInteger($objectId);
    }

    /**
     * Sets the value of an integer IPS variable.
     * @param int $objectId IPS object ID of the variable.
     * @param int $value New value of the variable.
     * @return bool True if the variable value was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenzugriff/setvalueinteger/
     */
    public static function SetValueInteger($objectId, $value)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @SetValueInteger($objectId, $value);
    }

    /**
     * Returns the value of a float IPS variable.
     * @param int $objectId IPS object ID of the variable.
     * @return float Value of the variable or NULL if the variable does not exist or is not a float variable.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenzugriff/getvaluefloat/
     */
    public static function GetValueFloat($objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @GetValueFloat($objectId);
    }

    /**
     * Sets the value of a float IPS variable.
     * @param int $objectId IPS object ID of the variable.
     * @param int $value New value of the variable.
     * @return bool True if the variable value was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenzugriff/setvaluefloat/
     */
    public static function SetValueFloat($objectId, $value)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @SetValueFloat($objectId, $value);
    }

    /**
     * Returns the value of a string IPS variable.
     * @param int $objectId IPS object ID of the variable.
     * @return float Value of the variable or NULL if the variable does not exist or is not a string variable.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenzugriff/getvaluestring/
     */
    public static function GetValueString($objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @GetValueString($objectId);
    }

    /**
     * Sets the value of a string IPS variable.
     * @param int $objectId IPS object ID of the variable.
     * @param int $value New value of the variable.
     * @return bool True if the variable value was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenzugriff/setvaluestring/
     */
    public static function SetValueString($objectId, $value)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @SetValueString($objectId, $value);
    }

    /**
     * Checks if an IPS object exists.
     * @param int $objectId IPS object ID.
     * @return bool True if the IPS object exists.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-objectExists/
     */
    public static function ObjectExists($objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_ObjectExists($objectId);
    }

    /**
     * Returns information about an IPS object.
     * @param int $objectId IPS object ID.
     * @return array Information about the IPS object or NULL if the object does not exist.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-getobject/
     */
    public static function GetObject($objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetObject($objectId);
    }

    /**
     * Returns the list of all IPS objects.
     * @return array IPS object IDs of all IPS objects.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-getobjectlist/
     */
    public static function GetObjectList()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetObjectList();
    }

    /**
     * Returns the number of all IPS objects.
     * @return int Number of all IPS objects.
     * @see IPS::GetObjectList()
     */
    public static function GetObjectCount()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return count(self::GetObjectList());
    }

    /**
     * Returns the name of an IPS object.
     * @param int $objectId IPS object ID.
     * @return string Name of the IPS object or NULL if the object does not exist.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-getname/
     */
    public static function GetName($objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetName($objectId);
    }

    /**
     * Sets the name of an IPS object.
     * @param int $objectId IPS object ID.
     * @param string $name New name of the IPS object.
     * @return bool True if the name was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-setname/
     */
    public static function SetName($objectId, $name)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetName($objectId, $name);
    }

    /**
     * Returns the location (the complete path) of an IPS object.
     * @param int $objectId IPS object ID.
     * @return string Complete path of the IPS object or NULL if the object does not exist.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-getlocation/
     */
    public static function GetLocation($objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetLocation($objectId);
    }

    /**
     * Sets the internal identifier of an IPS object.
     * @param int $objectId IPS object ID.
     * @param string $ident New internal identifier of the IPS object.
     * @return bool True if the identifier was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-setident/
     */
    public static function SetIdent($objectId, $ident)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetIdent($objectId, $ident);
    }

    /**
     * Disabled or enables an IPS object in the web frontend.
     * @param int $objectId IPS object ID.
     * @param bool $disabled True to disable the object in the web frontend.
     * @return bool True if the object was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-setdisabled/
     */
    public static function SetDisabled($objectId, $disabled = true)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetDisabled($objectId, $disabled);
    }

    /**
     * Hides or shows an IPS object in the web frontend.
     * @param int $objectId IPS object ID.
     * @param bool $hidden True to hide the object in the web frontend.
     * @return bool True if the object was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-sethidden/
     */
    public static function SetHidden($objectId, $hidden = true)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetHidden($objectId, $hidden);
    }

    /**
     * Sets the icon of an IPS object.
     * @param int $objectId IPS object ID.
     * @param string $iconName File name of the icon without path and extension.
     * @return bool True if the icon was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-seticon/
     */
    public static function SetIcon($objectId, $iconName)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetIcon($objectId, $iconName);
    }

    /**
     * Sets the info (data) of an IPS object.
     * @param int $objectId IPS object ID.
     * @param string $info Info text or data for the object.
     * @return bool True if the info was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-setinfo/
     */
    public static function SetInfo($objectId, $info)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetInfo($objectId, $info);
    }

    /**
     * Checks if an IPS object is a child of another IPS object.
     * @param int $objectId IPS object ID.
     * @param int $parentId IPS object ID of the parent object.
     * @param bool $recursive True if the method should check more than one level towards the object tree root.
     * @return bool True if the object is a child of the parent object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-ischild/
     */
    public static function IsChild($objectId, $parentId, $recursive)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_IsChild($objectId, $parentId, $recursive);
    }

    /**
     * Returns the object ID of an IPS objects parent.
     * @param int $objectId IPS object ID.
     * @return int IPS object ID of the object's parent object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-getparent/
     */
    public static function GetParent($objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetParent($objectId);
    }

    /**
     * Sets the parent object of an IPS object.
     * @param int $objectId IPS object ID.
     * @param int $parentId IPS object ID of the new parent.
     * @return bool True if the object was successfully assigned to the new parent object.
     */
    public static function SetParent($objectId, $parentId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetParent($objectId, $parentId);
    }

    /**
     * Check if the IPS object has child objects.
     * @param int $objectId IPS object ID.
     * @return bool True if the object has child objects.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-haschildren/
     */
    public static function HasChildren($objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_HasChildren($objectId);
    }

    /**
     * Returns a list of all child object IDs of an IPS object.
     * @param int $objectId IPS object ID.
     * @return array IPS object IDs of all children of the object or NULL if there was an error.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-getchildrenids/
     */
    public static function GetChildrenIds($objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetChildrenIDs($objectId);
    }

    /**
     * Returns the object ID of the first child object of an IPS object with the specified name.
     * @param string $name Name of the child object.
     * @param int $objectId IPS object ID of the parent object.
     * @return int IPS object ID of the first child object with the name or false if no object with the name exists.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-getobjectidbyname/
     */
    public static function GetObjectIdByName($name, $objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetObjectIDByName($name, $objectId);
    }

    /**
     * Returns the object ID of the first child object of an IPS object with the specified identifier.
     * @param string $ident Identifier of the child object.
     * @param int $objectId IPS object ID of the parent object.
     * @return int IPS object ID of the first child object with the identifier or false if no object with the identifier exists.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-getobjectidbyident/
     */
    public static function GetObjectIdByIdent($ident, $objectId)
    {
        $children = self::GetChildrenIds($objectId);
        if (!is_array($children)) {
            return false;
        }

        foreach ($children as $childId) {
            $object = self::GetObject($childId);
            if (!is_array($object)) {
                continue;
            }

            if (array_key_exists('ObjectIdent', $object) && ($object['ObjectIdent'] === $ident)) {
                return $childId;
            }
        }

        return false;
    }

    /**
     * Sets the position of an IPS object.
     * @param int $objectId IPS object ID.
     * @param int $position Position (index) of the object.
     * @return bool True if the position of the object was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/objektverwaltung/ips-setposition/
     */
    public static function SetPosition($objectId, $position)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetPosition($objectId, $position);
    }

    /**
     * Returns the JSON-encoded configuration of an IPS instance.
     * @param int $instanceId IPS object ID of the instance.
     * @return string JSON-encoded configuration of the instance.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/konfiguration/ips-getconfiguration/
     */
    public static function GetConfiguration($instanceId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetConfiguration($instanceId);
    }

    /**
     * Sets the JSON-encoded configuration of an IPS instance.
     * @param int $instanceId IPS object ID of the instance.
     * @param string $configuration New JSON-encoded configuration of the instance.
     * @return bool True if the configuration of the instance was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/konfiguration/ips-setconfiguration/
     */
    public static function SetConfiguration($instanceId, $configuration)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetConfiguration($instanceId, $configuration);
    }

    /**
     * Returns the JSON-encoded configuration form of an IPS instance.
     * @param int $instanceId IPS object ID of the instance.
     * @return string JSON-encoded configuration form of the instance.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/konfiguration/ips-getconfigurationform/
     */
    public static function GetConfigurationForm($instanceId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetConfigurationForm($instanceId);
    }

    /**
     * Returns the value of an configuration property of an IPS instance.
     * @param int $instanceId IPS object ID of the instance.
     * @param string $propertyName Name of the configuration property.
     * @return mixed Value of the configuration property.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/konfiguration/ips-getproperty/
     */
    public static function GetProperty($instanceId, $propertyName)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetProperty($instanceId, $propertyName);
    }

    /**
     * Sets the value of an configuration property of an IPS instance.
     * @param int $instanceId IPS object ID of the instance.
     * @param string $propertyName Name of the configuration property.
     * @param mixed $value Value of the configuration property.
     * @return bool True if the value of the configuration property was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/konfiguration/ips-setproperty/
     */
    public static function SetProperty($instanceId, $propertyName, $value)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetProperty($instanceId, $propertyName, $value);
    }

    /**
     * Checks if an IPS instance has unsaved configuration changes.
     * @param int $instanceId IPS object ID of the instance.
     * @return bool True if the instance has unsaved configuration changes.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/konfiguration/ips-haschanges/
     */
    public static function HasChanges($instanceId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_HasChanges($instanceId);
    }

    /**
     * Saves configuration changes of an IPS instance.
     * @param int $instanceId IPS object ID of the instance.
     * @return bool True if the configuration changes were successfully saved and applied.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/konfiguration/ips-applychanges/
     */
    public static function ApplyChanges($instanceId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_ApplyChanges($instanceId);
    }

    /**
     * Resets configuration changes of an IPS instance to the last saved configuration.
     * @param int $instanceId IPS object ID of the instance.
     * @return bool True if the configuration was successfully reset.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/konfiguration/ips-resetchanges/
     */
    public static function ResetChanges($instanceId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_ResetChanges($instanceId);
    }

    /**
     * Connects an IPS instance to its parent IPS instance.
     * @param int $instanceId IPS object ID of the instance.
     * @param int $parentId IPS object ID of the parent instance.
     * @return bool True if the instance was successfully connected to the parent instance.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/verbindungen/ips-connectinstance/
     */
    public static function ConnectInstance($instanceId, $parentId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_ConnectInstance($instanceId, $parentId);
    }

    /**
     * Disconnects an IPS instance from its parent IPS instance.
     * @param int $instanceId IPS object ID of the instance.
     * @return bool True if the instance was successfully disconnected from the parent instance.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/verbindungen/ips-disconnectinstance/
     */
    public static function DisconnectInstance($instanceId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_DisconnectInstance($instanceId);
    }

    /**
     * Returns a list of all IPS instance IDs that are compatible to the given instance ID.
     * @param int $instanceId IPS object ID of the instance.
     * @return array IPS instance IDs that are compatible to the instance.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/verbindungen/ips-getcompatibleinstances/
     */
    public static function GetCompatibleInstances($instanceId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetCompatibleInstances($instanceId);
    }

    /**
     * Returns the number of IPS instances that are compatible to the given instance ID.
     * @param int $instanceId IPS object ID of the instance.
     * @return int Number of IPS instances that are compatible to the instance.
     * @see IPS::GetCompatibleInstances()
     */
    public static function GetCompatibleInstancesCount($instanceId)
    {
        return count(self::GetCompatibleInstances($instanceId));
    }

    /**
     * Checks if two IPS instances are compatible to each other.
     * @param int $instanceId IPS object ID of the instance.
     * @param int $otherInstanceId IPS object ID of the instance to be checked for compatibility.
     * @return bool True if the two instances are compatible to each other.
     * https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/verbindungen/ips-isinstancecompatible/
     */
    public static function IsInstanceCompatible($instanceId, $otherInstanceId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_IsInstanceCompatible($instanceId, $otherInstanceId);
    }

    /**
     * Returns the list of GUIDs of all loaded IPS libraries.
     * @return array All IPS library GUIDs.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/modulverwaltung/ips-getlibrarylist/
     */
    public static function GetLibraryList()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetLibraryList();
    }

    /**
     * Returns the number of loaded IPS libraries.
     * @return int Number of loaded IPS libraries.
     * @see IPS::GetLibraryList()
     */
    public static function GetLibraryCount()
    {
        return count(self::GetLibraryList());
    }

    /**
     * Returns information about a loaded IPS library.
     * @param string $libraryId GUID of the IPS library.
     * @return array Attributes of the IPS library.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/modulverwaltung/ips-getlibrary/
     */
    public static function GetLibrary($libraryId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetLibrary($libraryId);
    }

    /**
     * Checks if an IPS library is loaded.
     * @param string $libraryId GUID of the IPS library.
     * @return bool True if the library is loaded.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/modulverwaltung/ips-libraryexists/
     */
    public static function LibraryExists($libraryId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_LibraryExists($libraryId);
    }

    /**
     * Returns the list of GUIDs of all modules provided by a given IPS library.
     * @param string $libraryId GUID of the IPS library.
     * @return array IPS module GUIDs provided by the library.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/modulverwaltung/ips-getlibrarymodules/
     */
    public static function GetLibraryModules($libraryId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetLibraryModules($libraryId);
    }

    /**
     * Returns the list of GUIDs of all modules provided by all loaded IPS libraries.
     * @return array All IPS module GUIDs.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/modulverwaltung/ips-getmodulelist/
     */
    public static function GetModuleList()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetModuleList();
    }

    /**
     * Returns the number of modules provided by all loaded IPS libraries.
     * @return int Number of modules.
     * @see IPS::GetModuleList()
     */
    public static function GetModuleCount()
    {
        return count(self::GetModuleList());
    }

    /**
     * Returns the list of GUIDs of all modules of the given type provided by all loaded IPS libraries.
     * @param int $moduleType Type of the module (see link).
     * @return array All IPS module GUIDs of the given type.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/modulverwaltung/ips-getmodulelistbytype/
     */
    public static function GetModuleListByType($moduleType)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetModuleListByType($moduleType);
    }

    /**
     * Returns the number of modules of the given type provided by all loaded IPS libraries.
     * @param int $moduleType Type of the module.
     * @return int Number of modules of the given type.
     * @see IPS::GetModuleListByType()
     */
    public static function GetModuleCountByType($moduleType)
    {
        return count(self::GetModuleListByType($moduleType));
    }

    /**
     * Returns information about an IPS module.
     * @param string $moduleId GUID of the IPS module.
     * @return array Attributes of the IPS module.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/modulverwaltung/ips-getmodule/
     */
    public static function GetModule($moduleId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetModule($moduleId);
    }

    /**
     * Returns the IPS library GUID of the IPS module.
     * @param string $moduleId GUID of the IPS module.
     * @return string|null IPS library GUID or null if there was an error.
     */
    public static function GetModuleLibraryId($moduleId)
    {
        // Get the instance info
        $info = IPS::GetModule($moduleId);

        // Return the library ID
        return @$info['LibraryID'];
    }

    /**
     * Checks if the given IPS module exists.
     * @param string $moduleId GUID of the IPS module.
     * @return bool True if the IPS module is loaded.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/modulverwaltung/ips-moduleexists/
     */
    public static function ModuleExists($moduleId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_ModuleExists($moduleId);
    }

    /**
     * Returns a list of IPS module GUIDs that are compatible to the given IPS module GUID.
     * @param string $moduleId GUID of the IPS module.
     * @return array All compatible IPS module GUIDs.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/modulverwaltung/ips-getcompatiblemodules/
     */
    public static function GetCompatibleModules($moduleId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetCompatibleModules($moduleId);
    }

    /**
     * Checks if two IPS modules are compatible to each other.
     * @param string $moduleId GUID of the IPS module.
     * @param string $otherModuleId GUID of the IPS module to be check for compatibility.
     * @return bool True if the two modules are compatible to each other.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/modulverwaltung/ips-ismodulecompatible/
     */
    public static function IsModuleCompatible($moduleId, $otherModuleId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_IsModuleCompatible($moduleId, $otherModuleId);
    }

    /**
     * Creates a new IPS category object (folder) in the IPS object tree.
     * @return int IPS object ID of the new category.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/kategorieverwaltung/ips-createcategory/
     */
    public static function CreateCategory()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_CreateCategory();
    }

    /**
     * Deletes an IPS category object.
     * @param int $categoryId IPS object ID of the category.
     * @return bool True if the category was successfully deleted.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/kategorieverwaltung/ips-deletecategory/
     */
    public static function DeleteCategory($categoryId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_DeleteCategory($categoryId);
    }

    /**
     * Returns the IPS object ID of the first category object with the given name under the given IPS object.
     * @param string $name Name of the IPS category.
     * @param int $parentId IPS object ID of the parent object.
     * @return int IPS object ID of the first matching category or false if no matching category was found.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/kategorieverwaltung/ips-getcategoryidbyname/
     */
    public static function GetCategoryIdByName($name, $parentId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetCategoryIDByName($name, $parentId);
    }

    /**
     * Returns a list of IPS object IDs of all category objects.
     * @return array IPS object IDs of all category objects.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/kategorieverwaltung/ips-getcategorylist/
     */
    public static function GetCategoryList()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetCategoryList();
    }

    /**
     * Returns the number of IPS category objects.
     * @return int Number of IPS category objects.
     * @see IPS::GetCategoryList()
     */
    public static function GetCategoryCount()
    {
        return count(self::GetCategoryList());
    }

    /**
     * Creates a new IPS instance object with the given GUID in the IPS object tree.
     * @param string $moduleId GUID of the IPS module.
     * @return int IPS object ID of the new instance or null if it could not be created.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/ips-createinstance/
     */
    public static function CreateInstance($moduleId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_CreateInstance($moduleId);
    }

    /**
     * Deletes an IPS instance object.
     * @param int $instanceId IPS object ID of the instance.
     * @return bool True if the instance was successfully deleted.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/ips-deleteinstance/
     */
    public static function DeleteInstance($instanceId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_DeleteInstance($instanceId);
    }

    /**
     * Returns the list of IPS object IDs of all IPS instance objects.
     * @return array IPS instance object IDs.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/ips-getinstancelist/
     */
    public static function GetInstanceList()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetInstanceList();
    }

    /**
     * Returns the number of IPS instance objects.
     * @return int Number of IPS instance objects.
     * @see IPS::GetInstanceList()
     */
    public static function GetInstanceCount()
    {
        return count(self::GetInstanceList());
    }

    /**
     * Returns information about an IPS instance object.
     * @param int $instanceId IPS object ID of the instance.
     * @return array Attributes of the IPS instance object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/ips-getinstance/
     */
    public static function GetInstance($instanceId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetInstance($instanceId);
    }

    /**
     * Returns the IPS module GUID of an IPS instance object.
     * @param int $instanceId IPS object ID of the instance.
     * @return string|null IPS module GUID or null if there was an error.
     */
    public static function GetInstanceModuleId($instanceId)
    {
        // Get the instance info
        $info = IPS::GetInstance($instanceId);

        // Return the module ID
        return @$info['ModuleInfo']['ModuleID'];
    }

    /**
     * Returns the module name of an IPS instance object.
     * @param int $instanceId IPS object ID of the instance.
     * @return string|null Module name or null if there was an error.
     */
    public static function GetInstanceModuleName($instanceId)
    {
        // Get the instance info
        $info = IPS::GetInstance($instanceId);

        // Return the module name
        return @$info['ModuleInfo']['ModuleName'];
    }

    /**
     * Checks if an IPS instance object exists.
     * @param int $instanceId IPS object ID of the instance.
     * @return bool True if the IPS instance exists.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/ips-instanceexists/
     */
    public static function InstanceExists($instanceId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_InstanceExists($instanceId);
    }

    /**
     * Checks if an IPS instance is initializing.
     * This can be used to check if the instance is being created during IPS startup or module updates.
     * @param int $instanceId IPS object ID of the instance.
     * @return bool True if the IPS instance is initializing.
     */
    public static function IsInstanceInitializing($instanceId)
    {
        // Return if the instance is initializing
        return @self::GetInstance($instanceId)['InstanceStatus'] == Module::STATUS_INSTANCE_NOT_CREATED;
    }

    /**
     * Returns the list of IPS object IDs of all instances with the given module GUID.
     * @param string $moduleId GUID of the IPS module.
     * @return array All IPS instance object IDs with the given module GUID.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/ips-getinstancelistbymoduleid/
     */
    public static function GetInstanceListByModuleId($moduleId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetInstanceListByModuleID($moduleId);
    }

    /**
     * Returns the number of IPS instance objects with the given module GUID.
     * @param string $moduleId GUID of the IPS module.
     * @return int Number of IPS instance objects with the given module GUID.
     * @see IPS::GetInstanceListByModuleId()
     */
    public static function GetInstanceCountByModuleId($moduleId)
    {
        return count(self::GetInstanceListByModuleId($moduleId));
    }

    /**
     * Returns the list of IPS object IDs of all instances with the given type.
     * @param int $moduleType Type of the instance (see link).
     * @return array All IPS instance object IDs with the given type.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/instanzenverwaltung/ips-getinstancelistbymoduletype/
     */
    public static function GetInstanceListByModuleType($moduleType)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetInstanceListByModuleType($moduleType);
    }

    /**
     * Returns the number of IPS instance objects with the given type.
     * @param int $moduleType Type of the instance.
     * @return int Number of IPS instance objects with the given type.
     * @see IPS::GetInstanceListByModuleType()
     */
    public static function GetInstanceCountByModuleType($moduleType)
    {
        return count(self::GetInstanceListByModuleType($moduleType));
    }

    /**
     * Creates a new IPS variable object of the given type.
     * @param int $variableType Type of the variable.
     * @return int IPS object ID of the new variable object or null if an error occurred.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/ips-createvariable/
     */
    public static function CreateVariable($variableType)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_CreateVariable($variableType);
    }

    /**
     * Deletes an IPS variable object.
     * @param int $variableId IPS object ID of the variable.
     * @return bool True if the variable object was successfully deleted.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/ips-deletevariable/
     */
    public static function DeleteVariable($variableId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_DeleteVariable($variableId);
    }

    /**
     * Checks if an IPS variable object exists.
     * @param int $variableId IPS object ID of the variable.
     * @return bool True if the variable object exists.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/ips-variableexists/
     */
    public static function VariableExists($variableId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_VariableExists($variableId);
    }

    /**
     * Returns a list of all IPS variable object IDs.
     * @return array IPS variable object IDs.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/ips-getvariablelist/
     */
    public static function GetVariableList()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetVariableList();
    }

    /**
     * Returns the number of all IPS variable objects.
     * @return int Number of all IPS variable objects.
     * @see IPS::GetVariableList()
     */
    public static function GetVariableCount()
    {
        return count(self::GetVariableList());
    }

    /**
     * Returns information about an IPS variable object.
     * @param int $variableId IPS object ID of the variable.
     * @return array Attributes of the IPS variable object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/ips-getvariable/
     */
    public static function GetVariable($variableId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetVariable($variableId);
    }

    /**
     * Returns the timestamp when the IPS variable object was last updated.
     * @param int $variableId IPS object ID of the variable.
     * @return int Timestamp when the IPS variable object was last updated.
     */
    public static function GetVariableUpdatedTimestamp($variableId)
    {
        // Get the variable info
        $info = IPS::GetVariable($variableId);

        // Return the timestamp
        return @$info['VariableUpdated'];
    }

    /**
     * Returns the timestamp when the IPS variable object was last changed (a new value was set).
     * @param int $variableId IPS object ID of the variable.
     * @return int Timestamp when the IPS variable object was last changed.
     */
    public static function GetVariableChangedTimestamp($variableId)
    {
        // Get the variable info
        $info = IPS::GetVariable($variableId);

        // Return the timestamp
        return @$info['VariableChanged'];
    }

    /**
     * Sets the custom variable profile of an IPS variable object.
     * @param int $variableId IPS object ID of the variable.
     * @param string $name Name of the IPS variable profile.
     * @return bool True if the variable profile was successfully assigned as the custom variable profile of the IPS variable object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/ips-setvariablecustomprofile/
     */
    public static function SetVariableCustomProfile($variableId, $name)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetVariableCustomProfile($variableId, $name);
    }

    /**
     * Sets the custom actions script of an IPS variable object.
     * @param int $variableId IPS object ID of the variable.
     * @param int $scriptId IPS object ID of the script that is called when the variable value is updated.
     * @return bool True if the script was successfully assigned as the custom action script of the IPS variable object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/ips-setvariablecustomaction/
     */
    public static function SetVariableCustomAction($variableId, $scriptId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetVariableCustomAction($variableId, $scriptId);
    }

    /**
     * Returns all IPS event object IDs associated to an IPS variable object.
     * @param int $variableId IPS object ID of the variable.
     * @return array IPS variable object IDs.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/ips-getvariableeventlist/
     */
    public static function GetVariableEventList($variableId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetVariableEventList($variableId);
    }

    /**
     * Returns the number of IPS event objects associated to an IPS variable object.
     * @param int $variableId IPS object ID of the variable.
     * @return int Number of IPS event objects associated to the given IPS variable object.
     * @see IPS::GetVariableEventList()
     */
    public static function GetVariableEventCount($variableId)
    {
        return count(self::GetVariableEventList($variableId));
    }

    /**
     * Returns the names of all IPS variable profiles of the given type.
     * @param int $type Type of the variable profile (see link).
     * @return array Names of all IPS variable profiles of the given type.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/variablenprofile/ips-getvariableprofilelistbytype/
     */
    public static function GetVariableProfileListByType($type)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetVariableProfileListByType($type);
    }

    /**
     * Returns the number of IPS variable profiles of the given type.
     * @param int $type Type of the variable profile.
     * @return int Number of IPS variable profiles of the given type.
     * @see IPS::GetVariableProfileListByType()
     */
    public static function GetVariableProfileCountByType($type)
    {
        return count(self::GetVariableProfileListByType($type));
    }

    /**
     * Returns information about an IPS variable profile.
     * @param string $name Name of the IPS variable profile.
     * @return array Attributes of the IPS variable profile.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/variablenprofile/ips-getvariableprofile/
     */
    public static function GetVariableProfile($name)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetVariableProfile($name);
    }

    /**
     * Checks if an IPS variable profile exists.
     * @param string $name Name of the IPS variable profile.
     * @return bool True if the IPS variable profile exists.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/variablenprofile/ips-variableprofileexists/
     */
    public static function VariableProfileExists($name)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_VariableProfileExists($name);
    }

    /**
     * Creates a new IPS variable profile of the given type.
     * @param string $name Name of the IPS variable profile.
     * @param int $profileType Type of the IPS variable profile (see link).
     * @return bool True if the variable profile was successfully created.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/variablenprofile/ips-createvariableprofile/
     */
    public static function CreateVariableProfile($name, $profileType)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_CreateVariableProfile($name, $profileType);
    }

    /**
     * Deletes an IPS variable profile.
     * @param string $name Name of the IPS variable profile.
     * @return bool True if the variable profile was successfully deleted.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/variablenprofile/ips_deletevariableprofile/
     */
    public static function DeleteVariableProfile($name)
    {
        // Return true if the variable profile does not exist
        if (! self::VariableProfileExists($name)) {
            return true;
        }

        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_DeleteVariableProfile($name);
    }

    /**
     * Sets the number of decimal digits used when formatting a value with the specified IPS variable profile.
     * @param string $name Name of the IPS variable profile.
     * @param int $numDigits Number of decimal digits.
     * @return bool True if the variable profile was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/variablenprofile/ips-setvariableprofiledigits/
     */
    public static function SetVariableProfileDigits($name, $numDigits)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetVariableProfileDigits($name, $numDigits);
    }

    /**
     * Sets the minimum und maximum value and the increment used when formatting a value with the specified IPS variable profile.
     * @param string $name Name of the IPS variable profile.
     * @param float $minValue Smallest value.
     * @param float $maxValue Largest value.
     * @param float $stepSize Increment used for visualization.
     * @return bool True if the variable profile was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/variablenprofile/ips-setvariableprofilevalues/
     */
    public static function SetVariableProfileValues($name, $minValue, $maxValue, $stepSize)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetVariableProfileValues($name, $minValue, $maxValue, $stepSize);
    }

    /**
     * Sets the prefix und suffix texts used when formatting a value with the specified IPS variable profile.
     * @param string $name Name of the IPS variable profile.
     * @param string $prefix Text used as a prefix before the formatted value.
     * @param string $suffix Text used as a suffix after the formatted value.
     * @return bool True if the variable profile was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/variablenprofile/ips-setvariableprofiletext/
     */
    public static function SetVariableProfileText($name, $prefix, $suffix)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetVariableProfileText($name, $prefix, $suffix);
    }

    /**
     * Sets the default icon of the variable profile.
     * @param string $name Name of the IPS variable profile.
     * @param string $iconName Name of the default icon.
     * @return bool True if the variable profile was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/variablenprofile/ips-setvariableprofileicon/
     */
    public static function SetVariableProfileIcon($name, $iconName)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetVariableProfileIcon($name, $iconName);
    }

    /**
     * Sets the name, icon and color of a specific value when formatting a value with the specified IPS variable profile.
     * The association will be deleted when the name and the icon are empty.
     * @param string $profileName Name of the IPS variable profile.
     * @param float $value Value with which the name, icon and color should be associated.
     * @param string $name Text to be used when formatting the value.
     * @param string $icon Icon to be displayed when visualizing the variable.
     * @param string $color Color to be used when visualizing the variable.
     * @return bool True if the variable profile was successfully updated.
     * https://www.symcon.de/service/dokumentation/befehlsreferenz/variablenverwaltung/variablenprofile/ips-setvariableprofileassociation/
     */
    public static function SetVariableProfileAssociation($profileName, $value, $name, $icon, $color)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetVariableProfileAssociation($profileName, $value, $name, $icon, $color);
    }

    /**
     * Creates a new IPS script object.
     * @param int $scriptType Type of the script (currently only 0 is supported for PHP scripts).
     * @return int IPS object ID of the new script object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/skriptverwaltung/ips-createscript/
     */
    public static function CreateScript($scriptType)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_CreateScript($scriptType);
    }

    /**
     * Deletes an IPS script object.
     * @param int $scriptId IPS object ID of the script.
     * @param bool $deleteFile True if the script file should also be deleted.
     * @return bool True if the script object (and file) was successfully deleted.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/skriptverwaltung/ips-deletescript/
     */
    public static function DeleteScript($scriptId, $deleteFile = true)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_DeleteScript($scriptId, $deleteFile);
    }

    /**
     * Checks if an IPS script object exists.
     * @param int $scriptId IPS object ID of the script.
     * @return bool True if the IPS script object exists.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/skriptverwaltung/ips-scriptexists/
     */
    public static function ScriptExists($scriptId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_ScriptExists($scriptId);
    }

    /**
     * Returns a list of all IPS script object IDs.
     * @return array IPS script object IDs.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/skriptverwaltung/ips-getscriptlist/
     */
    public static function GetScriptList()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetScriptList();
    }

    /**
     * Returns the number of all IPS script objects.
     * @return int Number of all IPS script objects.
     * @see IPS::GetScriptList()
     */
    public static function GetScriptCount()
    {
        return count(self::GetScriptList());
    }

    /**
     * Returns information about an IPS script object.
     * @param int $scriptId IPS object ID of the script.
     * @return array Attributes of the IPS script object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/skriptverwaltung/ips-getscript/
     */
    public static function GetScript($scriptId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetScript($scriptId);
    }

    /**
     * Returns the contents of the script file associated with an IPS script object.
     * @param int $scriptId IPS object ID of the script.
     * @return string Contents of the script file.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/skriptverwaltung/ips-getscriptcontent/
     */
    public static function GetScriptContent($scriptId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetScriptContent($scriptId);
    }

    /**
     * Replaces the contents of the script file associated with an IPS script object.
     * @param int $scriptId IPS object ID of the script.
     * @param string $content New contents of the script file.
     * @return bool True if the script file contents were successfully replaced.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/skriptverwaltung/ips-setscriptcontent/
     */
    public static function SetScriptContent($scriptId, $content)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetScriptContent($scriptId, $content);
    }

    /**
     * Returns the file name of the script file associated with an IPS script object.
     * @param int $scriptId IPS object ID of the script.
     * @return string Script file name.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/skriptverwaltung/ips-getscriptfile/
     */
    public static function GetScriptFile($scriptId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetScriptFile($scriptId);
    }

    /**
     * Sets the name of the script file associated with an IPS script object.
     * @param int $scriptId IPS object ID of the script.
     * @param string $fileName New script file name.
     * @return bool True if the script file was successfully set.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/skriptverwaltung/ips-setscriptfile/
     */
    public static function SetScriptFile($scriptId, $fileName)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetScriptFile($scriptId, $fileName);
    }

    /**
     * Returns the IPS object ID of the script object associated with the given script file.
     * @param string $fileName Script file name.
     * @return int IPS object ID of the script object or null if the script object can not be found.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/skriptverwaltung/ips-getscriptidbyfile/
     */
    public static function GetScriptIdByFile($fileName)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetScriptIDByFile($fileName);
    }

    /**
     * Returns the time in seconds of the timer set for an IPS script object.
     * @param int $scriptId IPS object ID of the script.
     * @return int Number of seconds configured as script timer for the script (not the remaining time) or 0 if no script timer was set.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/skriptverwaltung/ips-getscripttimer/
     */
    public static function GetScriptTimer($scriptId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetScriptTimer($scriptId);
    }

    /**
     * Sets the time in seconds when the script is called by the script timer.
     * @param int $scriptId IPS object ID of the script.
     * @param int $interval Number of seconds between script invocations by the script timer or 0 to delete the timer.
     * @return bool True if the script timer was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/skriptverwaltung/ips-setscripttimer/
     */
    public static function SetScriptTimer($scriptId, $interval)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetScriptTimer($scriptId, $interval);
    }

    /**
     * Runs the IPS script in a separate thread.
     * @param int $scriptId IPS object ID of the script.
     * @return bool True if the script was successfully started.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ablaufsteuerung/ips-runscript/
     */
    public static function RunScript($scriptId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_RunScript($scriptId);
    }

    /**
     * Runs the IPS script in a separate thread, passing an array of parameters.
     * @param int $scriptId IPS object ID of the script.
     * @param array $parameters Parameters to be passed to the script.
     * @return bool True if the script was successfully started.
     * @see IPS::RunScript()
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ablaufsteuerung/ips-runscriptex/
     */
    public static function RunScriptEx($scriptId, array $parameters)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_RunScriptEx($scriptId, $parameters);
    }

    /**
     * Runs the IPS script in a separate thread, waits for it and returns its output.
     * @param int $scriptId IPS object ID of the script.
     * @return string Output of the script.
     * @see IPS::RunScript()
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ablaufsteuerung/ips-runscriptwait/
     */
    public static function RunScriptWait($scriptId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_RunScriptWait($scriptId);
    }

    /**
     * Runs the IPS script in a separate thread, passing an array of parameters, waits for it and returns its output.
     * @param int $scriptId IPS object ID of the script.
     * @param array $parameters Parameters to be passed to the script.
     * @return string Output of the script.
     * @see IPS::RunScriptWait()
     * @see IPS::RunScriptEx()
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ablaufsteuerung/ips-runscriptwaitex/
     */
    public static function RunScriptWaitEx($scriptId, array $parameters)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_RunScriptWaitEx($scriptId, $parameters);
    }

    /**
     * Runs the given PHP code in a separate thread.
     * @param string $content PHP code to be executed.
     * @return bool True if the code was successfully started.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ablaufsteuerung/ips-runscripttext/
     */
    public static function RunScriptText($content)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_RunScriptText($content);
    }

    /**
     * Runs the given PHP code in a separate thread, passing an array of parameters.
     * @param string $content PHP code to be executed.
     * @param array $parameters Parameters to be passed to the script.
     * @see IPS::RunScriptText()
     * @return bool True if the code was successfully started.
     */
    public static function RunScriptTextEx($content, array $parameters)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_RunScriptTextEx($content, $parameters);
    }

    /**
     * Runs the given PHP code in a separate thread, waits for it and returns its output.
     * @param string $content PHP code to be executed.
     * @see IPS::RunScriptText()
     * @return string Output of the script.
     */
    public static function RunScriptTextWait($content)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_RunScriptTextWait($content);
    }

    /**
     * Runs the given PHP code in a separate thread, passing an array of parameters, waits for it and returns its output.
     * @param string $content PHP code to be executed.
     * @param array $parameters Parameters to be passed to the script.
     * @see IPS::RunScriptText()
     * @see IPS::RunScriptTextWait()
     * @return string Output of the script.
     */
    public static function RunScriptTextWaitEx($content, array $parameters)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_RunScriptTextWaitEx($content, $parameters);
    }

    /**
     * Creates a new IPS event object of the given type.
     * @param int $eventType Type of the event.
     * @return int IPS object ID of the new event object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-createevent/
     */
    public static function CreateEvent($eventType)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_CreateEvent($eventType);
    }

    /**
     * Deletes an IPS event object.
     * @param int $eventId IPS object ID of the event.
     * @return bool True if the event was successfully deleted.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-deleteevent/
     */
    public static function DeleteEvent($eventId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_DeleteEvent($eventId);
    }

    /**
     * Checks if an IPS event object exists.
     * @param int $eventId IPS object ID of the event.
     * @return bool True if the event object exists.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-eventexists/
     */
    public static function EventExists($eventId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_EventExists($eventId);
    }

    /**
     * Returns information about an IPS event object.
     * @param int $eventId IPS object ID of the event.
     * @return array Attributes of the event object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-getevent/
     */
    public static function GetEvent($eventId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetEvent($eventId);
    }

    /**
     * Returns the IPS object ID of the first event with the given name under the given IPS object.
     * @param string $eventName Name of the event.
     * @param int $objectId IPS object ID of the parent object.
     * @return int IPS object ID of the event or null if none was found.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-geteventidbyname/
     */
    public static function GetEventIdByName($eventName, $objectId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetEventIDByName($eventName, $objectId);
    }

    /**
     * Returns a list of the IPS object IDs of all event objects.
     * @return array IPS object IDs of all event objects.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-geteventlist/
     */
    public static function GetEventList()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetEventList();
    }

    /**
     * Returns the number of all IPS event objects.
     * @return int Number of all IPS event objects.
     * @see IPS::GetEventList()
     */
    public static function GetEventCount()
    {
        return count(self::GetEventList());
    }

    /**
     * Returns a list of the IPS object IDs of all event objects of the given type.
     * @param int $eventType Type of the events.
     * @return array IPS object IDs of all event objects of the given type.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-geteventlistbytype/
     */
    public static function GetEventListByType($eventType)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetEventListByType($eventType);
    }

    /**
     * Returns the number of all IPS event objects of the given type.
     * @param int $eventType Type of the events.
     * @return int Number of all IPS event objects of the given type.
     * @see IPS::GetEventListByType()
     */
    public static function GetEventCountByType($eventType)
    {
        return count(self::GetEventListByType($eventType));
    }

    /**
     * Enables or disabled the IPS event object.
     * @param int $eventId IPS object ID of the event.
     * @param bool $active True if the event shoudl be enabled.
     * @return bool True if the event was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-seteventactive/
     */
    public static function SetEventActive($eventId, $active)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetEventActive($eventId, $active);
    }

    /**
     * Sets the time when the cyclic IPS event should be triggered.
     * @param int $eventId IPS object ID of the event.
     * @param int $dateType Type of the date interval (see link).
     * @param int $dateInterval Date interval type (see link).
     * @param int $dateDays Week days when the event should be triggered (see link).
     * @param int $dateDayInterval Trigger the event each number of days (see link).
     * @param int $timeType Type of the time interval (see link).
     * @param int $timeInterval Time interval type (see link).
     * @return bool True if the event was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-seteventcyclic/
     */
    public static function SetEventCyclic($eventId, $dateType, $dateInterval, $dateDays, $dateDayInterval, $timeType, $timeInterval)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetEventCyclic($eventId, $dateType, $dateInterval, $dateDays, $dateDayInterval, $timeType, $timeInterval);
    }

    /**
     * Sets the bounds for the first occurrence for a cyclic IPS event.
     * @param int $eventId IPS object ID of the event.
     * @param int $day Start day.
     * @param int $month Start month.
     * @param int $year Start year.
     * @return bool True if the event was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-seteventcyclicdatefrom/
     */
    public static function SetEventCyclicDateFrom($eventId, $day, $month, $year)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetEventCyclicDateFrom($eventId, $day, $month, $year);
    }

    /**
     * Sets the bounds for the last occurrence for a cyclic IPS event.
     * @param int $eventId IPS object ID of the event.
     * @param int $day End day.
     * @param int $month End month.
     * @param int $year End year.
     * @return bool True if the event was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-seteventcyclicdateto/
     */
    public static function SetEventCyclicDateTo($eventId, $day, $month, $year)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetEventCyclicDateTo($eventId, $day, $month, $year);
    }

    /**
     * Sets the bounds for the first occurrence for a cyclic IPS event.
     * @param int $eventId IPS object ID of the event.
     * @param int $hour Start hour.
     * @param int $minute Start minute.
     * @param int $second Start second.
     * @return bool True if the event was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-seteventcyclictimefrom/
     */
    public static function SetEventCyclicTimeFrom($eventId, $hour, $minute, $second)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetEventCyclicTimeFrom($eventId, $hour, $minute, $second);
    }

    /**
     * Sets the bounds for the last occurrence for a cyclic IPS event.
     * @param int $eventId IPS object ID of the event.
     * @param int $hour End hour.
     * @param int $minute End minute.
     * @param int $second End second.
     * @return bool True if the event was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-seteventcyclictimeto/
     */
    public static function SetEventCyclicTimeTo($eventId, $hour, $minute, $second)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetEventCyclicTimeTo($eventId, $hour, $minute, $second);
    }

    /**
     * Sets the number of executions of a cyclic IPS event object.
     * @param int $eventId IPS object ID of the event.
     * @param int $count Number of executions of the cyclic event or 0 to execute indefinitely.
     * @return bool True if the event was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-seteventlimit/
     */
    public static function SetEventLimit($eventId, $count)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetEventLimit($eventId, $count);
    }

    /**
     * Sets the name, color and script contents for a week plan event or deletes it.
     * If the event's parent is an IPS script object, the given script is ignored and the parent script is executed instead.
     * @param int $eventId IPS object ID of the event.
     * @param int $actionId Unique ID of the action (used as sorting criteria).
     * @param string $name Name of the action. Leave empty to delete the action.
     * @param int $color RGB color value of the action.
     * @param string $scriptContent PHP script to be executed when the action is triggered.
     * @return bool True if the event was successfully updated.
     * @see IPS::SetEventScheduleGroupPoint()
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-seteventscheduleaction/
     */
    public static function SetEventScheduleAction($eventId, $actionId, $name, $color, $scriptContent)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetEventScheduleAction($eventId, $actionId, $name, $color, $scriptContent);
    }

    /**
     * Sets a group of days for a week plan event of deletes it.
     * @param int $eventId IPS object ID of the event.
     * @param int $groupId Unique ID of the group (used as sorting criteria).
     * @param int $days Days for the group (see link).
     * @return bool True if the event was successfully updated.
     * @see IPS::SetEventCyclic()
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-seteventschedulegroup/
     */
    public static function SetEventScheduleGroup($eventId, $groupId, $days)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetEventScheduleGroup($eventId, $groupId, $days);
    }

    /**
     * Sets a time point at a group of days for a week plan event or deletes it.
     * @param int $eventId IPS object ID of the event.
     * @param int $groupId ID of the group of days as defined by IPS::SetEventScheduleGroup().
     * @param int $pointId Unique (within the same $groupId) ID of the time point (used as sorting criteria).
     * @param int $hour Hour (or -1 to delete the time point).
     * @param int $minute Minute (or -1 to delete the time point).
     * @param int $second Second (or -1 to delete the time point).
     * @param int $actionId ID of the action as defined by IPS::SetEventScheduleAction().
     * @return bool True if the event was successfully updated.
     * @see IPS::SetEventScheduleGroup()
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-seteventschedulegrouppoint/
     */
    public static function SetEventScheduleGroupPoint($eventId, $groupId, $pointId, $hour, $minute, $second, $actionId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetEventScheduleGroupPoint($eventId, $groupId, $pointId, $hour, $minute, $second, $actionId);
    }

    /**
     * Sets the contents of the script that is executed when an event is triggered.
     * If the event's parent is an IPS script object, the given script is ignored and the parent script is executed instead.
     * @param int $eventId IPS object ID of the event.
     * @param string $scriptContent PHP script to be executed when the event is triggered.
     * @return bool True if the script was successfully associated with the event.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-seteventscript/
     */
    public static function SetEventScript($eventId, $scriptContent)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetEventScript($eventId, $scriptContent);
    }

    /**
     * Sets the method to determine when an event should be triggered by an IPS variable.
     * @param int $eventId IPS object ID of the event.
     * @param int $triggerType Type of the method to determine when the event should be triggered by the IPS variable.
     * @param int $variableId IPS object ID of the variable.
     * @return bool True if the variable was successfully associated with the event.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-seteventtrigger/
     */
    public static function SetEventTrigger($eventId, $triggerType, $variableId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetEventTrigger($eventId, $triggerType, $variableId);
    }

    /**
     * Enables or disables whether the event is triggered again before the condition that triggered the event clears.
     * @param int $eventId IPS object ID of the event.
     * @param bool $again True to trigger the event again before the condition cleared.
     * @return bool True if the event was successfully updated.
     * @see IPS::SetEventTrigger()
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-seteventtriggersubsequentexecution/
     */
    public static function SetEventTriggerSubsequentExecution($eventId, $again)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetEventTriggerSubsequentExecution($eventId, $again);
    }

    /**
     * Sets the reference values used to determine whether an event should be triggered by an IPS variable.
     * @param int $eventId IPS object ID of the event.
     * @param mixed $value Reference value used for the comparison.
     * @return bool True if the event was successfully updated.
     * @see IPS::SetEventTrigger()
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ereignisverwaltung/ips-seteventtriggervalue/
     */
    public static function SetEventTriggerValue($eventId, $value)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetEventTriggerValue($eventId, $value);
    }

    /**
     * Creates a new IPS link object.
     * @return int IPS object ID of the new IPS link object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/linkverwaltung/ips-createlink/
     */
    public static function CreateLink()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_CreateLink();
    }

    /**
     * Deletes an IPS link object.
     * @param int $linkId IPS object ID of the link.
     * @return bool True if the link was successfully deleted.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/linkverwaltung/ips-deletelink/
     */
    public static function DeleteLink($linkId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_DeleteLink($linkId);
    }

    /**
     * Returns information about an IPS link object.
     * @param int $linkId IPS object ID of the link.
     * @return array Attributes of the IPS link object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/linkverwaltung/ips-getlink/
     */
    public static function GetLink($linkId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetLink($linkId);
    }

    /**
     * Returns the first IPS link object with the given name under the given parent object.
     * @param string $name Name of the link.
     * @param int $parentId IPS object ID of the parent object.
     * @return int IPS object ID of the link or false if none was found.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/linkverwaltung/ips-getlinkidbyname/
     */
    public static function GetLinkIdByName($name, $parentId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetLinkIDByName($name, $parentId);
    }

    /**
     * Returns the IPS object IDs of all link objects.
     * @return array IPS object IDs of all link objects.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/linkverwaltung/ips-getlinklist/
     */
    public static function GetLinkList()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetLinkList();
    }

    /**
     * Returns the number of all IPS link objects.
     * @return int Number of all IPS link objects.
     * @see IPS::GetLinkList()
     */
    public static function GetLinkCount()
    {
        return count(self::GetLinkList());
    }

    /**
     * Checks if an IPS link object exists.
     * @param int $linkId IPS object ID of the link.
     * @return bool True if the IPS link object exists.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/linkverwaltung/ips-linkexists/
     */
    public static function LinkExists($linkId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_LinkExists($linkId);
    }

    /**
     * Returns the IPS object ID of the target object of a link.
     * @param int $linkId IPS object ID of the link.
     * @param int $targetId IPS object ID of the object to which the link points.
     * @return bool True if the link target was successfully updated.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/linkverwaltung/ips-setlinktargetid/
     */
    public static function SetLinkTargetId($linkId, $targetId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetLinkTargetID($linkId, $targetId);
    }

    /**
     * Creates a new IPS media object of the given type.
     * @param int $mediaType Type of the media object (see link).
     * @return int IPS object ID of the new media object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/medienverwaltung/ips-createmedia/
     */
    public static function CreateMedia($mediaType)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_CreateMedia($mediaType);
    }

    /**
     * Deletes an IPS media object.
     * @param int $mediaId IPS object ID of the media object.
     * @param bool $deleteFile True if the media file should also be deleted.
     * @return bool True if the media object was successfully deleted.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/medienverwaltung/ips-deletemedia/
     */
    public static function DeleteMedia($mediaId, $deleteFile)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_DeleteMedia($mediaId, $deleteFile);
    }

    /**
     * Returns information about an IPS media object.
     * @param int $mediaId IPS object ID of the media object.
     * @return array Attributes of the media object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/medienverwaltung/ips-getmedia/
     */
    public static function GetMedia($mediaId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetMedia($mediaId);
    }

    /**
     * Returns the Base64 encoded contents of an IPS media object.
     * @param int $mediaId IPS object ID of the media object.
     * @return string Base64 encoded contents of the media object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/medienverwaltung/ips-getmediacontent/
     */
    public static function GetMediaContent($mediaId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetMediaContent($mediaId);
    }

    /**
     * Updates the contents of an IPS media object's file.
     * @param int $mediaId IPS object ID of the media object.
     * @param string $content New Base64 encoded media contents.
     * @return bool True if the media file was successfully updated with the new contents.
     * @see IPS::SetMediaFile()
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/medienverwaltung/ips-setmediacontent/
     */
    public static function SetMediaContent($mediaId, $content)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetMediaContent($mediaId, $content);
    }

    /**
     * Associates the given file name with an IPS media object.
     * @param int $mediaId IPS object ID of the media object.
     * @param string $fileName Name of the file to be associated with the media object.
     * @param bool $mustExist True if the media file must already exist.
     * @return bool True if the media file was successfully associated with the media object.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/medienverwaltung/ips-setmediafile/
     */
    public static function SetMediaFile($mediaId, $fileName, $mustExist)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetMediaFile($mediaId, $fileName, $mustExist);
    }

    /**
     * Returns the first IPS media object associated to the given media name.
     * @param string $mediaName Name of the media object.
     * @return int IPS object ID of the media object or null if none was found.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/medienverwaltung/ips-getmediaid/
     */
    public static function GetMediaId($mediaName)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetMediaID($mediaName);
    }

    /**
     * Returns the IPS object ID of the media object associated to the given media file name.
     * @param string $fileName Name of the media file.
     * @return int IPS object ID of the media object or null if none was found.
     * @see IPS::SetMediaFile()
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/medienverwaltung/ips-getmediaidbyfile/
     */
    public static function GetMediaIdByFile($fileName)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetMediaIDByFile($fileName);
    }

    /**
     * Returns the first IPS media object with the given name under the given IPS object.
     * @param string $name Name of the IPS media object.
     * @param int $parentId IPS object ID of the parent object.
     * @return int IPS object ID of the media object or false if none was found.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/medienverwaltung/ips-getmediaidbyname/
     */
    public static function GetMediaIdByName($name, $parentId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetMediaIDByName($name, $parentId);
    }

    /**
     * Returns the list of the IPS object IDs of all media objects.
     * @return array IPS object IDs of all media objects.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/medienverwaltung/ips-getmedialist/
     */
    public static function GetMediaList()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetMediaList();
    }

    /**
     * Returns the number of all IPS media objects.
     * @return int Number of all IPS media objects.
     * @see IPS::GetMediaList()
     */
    public static function GetMediaCount()
    {
        return count(self::GetMediaList());
    }

    /**
     * Returns the list of the IPS object IDs of all media objects of the given type.
     * @param int $mediaType Type of the media objects.
     * @return array IPS object IDs of all media objects of the given type.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/medienverwaltung/ips-getmedialistbytype/
     */
    public static function GetMediaListByType($mediaType)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_GetMediaListByType($mediaType);
    }

    /**
     * Returns the number of all IPS media objects of the given type.
     * @param int $mediaType Type of the media objects.
     * @return int Number of all IPS media objects of the given type.
     * @see IPS::GetMediaListByType()
     */
    public static function GetMediaCountByType($mediaType)
    {
        return count(self::GetMediaListByType($mediaType));
    }

    /**
     * Checks if an IPS media object exists.
     * @param int $mediaId IPS object ID of the media object.
     * @return bool True if the media object exists.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/medienverwaltung/ips-mediaexists/
     */
    public static function MediaExists($mediaId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_MediaExists($mediaId);
    }

    /**
     * Notifies IP Symcon that the IPS media object was updated.
     * @param int $mediaId IPS object ID of the media object.
     * @return bool True if the update message was successfully processed by IP Symcon.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/medienverwaltung/ips-sendmediaevent/
     */
    public static function SendMediaEvent($mediaId)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetMediaEvent($mediaId);
    }

    /**
     * Enables or disables caching of the media file associated to an IPS media object.
     * @param int $mediaId IPS object ID of the media object.
     * @param bool $cacheEnabled True if the media file should be cached.
     * @return bool True if the caching was successfully enabled or disabled.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/medienverwaltung/ips-setmediacached/
     */
    public static function SetMediaCached($mediaId, $cacheEnabled)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SetMediaCached($mediaId, $cacheEnabled);
    }

    /**
     * Executes an external program.
     * @param string $fileName Command to be executed.
     * @param string $parameters Parameters passed to the external command as command line arguments.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ablaufsteuerung/ips-execute/
     */
    public static function Execute($fileName, $parameters)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        @IPS_Execute($fileName, $parameters, null, false);
    }

    /**
     * Executes an external program in the context of a user session.
     * @param string $fileName Command to be executed.
     * @param string $parameters Parameters passed to the external command as command line arguments.
     * @param bool $showWindow True if the program's window should be shown.
     * @param int $sessionId Windows session ID in which the program should be executed and optionally displayed.
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ablaufsteuerung/ips-executeex/
     */
    public static function ExecuteEx($fileName, $parameters, $showWindow, $sessionId = 0)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        @IPS_ExecuteEx($fileName, $parameters, $showWindow, false, $sessionId);
    }

    /**
     * Executes an external program, waits for it to end and returns the output.
     * @param string $fileName Command to be executed.
     * @param string $parameters Parameters passed to the external command as command line arguments.
     * @see IPS::Execute()
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ablaufsteuerung/ips-execute/
     */
    public static function ExecuteWait($fileName, $parameters)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_Execute($fileName, $parameters, null, true);
    }

    /**
     * Executes an external program in the context of a user session, waits for it to end and returns the output.
     * @param string $fileName Command to be executed.
     * @param string $parameters Parameters passed to the external command as command line arguments.
     * @param bool $showWindow True if the program's window should be shown.
     * @param int $sessionId Windows session ID in which the program should be executed and optionally displayed.
     * @see IPS::ExecuteEx()
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ablaufsteuerung/ips-executeex/
     */
    public static function ExecuteWaitEx($fileName, $parameters, $showWindow, $sessionId = 0)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_ExecuteEx($fileName, $parameters, $showWindow, true, $sessionId);
    }

    /**
     * Sets a semaphore to enable exclusive use of a resource or waits for another script to release the semaphore.
     * @param string $name Name of the semaphore.
     * @param int $waitTime Number of milliseconds to wait for another script to release the semaphore if it is already set.
     * @return bool True if the semaphore was successfully set or false if an timeout occurred while waiting for the release of the semaphore.
     * @see IPS::SemaphoreLeave()
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ablaufsteuerung/ips-semaphoreenter/
     */
    public static function SemaphoreEnter($name, $waitTime)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SemaphoreEnter($name, $waitTime);
    }

    /**
     * Releases a semaphore after the exclusive use of a resource is no longer necessary.
     * @param string $name Name of the semaphore.
     * @return bool True if the semaphore was successfully released.
     * @see IPS::SemaphoreEnter()
     * @link https://www.symcon.de/service/dokumentation/befehlsreferenz/ablaufsteuerung/ips-semaphoreleave/
     */
    public static function SemaphoreLeave($name)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        return @IPS_SemaphoreLeave($name);
    }

}