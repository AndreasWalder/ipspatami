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


use Patami\IPS\I18N\Translator;
use Patami\IPS\Services\Alexa\Skills\Custom\Intents\Configurator\Exceptions\InvalidDriverInterfaceNameException;
use Patami\IPS\Services\Alexa\Skills\Custom\SlotType;
use Patami\IPS\Services\Alexa\Skills\Custom\ModuleIntent;
use Patami\IPS\Services\Alexa\Skills\Custom\Intents\Configurator\Exceptions\InvalidConfiguratorAttributesException;
use Patami\IPS\Services\Alexa\Skills\Custom\Intents\Configurator\Exceptions\InvalidSlotTypeClassNameException;
use Patami\IPS\Helpers\StringHelper;
use Patami\IPS\Objects\Drivers\Drivers;
use Patami\IPS\Objects\Drivers\Driver;
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\System\Color;
use Patami\IPS\System\Locales;


/**
 * Class used to configure slot type value to IPS object mappings in a configurator intent.
 * @package IPSPATAMI
 */
class SlotTypeItems extends Items
{

    /** Base class used to check if the given slot type class name is valid. */
    const SLOT_TYPE_BASE_CLASS_NAME = '\\Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\SlotType';

    /** @var string Name of the configuration property used to store the list items. */
    protected $propertyName;

    /** @var string Label of the list. */
    protected $listCaption;

    /** @var string Label of the slot type column. */
    protected $slotTypeColumnLabel;

    /** @var string|null FQCN of the interface used for auto detecting the IPS object driver or null to use any driver. */
    protected $driverInterfaceName;

    /** @var bool True if the user is allowed to configure multiple entries with the same slot type. */
    protected $allowDuplicates;

    /**
     * @var array Cache for the slot type infos.
     * @see SlotType::GetInfoMap()
     */
    protected $infos;

    /**
     * Remembers the attributes of the intent configurator items.
     * @param array $attributes Attributes of the intent configurator items.
     * @throws InvalidConfiguratorAttributesException if an attribute is invalid.
     * @throws InvalidDriverInterfaceNameException if the driver interface could not be found.
     * @throws InvalidSlotTypeClassNameException if the slot type class could not be found.
     */
    protected function SetAttributes(array $attributes)
    {
        // Get the property name
        $propertyName = @$attributes['propertyName'];
        $this->Debug('Property Name', $propertyName);

        // Throw an error if the property name is not set
        if (! $propertyName) {
            throw new InvalidConfiguratorAttributesException();
        }

        // Remember the property name
        $this->propertyName = $propertyName;

        // Get the list caption
        $listCaption = @$attributes['listCaption'];
        $this->Debug('List Caption', $listCaption);

        // Throw an error if the list caption is not set
        if (! $listCaption) {
            throw new InvalidConfiguratorAttributesException();
        }

        // Remember the list caption
        $this->listCaption = $listCaption;

        // Get the slot type column label
        $slotTypeColumnLabel = @$attributes['slotTypeColumnLabel'];
        $this->Debug('Slot Type Column Label', $slotTypeColumnLabel);

        // Throw an error if the slot type column label is not set
        if (! $slotTypeColumnLabel) {
            throw new InvalidConfiguratorAttributesException();
        }

        // Remember the slot type column label
        $this->slotTypeColumnLabel = $slotTypeColumnLabel;

        // Get the class name
        $slotTypeClassName = @$attributes['slotTypeClassName'];
        $this->Debug('Slot Type Class Name', $slotTypeClassName);

        // Throw an error if the slot type class is not set
        if (! $slotTypeClassName) {
            throw new InvalidConfiguratorAttributesException();
        }

        // Throw an error if the slot type class cannot be found
        if (! class_exists($slotTypeClassName)) {
            throw new InvalidSlotTypeClassNameException();
        }

        // Throw an error if the slot type class is of the wrong type
        if (! is_a($slotTypeClassName, self::SLOT_TYPE_BASE_CLASS_NAME, true)) {
            throw new InvalidSlotTypeClassNameException();
        }

        // Get the driver interface name
        $driverInterfaceName = @$attributes['driverInterfaceName'];
        $this->Debug('Driver Interface Name', $driverInterfaceName);

        // Throw an error if the driver interface is invalid
        if (! is_null($driverInterfaceName)) {
            if (! interface_exists($driverInterfaceName)) {
                throw new InvalidDriverInterfaceNameException();
            }
        }

        // Remember the driver interface name
        $this->driverInterfaceName = $driverInterfaceName;

        // Determine if duplicate entries are allowed
        $allowDuplicates = @$attributes['allowDuplicates'];
        $allowDuplicates = ($allowDuplicates === true);
        $this->Debug('Allow Duplicates', StringHelper::GetBooleanAsYesNo($allowDuplicates, Locales::EN_US));

        // Remember if duplicate values are allowed
        $this->allowDuplicates = $allowDuplicates;

        // Remember the slot type infos
        /** @var SlotType $slotTypeClassName */
        $this->infos = $slotTypeClassName::GetInfoMap();

        // Remember the attributes
        parent::SetAttributes($attributes);
    }

    public function GetConfigurationProperties()
    {
        $properties = array(
            array(
                'type' => ModuleIntent::PROPERTY_STRING,
                'name' => $this->propertyName,
                'default' => null
            )
        );

        return $properties;
    }

    public function GetConfigurationFormData()
    {
        // Get the system locale
        $locale = Locales::GetIPSLocale();

        // Generate the dropdown values
        $options = array();
        foreach ($this->infos as $key => $info) {
            // Get the caption labels
            $text = @$info['name'][$locale];
            // Add the entry
            $options[$text] = array(
                'label' => $text,
                'value' => $text
            );
        }
        ksort($options);
        $options = array_values($options);

        // Generate the data structure
        $data['elements'] = array(
            array(
                'type' => 'List',
                'name' => $this->propertyName,
                'caption' => $this->listCaption,
                'rowCount' => 10,
                'add' => true,
                'delete' => true,
                'sort' => array(
                    'column' => 'slotType',
                    'direction' => 'ascending'
                ),
                'columns' => array(
                    array(
                        'label' => $this->slotTypeColumnLabel,
                        'name' => 'slotType',
                        'width' => '150px',
                        'add' => '',
                        'edit' => array(
                            'type' => 'Select',
                            'options' => $options
                        )
                    ),
                    array(
                        'label' => Translator::Get('patami.framework.services.alexa.custom.intents.configurator.slottypeitems.form.list.object_column'),
                        'name' => 'objectId',
                        'width' => '75px',
                        'add' => 0,
                        'edit' => array(
                            'type' => 'SelectObject'
                        )
                    ),
                    array(
                        'label' => Translator::Get('patami.framework.services.alexa.custom.intents.configurator.slottypeitems.form.list.status_column'),
                        'name' => 'status',
                        'width' => '300px',
                        'add' => Translator::Get('patami.framework.services.alexa.custom.intents.configurator.slottypeitems.form.list.new_entry')
                    )
                ),
                'values' => $this->GetValues()
            )
        );

        // Return the form data
        return $data;
    }

    /**
     * Returns the configured slot type to IPS object mappings.
     * @return array Slot type to IPS object mappings.
     */
    public function GetMappings()
    {
        // Get the configured mappings
        $text = $this->ReadPropertyString($this->propertyName);

        // Decode the mappings
        $mappings = json_decode($text, true);

        // Assume empty array if the decode failed
        if (is_null($mappings)) {
            $mappings = array();
        }

        // Return the mappings
        return $mappings;
    }

    /**
     * Returns the values to be display in the IPS list.
     * @return array List values.
     */
    protected function GetValues()
    {
        // Get the mappings
        $mappings = $this->GetMappings();

        // Get the duplicate slot types
        if ($this->allowDuplicates) {
            $duplicateSlotTypes = array();
        } else {
            $duplicateSlotTypes = $this->GetDuplicateSlotTypes();
        }
        foreach ($duplicateSlotTypes as $duplicateSlotType) {
            $this->Debug(null, sprintf('Duplicate Slot Type "%s"', $duplicateSlotType));
        }

        // Loop through all mappings and generate the values array
        $colorOK = Color::Create(Color::IPS_OK);
        $colorError = Color::Create(Color::IPS_ERROR);
        $values = array();
        foreach ($mappings as $mapping) {
            // Get the values
            $slotType = @$mapping['slotType'];
            $objectId = @$mapping['objectId'];
            $rowColor = $colorOK;
            // Check if the slot type is set
            if ($slotType == '') {
                $status = Translator::Get('patami.framework.services.alexa.custom.intents.configurator.slottypeitems.form.list.slot_type_not_set');
                $rowColor = $colorError;
            } else {
                // Check if the entry is duplicate
                if (in_array($slotType, $duplicateSlotTypes)) {
                    $status = Translator::Get('patami.framework.services.alexa.custom.intents.configurator.slottypeitems.form.list.duplicate_slot_type');
                    $rowColor = $colorError;
                } else {
                    // Check if a driver can be found
                    if ($objectId == 0) {
                        $status = Translator::Get('patami.framework.services.alexa.custom.intents.configurator.slottypeitems.form.list.object_not_set');
                        $rowColor = $colorError;
                    } else {
                        try {
                            /** @var Driver $driver */
                            $driver = Drivers::AutoDetect($objectId, $this->driverInterfaceName);
                            $status = Translator::Format(
                                'patami.framework.services.alexa.custom.intents.configurator.slottypeitems.form.list.driver_name',
                                $driver->GetName()
                            );
                        } catch (InvalidObjectException $e) {
                            $status = Translator::Get('patami.framework.services.alexa.custom.intents.configurator.slottypeitems.form.list.driver_not_found');
                            $rowColor = $colorError;
                        }
                    }
                }
            }
            // Add an entry to the array
            $values[] = array(
                'rowColor' => $rowColor->GetHex(),
                'slotType' => $slotType,
                'objectId' => $objectId,
                'status' => $status
            );
        }

        // Return the values
        return $values;
    }

    /**
     * Returns a list of slot type keys that are configured more than once.
     * @return array Duplicate slot type keys.
     */
    public function GetDuplicateSlotTypes()
    {
        // Get the mappings
        $mappings = $this->GetMappings();

        // Loop through the mappings and count the slot type ocurrences
        $slotTypeCounts = array();
        foreach ($mappings as $mapping) {
            // Get the slot type
            $slotType = @$mapping['slotType'];
            // Skip the entry if the slot type is not set
            if (is_null($slotType)) {
                continue;
            }
            // Increase the count
            if (! isset($slotTypeCounts[$slotType])) {
                $slotTypeCounts[$slotType] = 1;
            } else {
                $slotTypeCounts[$slotType]++;
            }
        }

        // Loop through the counts and find out which ones are duplicate
        $duplicateSlotTypes = array();
        foreach ($slotTypeCounts as $slotType => $count) {
            // Skip the entry if the count is 1
            if ($count == 1) {
                continue;
            }
            // Add the slot type to the list of duplicate slot types
            $duplicateSlotTypes[] = $slotType;
        }

        // Return the list of duplicate slot types
        return $duplicateSlotTypes;
    }

}