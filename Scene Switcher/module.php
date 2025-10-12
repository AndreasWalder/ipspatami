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


use Patami\IPS\Modules\Module;
use Patami\IPS\Modules\Exceptions\ModuleException;
use Patami\IPS\Objects\Objects;
use Patami\IPS\Objects\Instance;
use Patami\IPS\Objects\BooleanVariable;
use Patami\IPS\I18N\Translator;
use Patami\IPS\System\IPS;
use Patami\IPS\System\Semaphore;


Translator::AddDir(__DIR__ . DIRECTORY_SEPARATOR . 'translations');


/**
 * PatamiSceneSwitcher IPS Module.
 * The GUID of this module is {48116B13-0CC1-4856-B122-72058B2BDA18}.
 * @package IPSPATAMI
 */
class PatamiSceneSwitcher extends Module
{

    /** Patami Object Group instance is invalid. */
    const STATUS_ERROR_POG_INSTANCE_INVALID = 201;
    /** Trigger variable is invalid. */
    const STATUS_ERROR_TRIGGER_VARIABLE_INVALID = 202;

    /** Property name of the Patami Object Group instance ID. */
    const PROPERTY_POG_INSTANCE_ID = 'POGInstanceID';

    /** Maximum number of trigger variables. */
    const MAX_TRIGGER_VARIABLES = 8;

    /** Format string for the property names of the trigger variable IDs. */
    const PROPERTY_TRIGGER_VARIABLE_ID_FORMAT = 'TriggerVariableID%d';

    /** Property name for the checkbox to enable applying the first scene when false is triggered. */
    const PROPERTY_APPLY_FIRST_SCENE_ON_FALSE = 'ApplyFirstSceneOnFalse';

    /** Maximum number of scenes. */
    const MAX_SCENES = 32;

    /** Format string for the property names of the scene names. */
    const PROPERTY_SCENE_NAME_FORMAT = 'SceneName%d';

    /** Name of the IPS object buffer for the current scene index. */
    const CURRENT_SCENE_BUFFER_NAME = 'current_scene';

    /** Name of the IPS object buffer for the registered messages. */
    const REGISTERED_MESSAGES_BUFFER_NAME = 'registered_messages';

    public function Create()
    {
        // Call the parent method
        parent::Create();

        // Add Patami Object Group property
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_POG_INSTANCE_ID, 0);

        // Add trigger variable properties
        for ($index = 1; $index <= self::MAX_TRIGGER_VARIABLES; $index++) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->RegisterPropertyInteger(sprintf(self::PROPERTY_TRIGGER_VARIABLE_ID_FORMAT, $index), 0);
        }

        // Add property
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyBoolean(self::PROPERTY_APPLY_FIRST_SCENE_ON_FALSE, false);

        // Add scene name properties
        for ($index = 1; $index <= self::MAX_SCENES; $index++) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->RegisterPropertyString(sprintf(self::PROPERTY_SCENE_NAME_FORMAT, $index), '');
        }
    }

    protected function GetConfigurationFormData()
    {
        // Call the parent method
        $data = parent::GetConfigurationFormData();

        // Add Patami Object Group instance
        array_push($data['elements'],
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.patamisceneswitcher.form.pog_instance.label')
            ),
            array(
                'type' => 'SelectInstance',
                'name' => self::PROPERTY_POG_INSTANCE_ID,
                'caption' => ''
            )
        );
        $pogInstanceId = $this->GetPOGInstanceId();
        if ($pogInstanceId) {
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => IPS::GetLocation($pogInstanceId)
                )
            );
        }

        // Add trigger variables
        array_push($data['elements'],
            array(
                'type' => 'Label',
                'label' => ''
            ),
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.patamisceneswitcher.form.trigger_variable.label')
            )
        );
        for ($index = 1; $index <= self::MAX_TRIGGER_VARIABLES; $index++) {
            array_push($data['elements'],
                array(
                    'type' => 'SelectVariable',
                    'name' => sprintf(self::PROPERTY_TRIGGER_VARIABLE_ID_FORMAT, $index),
                    'caption' => Translator::Format('patami.patamisceneswitcher.form.trigger_variable.variable_label', $index)
                )
            );
            $triggerVariableId = $this->GetTriggerVariableId($index);
            if ($triggerVariableId) {
                array_push($data['elements'],
                    array(
                        'type' => 'Label',
                        'label' => IPS::GetLocation($triggerVariableId)
                    )
                );
            }
        }

        array_push($data['elements'],
            array(
                'type' => 'Label',
                'label' => ''
            ),
            array(
                'type' => 'CheckBox',
                'name' => self::PROPERTY_APPLY_FIRST_SCENE_ON_FALSE,
                'caption' => Translator::Get('patami.patamisceneswitcher.form.trigger_variable.apply_first_scene_on_false_label')
            )
        );

        // Add scene dropdowns
        if ($pogInstanceId) {
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => ''
                ),
                array(
                    'type' => 'Label',
                    'label' => Translator::Get('patami.patamisceneswitcher.form.scenes.label')
                )
            );
            for ($index = 1; $index <= self::MAX_SCENES; $index++) {
                array_push($data['elements'],
                    array(
                        'type' => 'Select',
                        'name' => sprintf(self::PROPERTY_SCENE_NAME_FORMAT, $index),
                        'caption' => Translator::Format('patami.patamisceneswitcher.form.scenes.scene_label', $index),
                        'options' => $this->GetSceneNameDropDownOptions()
                    )
                );
            }
        }

        // Add the scene switching buttons
        array_unshift($data['actions'],
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.patamisceneswitcher.form.actions.scene.label')
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.patamisceneswitcher.form.actions.scene.next_button'),
                'onClick' => 'PSS_NextScene($id);'
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.patamisceneswitcher.form.actions.scene.previous_button'),
                'onClick' => 'PSS_PreviousScene($id);'
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.patamisceneswitcher.form.actions.scene.first_button'),
                'onClick' => 'PSS_FirstScene($id);'
            )
        );

        // Add status messages
        array_push($data['status'],
            array(
                'code' => self::STATUS_ERROR_POG_INSTANCE_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamisceneswitcher.form.status.pog_instance_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_TRIGGER_VARIABLE_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamisceneswitcher.form.status.trigger_variable_invalid')
            )
        );

        // Return the configuration form
        return $data;
    }

    protected function Configure()
    {
        // Initialize data structures
        $messages = array();

        try {

            // Check if the POG instance is valid
            $tag = 'POG Instance Validation';
            $pogInstanceId = $this->GetPOGInstanceId();
            if ($pogInstanceId) {
                // Listen for instance reloads
                $this->ListenForInstanceReloads($pogInstanceId);
                // Get the instance object
                $pogInstance = Objects::GetByID($pogInstanceId);
                $this->Debug($tag, utf8_decode($pogInstance->GetLocation()));
                // Check if it is an instance
                if (! $pogInstance instanceof Instance) {
                    $this->Debug($tag, 'The instance is not an IPS instance');
                    throw new ModuleException('', self::STATUS_ERROR_POG_INSTANCE_INVALID);
                }
                // Check if it is a POG instance
                $pogInstanceModuleId = $pogInstance->GetModuleId();
                if ($pogInstanceModuleId != '{ED1BD621-3BA5-48C1-A016-33E6AECA12F2}') {
                    $this->Debug($tag, 'The instance is not a Patami Object Group instance');
                    throw new ModuleException('', self::STATUS_ERROR_POG_INSTANCE_INVALID);
                }
                // Check if the mode is scene
                /** @noinspection PhpUndefinedFunctionInspection */
                $pogInstanceMode = @POG_GetMode($pogInstanceId);
                if ($pogInstanceMode != 2) {
                    $this->Debug($tag, 'The instance is not in "scene" mode');
                    throw new ModuleException('', self::STATUS_ERROR_POG_INSTANCE_INVALID);
                }
            } else {
                $this->Debug($tag, 'Not configured');
                throw new ModuleException('', self::STATUS_ERROR_POG_INSTANCE_INVALID);
            }

            // Check if the trigger variables are boolean variables
            $configuredTriggers = 0;
            for ($index = 1; $index < self::MAX_TRIGGER_VARIABLES; $index++) {
                $tag = sprintf('Trigger Variable %d Validation', $index);
                $triggerVariableId = $this->GetTriggerVariableId($index);
                if ($triggerVariableId) {
                    $triggerVariable = Objects::GetByID($triggerVariableId);
                    $this->Debug($tag, utf8_decode($triggerVariable->GetLocation()));
                    if (!$triggerVariable instanceof BooleanVariable) {
                        $this->Debug($tag, 'The variable is not a boolean variable');
                        throw new ModuleException('', self::STATUS_ERROR_TRIGGER_VARIABLE_INVALID);
                    }
                    $configuredTriggers++;
                } else {
                    $this->Debug($tag, 'Not configured');
                }
            }
            if ($configuredTriggers == 0) {
                $this->Debug($tag, 'No trigger variable is configured');
                throw new ModuleException('', self::STATUS_ERROR_TRIGGER_VARIABLE_INVALID);
            }

            // Define lambda function to add messages to the data structure
            $addMessage = function (&$messages, $objectId, $messageId) {
                $key = sprintf('%d_%d', $objectId, $messageId);
                $messages[$key] = array(
                    'objectId' => $objectId,
                    'messageId' => $messageId
                );
            };

            // Listen for variable updates
            for ($index = 1; $index < self::MAX_TRIGGER_VARIABLES; $index++) {
                $triggerVariableId = $this->GetTriggerVariableId($index);
                if ($triggerVariableId) {
                    $addMessage($messages, $triggerVariableId, VM_UPDATE);
                }
            }

            // Call the parent method
            parent::Configure();

        } catch (ModuleException $e) {

            // Set the instance status
            /** @noinspection PhpUndefinedMethodInspection */
            $this->SetStatus($e->getCode());

        } finally {

            // Enter the semaphore to make sure the buffer is consistent
            $semaphore = new Semaphore(sprintf('%d_%s', $this->GetId(), self::REGISTERED_MESSAGES_BUFFER_NAME));
            $semaphore->Enter();

            try {

                // Load the registered messages
                /** @noinspection PhpUndefinedMethodInspection */
                $data = $this->GetBuffer(self::REGISTERED_MESSAGES_BUFFER_NAME);
                $oldMessages = @json_decode($data, true);
                if (is_null($oldMessages)) {
                    $oldMessages = array();
                }

                // Unregister obsolete messages
                $obsoleteMessages = array_diff_key($oldMessages, $messages);
                foreach ($obsoleteMessages as $data) {
                    $objectId = $data['objectId'];
                    $messageId = $data['messageId'];
                    // Log it
                    $this->Debug($objectId, sprintf('Unregistering message %d', $messageId));
                    // Remove it
                    /** @noinspection PhpUndefinedMethodInspection */
                    $this->UnregisterMessage($objectId, $messageId);
                }

                // Register new messages
                $newMessages = array_diff_key($messages, $oldMessages);
                foreach ($newMessages as $data) {
                    $objectId = $data['objectId'];
                    $messageId = $data['messageId'];
                    // Log it
                    $this->Debug($objectId, sprintf('Registering message %d', $messageId));
                    // Remove it
                    /** @noinspection PhpUndefinedMethodInspection */
                    $this->RegisterMessage($objectId, $messageId);
                }

                // Remember the registered messages
                /** @noinspection PhpUndefinedMethodInspection */
                $this->SetBuffer(self::REGISTERED_MESSAGES_BUFFER_NAME, json_encode($messages));

            } finally {

                // Leave the semaphore
                $semaphore->Leave();

            }

        }

    }

    /**
     * Returns the IPS object ID of the Patami Object Group instance.
     * @return int IPS object ID of the Patami Object Group instance.
     */
    public function GetPOGInstanceId()
    {
        // Return the instance ID
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_POG_INSTANCE_ID);
    }

    /**
     * Returns the IPS object ID of the trigger variable with the given index.
     * @param int $index Index of the trigger variable.
     * @return int IPS object ID of the trigger variable.
     */
    public function GetTriggerVariableId($index)
    {
        // Return the variable ID
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(sprintf(self::PROPERTY_TRIGGER_VARIABLE_ID_FORMAT, $index));
    }

    /**
     * Checks if the first scene is applied on when false is triggered.
     * @return bool True if the first scene is applied on false, false if the previous scene is applied on false.
     */
    public function IsFirstSceneAppliedOnFalse()
    {
        // Return the variable ID
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyBoolean(self::PROPERTY_APPLY_FIRST_SCENE_ON_FALSE);
    }

    /**
     * Returns the name of the scene with the given index.
     * @param int $index Index of the scene.
     * @return string Name of the scene.
     */
    public function GetSceneName($index)
    {
        // Return the scene name
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyString(sprintf(self::PROPERTY_SCENE_NAME_FORMAT, $index));
    }

    /**
     * Returns a data structure to be used for a scene name dropdown.
     * @return array Scene name drop down options.
     */
    public function GetSceneNameDropdownOptions()
    {
        // Initialize the list with the remove option
        $options[] = array(
            'label' => '',
            'value' => ''
        );

        // Get the scene names
        /** @noinspection PhpUndefinedFunctionInspection */
        $sceneNames = @POG_GetSceneNames($this->GetPOGInstanceId());

        // Loop through the names and generate the dropdown option list
        foreach ($sceneNames as $sceneName) {
            $options[] = array(
                'label' => $sceneName,
                'value' => $sceneName
            );
        }

        // Return the dropdown options
        return $options;
    }

    protected function OnMessage($timestamp, $senderId, $messageId, $data)
    {
        switch ($messageId) {
            case VM_UPDATE:
                // A monitored variable was updated
                $newValue = @$data[0];
                $oldValue = @$data[2];
                $this->OnVariableUpdated($senderId, $newValue, $oldValue);
                break;
            default:
                // Unexpected message received
                $this->Debug($senderId, sprintf('Unexpected message %d', $messageId));
        }
    }

    /**
     * Event handler called when variables are updated.
     * @param int $objectId IPS object ID of the object that that triggered the message.
     * @param mixed $newValue New variable value.
     * @param mixed $oldValue Old variable value.
     */
    protected function OnVariableUpdated($objectId, $newValue, $oldValue)
    {
        // Log the event
        $this->Debug($objectId, sprintf('Monitored variable updated (%s => %s)', serialize($oldValue), serialize($newValue)));

        if ($newValue) {
            $this->NextScene();
        } else {
            if ($this->IsFirstSceneAppliedOnFalse()) {
                $this->FirstScene();
            } else {
                $this->PreviousScene();
            }
        }
    }

    /**
     * Returns the index of the current scene.
     * @return int|null Index of the current scene or null if no scene is configured.
     */
    public function GetCurrentSceneIndex()
    {
        // Get the current scene index
        /** @noinspection PhpUndefinedMethodInspection */
        $index = $this->GetBuffer(self::CURRENT_SCENE_BUFFER_NAME);

        // Find the first configured scene if no current scene is in the buffer
        if ($index == '') {
            // Loop through all scenes
            for ($index = 1; $index <= self::MAX_SCENES; $index++) {
                // Get the scene name
                $sceneName = $this->GetSceneName($index);
                // Return the current index if the scene is configured
                if ($sceneName != '') {
                    return $index;
                }
            }
            // No scene was found
            return null;
        }

        // Return the index from the buffer
        return $index;
    }

    /**
     * Remembers the index of the current scene.
     * @param int $index Index of the new current scene.
     */
    public function SetCurrentSceneIndex($index)
    {
        // Set the current scene index
        /** @noinspection PhpUndefinedMethodInspection */
        $index = $this->SetBuffer(self::CURRENT_SCENE_BUFFER_NAME, $index);
    }

    /**
     * Applies the next configured scene.
     * If the current scene is already the last scene, the next scene will be the first scene.
     */
    public function NextScene()
    {
        $tag = 'Next Scene';

        // Get the current scene index
        $currentIndex = $this->GetCurrentSceneIndex();

        // Return if no scenes are configured
        if (is_null($currentIndex)) {
            $this->Debug($tag, 'No scenes configured');
            return;
        }

        // Loop through all scenes
        $index = $currentIndex + 1;
        while (true) {
            // Wrap around if necessary
            if ($index > self::MAX_SCENES) {
                $index = 1;
            }
            // Break if we reached the current scene
            if ($index == $currentIndex) {
                $this->Debug($tag, 'Only one scene configured');
                return;
            }
            // Get the scene name
            $sceneName = $this->GetSceneName($index);
            // Apply the scene if it is configured
            if ($sceneName != '') {
                $this->Debug($tag, sprintf('Applying scene "%s"', $sceneName));
                $this->SetCurrentSceneIndex($index);
                /** @noinspection PhpUndefinedFunctionInspection */
                POG_ApplyScene($this->GetPOGInstanceId(), $sceneName);
                return;
            }
            // Advance to the next scene
            $index++;
        }
    }

    /**
     * Applies the previous configured scene.
     * If the current scene is already the last scene, the next scene will be the first scene.
     */
    public function PreviousScene()
    {
        $tag = 'Previous Scene';

        // Get the current scene index
        $currentIndex = $this->GetCurrentSceneIndex();

        // Return if no scenes are configured
        if (is_null($currentIndex)) {
            $this->Debug($tag, 'No scenes configured');
            return;
        }

        // Loop through all scenes
        $index = $currentIndex - 1;
        while (true) {
            // Wrap around if necessary
            if ($index < 1) {
                $index = self::MAX_SCENES;
            }
            // Break if we reached the current scene
            if ($index == $currentIndex) {
                $this->Debug($tag, 'Only one scene configured');
                return;
            }
            // Get the scene name
            $sceneName = $this->GetSceneName($index);
            // Apply the scene if it is configured
            if ($sceneName != '') {
                $this->Debug($tag, sprintf('Applying scene "%s"', $sceneName));
                $this->SetCurrentSceneIndex($index);
                /** @noinspection PhpUndefinedFunctionInspection */
                POG_ApplyScene($this->GetPOGInstanceId(), $sceneName);
                return;
            }
            // Advance to the previous scene
            $index--;
        }
    }

    /**
     * Applies the first configured scene.
     */
    public function FirstScene()
    {
        $tag = 'First Scene';

        // Get the current scene index
        $currentIndex = $this->GetCurrentSceneIndex();

        // Return if no scenes are configured
        if (is_null($currentIndex)) {
            $this->Debug($tag, 'No scenes configured');
            return;
        }

        // Loop through all scenes
        $index = 1;
        while (true) {
            // Get the scene name
            $sceneName = $this->GetSceneName($index);
            // Apply the scene if it is configured
            if ($sceneName != '') {
                $this->Debug($tag, sprintf('Applying scene "%s"', $sceneName));
                $this->SetCurrentSceneIndex($index);
                /** @noinspection PhpUndefinedFunctionInspection */
                POG_ApplyScene($this->GetPOGInstanceId(), $sceneName);
                return;
            }
            // Advance to the next scene
            $index++;
        }
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
        return 'https://docs.braintower.de/display/IPSPATAMI/Patami+Scene+Switcher+Modul';
    }

    protected function GetReleaseNotesUrl()
    {
        return 'https://docs.braintower.de/display/IPSPATAMI/Versionshinweise';
    }

}