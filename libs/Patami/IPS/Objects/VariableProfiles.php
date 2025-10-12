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


use Patami\IPS\Helpers\StringHelper;
use Patami\IPS\Objects\Exceptions\VariableProfileInvalidTypeException;
use Patami\IPS\Objects\Exceptions\VariableProfileNotFoundException;
use Patami\IPS\System\Color;
use Patami\IPS\System\Icons;
use Patami\IPS\System\IPS;


/**
 * Provides functions to manage IPS variable profile objects.
 * @package IPSPATAMI
 */
class VariableProfiles
{

    /**
     * Pre-defined variable profile names.
     */
    const TYPE_UNIX_TIMESTAMP       = '~UnixTimestamp';
    const TYPE_UNIX_TIMESTAMP_DATE  = '~UnixTimestampDate';
    const TYPE_TEMPERATURE          = '~Temperature';
    const TYPE_SWITCH               = '~Switch';
    const TYPE_INTENSITY_1          = '~Intensity.1';
    const TYPE_INTENSITY_100        = '~Intensity.100';
    const TYPE_INTENSITY_255        = '~Intensity.255';
    const TYPE_HEX_COLOR            = '~HexColor';
    const TYPE_PATAMI_YESNO         = 'PatamiFramework.YesNo';
    const TYPE_PATAMI_YESNO_IC      = 'PatamiFramework.YesNo.IC';
    const TYPE_PATAMI_YESNO_NC      = 'PatamiFramework.YesNo.NC';
    const TYPE_PATAMI_ONOFF         = 'PatamiFramework.OnOff';
    const TYPE_PATAMI_ONOFF_IC      = 'PatamiFramework.OnOff.IC';
    const TYPE_PATAMI_ONOFF_NC      = 'PatamiFramework.OnOff.NC';
    const TYPE_PATAMI_LICENSE_LIMIT = 'PatamiFramework.LicenseLimit';
    const TYPE_PATAMI_MEGABYTES     = 'PatamiFramework.Megabytes';

    /**
     * Returns an existing IPS variable profile object with the given name.
     * @param string $name Name of the variable profile.
     * @return VariableProfile IPS variable profile object.
     * @throws VariableProfileNotFoundException if no profile with the given name could be found.
     * @see VariableProfile
     */
    public static function GetByName($name)
    {
        // Throw an exception if the object does not exist
        if (! IPS::VariableProfileExists($name)) {
            throw new VariableProfileNotFoundException();
        }

        // Get the object type
        $info = IPS::GetVariableProfile($name);
        $type = @$info['ProfileType'];

        // Get the profile class name
        /** @var $className VariableProfiles */
        $className = get_called_class();
        /** @var $profileClassName VariableProfile */
        $profileClassName = $className::GetClassNameByType($type);

        // Create and return a new instance of the profile
        return new $profileClassName($name);
    }

    /**
     * Returns all IPS variable profile objects of the given type.
     * @param int $type Type of the variable profiles.
     * @return array VariableProfile objects with the given type.
     * @throws VariableProfileInvalidTypeException if the type is invalid.
     */
    public static function GetByType($type)
    {
        // Get the class name
        /** @var $className VariableProfiles */
        $className = get_called_class();

        // Get the profile class name (just to make sure the type is valid)
        $className::GetClassNameByType($type);

        // Get the list of profile names by type
        $profileNames = IPS::GetVariableProfileListByType($type);

        // Return an empty array if the list cannot be retrieved
        if (! $profileNames) {
            return array();
        }

        // Loop through the object list and create the variable profile objects
        $profiles = array();
        foreach ($profileNames as $profileName) {
            $profiles[] = $className::GetByName($profileName);
        }

        // Return the list of profile objects
        return $profiles;
    }

    /**
     * Creates a new IPS variable profile object.
     * @param string $name Name of the new variable profile.
     * @param int $type Type of the new variable profile.
     * @return VariableProfile New IPS variable profile object.
     * @throws VariableProfileInvalidTypeException if the type is invalid.
     */
    public static function Create($name, $type)
    {
        // Get the profile class name
        /** @var $className VariableProfiles */
        $className = get_called_class();
        /** @var $profileClassName VariableProfile */
        $profileClassName = $className::GetClassNameByType($type);

        // Create and return an new instance of the object
        return new $profileClassName($name);
    }

    /**
     * Returns the class name associated to the IPS variable profile type.
     * @param int $type Type of the variable profile.
     * @return string FQCN of the IPS variable profile class.
     * @throws VariableProfileInvalidTypeException if the type is invalid.
     */
    public static function GetClassNameByType($type)
    {
        // Return the class name based on the object type
        switch ($type) {
            case Variable::VARIABLE_TYPE_BOOLEAN:
                return 'Patami\\IPS\\Objects\\BooleanVariableProfile';
            case Variable::VARIABLE_TYPE_INTEGER:
                return 'Patami\\IPS\\Objects\\IntegerVariableProfile';
            case Variable::VARIABLE_TYPE_FLOAT:
                return 'Patami\\IPS\\Objects\\FloatVariableProfile';
            case Variable::VARIABLE_TYPE_STRING:
                return 'Patami\\IPS\\Objects\\StringVariableProfile';
        }

        // Throw an exception if an unsupported type was specified
        throw new VariableProfileInvalidTypeException();
    }

    /**
     * Creates the Patami Yes/No boolean variable profile.
     */
    public static function CreateYesNo()
    {
        $profile = new BooleanVariableProfile(self::TYPE_PATAMI_YESNO);
        $profile->AddAssociationByValue(0, StringHelper::GetBooleanAsYesNo(false), Icons::CLOSE, new Color(Color::RED));
        $profile->AddAssociationByValue(1, StringHelper::GetBooleanAsYesNo(true), Icons::OK, new Color(Color::GREEN));
    }

    /**
     * Create the Patami Yes/No boolean variable profile with inverted colors.
     */
    public static function CreateYesNoIC()
    {
        $profile = new BooleanVariableProfile(self::TYPE_PATAMI_YESNO_IC);
        $profile->AddAssociationByValue(0, StringHelper::GetBooleanAsYesNo(false), Icons::CLOSE, new Color(Color::GREEN));
        $profile->AddAssociationByValue(1, StringHelper::GetBooleanAsYesNo(true), Icons::OK, new Color(Color::RED));
    }

    /**
     * Create the Patami Yes/No boolean variable profile without colors.
     */
    public static function CreateYesNoNC()
    {
        $transparent = new Color(Color::TRANSPARENT);
        $profile = new BooleanVariableProfile(self::TYPE_PATAMI_YESNO_NC);
        $profile->AddAssociationByValue(0, StringHelper::GetBooleanAsYesNo(false), Icons::CLOSE, $transparent);
        $profile->AddAssociationByValue(1, StringHelper::GetBooleanAsYesNo(true), Icons::OK, $transparent);
    }

    /**
     * Creates the Patami On/Off boolean variable profile.
     */
    public static function CreateOnOff()
    {
        $profile = new BooleanVariableProfile(self::TYPE_PATAMI_ONOFF);
        $profile->AddAssociationByValue(0, StringHelper::GetBooleanAsOnOff(false), Icons::CLOSE, new Color(Color::RED));
        $profile->AddAssociationByValue(1, StringHelper::GetBooleanAsOnOff(true), Icons::OK, new Color(Color::GREEN));
    }

    /**
     * Create the Patami On/Off boolean variable profile with inverted colors.
     */
    public static function CreateOnOffIC()
    {
        $profile = new BooleanVariableProfile(self::TYPE_PATAMI_ONOFF_IC);
        $profile->AddAssociationByValue(0, StringHelper::GetBooleanAsOnOff(false), Icons::CLOSE, new Color(Color::GREEN));
        $profile->AddAssociationByValue(1, StringHelper::GetBooleanAsOnOff(true), Icons::OK, new Color(Color::RED));
    }

    /**
     * Create the Patami On/Off boolean variable profile without colors.
     */
    public static function CreateOnOffNC()
    {
        $transparent = new Color(Color::TRANSPARENT);
        $profile = new BooleanVariableProfile(self::TYPE_PATAMI_ONOFF_NC);
        $profile->AddAssociationByValue(0, StringHelper::GetBooleanAsOnOff(false), Icons::CLOSE, $transparent);
        $profile->AddAssociationByValue(1, StringHelper::GetBooleanAsOnOff(true), Icons::OK, $transparent);
    }

}