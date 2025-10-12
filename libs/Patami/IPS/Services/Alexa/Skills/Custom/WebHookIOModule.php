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

abstract class WebHookIOModule extends BaseWebHookIOModule implements IOInterface, IntentContainerInterface, LocaleInterface
{
    use IOModuleTrait;

    const STATUS_ERROR_APPLICATION_ID_INVALID = 301;
    const STATUS_ERROR_USER_ID_INVALID = 302;
    const STATUS_ERROR_LAUNCH_REQUEST_INTENT_INVALID = 303;
    const STATUS_ERROR_LAUNCH_REQUEST_INTENT_WRONG_CONNECTION = 304;

    public function Create()
    {
        $this->CustomSkillCreate();
        $this->RegisterPropertyString('ApplicationID', '');
        $this->RegisterPropertyString('UserID', '');
        $this->RegisterPropertyInteger('LaunchIntentID', 0);
    }

    protected function GetConfigurationFormData()
    {
        $data = $this->GetCustomSkillConfigurationFormData();

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

        return $data;
    }

    protected function IsLaunchIntentIdPropertyVisible()
    {
        return true;
    }

    protected function GetWebHookLabel()
    {
        return Translator::Get('patami.framework.services.alexa.custom.webhookiomodule.form.webhook_path.label');
    }

    protected function GetDefaultWebHookSubPath()
    {
        return 'alexa/custom';
    }

    protected function Configure()
    {
        $id = $this->GetAllowedApplicationId();
        if (!preg_match('/^amzn1\.ask\.skill\.[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/', $id)) {
            $this->Debug('Application ID Validation', 'ID is invalid');
            $this->SetStatus(self::STATUS_ERROR_APPLICATION_ID_INVALID);
            return;
        }
        $this->Debug('Application ID Validation', 'ID is valid');

        $id = (string)$this->GetAllowedUserId();
        if ($id !== '') {
            if (strpos($id, 'amzn1.ask.account.') !== 0) {
                $this->Debug('User ID Validation', 'ID is invalid');
                $this->SetStatus(self::STATUS_ERROR_USER_ID_INVALID);
                return;
            }
            $this->Debug('User ID Validation', 'ID is valid');
        } else {
            $this->Debug('User ID Validation', 'ID is empty (allow all users)');
        }

        $id = $this->GetLaunchIntentId();
        if ($id == 0) {
            $this->Debug('Launch Request Intent ID Validation', 'No intent set');
        } else {
            $info = IPS::GetInstance($id);
            $moduleId = $info['ModuleInfo']['ModuleID'];
            if ($moduleId != '{F14921A2-405D-4576-A389-95FB9A5CD730}') {
                $this->Debug('Launch Request Intent ID Validation', sprintf('Wrong instance type (%s)', $moduleId));
                $this->SetStatus(self::STATUS_ERROR_LAUNCH_REQUEST_INTENT_INVALID);
                return;
            }
            $parentId = $info['ConnectionID'];
            if ($parentId !== $this->InstanceID) {
                $this->Debug('Launch Request Intent ID Validation', 'Intent instance is not connected to this WebHook');
                $this->SetStatus(self::STATUS_ERROR_LAUNCH_REQUEST_INTENT_WRONG_CONNECTION);
                return;
            }
            $this->Debug('Launch Request Intent ID Validation', 'ID is valid');
        }

        parent::Configure();
    }

    protected function GetRequestClassName()
    {
        return 'Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\WebHookRequest';
    }

    public function GetAllowedApplicationId()
    {
        return @$this->ReadPropertyString('ApplicationID');
    }

    public function GetAllowedUserId()
    {
        return @$this->ReadPropertyString('UserID');
    }

    public function GetLaunchIntentId()
    {
        return @$this->ReadPropertyInteger('LaunchIntentID');
    }
}
