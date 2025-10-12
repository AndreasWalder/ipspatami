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


use Patami\IPS\Objects\Exceptions\VariableProfileAssociationNotFoundException;
use Patami\IPS\Objects\Exceptions\VariableProfileAssociationSaveFailedException;
use Patami\IPS\Objects\Exceptions\VariableProfileCreateFailedException;
use Patami\IPS\Objects\Exceptions\VariableProfileDeleteFailedException;
use Patami\IPS\Objects\Exceptions\VariableProfileSetFailedException;
use Patami\IPS\System\Color;
use Patami\IPS\System\IPS;


/**
 * Abstract base class for the various IPS variable profile types.
 * @package IPSPATAMI
 */
abstract class VariableProfile
{

    /**
     * @var string Name of the variable profile.
     */
    protected $name;

    /**
     * VariableProfile constructor.
     * If the variable profile with the given name does not exist, it is created.
     * @param string $name Name of the variable profile.
     */
    public function __construct($name)
    {
        // Remember the profile name
        $this->name = $name;

        // Create the variable profile if it does not exist
        if (! $this->Exists()) {
            $this->Create();
        }
    }

    /**
     * Returns the type of the variable profile.
     * This method must be implemented by the concrete child class.
     * @return int Type of the variable profile.
     * @see Variable::VARIABLE_TYPE_BOOLEAN
     * @see Variable::VARIABLE_TYPE_INTEGER
     * @see Variable::VARIABLE_TYPE_FLOAT
     * @see Variable::VARIABLE_TYPE_STRING
     */
    abstract public function GetVariableType();

    /**
     * Creates a new IPS variable profile.
     * @throws VariableProfileCreateFailedException if the variable profile could not be created.
     * @see IPS::CreateVariableProfile()
     * @see VariableProfile::GetVariableType()
     */
    protected function Create()
    {
        // Create the instance
        $result = IPS::CreateVariableProfile($this->name, $this->GetVariableType());

        // Throw an exception if the profile could not be created
        if ($result === false) {
            throw new VariableProfileCreateFailedException();
        }
    }

    /**
     * Deletes the IPS variable profile.
     * @throws VariableProfileDeleteFailedException if the variable profile could not be deleted.
     * @see IPS::DeleteVariableProfile()
     */
    public function Delete()
    {
        // Delete the object
        $result = IPS::DeleteVariableProfile($this->name);

        // Throw an exception if the profile could not be deleted
        if ($result === false) {
            throw new VariableProfileDeleteFailedException();
        }
    }

    /**
     * Checks if the IPS variable profile exists.
     * @return bool True if the variable profile exists.
     * @see IPS::VariableProfileExists()
     */
    public function Exists()
    {
        return IPS::VariableProfileExists($this->name);
    }

    /**
     * Returns the name of the IPS variable profile.
     * @return string Name of the variable profile.
     */
    public function GetName()
    {
        // Return the name
        return $this->name;
    }

    /**
     * Returns the icon associated to the IPS variable profile.
     * @return string File name of the icon without path and extension.
     * @see IPS::GetVariableProfile()
     */
    public function GetIcon()
    {
        // Get the variable profile info
        $info = IPS::GetVariableProfile($this->name);

        // Return the icon name
        return @$info['Icon'];
    }

    /**
     * Sets the default icon of the variable profile.
     * @param string $iconName Name of the default icon.
     * @return $this Fluent interface.
     * @throws VariableProfileSetFailedException if the variable profile could not be updated.
     */
    public function SetIcon($iconName)
    {
        // Set the values via IPS
        $result = IPS::SetVariableProfileIcon($this->name, $iconName);

        // Throw an exception if an error occurred
        if ($result === false) {
            throw new VariableProfileSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Checks if the IPS variable profile is read-only.
     * Variable profiles are read-only if they are system created. The name of a system created profile begins with the ~ sign.
     * @return bool True if the variable profile is read-only.
     */
    public function IsReadOnly()
    {
        // Get the variable profile info
        $info = IPS::GetVariableProfile($this->name);

        // Return if the profile is read-only (system created profile)
        return @$info['IsReadOnly'] == true;
    }

    /**
     * Returns the minimum value used for formatting an IPS variable object.
     * @return float Minimum value used for formatting an IPS variable object.
     * @see IPS::GetVariableProfile()
     */
    public function GetMinValue()
    {
        // Get the variable profile info
        $info = IPS::GetVariableProfile($this->name);

        // Return the minimum visualization value
        return @$info['MinValue'];
    }

    /**
     * Sets the minimum value used for formatting an IPS variable object.
     * @param float $minValue New minimum value used for formatting an IPS variable object.
     * @return $this Fluent interface.
     * @throws VariableProfileSetFailedException if the value could not be set.
     * @see VariableProfile::SetValues()
     */
    public function SetMinValue($minValue)
    {
        // Set the value and enable fluent interface
        return $this->SetValues($minValue, $this->GetMaxValue(), $this->GetStepSize());
    }

    /**
     * Returns the maximum value used for formatting an IPS variable object.
     * @return float Maximum value used for formatting an IPS variable object.
     * @see IPS::GetVariableProfile()
     */
    public function GetMaxValue()
    {
        // Get the variable profile info
        $info = IPS::GetVariableProfile($this->name);

        // Return the maximum visualization value
        return @$info['MaxValue'];
    }

    /**
     * Sets the maximum value used for formatting an IPS variable object.
     * @param float $maxValue New maximum value used for formatting an IPS variable object.
     * @return $this Fluent interface.
     * @throws VariableProfileSetFailedException if the value could not be set.
     * @see VariableProfile::SetValues()
     */
    public function SetMaxValue($maxValue)
    {
        // Set the value and enable fluent interface
        return $this->SetValues($this->GetMinValue(), $maxValue, $this->GetStepSize());
    }

    /**
     * Returns the step size used for formatting an IPS variable object.
     * @return float Step size used for formatting an IPS variable object.
     * @see IPS::GetVariableProfile()
     */
    public function GetStepSize()
    {
        // Get the variable profile info
        $info = IPS::GetVariableProfile($this->name);

        // Return the step size for the visualization
        return @$info['StepSize'];
    }

    /**
     * Sets the step size used for formatting an IPS variable object.
     * @param float $stepSize New step size used for formatting an IPS variable object.
     * @return $this Fluent interface.
     * @throws VariableProfileSetFailedException if the value could not be set.
     * @see VariableProfile::SetValues()
     */
    public function SetStepSize($stepSize)
    {
        // Set the value and enable fluent interface
        return $this->SetValues($this->GetMinValue(), $this->GetMaxValue(), $stepSize);
    }

    /**
     * Sets the minimum, maximum and step size values used for formatting an IPS variable object.
     * @param float $minValue New minimum value.
     * @param float $maxValue New maximum value.
     * @param float $stepSize New step size.
     * @return $this Fluent interface.
     * @throws VariableProfileSetFailedException if the values could not be set.
     * @see IPS::SetVariableProfileValues()
     */
    public function SetValues($minValue, $maxValue, $stepSize)
    {
        // Set the values via IPS
        $result = IPS::SetVariableProfileValues($this->name, $minValue, $maxValue, $stepSize);

        // Throw an exception if an error occurred
        if ($result === false) {
            throw new VariableProfileSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the number of decimal digits used for formatting an IPS variable object.
     * @return int Number of decimal digits used for formatting an IPS variable object.
     * @see IPS::GetVariableProfile()
     */
    public function GetDigits()
    {
        // Get the variable profile info
        $info = IPS::GetVariableProfile($this->name);

        // Return the number of decimal digits
        return @$info['Digits'];
    }

    /**
     * Sets the number of decimal digits used for formatting an IPS variable object.
     * @param int $numDigits New number of decimal digits used for formatting an IPS variable object.
     * @return $this Fluent interface.
     * @throws VariableProfileSetFailedException if the value could not be set.
     * @see IPS::SetVariableProfileDigits()
     */
    public function SetDigits($numDigits)
    {
        // Set the number of digits via IPS
        $result = IPS::SetVariableProfileDigits($this->name, $numDigits);

        // Throw an exception if an error occurred
        if ($result === false) {
            throw new VariableProfileSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the prefix text used for formatting an IPS variable object.
     * @return string Prefix text used for formatting an IPS variable object.
     * @see IPS::GetVariableProfile()
     */
    public function GetPrefix()
    {
        // Get the variable profile info
        $info = IPS::GetVariableProfile($this->name);

        // Return the prefix string
        return @$info['Prefix'];
    }

    /**
     * Sets prefix text used for formatting an IPS variable object.
     * @param string $prefix New prefix text used for formatting an IPS variable object.
     * @return $this Fluent interface.
     * @throws VariableProfileSetFailedException if the value could not be set.
     * @see VariableProfile::SetText()
     */
    public function SetPrefix($prefix)
    {
        // Set the value and enable fluent interface
        return $this->SetText($prefix, $this->GetSuffix());
    }

    /**
     * Returns the suffix text used for formatting an IPS variable object.
     * @return string Suffix text used for formatting an IPS variable object.
     * @see IPS::GetVariableProfile()
     */
    public function GetSuffix()
    {
        // Get the variable profile info
        $info = IPS::GetVariableProfile($this->name);

        // Return the suffix string
        return @$info['Suffix'];
    }

    /**
     * Sets suffix text used for formatting an IPS variable object.
     * @param string $suffix New suffix text used for formatting an IPS variable object.
     * @return $this Fluent interface.
     * @throws VariableProfileSetFailedException if the value could not be set.
     * @see VariableProfile::SetText()
     */
    public function SetSuffix($suffix)
    {
        // Set the value and enable fluent interface
        return $this->SetText($this->GetPrefix(), $suffix);
    }

    /**
     * Sets the prefix and suffix texts used for formatting an IPS variable object.
     * @param string $prefix New prefix text.
     * @param string $suffix New suffix text.
     * @return $this Flient interface.
     * @throws VariableProfileSetFailedException if the values could not be set.
     * @see IPS::SetVariableProfileText()
     */
    public function SetText($prefix, $suffix)
    {
        // Set the values via IPS
        $result = IPS::SetVariableProfileText($this->name, $prefix, $suffix);

        // Throw an exception if an error occurred
        if ($result === false) {
            throw new VariableProfileSetFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns all variable profile association objects associated to the IPS variable profile.
     * @return array IPSVariableProfileAssociation objects associated to the variable profile.
     * @see IPS::GetVariableProfile()
     * @see VariableProfileAssociation
     */
    public function GetAssociations()
    {
        // Get the variable profile info
        $info = IPS::GetVariableProfile($this->name);

        // Return the suffix string
        $associationsData = @$info['Associations'];

        // Return an empty array if the associations cannot be read
        if (is_null($associationsData)) {
            return array();
        }

        // Loop through the associations and create the objects
        $associations = array();
        foreach ($associationsData as $data) {
            $associations[] = VariableProfileAssociation::CreateFromArray($data);
        }

        // Return the association object list
        return $associations;
    }

    /**
     * Returns the variable profile association assigned to the given value.
     * @param mixed $value Value for which the variable profile association should be returned.
     * @return VariableProfileAssociation Association object assigned to the given value.
     * @throws VariableProfileAssociationNotFoundException if no variable profile association object is assigned to the given value.
     */
    public function GetAssociationByValue($value)
    {
        // Get the list of associations
        $associations = $this->GetAssociations();

        // Loop through the list and find the value
        /** @var $association VariableProfileAssociation */
        foreach ($associations as $association) {
            if ($value == $association->GetValue()) {
                // Return the matching association
                return $association;
            }
        }

        // Throw an exception, no matching association was found
        throw new VariableProfileAssociationNotFoundException();
    }

    /**
     * Adds a new variable profile association to the IPS variable profile.
     * @param VariableProfileAssociation $association Association to be added to the variable profile.
     * @return $this Fluent interface.
     * @throws VariableProfileAssociationSaveFailedException if the association could not be added.
     */
    public function AddAssociation(VariableProfileAssociation $association)
    {
        // Save the association
        $association->Save($this->name);

        // Enable fluent interface
        return $this;
    }

    /**
     * Creates a new variable profile association and adds it to the IPS variable profile.
     * @param float $value Value with which the name, icon and color should be associated.
     * @param string $name Text to be used when formatting the value.
     * @param string $icon Icon to be displayed when visualizing the variable.
     * @param Color $color Color to be used when visualizing the variable.
     * @return $this Fluent interface.
     * @throws VariableProfileAssociationSaveFailedException if the association could not be added.
     * @see VariableProfileAssociation
     */
    public function AddAssociationByValue($value, $name, $icon, Color $color)
    {
        // Create a new object
        $association = new VariableProfileAssociation($value, $name, $icon, $color);

        // Add the association and enable fluent interface
        return $this->AddAssociation($association);
    }

    /**
     * Deletes the association of a variable profile association with the IPS variable profile.
     * @param VariableProfileAssociation $association Association to be deleted.
     * @return $this Fluent interface.
     * @throws VariableProfileAssociationSaveFailedException if the association could not be deleted.
     */
    public function DeleteAssociation(VariableProfileAssociation $association)
    {
        // Delete the association
        $association->Delete($this->name);

        // Enable fluent interface
        return $this;
    }

    /**
     * Deletes the association of the variable profile association bound to the given value with the IPS variable profile.
     * @param float $value Value for which the association should be deleted.
     * @return $this Fluent interface.
     * @throws VariableProfileAssociationSaveFailedException if the association could not be deleted.
     */
    public function DeleteAssociationByValue($value)
    {
        // Create a new object
        $association = new VariableProfileAssociation($value, null, null, new Color(Color::TRANSPARENT));

        // Delete the association and enable fluent interfae
        return $this->DeleteAssociation($association);
    }

    /**
     * Deletes all variable profile associations from the IPS variable profile.
     * @return $this Fluent interface.
     * @throws VariableProfileAssociationSaveFailedException if the association could not be deleted.
     */
    public function DeleteAssociations()
    {
        // Get the associations
        $associations = $this->GetAssociations();

        // Loop through the associations and delete them
        foreach ($associations as $association) {
            $this->DeleteAssociation($association);
        }

        // Enable fluent interface
        return $this;
    }

}