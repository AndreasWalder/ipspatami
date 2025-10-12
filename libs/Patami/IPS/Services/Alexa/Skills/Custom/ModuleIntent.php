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


namespace Patami\IPS\Services\Alexa\Skills\Custom;


use Patami\IPS\I18N\Translator;
use Patami\IPS\Objects\Variable;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\IntentNotFoundException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\IntentDoExecuteMethodNotOverriddenException;


/**
 * Base class for intents built-in into custom IPS modules.
 *
 * Module intents can contain configuration properties and own configuration forms that are embedded into the
 * configuration form of the WebHook or WebOAuth I/O module that uses the module intent.
 *
 * @package IPSPATAMI
 */
class ModuleIntent extends Intent
{

    /** Boolean configuration property. */
    const PROPERTY_BOOLEAN = Variable::VARIABLE_TYPE_BOOLEAN;

    /** Interger configuration property. */
    const PROPERTY_INTEGER = Variable::VARIABLE_TYPE_INTEGER;

    /** Float configuration property. */
    const PROPERTY_FLOAT = Variable::VARIABLE_TYPE_FLOAT;

    /** String configuration property. */
    const PROPERTY_STRING = Variable::VARIABLE_TYPE_STRING;

    /**
     * Static factory method that creates a new module intent object by using the class that provides an intent with the specified name.
     * @param IntentContainerInterface $container WebHook or WebOAuth I/O module object which uses the intent.
     * @param string $name Intent name.
     * @return ModuleIntent|Intent Module intent object that provides an intent implementation for the specified intent name.
     * @throws IntentNotFoundException if no module intent class implementing the intent name could be found.
     */
    public static function CreateByName(IntentContainerInterface $container, $name)
    {
        // Get the list of associated intent class names for the container
        $classNames = $container->GetIntentClassNames();

        // Loop through the intents
        foreach ($classNames as $className) {
            // Create the intent object
            /** @var Intent $className */
            $intent = $className::Create($container);
            // Return the object if the name matches
            /** @var Intent $intent */
            if ($name == $intent->GetName()) {
                return $intent;
            }
        }

        // Throw an error if no matching class was found
        throw new IntentNotFoundException();
    }

    /**
     * Returns the label of the configuration section for the intent on the I/Os configuration page.
     * @return string Configuration section label.
     */
    public function GetLabel()
    {
        // Return the intent configuration form label
        return sprintf('%s Intent', $this->GetName());
    }

    /**
     * Returns the configuration properties of the intent.
     * This can be extended by child classes and is be used to register the configuration properties of the intent in
     * the context of the I/O module.
     * @return array Configuration properties.
     */
    public function GetConfigurationProperties()
    {
        // Return the intent enabled property
        return array(
            array(
                'type' => self::PROPERTY_BOOLEAN,
                'name' => 'Enabled',
                'default' => $this->GetDefaultEnabledProperty()
            )
        );
    }

    /**
     * Returns the default value of the enabled property.
     * @return bool Default value of the property.
     */
    protected function GetDefaultEnabledProperty()
    {
        return true;
    }

    /**
     * Returns the configuration form of the intent.
     * This can be extended by child classes and is used to compose the configuration form of the I/O module.
     * @return array Configuration form.
     */
    public function GetConfigurationFormData()
    {
        // Initialize the data structure
        $data = array(
            'elements' => array()
        );

        // Add the intent enabled checkbox is it is visible
        if ($this->IsEnabledPropertyVisible()) {
            array_push($data['elements'],
                array(
                    'type' => 'CheckBox',
                    'name' => 'Enabled',
                    'caption' => Translator::Get('patami.framework.services.alexa.custom.moduleintent.form.enabled.label')
                )
            );
        }

        // Return the form data
        return $data;
    }

    /**
     * Checks if the enabled configuration field is visible.
     * This is used to control if the user can enable or disable the intent on the configuration page of the I/O module.
     * @return bool True if the property is visible.
     */
    public function IsEnabledPropertyVisible()
    {
        return false;
    }

    /**
     * Returns the value of a configuration property.
     * It reads the property from the I/O's configuration.
     * @param string $name Configuration property name.
     * @return mixed Value of the configuration property.
     */
    public function ReadProperty($name)
    {
        return $this->container->ReadIntentProperty($this, $name);
    }

    /**
     * Returns the value of a boolean configuration property.
     * It reads the property from the I/O's configuration.
     * @param string $name Configuration property name.
     * @return bool Value of the configuration property.
     * @see ModuleIntent::ReadProperty()
     */
    public function ReadPropertyBoolean($name)
    {
        return $this->ReadProperty($name);
    }

    /**
     * Returns the value of an integer configuration property.
     * It reads the property from the I/O's configuration.
     * @param string $name Configuration property name.
     * @return int Value of the configuration property.
     * @see ModuleIntent::ReadProperty()
     */
    public function ReadPropertyInteger($name)
    {
        return intval($this->ReadProperty($name));
    }

    /**
     * Returns the value of a float configuration property.
     * It reads the property from the I/O's configuration.
     * @param string $name Configuration property name.
     * @return float Value of the configuration property.
     * @see ModuleIntent::ReadProperty()
     */
    public function ReadPropertyFloat($name)
    {
        return floatval($this->ReadProperty($name));
    }

    /**
     * Returns the value of a string configuration property.
     * It reads the property from the I/O's configuration.
     * @param string $name Configuration property name.
     * @return string Value of the configuration property.
     * @see ModuleIntent::ReadProperty()
     */
    public function ReadPropertyString($name)
    {
        return (String)$this->ReadProperty($name);
    }

    public function IsEnabled()
    {
        return $this->ReadPropertyBoolean('Enabled');
    }

    protected function GetType()
    {
        return 'ModuleIntent';
    }

    /**
     * Processes the Alexa Custom Skill Intent request.
     * This method is automatically called by the Execute() method.
     * It needs to be overridden by the concrete child class implementation.
     * @param Request $request Request object of the incoming request.
     * @return Response Response object to be sent back to the Amazon servers.
     * @throws IntentDoExecuteMethodNotOverriddenException if a child class does not override this method.
     */
    protected function DoExecute(Request $request)
    {
        // Throw an exception when the method has not been overridden (which is required)
        throw new IntentDoExecuteMethodNotOverriddenException();
    }

}