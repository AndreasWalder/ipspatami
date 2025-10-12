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


use Patami\IPS\System\Locales;
use Patami\IPS\System\Logging\DebugInterface;


/**
 * Abstract base class for multiple intent configurator items.
 * @package IPSPATAMI
 */
abstract class Items implements DebugInterface
{

    /** @var ConfiguratorIntent Configurator intent object. */
    protected $intent;

    /** @var int Index (position) of the items on the configuration page. */
    protected $index;

    /** @var array Attributes of the intent configurator items. */
    protected $attributes;

    /**
     * Items constructor.
     * @param ConfiguratorIntent $intent Configurator intent object which used the items.
     * @param int $index Index (position) of the items on the configuration page.
     * @param array $attributes Attributes of the intent configurator items.
     */
    public function __construct(ConfiguratorIntent $intent, $index, array $attributes)
    {
        // Remember the intent
        $this->intent = $intent;

        // Remember the index
        $this->index = $index;

        // Set the attributes
        $this->SetAttributes($attributes);
    }

    /**
     * Remembers the attributes of the intent configurator items.
     * @param array $attributes Attributes of the intent configurator items.
     */
    protected function SetAttributes(array $attributes)
    {
        // Remember the attributes
        $this->attributes = $attributes;
    }

    /**
     * Returns the configuration properties required by the configurator items.
     * @return array Configuration properties.
     */
    abstract public function GetConfigurationProperties();

    /**
     * Returns the configuration form required by the configurator items.
     * @return array Configuration form.
     */
    abstract public function GetConfigurationFormData();

    /**
     * Returns the value of a configuration property.
     * It reads the property from the I/O's configuration.
     * @param string $name Configuration property name.
     * @return mixed Value of the configuration property.
     */
    protected function ReadProperty($name)
    {
        return $this->intent->ReadProperty($name);
    }

    /**
     * Returns the value of a boolean configuration property.
     * It reads the property from the I/O's configuration.
     * @param string $name Configuration property name.
     * @return bool Value of the configuration property.
     * @see Items::ReadProperty()
     */
    protected function ReadPropertyBoolean($name)
    {
        return $this->intent->ReadPropertyBoolean($name);
    }

    /**
     * Returns the value of an integer configuration property.
     * It reads the property from the I/O's configuration.
     * @param string $name Configuration property name.
     * @return int Value of the configuration property.
     * @see Items::ReadProperty()
     */
    protected function ReadPropertyInteger($name)
    {
        return $this->intent->ReadPropertyInteger($name);
    }

    /**
     * Returns the value of a float configuration property.
     * It reads the property from the I/O's configuration.
     * @param string $name Configuration property name.
     * @return float Value of the configuration property.
     * @see Items::ReadProperty()
     */
    protected function ReadPropertyFloat($name)
    {
        return $this->intent->ReadPropertyFloat($name);
    }

    /**
     * Returns the value of a string configuration property.
     * It reads the property from the I/O's configuration.
     * @param string $name Configuration property name.
     * @return string Value of the configuration property.
     * @see Items::ReadProperty()
     */
    protected function ReadPropertyString($name)
    {
        return $this->intent->ReadPropertyString($name);
    }

    public function Debug($tag, $message, array $data = null)
    {
        // Use default tag if none set
        if (is_null($tag)) {
            $tag = sprintf('Configurator Item %d', $this->index);
        }

        // Forward debugging to the intent
        $this->intent->Debug($tag, $message, $data);

        // Enable fluent interface
        return $this;
    }

}