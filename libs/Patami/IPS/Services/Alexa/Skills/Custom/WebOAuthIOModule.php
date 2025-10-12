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


use Patami\IPS\IO\WebOAuthIOModule as BaseWebOAuthIOModule;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidIntentConfigurationPropertyException;
use Patami\IPS\Services\Alexa\Skills\LocaleInterface;


/**
 * Abstract base class used to implement IPS WebOAuth modules that communicate with Amazon servers for (to be) certified Alexa Custom Skills.
 *
 * If you want to implement an IPS module for an uncertified skill, use the WebHookModule base class instead.
 *
 * @see WebHookIOModule
 *
 * @package IPSPATAMI
 */
abstract class WebOAuthIOModule extends BaseWebOAuthIOModule implements IOInterface, IntentContainerInterface, LocaleInterface
{

    // Include the common code for Custom skills
    use IOModuleTrait;

    /**
     * Registers configuration properties for the instance's built-in intent classes.
     * @throws InvalidIntentConfigurationPropertyException if the type of an intent configuration property is invalid.
     * @see IOModuleTrait::CustomSkillCreate()
     */
    public function Create()
    {
        // Register the configuration properties requested by the built-in intent classes and the base WebOAuthIOModule class
        $this->CustomSkillCreate();
    }

    /**
     * Returns the IPS module configuration form data.
     * {@inheritDoc}
     * @see IOModuleTrait::GetCustomSkillConfigurationFormData()
     */
    protected function GetConfigurationFormData()
    {
        // Get the configuration form specified by the build-in intent classes and the base WebOAuthIOModule class
        return $this->GetCustomSkillConfigurationFormData();
    }

    /**
     * Returns the FQCN of the WebOAuthRequest class.
     * The WebOAuth I/O requires a specialized Request class.
     * You normally don't need to override this method or the WebOAuthRequest class.
     * @return string FQCN of the WebOAuthRequest class.
     * @see WebOAuthRequest
     */
    protected function GetRequestClassName()
    {
        return 'Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\WebOAuthRequest';
    }

}