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
use Patami\IPS\Objects\Variable;
use Patami\IPS\Objects\IntegerVariable;
use Patami\IPS\Objects\BooleanVariable;
use Patami\IPS\Objects\VariableProfiles;
use Patami\IPS\Objects\IntegerVariableProfile;
use Patami\IPS\I18N\Translator;
use Patami\IPS\System\Icons;
use Patami\IPS\System\IPS;
use Patami\IPS\System\Semaphore;
use Patami\IPS\System\Color;
use Patami\IPS\Objects\Drivers\Driver;
use Patami\IPS\Objects\Drivers\Drivers;
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\Drivers\SetSwitchInterface;


Translator::AddDir(__DIR__ . DIRECTORY_SEPARATOR . 'translations');


/**
 * PatamiAutoSwitch IPS Module.
 * The GUID of this module is {C6822771-D04A-4903-BD75-DA1939084C91}.
 * @package IPSPATAMI
 */
class PatamiAutoSwitch extends Module
{

    /** Trigger variable is invalid. */
    const STATUS_ERROR_TRIGGER_VARIABLE_INVALID = 201;

    /** Delayed trigger variable is invalid. */
    const STATUS_ERROR_DELAYED_TRIGGER_VARIABLE_INVALID = 202;

    /** Minimum switch on delay is lower than 0. */
    const STATUS_ERROR_DELAYED_TRIGGER_ON_DELAY_MINIMUM_INVALID = 203;
    /** Maximum switch on delay is lower than the minimum switch on delay. */
    const STATUS_ERROR_DELAYED_TRIGGER_ON_DELAY_MAXIMUM_INVALID = 204;
    /** Switch on delay step size if lower than 0 or larger than the difference between the maximum and the minimum delay. */
    const STATUS_ERROR_DELAYED_TRIGGER_ON_DELAY_STEP_SIZE_INVALID = 205;

    /** Minimum switch off delay is lower than 0. */
    const STATUS_ERROR_DELAYED_TRIGGER_OFF_DELAY_MINIMUM_INVALID = 206;
    /** Maximum switch off delay is lower than the minimum switch on delay. */
    const STATUS_ERROR_DELAYED_TRIGGER_OFF_DELAY_MAXIMUM_INVALID = 207;
    /** Switch off delay step size if lower than 0 or larger than the difference between the maximum and the minimum delay. */
    const STATUS_ERROR_DELAYED_TRIGGER_OFF_DELAY_STEP_SIZE_INVALID = 208;

    /** Switch object is not supported. */
    const STATUS_ERROR_SWITCH_OBJECT_INVALID = 209;

    /** Property name of the switch off trigger variable ID. */
    const PROPERTY_TRIGGER_VARIABLE_ID = 'TriggerVariableID';

    /** Property name of the delayed trigger variable ID. */
    const PROPERTY_DELAYED_TRIGGER_VARIABLE_ID = 'DelayedTriggerVariableID';

    /** Property name for the minimum time of the on delay. */
    const PROPERTY_DELAYED_TRIGGER_ON_DELAY_MINIMUM = 'DelayedTriggerOnDelayMinimum';
    /** Property name for the maximum time of the on delay. */
    const PROPERTY_DELAYED_TRIGGER_ON_DELAY_MAXIMUM = 'DelayedTriggerOnDelayMaximum';
    /** Property name for the step size of the on delay. */
    const PROPERTY_DELAYED_TRIGGER_ON_DELAY_STEP_SIZE = 'DelayedTriggerOnDelayStepSize';

    /** Property name for the minimum time of the off delay. */
    const PROPERTY_DELAYED_TRIGGER_OFF_DELAY_MINIMUM = 'DelayedTriggerOffDelayMinimum';
    /** Property name for the maximum time of the off delay. */
    const PROPERTY_DELAYED_TRIGGER_OFF_DELAY_MAXIMUM = 'DelayedTriggerOffDelayMaximum';
    /** Property name for the step size of the off delay. */
    const PROPERTY_DELAYED_TRIGGER_OFF_DELAY_STEP_SIZE = 'DelayedTriggerOffDelayStepSize';

    /** Property name for the object which should be switched together with the switch. */
    const PROPERTY_SWITCH_OBJECT_ID = 'SwitchScriptID';

    /** Ident of the automatic enabled variable. */
    const VAR_AUTOMATIC_ENABLED = 'AutomaticEnabled';

    /** Ident of the on delay variable. */
    const VAR_DELAYED_TRIGGER_ON_DELAY = 'DelayedTriggerOnDelay';
    /** Name format of the on delay variable profile. */
    const VAR_PROFILE_DELAYED_TRIGGER_ON_DELAY_NAME_FORMAT = 'PatamiAutoSwitch.%d.DelayedTriggerOnDelay';

    /** Ident of the off delay variable. */
    const VAR_DELAYED_TRIGGER_OFF_DELAY = 'DelayedTriggerOffDelay';
    /** Name format of the off delay variable profile. */
    const VAR_PROFILE_DELAYED_TRIGGER_OFF_DELAY_NAME_FORMAT = 'PatamiAutoSwitch.%d.DelayedTriggerOffDelay';

    /** Ident of the activity variable. */
    const VAR_ACTIVITY = 'Activity';
    /** Name of the activity variable profile. */
    const VAR_PROFILE_ACTIVITY_NAME = 'PatamiAutoSwitch.Activity';

    /** Instance is off. */
    const ACTIVITY_OFF = 0;
    /** Instance is waiting to switch on. */
    const ACTIVITY_WAITING_SWITCH_ON = 1;
    /** Instance is on. */
    const ACTIVITY_ON = 2;
    /** Instance is waiting to switch off. */
    const ACTIVITY_WAITING_SWITCH_OFF = 3;
    
    /** Ident of the switch status variable. */
    const VAR_SWITCH = 'Switch';

    /** Name of the timer that handles the delay. */
    const TIMER_NAME = 'Delay';

    /** Name of the IPS object buffer for switch object driver. */
    const SWITCH_OBJECT_DRIVER_BUFFER_NAME = 'switch_object_driver';
    /** Name of the IPS object buffer for the registered messages. */
    const REGISTERED_MESSAGES_BUFFER_NAME = 'registered_messages';

    public function Create()
    {
        // Call the parent method
        parent::Create();

        // Add properties
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_TRIGGER_VARIABLE_ID, 0);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_DELAYED_TRIGGER_VARIABLE_ID, 0);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_DELAYED_TRIGGER_ON_DELAY_MINIMUM, 0);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_DELAYED_TRIGGER_ON_DELAY_MAXIMUM, 300);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_DELAYED_TRIGGER_ON_DELAY_STEP_SIZE, 5);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_DELAYED_TRIGGER_OFF_DELAY_MINIMUM, 0);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_DELAYED_TRIGGER_OFF_DELAY_MAXIMUM, 300);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_DELAYED_TRIGGER_OFF_DELAY_STEP_SIZE, 5);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_SWITCH_OBJECT_ID, 0);

        // Register timer
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterTimer(self::TIMER_NAME, 0, sprintf('PAS_OnTimerElapsed(%d);', $this->GetId()));
    }

    /**
     * Performs cleanup tasks when the instance is deleted.
     * {@inheritDoc}
     * The PatamiAutoSwitch implementation removes the variable profiles.
     */
    public function Destroy()
    {
        // Remove variable profiles
        $id = $this->GetId();
        $onDelayProfileName = sprintf(self::VAR_PROFILE_DELAYED_TRIGGER_ON_DELAY_NAME_FORMAT, $id);
        IPS::DeleteVariableProfile($onDelayProfileName);
        $offDelayProfileName = sprintf(self::VAR_PROFILE_DELAYED_TRIGGER_OFF_DELAY_NAME_FORMAT, $id);
        IPS::DeleteVariableProfile($offDelayProfileName);

        // Check if there are any other instances
        if (IPS::GetInstanceCountByModuleId($this->GetModuleId()) <= 1) {
            // Remove the common variable profile
            IPS::DeleteVariableProfile(self::VAR_PROFILE_ACTIVITY_NAME);
        }

        // Call the parent method
        parent::Destroy();
    }

    protected function GetConfigurationFormData()
    {
        // Call the parent method
        $data = parent::GetConfigurationFormData();

        // Add trigger variable
        array_push($data['elements'],
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.patamiautoswitch.form.trigger.label')
            ),
            array(
                'type' => 'SelectVariable',
                'name' => self::PROPERTY_TRIGGER_VARIABLE_ID,
                'caption' => ''
            )
        );
        $triggerVariableId = $this->GetTriggerVariableId();
        if ($triggerVariableId) {
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => IPS::GetLocation($triggerVariableId)
                )
            );
        }

        // Add delay trigger variable
        array_push($data['elements'],
            array(
                'type' => 'Label',
                'label' => ''
            ),
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.patamiautoswitch.form.delayed_trigger.label')
            ),
            array(
                'type' => 'SelectVariable',
                'name' => self::PROPERTY_DELAYED_TRIGGER_VARIABLE_ID,
                'caption' => ''
            )
        );
        $delayedTriggerVariableId = $this->GetDelayedTriggerVariableId();
        if ($delayedTriggerVariableId) {
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => IPS::GetLocation($delayedTriggerVariableId)
                )
            );
        }

        // Add delay settings variable
        array_push($data['elements'],
            array(
                'type' => 'Label',
                'label' => ''
            ),
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.patamiautoswitch.form.delayed_trigger.on_delay.label')
            ),
            array(
                'type' => 'NumberSpinner',
                'name' => self::PROPERTY_DELAYED_TRIGGER_ON_DELAY_MINIMUM,
                'caption' => Translator::Get('patami.patamiautoswitch.form.delayed_trigger.on_delay.minimum_label')
            ),
            array(
                'type' => 'NumberSpinner',
                'name' => self::PROPERTY_DELAYED_TRIGGER_ON_DELAY_MAXIMUM,
                'caption' => Translator::Get('patami.patamiautoswitch.form.delayed_trigger.on_delay.maximum_label')
            ),
            array(
                'type' => 'NumberSpinner',
                'name' => self::PROPERTY_DELAYED_TRIGGER_ON_DELAY_STEP_SIZE,
                'caption' => Translator::Get('patami.patamiautoswitch.form.delayed_trigger.on_delay.step_size_label')
            ),
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.patamiautoswitch.form.delayed_trigger.off_delay.label')
            ),
            array(
                'type' => 'NumberSpinner',
                'name' => self::PROPERTY_DELAYED_TRIGGER_OFF_DELAY_MINIMUM,
                'caption' => Translator::Get('patami.patamiautoswitch.form.delayed_trigger.off_delay.minimum_label')
            ),
            array(
                'type' => 'NumberSpinner',
                'name' => self::PROPERTY_DELAYED_TRIGGER_OFF_DELAY_MAXIMUM,
                'caption' => Translator::Get('patami.patamiautoswitch.form.delayed_trigger.off_delay.maximum_label')
            ),
            array(
                'type' => 'NumberSpinner',
                'name' => self::PROPERTY_DELAYED_TRIGGER_OFF_DELAY_STEP_SIZE,
                'caption' => Translator::Get('patami.patamiautoswitch.form.delayed_trigger.off_delay.step_size_label')
            )
        );

        // Add switch object
        array_push($data['elements'],
            array(
                'type' => 'Label',
                'label' => ''
            ),
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.patamiautoswitch.form.switch_object.label')
            ),
            array(
                'type' => 'SelectObject',
                'name' => self::PROPERTY_SWITCH_OBJECT_ID,
                'caption' => ''
            )
        );
        $switchObjectId = $this->GetSwitchObjectId();
        if ($switchObjectId) {
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => IPS::GetLocation($switchObjectId)
                )
            );
        }

        // Add status messages
        array_push($data['status'],
            array(
                'code' => self::STATUS_ERROR_TRIGGER_VARIABLE_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiautoswitch.form.status.trigger_variable_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_DELAYED_TRIGGER_VARIABLE_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiautoswitch.form.status.delayed_trigger_variable_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_DELAYED_TRIGGER_ON_DELAY_MINIMUM_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiautoswitch.form.status.delayed_trigger_on_delay_minimum_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_DELAYED_TRIGGER_ON_DELAY_MAXIMUM_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiautoswitch.form.status.delayed_trigger_on_delay_maximum_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_DELAYED_TRIGGER_ON_DELAY_STEP_SIZE_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiautoswitch.form.status.delayed_trigger_on_delay_step_size_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_DELAYED_TRIGGER_OFF_DELAY_MINIMUM_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiautoswitch.form.status.delayed_trigger_off_delay_minimum_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_DELAYED_TRIGGER_OFF_DELAY_MAXIMUM_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiautoswitch.form.status.delayed_trigger_off_delay_maximum_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_DELAYED_TRIGGER_OFF_DELAY_STEP_SIZE_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiautoswitch.form.status.delayed_trigger_off_delay_step_size_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_SWITCH_OBJECT_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiautoswitch.form.status.switch_object_invalid')
            )
        );

        // Return the configuration form
        return $data;
    }

    protected function Configure()
    {
        // Initialize data structures
        $messages = array();

        // Initialize values
        $minOnDelay = $maxOnDelay = $onStepSize = 0;
        $minOffDelay = $maxOffDelay = $offStepSize = 0;

        try {

            // Check if the trigger variable is a boolean variable
            $tag = 'Trigger Variable Validation';
            $triggerVariableId = $this->GetTriggerVariableId();
            if ($triggerVariableId) {
                $triggerVariable = Objects::GetByID($triggerVariableId);
                $this->Debug($tag, utf8_decode($triggerVariable->GetLocation()));
                if (! $triggerVariable instanceof BooleanVariable) {
                    $this->Debug($tag, 'The variable is not a boolean variable');
                    throw new ModuleException('', self::STATUS_ERROR_TRIGGER_VARIABLE_INVALID);
                }
            } else {
                $this->Debug($tag, 'Not configured');
            }

            // Check if the delayed trigger variable is a boolean variable
            $tag = 'Delayed Trigger Variable Validation';
            $delayedTriggerVariableId = $this->GetDelayedTriggerVariableId();
            if ($delayedTriggerVariableId) {
                $delayedTriggerVariable = Objects::GetByID($delayedTriggerVariableId);
                $this->Debug($tag, utf8_decode($delayedTriggerVariable->GetLocation()));
                if (! $delayedTriggerVariable instanceof BooleanVariable) {
                    $this->Debug($tag, 'The variable is not a boolean variable');
                    throw new ModuleException('', self::STATUS_ERROR_DELAYED_TRIGGER_VARIABLE_INVALID);
                }
            } else {
                $this->Debug($tag, 'Not configured');
            }

            // Check the delayed trigger switch on delay settings
            // Check if the minimum delay is too low
            $tag = 'Delayed Trigger On Delay Validation';
            $minOnDelay = $this->GetDelayedTriggerOnDelayMinimum();
            $this->Debug($tag, sprintf('Minimum: %d', $minOnDelay));
            if ($minOnDelay < 0) {
                $this->Debug($tag, 'The minimum delay is lower than 0');
                throw new ModuleException('', self::STATUS_ERROR_DELAYED_TRIGGER_ON_DELAY_MINIMUM_INVALID);
            }
            // Check if the maximum delay is too low
            $maxOnDelay = $this->GetDelayedTriggerOnDelayMaximum();
            $this->Debug($tag, sprintf('Maximum: %d', $maxOnDelay));
            if ($maxOnDelay < $minOnDelay) {
                $this->Debug($tag, 'The maximum delay is lower than the minimum delay');
                throw new ModuleException('', self::STATUS_ERROR_DELAYED_TRIGGER_ON_DELAY_MAXIMUM_INVALID);
            }
            // Check if the step size is too low
            $onStepSize = $this->GetDelayedTriggerOnDelayStepSize();
            $this->Debug($tag, sprintf('Step Size: %d', $onStepSize));
            if ($onStepSize < 1) {
                $this->Debug($tag, 'The step size is lower than 1');
                throw new ModuleException('', self::STATUS_ERROR_DELAYED_TRIGGER_ON_DELAY_STEP_SIZE_INVALID);
            }
            // Check if the step size is too high
            $maxOnStepSize = $maxOnDelay - $minOnDelay;
            if ($onStepSize > $maxOnStepSize) {
                $this->Debug($tag, sprintf('The step size is higher than %d', $maxOnStepSize));
                throw new ModuleException('', self::STATUS_ERROR_DELAYED_TRIGGER_ON_DELAY_STEP_SIZE_INVALID);
            }

            // Check the delayed trigger switch off delay settings
            // Check if the minimum delay is too low
            $tag = 'Delayed Trigger Off Delay Validation';
            $minOffDelay = $this->GetDelayedTriggerOffDelayMinimum();
            $this->Debug($tag, sprintf('Minimum: %d', $minOffDelay));
            if ($minOffDelay < 0) {
                $this->Debug($tag, 'The minimum delay is lower than 0');
                throw new ModuleException('', self::STATUS_ERROR_DELAYED_TRIGGER_OFF_DELAY_MINIMUM_INVALID);
            }
            // Check if the maximum delay is too low
            $maxOffDelay = $this->GetDelayedTriggerOffDelayMaximum();
            $this->Debug($tag, sprintf('Maximum: %d', $maxOffDelay));
            if ($maxOffDelay < $minOffDelay) {
                $this->Debug($tag, 'The maximum delay is lower than the minimum delay');
                throw new ModuleException('', self::STATUS_ERROR_DELAYED_TRIGGER_OFF_DELAY_MAXIMUM_INVALID);
            }
            // Check if the step size is too low
            $offStepSize = $this->GetDelayedTriggerOffDelayStepSize();
            $this->Debug($tag, sprintf('Step Size: %d', $offStepSize));
            if ($offStepSize < 1) {
                $this->Debug($tag, 'The step size is lower than 1');
                throw new ModuleException('', self::STATUS_ERROR_DELAYED_TRIGGER_OFF_DELAY_STEP_SIZE_INVALID);
            }
            // Check if the step size is too high
            $maxOffStepSize = $maxOffDelay - $minOffDelay;
            if ($offStepSize > $maxOffStepSize) {
                $this->Debug($tag, sprintf('The step size is higher than %d', $maxOffStepSize));
                throw new ModuleException('', self::STATUS_ERROR_DELAYED_TRIGGER_OFF_DELAY_STEP_SIZE_INVALID);
            }

            // Check if the switch object is supported
            $tag = 'Switch Object Validation';
            /** @noinspection PhpUndefinedMethodInspection */
            $this->SetBuffer(self::SWITCH_OBJECT_DRIVER_BUFFER_NAME, '');
            $switchObjectId = $this->GetSwitchObjectId();
            if ($switchObjectId) {
                $this->Debug($tag, utf8_decode(IPS::GetLocation($switchObjectId)));
                // Listen for instance reloads
                $this->ListenForInstanceReloads($switchObjectId);
                try {
                    // Detect the driver
                    /** @var Driver $driver */
                    $driver = Drivers::AutoDetect($switchObjectId, 'Patami\\IPS\\Objects\\Drivers\\SetSwitchInterface');
                    $this->Debug($tag, sprintf('Driver: %s', $driver->GetName()));
                    // Remember the driver
                    /** @noinspection PhpUndefinedMethodInspection */
                    $this->SetBuffer(self::SWITCH_OBJECT_DRIVER_BUFFER_NAME, serialize($driver));
                } catch (InvalidObjectException $e) {
                    // No driver found
                    $this->Debug($tag, 'Object is not supported');
                    throw new ModuleException('', self::STATUS_ERROR_SWITCH_OBJECT_INVALID);
                }
            } else {
                $this->Debug($tag, 'Not configured');
            }

            // Initialize status variable position index
            $index = 0;

            // Add the automatic enabled variable
            VariableProfiles::CreateYesNo();
            /** @var Variable $variable */
            $variable = new BooleanVariable($this->RegisterVariableBooleanEx(self::VAR_AUTOMATIC_ENABLED, Translator::Get('patami.patamiautoswitch.vars.automatic_enabled'), VariableProfiles::TYPE_PATAMI_YESNO));
            $variable->SetPosition($index++)->SetIcon('Power');
            // Initialize the variable
            if (! $variable->IsUpdated()) {
                $variable->Set(true);
            }
            // Enable action
            /** @noinspection PhpUndefinedMethodInspection */
            $this->EnableAction(self::VAR_AUTOMATIC_ENABLED);

            // Define lambda function to add messages to the data structure
            $addMessage = function (&$messages, $objectId, $messageId) {
                $key = sprintf('%d_%d', $objectId, $messageId);
                $messages[$key] = array(
                    'objectId' => $objectId,
                    'messageId' => $messageId
                );
            };

            // Configure the trigger variable
            if ($triggerVariableId) {
                // Listen for variable updates
                $addMessage($messages, $triggerVariableId, VM_UPDATE);
            }

            // Configure the delayed trigger variable
            if ($delayedTriggerVariableId) {
                // Listen for variable updates
                $addMessage($messages, $delayedTriggerVariableId, VM_UPDATE);
            }

            // Configure the on delay variable and profile
            // Add the variable profile
            $onDelayProfileName = sprintf(self::VAR_PROFILE_DELAYED_TRIGGER_ON_DELAY_NAME_FORMAT, $this->GetId());
            $onDelayProfile = new IntegerVariableProfile($onDelayProfileName);
            $onDelayProfile->SetValues(
                $minOnDelay,
                $maxOnDelay,
                $onStepSize
            )->SetIcon(Icons::HOURGLASS)->SetSuffix(' s');
            // Add the delay variable
            $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_DELAYED_TRIGGER_ON_DELAY, Translator::Get('patami.patamiautoswitch.vars.on_delay'), $onDelayProfileName));
            $variable->SetPosition($index++);
            // Initialize the variable
            $onDelay = $variable->Get();
            if (! $variable->IsUpdated() || $onDelay < $minOnDelay || $onDelay > $maxOnDelay) {
                $variable->Set($minOnDelay);
            }
            // Enable action
            /** @noinspection PhpUndefinedMethodInspection */
            $this->EnableAction(self::VAR_DELAYED_TRIGGER_ON_DELAY);

            // Configure the off delay variable and profile
            // Add the variable profile
            $offDelayProfileName = sprintf(self::VAR_PROFILE_DELAYED_TRIGGER_OFF_DELAY_NAME_FORMAT, $this->GetId());
            $offDelayProfile = new IntegerVariableProfile($offDelayProfileName);
            $offDelayProfile->SetValues(
                $minOffDelay,
                $maxOffDelay,
                $offStepSize
            )->SetIcon(Icons::HOURGLASS)->SetSuffix(' s');;
            // Add the delay variable
            $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_DELAYED_TRIGGER_OFF_DELAY, Translator::Get('patami.patamiautoswitch.vars.off_delay'), $offDelayProfileName));
            $variable->SetPosition($index++);
            // Initialize the variable
            $offDelay = $variable->Get();
            if (! $variable->IsUpdated() || $offDelay < $minOffDelay || $offDelay > $maxOffDelay) {
                $variable->Set($minOffDelay);
            }
            // Enable action
            /** @noinspection PhpUndefinedMethodInspection */
            $this->EnableAction(self::VAR_DELAYED_TRIGGER_OFF_DELAY);

            // Configure the activity status variable and profile
            // Add the variable profile
            $transparent = new Color(Color::TRANSPARENT);
            $activityProfile = new IntegerVariableProfile(self::VAR_PROFILE_ACTIVITY_NAME);
            $activityProfile
                ->AddAssociationByValue(self::ACTIVITY_OFF, Translator::Get('patami.patamiautoswitch.vars.activity.off'), Icons::CLOSE, $transparent)
                ->AddAssociationByValue(self::ACTIVITY_WAITING_SWITCH_ON, Translator::Get('patami.patamiautoswitch.vars.activity.waiting_switch_on'), Icons::CLOCK, $transparent)
                ->AddAssociationByValue(self::ACTIVITY_ON, Translator::Get('patami.patamiautoswitch.vars.activity.on'), Icons::OK, $transparent)
                ->AddAssociationByValue(self::ACTIVITY_WAITING_SWITCH_OFF, Translator::Get('patami.patamiautoswitch.vars.activity.waiting_switch_off'), Icons::CLOCK, $transparent);
            // Add the activity status variable
            $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_ACTIVITY, Translator::Get('patami.patamiautoswitch.vars.activity'), self::VAR_PROFILE_ACTIVITY_NAME));
            $variable->SetPosition($index);
            // Initialize the variable
            if (! $variable->IsUpdated()) {
                $variable->Set(self::ACTIVITY_OFF);
            }

            // Add the switch status variable
            VariableProfiles::CreateOnOff();
            $variable = new BooleanVariable($this->RegisterVariableBooleanEx(self::VAR_SWITCH, Translator::Get('patami.patamiautoswitch.vars.switch'), VariableProfiles::TYPE_PATAMI_ONOFF));
            $variable->SetPosition($index);
            // Initialize the variable
            if (! $variable->IsUpdated()) {
                $variable->Set(false);
            }
            // Enable action
            /** @noinspection PhpUndefinedMethodInspection */
            $this->EnableAction(self::VAR_SWITCH);

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
     * Returns if automatic switching is enabled.
     * @return bool True if automatic switching is enabled.
     */
    public function IsAutomaticEnabled()
    {
        // Get the instance
        $instance = $this->GetInstance();

        // Get the status variable
        /** @var BooleanVariable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_AUTOMATIC_ENABLED);

        // Return the status
        return $variable->Get();
    }

    /**
     * Returns the IPS object ID of the trigger variable.
     * @return int IPS object ID of the trigger variable.
     */
    public function GetTriggerVariableId()
    {
        // Return the variable ID
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_TRIGGER_VARIABLE_ID);
    }

    /**
     * Returns the IPS object ID of the delayed trigger variable.
     * @return int IPS object ID of the delayed trigger variable.
     */
    public function GetDelayedTriggerVariableId()
    {
        // Return the variable ID
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_DELAYED_TRIGGER_VARIABLE_ID);
    }

    /**
     * Returns the switch on delay in seconds.
     * @return int Switch on delay in seconds.
     */
    public function GetDelayedTriggerOnDelay()
    {
        // Get the instance
        $instance = $this->GetInstance();

        // Get the status variable
        /** @var Variable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_DELAYED_TRIGGER_ON_DELAY);

        // Return the delay
        return $variable->Get();
    }

    /**
     * Returns the minimum switch on delay in seconds.
     * @return int Minimum switch on delay in seconds.
     */
    public function GetDelayedTriggerOnDelayMinimum()
    {
        // Return the minimum switch on delay
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_DELAYED_TRIGGER_ON_DELAY_MINIMUM);
    }

    /**
     * Returns the maximum switch on delay in seconds.
     * @return int Maximum switch on delay in seconds.
     */
    public function GetDelayedTriggerOnDelayMaximum()
    {
        // Return the maximum switch on delay
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_DELAYED_TRIGGER_ON_DELAY_MAXIMUM);
    }

    /**
     * Returns the switch on delay step size in seconds.
     * @return int Switch on delay step size in seconds.
     */
    public function GetDelayedTriggerOnDelayStepSize()
    {
        // Return the switch on delay step size
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_DELAYED_TRIGGER_ON_DELAY_STEP_SIZE);
    }

    /**
     * Returns the switch off delay in seconds.
     * @return int Switch off delay in seconds.
     */
    public function GetDelayedTriggerOffDelay()
    {
        // Get the instance
        $instance = $this->GetInstance();

        // Get the status variable
        /** @var Variable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_DELAYED_TRIGGER_OFF_DELAY);

        // Return the delay
        return $variable->Get();
    }

    /**
     * Returns the minimum switch off delay in seconds.
     * @return int Minimum switch off delay in seconds.
     */
    public function GetDelayedTriggerOffDelayMinimum()
    {
        // Return the minimum switch off delay
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_DELAYED_TRIGGER_OFF_DELAY_MINIMUM);
    }

    /**
     * Returns the maximum switch off delay in seconds.
     * @return int Maximum switch off delay in seconds.
     */
    public function GetDelayedTriggerOffDelayMaximum()
    {
        // Return the maximum switch off delay
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_DELAYED_TRIGGER_OFF_DELAY_MAXIMUM);
    }

    /**
     * Returns the switch off delay step size in seconds.
     * @return int Switch off delay step size in seconds.
     */
    public function GetDelayedTriggerOffDelayStepSize()
    {
        // Return the switch off delay step size
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_DELAYED_TRIGGER_OFF_DELAY_STEP_SIZE);
    }

    /**
     * Returns the IPS object ID of the switch object.
     * @return int IPS object ID of the switch object.
     */
    public function GetSwitchObjectId()
    {
        // Return the object ID
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_SWITCH_OBJECT_ID);
    }

    /**
     * Returns the current activity of the instance.
     * @return int Current activity of the instance.
     */
    public function GetActivity()
    {
        // Get the instance
        $instance = $this->GetInstance();

        // Get the activity variable
        /** @var IntegerVariable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_ACTIVITY);

        // Return the switch status
        return $variable->Get();
    }

    /**
     * Sets the activity of the instance.
     * @param int $activity New activity of the instance.
     */
    protected function SetActivity($activity)
    {
        switch ($activity) {
            case self::ACTIVITY_OFF:
                // Switch off
                $this->SetSwitch(false);
                break;
            case self::ACTIVITY_WAITING_SWITCH_ON:
                // Start the timer to switch on
                $this->StartOnTimer();
                break;
            case self::ACTIVITY_ON:
                // Switch off
                $this->SetSwitch(true);
                break;
            case self::ACTIVITY_WAITING_SWITCH_OFF:
                // Start the timer to switch off
                $this->StartOffTimer();
                break;
            default:
                $this->Debug('Set Activity', sprintf('Unknown activity %d', $activity));
                return;
        }
    }

    /**
     * Checks if the switch is on.
     * @return bool True if the switch is on.
     */
    public function IsSwitchOn()
    {
        // Get the instance
        $instance = $this->GetInstance();

        // Get the status variable
        /** @var BooleanVariable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_SWITCH);

        // Return the switch status
        return $variable->Get();
    }

    /**
     * Switches on immediately.
     * @return bool True if switching was successful, false if not.
     */
    public function SetSwitchOn()
    {
        // Switch on
        return $this->SetSwitch(true);
    }

    /**
     * Switches off immediately.
     * @return bool True if switching was successful, false if not.
     */
    public function SetSwitchOff()
    {
        // Switch off
        return $this->SetSwitch(false);
    }

    /**
     * Switches on or off immediately.
     * @param bool $value True to switch on, false to switch off.
     * @return bool True if switching was successful, false if not.
     */
    public function SetSwitch($value)
    {
        // Determine values
        if ($value) {
            $tag = 'Switch On';
            $activity = self::ACTIVITY_ON;
        } else {
            $tag = 'Switch Off';
            $activity = self::ACTIVITY_OFF;
        }

        // Disable the timer
        $this->StopTimer();

        // Return if the instance is invalid
        if (! $this->IsValid()) {
            $this->Debug($tag, 'Instance is invalid, skipping');
            return false;
        }

        // Set the activity
        $instance = $this->GetInstance();
        /** @var IntegerVariable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_ACTIVITY);
        $variable->Set($activity);

        // Return if there is no need to switch
        if ($value == $this->IsSwitchOn()) {
            $this->Debug($tag, 'Already at the desired state, skipping');
            return true;
        }

        // Set the value
        $this->Debug($tag, 'Setting status variable');
        /** @var BooleanVariable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_SWITCH);
        $variable->Set($value);

        // Switch the switch object
        /** @var SetSwitchInterface $driver */
        /** @noinspection PhpUndefinedMethodInspection */
        $driver = @unserialize($this->GetBuffer(self::SWITCH_OBJECT_DRIVER_BUFFER_NAME));
        if ($driver instanceof SetSwitchInterface) {
            $this->Debug($tag, 'Switching object');
            $driver->SetSwitch($value);
        }

        // Return the result
        return true;
    }

    /**
     * Processes variable actions.
     * @param string $ident Ident of the status variable.
     * @param mixed $value New value of the status variable.
     */
    public function RequestAction($ident, $value)
    {
        switch ($ident) {
            case self::VAR_AUTOMATIC_ENABLED:
                $this->OnAutomaticEnabledAction($value);
                break;
            case self::VAR_DELAYED_TRIGGER_ON_DELAY:
                $this->OnDelayedTriggerOnDelayAction($value);
                break;
            case self::VAR_DELAYED_TRIGGER_OFF_DELAY:
                $this->OnDelayedTriggerOffDelayAction($value);
                break;
            case self::VAR_SWITCH:
                $this->SetSwitch($value);
                break;
            default:
                $this->Debug('Variable Action', sprintf('Unexpected action for variable %s (value %s)', $ident, $value));
        }
    }

    /**
     * Processes actions of the automatic switching status variable.
     * @param bool $value New value of the automatic switching status variable.
     */
    protected function OnAutomaticEnabledAction($value)
    {
        $tag = 'Automatic Switching';

        // Set the variable value
        $instance = $this->GetInstance();
        /** @var BooleanVariable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_AUTOMATIC_ENABLED);
        $variable->Set($value);

        // Perform action based on the new value
        if ($value) {
            // Enabled automatic switching
            $this->Debug($tag, 'Enabled');
        } else {
            // Disabled automatic switching
            $this->Debug($tag, 'Disabled');
            // Change activity is necessary
            $activity = $this->GetActivity();
            if ($activity == self::ACTIVITY_WAITING_SWITCH_ON) {
                $this->Debug($tag, 'Waiting to switch on, switching off again');
                $this->SetActivity(self::ACTIVITY_OFF);
            } elseif ($activity == self::ACTIVITY_WAITING_SWITCH_OFF) {
                $this->Debug($tag, 'Waiting to switch off, switching on again');
                $this->SetActivity(self::ACTIVITY_ON);
            }
        }
    }

    /**
     * Processes actions of the on delay status variable.
     * @param int $value New value of the on delay status variable.
     */
    protected function OnDelayedTriggerOnDelayAction($value)
    {
        $tag = 'Delayed Trigger On Delay';

        // Set the variable value
        $instance = $this->GetInstance();
        /** @var IntegerVariable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_DELAYED_TRIGGER_ON_DELAY);
        $variable->Set($value);

        // Log the new value
        $this->Debug($tag, sprintf('New delay is %d seconds', $value));

        // Restart the timer if necessary
        $activity = $this->GetActivity();
        if ($activity == self::ACTIVITY_WAITING_SWITCH_ON) {
            $this->Debug($tag, 'Waiting to switch on, restarting timer');
            $this->StartOnTimer();
        }
    }

    /**
     * Processes actions of the off delay status variable.
     * @param int $value New value of the off delay status variable.
     */
    protected function OnDelayedTriggerOffDelayAction($value)
    {
        $tag = 'Delayed Trigger Off Delay';

        // Set the variable value
        $instance = $this->GetInstance();
        /** @var IntegerVariable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_DELAYED_TRIGGER_OFF_DELAY);
        $variable->Set($value);

        // Log the new value
        $this->Debug($tag, sprintf('New delay is %d seconds', $value));

        // Restart the timer if necessary
        $activity = $this->GetActivity();
        if ($activity == self::ACTIVITY_WAITING_SWITCH_OFF) {
            $this->Debug($tag, 'Waiting to switch off, restarting timer');
            $this->StartOffTimer();
        }
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

        // Check if the trigger variable was updated
        if ($objectId == $this->GetTriggerVariableId()) {
            $this->Debug($objectId, 'Trigger variable');
            $this->OnTrigger($newValue);
        }

        // Check if the delayed trigger variable was updated
        if ($objectId == $this->GetDelayedTriggerVariableId()) {
            $this->Debug($objectId, 'Delayed trigger variable');
            $this->OnDelayedTrigger($newValue);
        }
    }

    /**
     * Event handler called when the trigger variable is updated.
     * @param bool $value New value of the variable.
     */
    protected function OnTrigger($value)
    {
        $tag = 'Trigger';

        // Do nothing if automatic switching is disabled
        if (! $this->IsAutomaticEnabled()) {
            $this->Debug($tag, 'Automatic switching is disabled, skipping');
            return;
        }

        // Get the activity
        $activity = $this->GetActivity();

        if ($value) {
            if ($activity == self::ACTIVITY_ON) {
                // Switch is already on
                $this->Debug($tag, 'Switch is already on, skipping');
            } else {
                // Switch on immediately
                $this->SetActivity(self::ACTIVITY_ON);
            }
        } else {
            if ($activity == self::ACTIVITY_OFF) {
                // Switch is already off
                $this->Debug($tag, 'Switch is already off, skipping');
            } else {
                // Switch on immediately
                $this->SetActivity(self::ACTIVITY_OFF);
            }
        }
    }

    /**
     * Event handler called when the delayed trigger variable is updated.
     * @param bool $value New value of the variable.
     */
    protected function OnDelayedTrigger($value)
    {
        $tag = 'Delayed Trigger';

        // Do nothing if automatic switching is disabled
        if (! $this->IsAutomaticEnabled()) {
            $this->Debug($tag, 'Automatic switching is disabled, skipping');
            return;
        }

        // Get the activity
        $activity = $this->GetActivity();

        if ($value) {
            if ($activity == self::ACTIVITY_ON) {
                // Switch is already on
                $this->Debug($tag, 'Switch is already on, skipping');
            } elseif ($activity == self::ACTIVITY_WAITING_SWITCH_ON) {
                // Already waiting to switch on
                $this->Debug($tag, 'Already waiting to switch on, skipping');
            } elseif ($activity == self::ACTIVITY_WAITING_SWITCH_OFF) {
                // Already waiting to switch on
                $this->Debug($tag, 'Waiting to switch off, switching on again');
                $this->SetActivity(self::ACTIVITY_ON);
            } else {
                // Start switch on timer
                $this->SetActivity(self::ACTIVITY_WAITING_SWITCH_ON);
            }
        } else {
            if ($activity == self::ACTIVITY_OFF) {
                // Switch is already off
                $this->Debug($tag, 'Switch is already off, skipping');
            } elseif ($activity == self::ACTIVITY_WAITING_SWITCH_OFF) {
                // Already waiting to switch off
                $this->Debug($tag, 'Already waiting to switch off, skipping');
            } elseif ($activity == self::ACTIVITY_WAITING_SWITCH_ON) {
                // Already waiting to switch off
                $this->Debug($tag, 'Waiting to switch on, switching off again');
                $this->SetActivity(self::ACTIVITY_OFF);
            } else {
                // Start switch off timer
                $this->SetActivity(self::ACTIVITY_WAITING_SWITCH_OFF);
            }
        }
    }

    /**
     * Starts the on delay timer.
     */
    protected function StartOnTimer()
    {
        // Get the delay
        $delay = $this->GetDelayedTriggerOnDelay();
        $this->Debug('Switch On Timer', sprintf('Starting %d second timer', $delay));

        // Switch on and return if the delay is 0
        if ($delay == 0) {
            $this->SetSwitch(true);
            return;
        }

        // Set the activity
        $instance = $this->GetInstance();
        /** @var IntegerVariable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_ACTIVITY);
        $variable->Set(self::ACTIVITY_WAITING_SWITCH_ON);

        // Set the timer interval
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SetTimerInterval(self::TIMER_NAME, $delay * 1000);
    }

    /**
     * Starts the off delay timer.
     */
    protected function StartOffTimer()
    {
        // Get the delay
        $delay = $this->GetDelayedTriggerOffDelay();
        $this->Debug('Switch Off Timer', sprintf('Starting %d second timer', $delay));

        // Switch off and return if the delay is 0
        if ($delay == 0) {
            $this->SetSwitch(false);
            return;
        }

        // Set the activity
        $instance = $this->GetInstance();
        /** @var IntegerVariable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_ACTIVITY);
        $variable->Set(self::ACTIVITY_WAITING_SWITCH_OFF);

        // Set the timer interval
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SetTimerInterval(self::TIMER_NAME, $delay * 1000);
    }

    /**
     * Stops the switch on delay timer.
     */
    protected function StopTimer()
    {
        // Get the activity
        $activity = $this->GetActivity();

        // Determine the tag
        switch ($activity) {
            case self::ACTIVITY_WAITING_SWITCH_ON:
                $tag = 'Switch On Timer';
                break;
            case self::ACTIVITY_WAITING_SWITCH_OFF:
                $tag = 'Switch Off Timer';
                break;
            default:
                $tag = 'Timer';
        }

        // Disable the timer
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SetTimerInterval(self::TIMER_NAME, 0);

        // Log that the timer was stopped
        $this->Debug($tag, 'Timer stopped');
    }

    /**
     * Event handler called when the switch on delay timer expired.
     * @internal
     */
    public function OnTimerElapsed()
    {
        // Get the activity
        $activity = $this->GetActivity();

        // Perform action
        switch ($activity) {
            case self::ACTIVITY_WAITING_SWITCH_ON:
                $this->Debug('Switch On Timer', 'Timer elapsed, switching on');
                $this->SetActivity(self::ACTIVITY_ON);
                break;
            case self::ACTIVITY_WAITING_SWITCH_OFF:
                $this->Debug('Switch Off Timer', 'Timer elapsed, switching off');
                $this->SetActivity(self::ACTIVITY_OFF);
                break;
            default:
                $this->Debug('Timer', sprintf('Unknown timer action for activity %d', $activity));
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
        return 'https://docs.braintower.de/display/IPSPATAMI/Patami+Auto+Switch+Modul';
    }

    protected function GetReleaseNotesUrl()
    {
        return 'https://docs.braintower.de/display/IPSPATAMI/Versionshinweise';
    }

}