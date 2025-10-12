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


namespace Patami\IPS\Services\Alexa\Skills\Custom\Intents\Configurator;


use Patami\IPS\Services\Alexa\Skills\Custom\ModuleIntent;
use Patami\IPS\Services\Alexa\Skills\Custom\Intents\Configurator\Exceptions\InvalidConfiguratorItemException;
use Patami\IPS\Services\Alexa\Skills\Custom\Intents\Configurator\Exceptions\InvalidConfiguratorItemsException;
use Patami\IPS\Services\Alexa\Skills\Custom\Intents\Configurator\Exceptions\InvalidItemClassNameException;


/**
 * Abstract base class for an intent that displays a custom configurator on the I/O module's configuration page.
 * @package IPSPATAMI
 */
abstract class ConfiguratorIntent extends ModuleIntent
{

    /** FQCN of the intent configurator item base class. */
    const ITEM_BASE_CLASS_NAME = '\\Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\Intents\\Configurator\\Items';

    protected function GetType()
    {
        return 'ConfiguratorIntent';
    }

    /**
     * Returns the configuration properties of the configurator intent items.
     * The configurator intent configuration properties are dynamically composed using the configuration properties
     * specified by the configurator intent items.
     * The user can configure the items defined by the configurator.
     * @return array Configuration properties.
     */
    public function GetConfigurationProperties()
    {
        // Get the list of properties from the parent method
        $properties = parent::GetConfigurationProperties();

        // Get the configuration items
        $items = $this->GetConfigurationItemObjects();

        // Loop through all items
        foreach ($items as $item) {
            /** @var Items $item */
            $properties = array_merge($properties, $item->GetConfigurationProperties());
        }

        // Return the property list
        return $properties;
    }

    /**
     * Returns the configuration form of the configurator intent.
     * The configurator intent configuration items are dynamically composed using the configuration items specified by
     * the configurator intent items.
     * The user can configure the items defined by the configurator.
     * @return array Configuration form.
     */
    public function GetConfigurationFormData()
    {
        // Get the parent form data
        $data = parent::GetConfigurationFormData();

        // Get the configuration items
        $items = $this->GetConfigurationItemObjects();

        // Loop through all items
        foreach ($items as $item) {
            /** @var Items $item */
            $itemData = $item->GetConfigurationFormData();
            // Merge the items
            $elements = @$itemData['elements'];
            if ($elements) {
                foreach ($elements as $element) {
                    $data['elements'][] = $element;
                }
            }
            $translations = @$itemData['translations']['de'];
            if ($translations) {
                foreach ($translations as $textEN => $textDE) {
                    $data['translations']['de'][$textEN] = $textDE;
                }
            }
        }

        // Return the form data
        return $data;
    }

    /**
     * Returns a list of configuration items.
     * This method needs to be overridden by the concrete child class implementation.
     * @return array Intent configurator items.
     */
    abstract protected function GetConfigurationItems();

    /**
     * Returns a list of configurator item objects.
     * @return array Intent configurator item objects.
     * @throws InvalidConfiguratorItemsException if the configurator items are invalid.
     * @throws InvalidConfiguratorItemException if a configurator item is invalid.
     * @throws InvalidItemClassNameException if the configurator item class cannot be found.
     */
    protected function GetConfigurationItemObjects()
    {
        // Get the configuration items
        $items = $this->GetConfigurationItems();

        // Throw an error if the item list if not an array
        if (! is_array($items)) {
            throw new InvalidConfiguratorItemsException();
        }

        // Loop through all items
        $objects = array();
        foreach ($items as $index => $item) {
            // Throw an error if the item is not an array
            if (! is_array($item)) {
                throw new InvalidConfiguratorItemException();
            }
            // Get the item class name
            $className = @$item['className'];
            // Throw an error if the class name is not set
            if (! $className) {
                throw new InvalidItemClassNameException();
            }
            // Throw an error if the class cannot be found
            if (! class_exists($className)) {
                throw new InvalidItemClassNameException();
            }
            // Throw an error if the item class is of the wrong type
            if (! is_a($className, self::ITEM_BASE_CLASS_NAME, true)) {
                throw new InvalidItemClassNameException();
            }
            // Create the object
            $this->Debug(sprintf('Configurator Item %d', $index), $className);
            $objects[] = new $className($this, $index, $item);
        }

        // Return the objects
        return $objects;
    }

}