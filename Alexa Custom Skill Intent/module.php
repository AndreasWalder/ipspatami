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


require_once(__DIR__ . "/../bootstrap.php");


use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidIntentConfigurationPropertyException;
use Patami\IPS\Modules\Module;
use Patami\IPS\System\IPS;
use Patami\IPS\Services\Alexa\Skills\Custom\IntentContainerInterface;
use Patami\IPS\Services\Alexa\Skills\Custom\ModuleIntent;
use Patami\IPS\Services\Alexa\Skills\Custom\WebHookRequest;
use Patami\IPS\Services\Alexa\Skills\Custom\Response;
use Patami\IPS\I18N\Translator;


Translator::AddDir(__DIR__ . DIRECTORY_SEPARATOR . 'translations');


/**
 * AlexaCustomSkillIntent IPS Module.
 * The GUID of this module is {F14921A2-405D-4576-A389-95FB9A5CD730}.
 * @package IPSPATAMI
 */
class AlexaCustomSkillIntent extends Module implements IntentContainerInterface
{

    /** IPS status code used to indicate that the parent WebHook I/O instance is invalid. */
    const STATUS_ERROR_WEBHOOK_INVALID = 201;

    /** IPS status code used to indicate that the intent name is invalid. */
    const STATUS_ERROR_INTENT_NAME_INVALID = 202;

    /**
     * IPS status code used to indicate that the same intent name is configured in another intent instance bound to the
     * same WebHook I/O instance.
     */
    const STATUS_ERROR_DUPLICATE_INTENT_NAME = 203;

    /** IPS module GUID of the WebHook I/O instance. */
    const PARENT_MODULE_ID = '{F416D335-7D66-4B91-97AA-02C7F4E3D343}';

    /** Return speech output when the intent is executed. */
    const INTENT_TYPE_SPEECH_OUTPUT = 0;

    /** Execute an IPS script when the intent is executed. */
    const INTENT_TYPE_SCRIPT = 1;

    /** Redirect to another intent when the intent is executed. */
    const INTENT_TYPE_REDIRECT = 2;

    /** Redirect to the callback intent set in the user's active session when the intent is executed. */
    const INTENT_TYPE_REDIRECT_CALLBACK = 3;

    /** Repeat the last response when the intent is executed. */
    const INTENT_TYPE_REPEAT_LAST_RESPONSE = 4;

    /** AMAZON.CancelIntent standard intent name. */
    const AMAZONCancelIntent = 'AMAZON.CancelIntent';

    /** AMAZON.HelpIntent standard intent name. */
    const AMAZONHelpIntent = 'AMAZON.HelpIntent';

    /** AMAZON.LoopOffIntent standard intent name. */
    const AMAZONLoopOffIntent = 'AMAZON.LoopOffIntent';

    /** AMAZON.LoopOnIntent standard intent name. */
    const AMAZONLoopOnIntent = 'AMAZON.LoopOnIntent';

    /** AMAZON.NextIntent standard intent name. */
    const AMAZONNextIntent = 'AMAZON.NextIntent';

    /** AMAZON.NoIntent standard intent name. */
    const AMAZONNoIntent = 'AMAZON.NoIntent';

    /** AMAZON.PauseIntent standard intent name. */
    const AMAZONPauseIntent = 'AMAZON.PauseIntent';

    /** AMAZON.PreviousIntent standard intent name. */
    const AMAZONPreviousIntent = 'AMAZON.PreviousIntent';

    /** AMAZON.RepeatIntent standard intent name. */
    const AMAZONRepeatIntent = 'AMAZON.RepeatIntent';

    /** AMAZON.ResumeIntent standard intent name. */
    const AMAZONResumeIntent = 'AMAZON.ResumeIntent';

    /** AMAZON.ShuffleOffIntent standard intent name. */
    const AMAZONShuffleOffIntent = 'AMAZON.ShuffleOffIntent';

    /** AMAZON.ShuffleOnIntent standard intent name. */
    const AMAZONShuffleOnIntent = 'AMAZON.ShuffleOnIntent';

    /** AMAZON.StartOverIntent standard intent name. */
    const AMAZONStartOverIntent = 'AMAZON.StartOverIntent';

    /** AMAZON.StopIntent standard intent name. */
    const AMAZONStopIntent = 'AMAZON.StopIntent';

    /** AMAZON.YesIntent standard intent name. */
    const AMAZONYesIntent = 'AMAZON.YesIntent';

    /**
     * Registers configuration properties and connects the intent instance to its parent WebHook I/O module.
     * The method registers properties for the intent name and the intent type. It also creates a skeleton for an action
     * script. Additionally, it registers the properties required by the different intent classes for all intent types.
     * @throws InvalidIntentConfigurationPropertyException if the property of an intent type class is invalid.
     */
    public function Create()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        parent::Create();

        // These lines are parsed on IP-Symcon startup or instance creation.
        // You cannot use variables here. Just static values.
        /** @noinspection PhpUndefinedMethodInspection */
        $this->ConnectParent(self::PARENT_MODULE_ID);

        // Register properties for the intent name
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString('StandardIntentName', '');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString('IntentName', 'MyIntentName');

        // Register property for the intent type
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger('IntentType', self::INTENT_TYPE_SCRIPT);

        // Register default action script
        $id = $this->GetDefaultScriptId();
        if ($id === false) {
            /** @noinspection PhpUndefinedMethodInspection */
            $id = $this->RegisterScript('Script', 'Action', "<?\nfunction Execute(Request \$request)\n{\n\t// Write your own code here\n\treturn TellResponse::CreatePlainText('Test');\n}\n");
            IPS::SetHidden($id, true);
        }

        // Loop through the intent types and add the properties
        $intentTypes = $this->GetIntentTypes();
        foreach ($intentTypes as $index => $intentType) {
            // Create the intent object
            /** @var ModuleIntent $className */
            $className = $intentType['className'];
            /** @var ModuleIntent $intent */
            $intent = $className::Create($this);
            // Get the properties
            $properties = $intent->GetConfigurationProperties();
            // Loop through the intent properties
            foreach ($properties as $property) {
                // Get the values
                $type = @$property['type'];
                $name = @$property['name'];
                $default = @$property['default'];
                // Check the name and throw an exception if something is wrong
                if (! $name) {
                    throw new InvalidIntentConfigurationPropertyException();
                }
                // Register the property
                $name = $this->GetTranslatedPropertyName($className, $name);
                switch ($type) {
                    case ModuleIntent::PROPERTY_BOOLEAN:
                        /** @noinspection PhpUndefinedMethodInspection */
                        $this->RegisterPropertyBoolean($name, $default);
                        break;
                    case ModuleIntent::PROPERTY_INTEGER:
                        /** @noinspection PhpUndefinedMethodInspection */
                        $this->RegisterPropertyInteger($name, $default);
                        break;
                    case ModuleIntent::PROPERTY_FLOAT:
                        /** @noinspection PhpUndefinedMethodInspection */
                        $this->RegisterPropertyFloat($name, $default);
                        break;
                    case ModuleIntent::PROPERTY_STRING:
                        /** @noinspection PhpUndefinedMethodInspection */
                        $this->RegisterPropertyString($name, $default);
                        break;
                    default:
                        // Unknown or invalid type, throw an exception
                        throw new InvalidIntentConfigurationPropertyException();
                }
            }
        }
    }

    /**
     * Returns the IPS module configuration form data.
     * It creates a dynamic configuration form which allows the user to configure the intent name and the intent type.
     * Based on the intent type, the configuration form will be dynamically populated with the form fields required by
     * the specific intent type class implementation.
     * @return array IPS module configuration form data.
     */
    protected function GetConfigurationFormData()
    {
        // Call the parent method to get the base configuration form
        $data = parent::GetConfigurationFormData();

        // Add the dropdown for the intent name
        array_push($data['elements'],
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.alexacustomskillintent.form.name.label')
            ),
            array(
                'type' => 'Select',
                'name' => 'StandardIntentName',
                'caption' => 'Standard name:',
                'options' => array(
                    array(
                        'label' => Translator::Get('patami.alexacustomskillintent.form.name.use_own_name_option'),
                        'value' => ''
                    ),
                    array(
                        'label' => self::AMAZONCancelIntent,
                        'value' => self::AMAZONCancelIntent
                    ),
                    array(
                        'label' => self::AMAZONHelpIntent,
                        'value' => self::AMAZONHelpIntent
                    ),
                    array(
                        'label' => self::AMAZONLoopOffIntent,
                        'value' => self::AMAZONLoopOffIntent
                    ),
                    array(
                        'label' => self::AMAZONLoopOnIntent,
                        'value' => self::AMAZONLoopOnIntent
                    ),
                    array(
                        'label' => self::AMAZONNextIntent,
                        'value' => self::AMAZONNextIntent
                    ),
                    array(
                        'label' => self::AMAZONNoIntent,
                        'value' => self::AMAZONNoIntent
                    ),
                    array(
                        'label' => self::AMAZONPauseIntent,
                        'value' => self::AMAZONPauseIntent
                    ),
                    array(
                        'label' => self::AMAZONPreviousIntent,
                        'value' => self::AMAZONPreviousIntent
                    ),
                    array(
                        'label' => self::AMAZONRepeatIntent,
                        'value' => self::AMAZONRepeatIntent
                    ),
                    array(
                        'label' => self::AMAZONResumeIntent,
                        'value' => self::AMAZONResumeIntent
                    ),
                    array(
                        'label' => self::AMAZONShuffleOffIntent,
                        'value' => self::AMAZONShuffleOffIntent
                    ),
                    array(
                        'label' => self::AMAZONShuffleOnIntent,
                        'value' => self::AMAZONShuffleOnIntent
                    ),
                    array(
                        'label' => self::AMAZONStartOverIntent,
                        'value' => self::AMAZONStartOverIntent
                    ),
                    array(
                        'label' => self::AMAZONStopIntent,
                        'value' => self::AMAZONStopIntent
                    ),
                    array(
                        'label' => self::AMAZONYesIntent,
                        'value' => self::AMAZONYesIntent
                    )
                )
            )
        );

        // Add the configuration field for the custom intent name (if required)
        if ($this->HasCustomIntentName()) {
            array_push($data['elements'],
                array(
                    'type' => 'ValidationTextBox',
                    'name' => 'IntentName',
                    'caption' => Translator::Get('patami.alexacustomskillintent.form.name.custom_name_label')
                )
            );
        } else {
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => Translator::Get('patami.alexacustomskillintent.form.name.notice')
                )
            );
        }

        // Add the dropdown for the intent type (action)
        // Loop through the intent types and add the drop down options
        $options = array();
        $intentTypes = $this->GetIntentTypes();
        foreach ($intentTypes as $index => $intentType) {
            $options[] = array(
                'label' => $intentType['label'],
                'value' => $index
            );
        }
        array_push($data['elements'],
            array(
                'type' => 'Label',
                'label' => ''
            ),
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.alexacustomskillintent.form.action.label')
            ),
            array(
                'type' => 'Select',
                'name' => 'IntentType',
                'caption' => '',
                'options' => $options
            ),
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.alexacustomskillintent.form.action.notice')
            )
        );

        // Add the form fields for the selected intent type
        $intentType = $this->GetIntentType();
        /** @var ModuleIntent $className */
        $className = $intentTypes[$intentType]['className'];
        /** @var ModuleIntent $intent */
        $intent = $className::Create($this);
        // Get the configuration form data
        $intentData = $intent->GetConfigurationFormData();
        // Merge the elements
        $elements = @$intentData['elements'];
        if ($elements) {
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => ''
                )
            );
            // Translate the property names
            array_walk_recursive($elements, function(&$item, $key) use ($className) {
                if ($key == 'name') {
                    $item = $this->GetTranslatedPropertyName($className, $item);
                }
            });
            // Merge in elements
            $data['elements'] = array_merge($data['elements'], $elements);
        }
        // Merge the translations
        $translations = @$intentData['translations'];
        if ($translations) {
            // Loop through the languages
            foreach ($translations as $language => $messages) {
                // Loop through the messages
                foreach ($messages as $originalMessage => $translatedMessage) {
                    // Set the translation text (possibly overwriting translations with the same original
                    // but different translated message texts)
                    $data['translations'][$language][$originalMessage] = $translatedMessage;
                }
            }
        }

        // Add the action button used to rename the intent instance to match the intent name
        array_push($data['actions'],
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.alexacustomskillintent.form.actions.rename.label')
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.alexacustomskillintent.form.actions.rename.button'),
                'onClick' => 'ACSIntent_MatchName($id);'
            )
        );

        // Add the status codes
        array_push($data['status'],
            array(
                'code' => self::STATUS_ERROR_WEBHOOK_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.alexacustomskillintent.form.status.webhook_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_INTENT_NAME_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.alexacustomskillintent.form.status.intent_name_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_DUPLICATE_INTENT_NAME,
                'icon' => 'error',
                'caption' => Translator::Get('patami.alexacustomskillintent.form.status.duplicate_intent_name')
            )
        );

        // Return the configuration form
        return $data;
    }

    /**
     * Performs actions required when the configuration of this instance was saved.
     * Validates the parent WebHook I/O instance, the intent name, checks for other intent instances with the same
     * parent instance and the same intent name.
     */
    protected function Configure()
    {
        // Validate the WebHook instance
        /** @noinspection PhpUndefinedFieldInspection */
        $webHookId = @IPS::GetInstance($this->InstanceID)['ConnectionID'];
        $this->Debug('WebHook Instance', $webHookId);
        if (!$webHookId) {
            $this->Debug('WebHook Validation', 'WebHook is invalid');
            /** @noinspection PhpUndefinedMethodInspection */
            $this->SetStatus(self::STATUS_ERROR_WEBHOOK_INVALID);
            /** @noinspection PhpUndefinedMethodInspection */
            $this->SetSummary('');
            return;
        }

        // Validate the intent name
        $name = $this->GetIntentName();
        $this->Debug('Intent Name', $name);
        $name = strtolower($name);
        if ($this->HasCustomIntentName()) {
            if (!preg_match('/^[a-z]+$/', $name)) {
                $this->Debug('Intent Name Validation', 'Name is invalid');
                /** @noinspection PhpUndefinedMethodInspection */
                $this->SetStatus(self::STATUS_ERROR_INTENT_NAME_INVALID);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->SetSummary('');
                return;
            }
        }
        $this->Debug('Intent Name Validation', 'Name is valid');

        // Check if other instances that share the same WebHook (= skill) use the same intent name
        $instanceIds = IPS::GetInstanceListByModuleId('{F14921A2-405D-4576-A389-95FB9A5CD730}');
        foreach ($instanceIds as $instanceId) {
            // Skip the current instance
            if ($instanceId == $this->GetId()) {
                continue;
            }
            // Listen for instance reloads
            $this->ListenForInstanceReloads($instanceId);
            // Skip if the instance is still initializing
            if (IPS::IsInstanceInitializing($instanceId)) {
                continue;
            }
            // Skip if connected to a different WebHook
            $parentId = @IPS::GetInstance($instanceId)['ConnectionID'];
            if ($webHookId != $parentId) {
                continue;
            }
            // Report error if instance name matches
            $otherName = @IPS::GetProperty($instanceId, 'StandardIntentName');
            if ($otherName == '') {
                $otherName = @IPS::GetProperty($instanceId, 'IntentName');
            }
            /** @noinspection PhpUndefinedFunctionInspection */
            if ($name == strtolower($otherName)) {
                $this->Debug('Intent Name Validation', sprintf('Duplicate name (other instance ID is %d)', $instanceId));
                /** @noinspection PhpUndefinedMethodInspection */
                $this->SetStatus(self::STATUS_ERROR_DUPLICATE_INTENT_NAME);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->SetSummary('');
                return;
            }
        }
        $this->Debug('Intent Name Validation', 'Name is unique');

        // Set the intent name as summary info
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SetSummary($this->GetIntentName());

        // Call the parent method
        parent::Configure();
    }

    /**
     * Checks if the intent has a custom name.
     * @return bool True if the intent has a custom name.
     */
    protected function HasCustomIntentName()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $standardName = $this->ReadPropertyString('StandardIntentName');
        return $standardName == '';
    }

    /**
     * Returns the intent name of the instance.
     * @return string Intent name.
     */
    public function GetIntentName()
    {
        if ($this->HasCustomIntentName()) {
            /** @noinspection PhpUndefinedMethodInspection */
            $name = $this->ReadPropertyString('IntentName');
        } else {
            /** @noinspection PhpUndefinedMethodInspection */
            $name = $this->ReadPropertyString('StandardIntentName');
        }
        return $name;
    }

    /**
     * Sets the intent name and saves the configuration.
     * @param string $name Intent name.
     */
    public function SetIntentName($name)
    {
        $validStandardIntents = array(
            self::AMAZONCancelIntent,
            self::AMAZONHelpIntent,
            self::AMAZONLoopOffIntent,
            self::AMAZONLoopOnIntent,
            self::AMAZONNextIntent,
            self::AMAZONNoIntent,
            self::AMAZONPauseIntent,
            self::AMAZONPreviousIntent,
            self::AMAZONRepeatIntent,
            self::AMAZONResumeIntent,
            self::AMAZONShuffleOffIntent,
            self::AMAZONShuffleOnIntent,
            self::AMAZONStartOverIntent,
            self::AMAZONStopIntent,
            self::AMAZONYesIntent
        );

        // Set the intent name
        if (in_array($name, $validStandardIntents)) {
            /** @noinspection PhpUndefinedFieldInspection */
            IPS::SetProperty($this->InstanceID, 'StandardIntentName', $name);
        } else {
            /** @noinspection PhpUndefinedFieldInspection */
            IPS::SetProperty($this->InstanceID, 'IntentName', $name);
        }

        // Apply the changes
        /** @noinspection PhpUndefinedFieldInspection */
        IPS::ApplyChanges($this->InstanceID);
    }

    /**
     * Renames the IPS intent instance to match the intent name.
     */
    public function MatchName()
    {
        // Get the intent name
        $name = $this->GetIntentName();

        // Rename the object
        /** @noinspection PhpUndefinedFieldInspection */
        IPS::SetName($this->InstanceID, $name);
    }

    /**
     * Returns the intent type.
     * @return int Intent type.
     */
    public function GetIntentType()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger('IntentType');
    }

    /**
     * Sets the intent type and saves the configuration.
     * @param int $intentType New intent type.
     */
    public function SetIntentType($intentType)
    {
        // Set the action
        /** @noinspection PhpUndefinedFieldInspection */
        IPS::SetProperty($this->InstanceID, 'IntentType', $intentType);

        // Apply the changes
        /** @noinspection PhpUndefinedFieldInspection */
        IPS::ApplyChanges($this->InstanceID);
    }

    /**
     * Returns information about the available intent types.
     * @return array Intent types attributes.
     */
    protected function GetIntentTypes()
    {
        return array(
            self::INTENT_TYPE_SPEECH_OUTPUT => array(
                'label' => Translator::Get('patami.alexacustomskillintent.form.action.speech_output_option'),
                'className' => 'Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\Intents\\SpeechOutputIntent'
            ),
            self::INTENT_TYPE_SCRIPT => array(
                'label' => Translator::Get('patami.alexacustomskillintent.form.action.script_option'),
                'className' => 'Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\Intents\\Custom\\ScriptIntent'
            ),
            self::INTENT_TYPE_REDIRECT => array(
                'label' => Translator::Get('patami.alexacustomskillintent.form.action.redirect_option'),
                'className' => 'Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\Intents\\RedirectIntent'
            ),
            self::INTENT_TYPE_REDIRECT_CALLBACK => array(
                'label' => Translator::Get('patami.alexacustomskillintent.form.action.redirect_callback_option'),
                'className' => 'Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\Intents\\RedirectToCallbackIntent'
            ),
            self::INTENT_TYPE_REPEAT_LAST_RESPONSE => array(
                'label' => Translator::Get('patami.alexacustomskillintent.form.action.repeat_last_response_option'),
                'className' => 'Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\Intents\\RepeatLastResponseIntent'
            )
        );
    }

    /**
     * Returns the IPS object ID of the default IPS action script.
     * @return int IPS object ID of the default IPS action script.
     */
    public function GetDefaultScriptId()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        return IPS::GetObjectIdByIdent('Script', $this->InstanceID);
    }

    /**
     * Returns the list of FQCNs of the intent type classes.
     * @return array FQCNs of the intent classes.
     */
    public function GetIntentClassNames()
    {
        $classNames = array();

        $intentTypes = $this->GetIntentTypes();
        foreach ($intentTypes as $intentType) {
            $classNames[] = $intentType['className'];
        }

        return $classNames;
    }

    /**
     * Translated the name of a property to make sure the configuration properties of the intent type classes are
     * uniqie.
     * @param string $className FQCN of the intent class.
     * @param string $name Name of the configuration property.
     * @return string Translated configuration property name.
     */
    protected function GetTranslatedPropertyName($className, $name)
    {
        return sprintf('%s_%s', str_replace('\\', '_', $className), $name);
    }

    /**
     * Returns the value of an intent configuration property.
     * @param ModuleIntent $intent Module intent object.
     * @param string $name Name of the configuration property.
     * @return mixed Value of the configuration property.
     */
    public function ReadIntentProperty(ModuleIntent $intent, $name)
    {
        // Get the property name
        $propertyName = $this->GetTranslatedPropertyName(get_class($intent), $name);

        // Return the property value
        /** @noinspection PhpUndefinedFieldInspection */
        return IPS::GetProperty($this->InstanceID, $propertyName);
    }

    /**
     * Executes the intent.
     * It created a new module intent object based on the configuration and executes it.
     * @param WebHookRequest $request Request object.
     * @return Response Response object generated by the intent which is returned to the Amazon servers.
     */
    public function Execute(WebHookRequest $request)
    {
        // Create the intent instance
        $intentTypes = $this->GetIntentTypes();
        $intentType = $this->GetIntentType();
        /** @var ModuleIntent $className */
        $className = $intentTypes[$intentType]['className'];
        /** @var ModuleIntent $intent */
        $intent = $className::Create($this);

        // Execute the intent
        return $intent->Execute($request);
    }

    protected function GetNewIssueUrl()
    {
        return 'https://bitbucket.org/patami/ipspatami/issues/new';
    }

    protected function GetLicenseUrl()
    {
        return 'https://docs.braintower.de/display/IPSPATAMI/Lizenz';
    }

    protected function GetDocumentationUrl()
    {
        return 'https://docs.braintower.de/display/IPSPATAMI/Alexa+Custom+Skill+Intent+Modul';
    }

    protected function GetReleaseNotesUrl()
    {
        return 'https://docs.braintower.de/display/IPSPATAMI/Versionshinweise';
    }

}