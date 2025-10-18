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


namespace Patami\IPS\Objects;


use Patami\IPS\Objects\Exceptions\ObjectInvalidClassNameException;
use Patami\IPS\Objects\Exceptions\ObjectNotFoundException;
use Patami\IPS\Objects\Exceptions\ObjectInvalidTypeException;
use Patami\IPS\System\IPS;


/**
 * Provides functions to manage IPS objects.
 * @package IPSPATAMI
 */
class Objects
{

    /**
     * Returns the IPS object with the given object ID.
     * It will try to load the class name saves in the object info.
     * If the class name is not remembered there, the class name is determined by inspecting the object type.
     * @param int $objectId IPS object ID.
     * @return IPSObject Instance of the IPS object.
     * @throws ObjectNotFoundException if the object could not be found.
     * @throws ObjectInvalidTypeException if the object type is invalid.
     * @throws ObjectInvalidClassNameException if the detected or loaded class name is invalid.
     * @see IPSObject
     * @see Objects::GetClassNameByType()
     */
    public static function GetByID($objectId)
    {
        // Throw an exception if the object does not exist
        if (! IPS::ObjectExists($objectId)) {
            throw new ObjectNotFoundException();
        }

        // Get the object type
        $info = IPS::GetObject($objectId);

        if (is_object($info)) {
            $info = get_object_vars($info);
        }

        if (!is_array($info)) {
            $info = [];
        }

        $type = array_key_exists('ObjectType', $info) ? $info['ObjectType'] : null;
        $subType = null;

        // Get the object info
        $objectInfo = array_key_exists('ObjectInfo', $info) ? $info['ObjectInfo'] : null;

        if (is_object($objectInfo)) {
            $objectInfo = get_object_vars($objectInfo);
        } elseif (is_string($objectInfo)) {
            $decodedInfo = json_decode($objectInfo, true);
            $objectInfo = is_array($decodedInfo) ? $decodedInfo : [];
        }

        if (!is_array($objectInfo)) {
            $objectInfo = [];
        }

        // Get the class name from the object info
        /** @var $objectClassName IPSObject */
        $objectClassName = array_key_exists('className', $objectInfo) ? $objectInfo['className'] : null;

        if (is_null($objectClassName)) {
            // No object info or no class name
            // Get the sub type
            switch ($type) {
                case IPSObject::TYPE_VARIABLE:
                    // Get the variable info
                    $varInfo = IPS::GetVariable($objectId);

                    if (is_object($varInfo)) {
                        $varInfo = get_object_vars($varInfo);
                    }

                    if (!is_array($varInfo)) {
                        $varInfo = [];
                    }

                    $subType = array_key_exists('VariableType', $varInfo) ? $varInfo['VariableType'] : null;
                    break;
            }
            // Get the object class name
            /** @var $className Objects */
            $className = get_called_class();
            $objectClassName = $className::GetClassNameByType($type, $subType);
        } else {
            // Class name set
            // Check if the class is valid, otherwise throw an exception
            if (! is_a($objectClassName, 'Patami\\IPS\\Objects\\IPSObject', true)) {
                throw new ObjectInvalidClassNameException();
            }
        }

        // Create and return a new instance of the object
        return new $objectClassName($objectId);
    }

    /**
     * Returns the IPS script object associated with the given script file name.
     * @param string $fileName File name of the script.
     * @return IPSObject Instance of the IPS script object.
     * @throws ObjectNotFoundException if the object could not be found.
     * @throws ObjectInvalidTypeException if the object type is invalid.
     * @throws ObjectInvalidClassNameException if the detected or loaded class name is invalid.
     * @see Script
     */
    public static function GetScriptByFileName($fileName)
    {
        // Get the script ID
        $scriptId = IPS::GetScriptIdByFile($fileName);

        // Throw an error if the script was not found
        if ($scriptId === false) {
            throw new ObjectNotFoundException();
        }

        // Return the script object
        /** @var $className Objects */
        $className = get_called_class();
        return $className::GetByID($scriptId);
    }

    /**
     * Factory to create a new IPS object of the given type (and subtype).
     * @param int $type Type of the IPS object.
     * @param int|null $subType Subtype of the IPS object (eg. to create boolean variable objects).
     * @return IPSObject Instance of the new IPS object.
     * @throws ObjectInvalidTypeException if the object type is invalid.
     */
    public static function Create($type, $subType = null)
    {
        // Get the object class name
        /** @var $className Objects */
        $className = get_called_class();
        /** @var $objectClassName Object */
        $objectClassName = $className::GetClassNameByType($type, $subType);

        // Create and return an new instance of the object
        return new $objectClassName();
    }

    /**
     * Returns the class name associated to the object type and subtype.
     * @param int $type Type of the IPS object.
     * @param int $subType Subtype of the IPS object.
     * @return string FQCN of the IPS object class.
     * @throws ObjectInvalidTypeException if the object type is invalid.
     */
    public static function GetClassNameByType($type, $subType)
    {
        // Return the class name based on the object type
        switch ($type) {
            case IPSObject::TYPE_CATEGORY:
                return 'Patami\\IPS\\Objects\\Category';
            case IPSObject::TYPE_INSTANCE:
                return 'Patami\\IPS\\Objects\\Instance';
            case IPSObject::TYPE_VARIABLE:
                switch ($subType) {
                    case Variable::VARIABLE_TYPE_BOOLEAN:
                        return 'Patami\\IPS\\Objects\\BooleanVariable';
                    case Variable::VARIABLE_TYPE_INTEGER:
                        return 'Patami\\IPS\\Objects\\IntegerVariable';
                    case Variable::VARIABLE_TYPE_FLOAT:
                        return 'Patami\\IPS\\Objects\\FloatVariable';
                    case Variable::VARIABLE_TYPE_STRING:
                        return 'Patami\\IPS\\Objects\\StringVariable';
                }
                break;
            case IPSObject::TYPE_SCRIPT:
                return 'Patami\\IPS\\Objects\\Script';
            case IPSObject::TYPE_EVENT:
                return 'Patami\\IPS\\Objects\\Event';
            case IPSObject::TYPE_MEDIA:
                return 'Patami\\IPS\\Objects\\Media';
            case IPSObject::TYPE_LINK:
                return 'Patami\\IPS\\Objects\\Link';
        }

        // Throw an exception if an unsupported type was specified
        throw new ObjectInvalidTypeException();
    }

}