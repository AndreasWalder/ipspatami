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
use Patami\IPS\IO\WebHookIOModule as BaseWebHookIOModule;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidIntentConfigurationPropertyException;
use Patami\IPS\Services\Alexa\Skills\LocaleInterface;
use Patami\IPS\System\IPS;


/**
 * Abstract base class used to implement IPS WebHook modules that communicate with Amazon servers for uncertified Alexa Custom Skills.
 *
 * If you want to implement an IPS module for a certified skill, use the WebOAuthModule base class instead.
 *
 * @see WebOAuthIOModule
 *
 * @package IPSPATAMI
 */
abstract class WebHookIOModule extends BaseWebHookIOModule implements IOInterface, IntentContainerInterface, LocaleInterface
{

    // Include the common code for Custom skills
    use IOModuleTrait;

    /** IPS status code used to indicate that the format of the Application ID is invalid. */
    const STATUS_ERROR_APPLICATION_ID_INVALID = 301;

    /** IPS status code used to indicate that the format of the User ID is invalid. */
    const STATUS_ERROR_USER_ID_INVALID = 302;

    /** IPS status code used to indicate that the configured LaunchIntent intent instance is incorrect (wrong module GUID). */
    const STATUS_ERROR_LAUNCH_REQUEST_INTENT_INVALID = 303;

    /** IPS status code used to indicate that the configured LaunchIntent intent instance is bound to another WebHook instance. */
    const STATUS_ERROR_LAUNCH_REQUEST_INTENT_WRONG_CONNECTION = 304;

    /**
     * Registers configuration properties for the instance and its built-in intent classes.
     * @throws InvalidIntentConfigurationPropertyException if the type of an intent configuration property is invalid.
     * @see IOModuleTrait::CustomSkillCreate()
     */
    public function Create()
    {
        // Register the configuration properties requested by the built-in intent classes and the base WebHookIOModule class
        $this->CustomSkillCreate();

        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString('ApplicationID', '');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString('UserID', '');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger('LaunchIntentID', 0);
    }

    /**
     * Returns the IPS module configuration form data.
     * {@inheritDoc}
     * The Alexa WebHookIOModule implementation adds status codes, translations and configuration form fields to configure
     * the Application ID, the user ID and the LaunchRequest intent instance.
     * @see IOModuleTrait::GetCustomSkillConfigurationFormData()
     */
    protected function GetConfigurationFormData()
    {
        // Get the configuration form specified by the build-in intent classes and the base WebHookIOModule class
        $data = $this->GetCustomSkillConfigurationFormData();

        // Add the configuration field to select the LaunchRequest intent instance if it should be displayed
        if ($this->IsLaunchIntentIdPropertyVisible()) {
            array_unshift($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => Translator::Get('patami.framework.services.alexa.custom.webhookiomodule.form.launch_intent.label')
                ),
                array(
                    'type' => 'SelectInstance',
                    'name' => 'LaunchIntentID',
                    'caption' => ''
                )
            );
        }

        // Add the configuration fields to specify the Application ID and the user ID
        array_unshift($data['elements'],
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.framework.services.alexa.custom.webhookiomodule.form.application_id.label')
            ),
            array(
                'type' => 'ValidationTextBox',
                'name' => 'ApplicationID',
                'caption' => ''
            ),
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.framework.services.alexa.custom.webhookiomodule.form.user_id.label')
            ),
            array(
                'type' => 'ValidationTextBox',
                'name' => 'UserID',
                'caption' => ''
            )
        );

        // Add the status codes used to indicate errors
        array_unshift($data['status'],
            array(
                'code' => self::STATUS_ERROR_APPLICATION_ID_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.framework.services.alexa.custom.webhookiomodule.form.status.application_id_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_USER_ID_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.framework.services.alexa.custom.webhookiomodule.form.status.user_id_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_LAUNCH_REQUEST_INTENT_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.framework.services.alexa.custom.webhookiomodule.form.status.launch_request_intent_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_LAUNCH_REQUEST_INTENT_WRONG_CONNECTION,
                'icon' => 'error',
                'caption' => Translator::Get('patami.framework.services.alexa.custom.webhookiomodule.form.status.launch_request_intent_wrong_connection')
            )
        );

        // Return the configuration form
        return $data;
    }

    /**
     * Checks if the LaunchIntent select field should be displayed on the configuration form.
     * Concrete child classes can override this method to hide the form field. In that case, the launch intent class
     * name should be specified using WebOAuthIOModule::GetLaunchIntentClassName(). If you don't do that, Alexa will
     * read an error message if a LaunchIntent request comes in.
     * @return bool True if the LaunchIntent select field should be displayed on the configuration form.
     * @see WebOAuthIOModule::GetLaunchIntentClassName()
     */
    protected function IsLaunchIntentIdPropertyVisible()
    {
        return true;
    }

    /**
     * Returns the translated label of the WebHook URL sub path field.
     * @return string|null Translated label of the WebHook URL sub path field.
     */
    protected function GetWebHookLabel()
    {
        return Translator::Get('patami.framework.services.alexa.custom.webhookiomodule.form.webhook_path.label');
    }

    /**
     * Returns the default WebHook sub path to be used when none has been configured yet.
     * It is recommended that you override this method in your concrete child class to make sure every module uses
     * an unique default sub path to avoid conflicts.
     * @return string Default WebHook sub path.
     */
    protected function GetDefaultWebHookSubPath()
    {
        return 'alexa/custom';
    }

    /**
     * Validates the Application ID, the User ID and the LaunchRequest intent instance and calls the parent method.
     * The parent method is used to validate and register the WebHook.
     */
    protected function Configure()
    {
        // Validate Application ID
        $id = $this->GetAllowedApplicationId();
        if (! preg_match('/^amzn1\.ask\.skill\.[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/', $id)) {
            $this->Debug('Application ID Validation', 'ID is invalid');
            /** @noinspection PhpUndefinedMethodInspection */
            $this->SetStatus(self::STATUS_ERROR_APPLICATION_ID_INVALID);
            return;
        }
        $this->Debug('Application ID Validation', 'ID is valid');

        // Validate User ID
        $id = (string)$this->GetAllowedUserId();
        if ($id !== '') {
            if (!preg_match('/^amzn1\.ask\.account\.[0-9A-Z]+$/', $id)) {
                $this->Debug('User ID Validation', 'ID is invalid');
                /** @noinspection PhpUndefinedMethodInspection */
                $this->SetStatus(self::STATUS_ERROR_USER_ID_INVALID);
                return;
            }
            $this->Debug('User ID Validation', 'ID is valid');
        } else {
            // empty = allow all users (no whitelist)
            $this->Debug('User ID Validation', 'ID is empty (allow all users)');
        }

        // Validate Launch Request Intent ID
        $id = $this->GetLaunchIntentId();
        if ($id == 0) {
            // No intent is set, this is OK, but will generate a voice warning if the skill is called without a command
            $this->Debug('Launch Request Intent ID Validation', 'No intent set');
        } else {
            $info = IPS::GetInstance($id);
            // Validate module is of the correct type
            $moduleId = $info['ModuleInfo']['ModuleID'];
            if ($moduleId != '{F14921A2-405D-4576-A389-95FB9A5CD730}') {
                $this->Debug('Launch Request Intent ID Validation', sprintf('Wrong instance type (%s)', $moduleId));
                /** @noinspection PhpUndefinedMethodInspection */
                $this->SetStatus(self::STATUS_ERROR_LAUNCH_REQUEST_INTENT_INVALID);
                return;
            }
            // Validate the module is connected to ourselves
            $parentId = $info['ConnectionID'];
            /** @noinspection PhpUndefinedFieldInspection */
            if ($parentId !== $this->InstanceID) {
                $this->Debug('Launch Request Intent ID Validation', 'Intent instance is not connected to this WebHook');
                /** @noinspection PhpUndefinedMethodInspection */
                $this->SetStatus(self::STATUS_ERROR_LAUNCH_REQUEST_INTENT_WRONG_CONNECTION);
                return;
            }
            $this->Debug('Launch Request Intent ID Validation', 'ID is valid');
        }

        parent::Configure();
    }

    /**
     * Returns the FQCN of the WebHookRequest class.
     * The WebHook I/O requires a specialized Request class which is used to authenticate the user using the user ID.
     * You normally don't need to override this method or the WebHookRequest class.
     * @return string FQCN of the WebHookRequest class.
     * @see WebHookRequest
     */
    protected function GetRequestClassName()
    {
        return 'Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\WebHookRequest';
    }

    /**
     * Returns the configured application ID.
     * @return string Allowed application ID.
     */
    public function GetAllowedApplicationId()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return @$this->ReadPropertyString('ApplicationID');
    }

    /**
     * Returns the configured user ID.
     * @return string Allowed user ID.
     */
    public function GetAllowedUserId()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return @$this->ReadPropertyString('UserID');
    }

    /**
     * Returns the IPS object ID of the configured LaunchRequest intent instance.
     * @return int IPS object ID of the LaunchRequest intent instance.
     */
    public function GetLaunchIntentId()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return @$this->ReadPropertyInteger('LaunchIntentID');
    }

}
