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
use Patami\IPS\Objects\Drivers\Drivers;
use Patami\IPS\Objects\Drivers\Driver;
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\Drivers\GetSwitchInterface;
use Patami\IPS\Objects\Drivers\SetSwitchInterface;
use Patami\IPS\Objects\Drivers\GetDimLevelInterface;
use Patami\IPS\Objects\Drivers\SetDimLevelInterface;
use Patami\IPS\Objects\IPSObject;
use Patami\IPS\Objects\Variable;
use Patami\IPS\Objects\IntegerVariable;
use Patami\IPS\Objects\BooleanVariable;
use Patami\IPS\Objects\VariableProfiles;
use Patami\IPS\System\Semaphore;
use Patami\IPS\I18N\Translator;
use Patami\IPS\System\IPS;
use Patami\IPS\System\Color;


Translator::AddDir(__DIR__ . DIRECTORY_SEPARATOR . 'translations');


/**
 * PatamiSplitRGB IPS Module.
 * The GUID of this module is {42A59F8E-2797-4589-B55D-C81D93B7BB79}.
 * @package IPSPATAMI
 */
class PatamiSplitRGB extends Module
{

    /** No object for the red channel is specified. */
    const STATUS_ERROR_NO_RED_OBJECT = 201;
    /** The object for the red channel is invalid. */
    const STATUS_ERROR_INVALID_RED_OBJECT = 202;

    /** No object for the green channel is specified. */
    const STATUS_ERROR_NO_GREEN_OBJECT = 203;
    /** The object for the green channel is invalid. */
    const STATUS_ERROR_INVALID_GREEN_OBJECT = 204;

    /** No object for the blue channel is specified. */
    const STATUS_ERROR_NO_BLUE_OBJECT = 205;
    /** The object for the blue channel is invalid. */
    const STATUS_ERROR_INVALID_BLUE_OBJECT = 206;

    /** Red channel object. */
    const PROPERTY_RED_OBJECT_ID = 'RedObjectID';
    /** Green channel object. */
    const PROPERTY_GREEN_OBJECT_ID = 'GreenObjectID';
    /** Blue channel object. */
    const PROPERTY_BLUE_OBJECT_ID = 'BlueObjectID';

    /** Ident of the status status variable. */
    const VAR_STATUS = 'Status';
    /** Ident of the color status variable. */
    const VAR_COLOR = 'Color';

    /** Name of the IPS buffer for color channel name drivers. */
    const DRIVERS_BUFFER_NAME = 'drivers';
    /** Name of the IPS object buffer for the registered messages. */
    const REGISTERED_MESSAGES_BUFFER_NAME = 'registered_messages';

    public function Create()
    {
        // Call the parent method
        parent::Create();

        // Add properties
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_RED_OBJECT_ID, 0);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_GREEN_OBJECT_ID, 0);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_BLUE_OBJECT_ID, 0);
    }

    protected function GetConfigurationFormData()
    {
        // Call the parent method
        $data = parent::GetConfigurationFormData();

        // Add the channel objects
        array_push($data['elements'],
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.patamisplitrgb.form.objects.label')
            ),
            array(
                'type' => 'SelectObject',
                'name' => self::PROPERTY_RED_OBJECT_ID,
                'caption' => Translator::Get('patami.patamisplitrgb.form.red_object.label')
            ),
            array(
                'type' => 'SelectObject',
                'name' => self::PROPERTY_GREEN_OBJECT_ID,
                'caption' => Translator::Get('patami.patamisplitrgb.form.green_object.label')
            ),
            array(
                'type' => 'SelectObject',
                'name' => self::PROPERTY_BLUE_OBJECT_ID,
                'caption' => Translator::Get('patami.patamisplitrgb.form.blue_object.label')
            )
        );

        // Add the action buttons
        array_unshift($data['actions'],
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.patamisplitrgb.form.actions.color.label')
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.patamisplitrgb.form.actions.color.off_button'),
                'onClick' => sprintf('PSRGB_SetColor($id, %s);', Color::BLACK)
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.patamisplitrgb.form.actions.color.white_button'),
                'onClick' => sprintf('PSRGB_SetColor($id, %s);', Color::WHITE)
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.patamisplitrgb.form.actions.color.red_button'),
                'onClick' => sprintf('PSRGB_SetColor($id, %s);', Color::RED)
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.patamisplitrgb.form.actions.color.green_button'),
                'onClick' => sprintf('PSRGB_SetColor($id, %s);', Color::GREEN)
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.patamisplitrgb.form.actions.color.blue_button'),
                'onClick' => sprintf('PSRGB_SetColor($id, %s);', Color::BLUE)
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.patamisplitrgb.form.actions.color.yellow_button'),
                'onClick' => sprintf('PSRGB_SetColor($id, %s);', Color::YELLOW)
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.patamisplitrgb.form.actions.color.magenta_button'),
                'onClick' => sprintf('PSRGB_SetColor($id, %s);', Color::MAGENTA)
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.patamisplitrgb.form.actions.color.cyan_button'),
                'onClick' => sprintf('PSRGB_SetColor($id, %s);', Color::CYAN)
            ),
            array(
                'type' => 'Label',
                'label' => ''
            ),
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.patamisplitrgb.form.actions.color.color_label')
            ),
            array(
                'type' => 'SelectColor',
                'name' => 'color',
                'caption' => Translator::Get('patami.patamisplitrgb.form.actions.color.color_caption')
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.patamisplitrgb.form.actions.color.color_button'),
                'onClick' => 'PSRGB_SetColor($id, $color);'
            )
        );

        // Add status messages
        array_push($data['status'],
            array(
                'code' => self::STATUS_ERROR_NO_RED_OBJECT,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamisplitrgb.form.status.no_red_object')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_RED_OBJECT,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamisplitrgb.form.status.invalid_red_object')
            ),
            array(
                'code' => self::STATUS_ERROR_NO_GREEN_OBJECT,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamisplitrgb.form.status.no_green_object')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_GREEN_OBJECT,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamisplitrgb.form.status.invalid_green_object')
            ),
            array(
                'code' => self::STATUS_ERROR_NO_BLUE_OBJECT,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamisplitrgb.form.status.no_blue_object')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_BLUE_OBJECT,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamisplitrgb.form.status.invalid_blue_object')
            )
        );

        return $data;
    }

    protected function Configure()
    {
        // Add status variables
        $index = 0;
        $variable = new BooleanVariable($this->RegisterVariableBooleanEx(self::VAR_STATUS, Translator::Get('patami.patamisplitrgb.vars.status'), VariableProfiles::TYPE_SWITCH));
        $variable->SetPosition($index++);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->EnableAction(self::VAR_STATUS);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_COLOR, Translator::Get('patami.patamisplitrgb.vars.color'), VariableProfiles::TYPE_HEX_COLOR));
        $variable->SetPosition($index);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->EnableAction(self::VAR_COLOR);

        // Initialize data structures
        $drivers = array();
        $messages = array();

        try {

            // Detect the drivers
            $drivers = array(
                'red' => $this->GetDriver(
                    $this->GetRedObjectId(),
                    'red',
                    self::STATUS_ERROR_NO_RED_OBJECT,
                    self::STATUS_ERROR_INVALID_RED_OBJECT
                ),
                'green' => $this->GetDriver(
                    $this->GetGreenObjectId(),
                    'green',
                    self::STATUS_ERROR_NO_GREEN_OBJECT,
                    self::STATUS_ERROR_INVALID_GREEN_OBJECT
                ),
                'blue' => $this->GetDriver(
                    $this->GetBlueObjectId(),
                    'blue',
                    self::STATUS_ERROR_NO_BLUE_OBJECT,
                    self::STATUS_ERROR_INVALID_BLUE_OBJECT
                )
            );

            // Define lambda function to add messages to the data structure
            $addMessage = function (&$messages, $objectId, $messageId) {
                $key = sprintf('%d_%d', $objectId, $messageId);
                $messages[$key] = array(
                    'objectId' => $objectId,
                    'messageId' => $messageId
                );
            };

            // Loop through the drivers to add messages
            /** @var Driver $driver */
            foreach ($drivers as $channelName => $driver) {
                $objectId = $driver->GetObject()->GetId();
                // Register message to get notified if the object is being deleted
                $addMessage($messages, $objectId, OM_UNREGISTER);
                // Get the monitored objects
                $monitoredObjects = $driver->GetMonitoredObjects();
                /** @var IPSObject $monitoredObject */
                // Loop through the monitored objects
                foreach ($monitoredObjects as $monitoredObject) {
                    $monitoredObjectId = $monitoredObject->GetId();
                    // Log information about the monitored object
                    $this->Debug($objectId, sprintf(
                        'Monitored object: %d (%s)',
                        $monitoredObjectId,
                        utf8_decode($monitoredObject->GetLocation())
                    ));
                    // Register message to get notified if the monitored object changes
                    if ($monitoredObject instanceof Variable) {
                        // Listen for variable updates
                        $addMessage($messages, $monitoredObjectId, VM_UPDATE);
                    } else {
                        // We don't know how to handle this object
                        $this->Debug($objectId, sprintf('Cannot monitor %d (not supported)', $monitoredObjectId));
                    }
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

                // Remember the the drivers
                /** @noinspection PhpUndefinedMethodInspection */
                $this->SetBuffer(self::DRIVERS_BUFFER_NAME, serialize($drivers));

                // Remember the registered messages
                /** @noinspection PhpUndefinedMethodInspection */
                $this->SetBuffer(self::REGISTERED_MESSAGES_BUFFER_NAME, json_encode($messages));

            } finally {

                // Leave the semaphore
                $semaphore->Leave();

                // Update the status variables
                $this->UpdateStatus();

            }

        }

    }

    /**
     * Detects and returns the driver for the color channel object.
     * @param int $objectId IPS object ID of the color channel object.
     * @param string $name Name of the color channel.
     * @param int $noObjectError Status code if no object is configured.
     * @param int $invalidObjectError Status code if the object is invalid (no driver found).
     * @return Driver Driver for the color channel object.
     * @throws ModuleException if no object is configured or the driver for the object could not be found.
     */
    protected function GetDriver($objectId, $name, $noObjectError, $invalidObjectError)
    {
        $interfaceNames = array(
            'Patami\\IPS\\Objects\\Drivers\\GetSwitchInterface',
            'Patami\\IPS\\Objects\\Drivers\\GetDimLevelInterface',
            'Patami\\IPS\\Objects\\Drivers\\SetSwitchInterface',
            'Patami\\IPS\\Objects\\Drivers\\SetDimLevelInterface'
        );

        // Log the channel name
        $this->Debug($objectId, sprintf('%s color channel', ucfirst($name)));

        // Check if the object is configured
        if ($objectId == 0) {
            $this->Debug($objectId, 'No object configured');
            throw new ModuleException('', $noObjectError);
        }

        // Listen for instance reloads
        $this->ListenForInstanceReloads($objectId);

        try {
            // Detect the driver
            /** @var Driver $driver */
            $driver = Drivers::AutoDetect($objectId, $interfaceNames);
            // Log information about the object
            $this->Debug($objectId, sprintf('Name: %s', utf8_decode(IPS::GetLocation($objectId))));
            $this->Debug($objectId, sprintf('Driver: %s', $driver->GetName()));
            // Return the driver
            return $driver;
        } catch (InvalidObjectException $e) {
            $this->Debug($objectId, 'No matching driver found');
            throw new ModuleException('', $invalidObjectError);
        }
    }

    /**
     * Returns the IPS object ID of the red channel object.
     * @return int IPS object ID of the red channel object.
     */
    public function GetRedObjectId()
    {
        // Return the ID of the red channel object
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_RED_OBJECT_ID);
    }

    /**
     * Returns the IPS object ID of the green channel object.
     * @return int IPS object ID of the green channel object.
     */
    public function GetGreenObjectId()
    {
        // Return the ID of the green channel object
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_GREEN_OBJECT_ID);
    }

    /**
     * Returns the IPS object ID of the blue channel object.
     * @return int IPS object ID of the blue channel object.
     */
    public function GetBlueObjectId()
    {
        // Return the ID of the blue channel object
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_BLUE_OBJECT_ID);
    }

    /**
     * Returns the cached driver objects.
     * @return array|false Driver objects.
     */
    protected function GetCachedDrivers()
    {
        // Load the drivers
        /** @noinspection PhpUndefinedMethodInspection */
        $data = $this->GetBuffer(self::DRIVERS_BUFFER_NAME);
        $drivers = @unserialize($data);

        // Return false if the drivers cache is invalid
        if (! isset($drivers['red']) || ! isset($drivers['green']) || ! isset($drivers['red'])) {
            return false;
        }

        // Return the drivers
        return $drivers;
    }

    /**
     * Returns the status of the RGB lamp.
     * @return bool Status of the RGB lamp.
     */
    public function GetSwitch()
    {
        // Get and return the status
        $instance = $this->GetInstance();
        /** @var BooleanVariable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_STATUS);
        return $variable->Get();
    }

    /**
     * Sets the status of the RGB lamp.
     * @param bool $status New status. True to switch on, false to switch off.
     */
    public function SetSwitch($status)
    {
        // Load the drivers
        $drivers = $this->GetCachedDrivers();

        // Set the status of the color channels if the instance is valid
        if ($this->IsValid() && $drivers !== false) {

            // Log the action
            $this->Debug('Set Status', $status);

            // Set the status
            /** @var SetSwitchInterface $driver */
            foreach ($drivers as $driver) {
                $driver->SetSwitch($status);
            }

        }
    }

    /**
     * Returns the color of the RGB lamp.
     * @return int Color of the RGB lamp.
     */
    public function GetColor()
    {
        // Get and return the color
        $instance = $this->GetInstance();
        /** @var IntegerVariable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_COLOR);
        return $variable->Get();
    }

    /**
     * Sets the new color of the instance.
     * @param int|Color $color New color.
     */
    public function SetColor($color)
    {
        // Load the drivers
        $drivers = $this->GetCachedDrivers();

        // Set the color of the channels if the instance is valid
        if ($this->IsValid() && $drivers !== false) {

            // Get the colors
            $color = new Color($color);
            $channels = $color->GetRGB();

            // Log the action
            $this->Debug('Set Color', $color->GetHex());

            // Set the colors
            /** @var SetDimLevelInterface $driver */
            foreach ($drivers as $driver) {
                $driver->SetDimLevel(array_shift($channels) / 0xFF);
            }

        }
    }

    /**
     * Processes variable updates.
     * @param string $ident Ident of the status variable.
     * @param mixed $value New value of the status variable.
     */
    public function RequestAction($ident, $value)
    {
        switch ($ident) {
            case self::VAR_STATUS:
                // Status variable
                $this->OnStatusAction($value);
                break;
            case self::VAR_COLOR:
                // Color variable
                $this->OnColorAction($value);
                break;
            default:
                $this->Debug('Variable Action', sprintf('Unexpected action for variable %s (value %s)', $ident, $value));
        }
    }

    /**
     * Processes an action of the status status variable.
     * @param bool $value New status value.
     */
    protected function OnStatusAction($value)
    {
        // Set the status
        $this->SetSwitch($value);
    }

    /**
     * Processes an action of the color status variable.
     * @param int $value New color value.
     */
    protected function OnColorAction($value)
    {
        // Set the color
        $this->SetColor($value);
    }

    protected function OnMessage($timestamp, $senderId, $messageId, $data)
    {
        switch ($messageId) {
            case OM_UNREGISTER:
                // An object was deleted
                $this->OnObjectDeleted($senderId);
                break;
            case VM_UPDATE:
                // A monitored variable was updated
                $newValue = @$data[0];
                $oldValue = @$data[2];
                if ($newValue != $oldValue) {
                    // Only process the message if the variable actually changed
                    $this->OnVariableChanged($senderId, $newValue, $oldValue);
                }
                break;
            default:
                // Unexpected message received
                $this->Debug($senderId, sprintf('Unexpected message %d', $messageId));
        }
    }

    /**
     * Event handler called when objects are deleted.
     * @param int $objectId IPS object ID of the object that that triggered the message.
     */
    protected function OnObjectDeleted($objectId)
    {
        // Log the event
        $this->Debug($objectId, 'Object deleted');

        // Apply the changes to trigger error message
        IPS::ApplyChanges($this->GetId());
    }

    /**
     * Event handler called when variables are changed.
     * @param int $objectId IPS object ID of the object that that triggered the message.
     * @param mixed $newValue New variable value.
     * @param mixed $oldValue Old variable value.
     */
    protected function OnVariableChanged($objectId, $newValue, $oldValue)
    {
        // Log the event
        $this->Debug($objectId, sprintf('Monitored variable changed (%s => %s)', serialize($oldValue), serialize($newValue)));

        // Update the status variables
        $this->UpdateStatus();
    }

    /**
     * Updates the status variables.
     */
    protected function UpdateStatus()
    {
        // Set default values
        $status = false;
        $color = new Color(Color::BLACK);

        // Load the drivers
        $drivers = $this->GetCachedDrivers();

        // Determine the status if the instance is valid
        if ($this->IsValid() && $drivers !== false) {

            // Determine the status
            /** @var GetSwitchInterface $driver */
            foreach ($drivers as $driver) {
                $status |= $driver->GetSwitch();
            }
            $this->Debug('New Status', $status);

            // Determine the color
            /** @var GetDimLevelInterface $driver */
            $driver = $drivers['red'];
            $redValue = intval($driver->GetDimLevel() * 0xFF);
            $this->Debug('Red', $redValue);
            $driver = $drivers['green'];
            $greenValue = intval($driver->GetDimLevel() * 0xFF);
            $this->Debug('Green', $greenValue);
            $driver = $drivers['blue'];
            $blueValue = intval($driver->GetDimLevel() * 0xFF);
            $this->Debug('Blue', $blueValue);
            $color->SetRGB($redValue, $greenValue, $blueValue);
            $this->Debug('New Color', $color->GetHex());
        }

        // Update the variables
        $instance = $this->GetInstance();
        /** @var Variable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_STATUS);
        $variable->Set($status);
        $variable = $instance->GetChildByIdent(self::VAR_COLOR);
        $variable->Set($color->Get());
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
        return 'https://docs.braintower.de/display/IPSPATAMI/Patami+Split+RGB+Modul';
    }

    protected function GetReleaseNotesUrl()
    {
        return 'https://docs.braintower.de/display/IPSPATAMI/Versionshinweise';
    }

}