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


use Patami\IPS\Objects\Exceptions\VariableProfileAssociationSaveFailedException;
use Patami\IPS\System\Color;
use Patami\IPS\System\IPS;


/**
 * Provides an OO implementation of an IPS variable profile association.
 * @package IPSPATAMI
 */
class VariableProfileAssociation
{

    /**
     * @var float Value to which the association is bound.
     */
    protected $value;

    /**
     * @var string Text to be used instead of the value when formatting the value.
     */
    protected $name;

    /**
     * @var string Icon to be displayed when visualizing the variable.
     */
    protected $icon;

    /**
     * @var Color Color to be used when visualizing the variable.
     */
    protected $color;

    /**
     * VariableProfileAssociation constructor.
     * @param float $value Value with which the name, icon and color should be associated.
     * @param string $name Text to be used when formatting the value.
     * @param string $icon Icon to be displayed when visualizing the variable.
     * @param Color $color Color to be used when visualizing the variable.
     */
    public function __construct($value, $name, $icon, Color $color)
    {
        // Remember the values
        $this->value = $value;
        $this->name = $name;
        $this->icon = $icon;
        $this->color = $color;
    }

    /**
     * Factory method to create a new variable profile association from an array.
     * @param array $data Associative array containing the data.
     * @return VariableProfileAssociation New variable profile association.
     */
    public static function CreateFromArray(array $data)
    {
        // Extract the values
        $value = @$data['Value'];
        $name = @$data['Name'];
        $icon = @$data['Icon'];
        $color = new Color(@$data['Color']);

        // Get the class name
        /** @var $className VariableProfiles */
        $className = get_called_class();

        // Create and return a new object
        return new $className($value, $name, $icon, $color);
    }

    /**
     * Returns the value to which the variable profile association is bound.
     * @return float Value to which the association is bound.
     */
    public function GetValue()
    {
        // Return the value
        return $this->value;
    }

    /**
     * Returns the text to be used instead of the value of the variable when formatting an IPS variable object.
     * @return string Text to be used instead of the value.
     */
    public function GetName()
    {
        // Return the name
        return $this->name;
    }

    /**
     * Sets the text to be used instead of the value of the variable when formatting an IPS variable object.
     * @param string $name Text to be used instead of the value.
     * @return $this Fluent interface.
     */
    public function SetName($name)
    {
        // Remember the value
        $this->name = $name;

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the icon to be displayed when visualizing the variable.
     * @return string Icon to be displayed when visualizing the variable.
     */
    public function GetIcon()
    {
        return $this->icon;
    }

    /**
     * Sets the icon to be displayed when visualizing the variable.
     * @param string $icon Icon to be displayed when visualizing the variable.
     * @return $this Fluent interface.
     */
    public function SetIcon($icon)
    {
        // Remember the value
        $this->icon = $icon;

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the color to be used when visualizing the variable.
     * @return Color Color to be used when visualizing the variable.
     */
    public function GetColor()
    {
        return $this->color;
    }

    /**
     * Sets the color to be used when visualizing the variable.
     * @param Color $color Color to be used when visualizing the variable.
     * @return $this Fluent interface.
     */
    public function SetColor(Color $color)
    {
        // Remember the value
        $this->color = $color;

        // Enable fluent interface
        return $this;
    }

    /**
     * Saves the variable profile association in the IPS variable profile.
     * @param string $profileName Name of the variable profile.
     * @return $this Fluent interface.
     * @throws VariableProfileAssociationSaveFailedException if the association could not be saved.
     * @see IPS::SetVariableProfileAssociation()
     */
    public function Save($profileName)
    {
        // Set the association via IPS
        $result = IPS::SetVariableProfileAssociation($profileName, $this->value, $this->name, $this->icon, $this->color->Get());

        // Throw an exception if an error occurred
        if ($result === false) {
            throw new VariableProfileAssociationSaveFailedException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Deletes the variable profile association from the IPS variable profile.
     * @param string $profileName Name of the variable profile.
     * @return $this Fluent interface.
     * @throws VariableProfileAssociationSaveFailedException if the association could not be deleted.
     * @see IPS::SetVariableProfileAssociation()
     */
    public function Delete($profileName)
    {
        // Set the association via IPS
        $result = IPS::SetVariableProfileAssociation($profileName, $this->value, $this->name, null, null);

        // Throw an exception if an error occurred
        if ($result === false) {
            throw new VariableProfileAssociationSaveFailedException();
        }

        // Enable fluent interface
        return $this;
    }

}