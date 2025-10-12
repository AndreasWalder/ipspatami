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


namespace Patami\IPS\Services\Alexa\Skills;


use Patami\IPS\Modules\Module;
use Patami\IPS\System\Locales;


/**
 * Provides common methods for Amazon Alexa Smart Home and Custom Skill I/O modules.
 * Eliminates the need for a common base class, since both WebHook and WebOAuth I/Os are being used.
 * @package IPSPATAMI
 */
trait IOModuleTrait
{

    /**
     * Registers configuration properties for Alexa Skill I/O modules.
     * Called by IOModuleTrait::CustomSkillCreate().
     * Calls the regular Create() method of the WebHook or WebOAuth parent class.
     * @see Module
     * @see \Patami\IPS\Services\Alexa\Skills\Custom\IOModuleTrait
     */
    protected function SkillCreate()
    {
        // Call the regular method
        /** @noinspection PhpUndefinedClassInspection */
        parent::Create();

        // Register the property for the default locale
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger('DefaultLocale', Locales::GetDefaultSelectOption());
    }

    /**
     * Adds the default locale configuration field to Alexa Skill I/O modules.
     * Called by IOModuleTrait::GetCustomSkillConfigurationFormData().
     * Calls the regular GetConfigurationFormData() method of the WebHook or WebOAuth parent class.
     * @return array IPS module configuration form data.
     * @see Module
     */
    protected function GetSkillConfigurationFormData()
    {
        // Call the regular method
        /** @noinspection PhpUndefinedClassInspection */
        $data = parent::GetConfigurationFormData();

        // Add the default locale dropdown configuration field
        array_unshift($data['elements'],
            array(
                'type' => 'Label',
                'label' => $this->GetLocaleLabel()
            ),
            array(
                'type' => 'Select',
                'name' => 'DefaultLocale',
                'caption' => '',
                'options' => Locales::GetSelectOptions()
            )
        );

        return $data;
    }

    /**
     * Returns the configured default locale.
     * @return string Locale code of the default locale.
     * @see Locales
     */
    public function GetLocale()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return Locales::GetCodeByIndex($this->ReadPropertyInteger('DefaultLocale'));
    }

    /**
     * Returns the label of the default locale configuration field.
     * This needs to be overridden by the concrete child class to customize the label.
     * @return string Translated label of the configuration field.
     */
    abstract protected function GetLocaleLabel();

}