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


namespace Patami\IPS\Modules;


use Patami\IPS\Framework;
use Patami\IPS\Helpers\StringHelper;
use Patami\IPS\I18N\Translator;
use Patami\IPS\Libraries\Library;
use Patami\IPS\Objects\Instance;
use Patami\IPS\System\IPSConnect;
use Patami\IPS\System\IPSLive;
use Patami\IPS\System\Logging\DebugInterface;
use Patami\IPS\System\IPS;
use Patami\IPS\System\System;


/** @noinspection PhpUndefinedClassInspection */
/**
 * Abstract base class for IPS modules.
 *
 * This class extends the functionality provided by IP Symcon's IPSModule class and should be used as the base class
 * for all modules created with the Patami framework.
 *
 * @see SingletonModule for modules that must not have more than one instance.
 * @see https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/
 *
 * @package IPSPATAMI
 */
abstract class Module extends \IPSModule implements DebugInterface
{

    /** Instance is being created. */
    const STATUS_INSTANCE_CREATING    = 101;
    /** Instance is active and configuration is valid. */
    const STATUS_INSTANCE_ACTIVE      = 102;
    /** Instance is being deleted. */
    const STATUS_INSTANCE_DELETING    = 103;
    /** Instance is inactive (ie. disabled). */
    const STATUS_INSTANCE_INACTIVE    = 104;

    /** Instance is not yet created. */
    const STATUS_INSTANCE_NOT_CREATED = 201;

    /** Use plain text for debug message. */
    const DEBUG_TEXT = 0;
    /** Use hex for debug message. */
    const DEBUG_HEX  = 1;

    /**
     * @var Library IPS library object instance to which this module belongs to.
     */
    protected $library;

    /**
     * Module constructor.
     * Creates a new object of the IPS module instance.
     * This method is automatically called by IPS when one of the registered functions are called or when the instance
     * is used by IPS in any other way (eg. when creating the instance or when opening the configuration form).
     * @param int $instanceId IPS object ID of the instance.
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/construct/
     */
    public function __construct($instanceId)
    {
        // Call the parent constructor
        /** @noinspection PhpUndefinedClassInspection */
        parent::__construct($instanceId);

        // Create the Library object
        $this->library = Library::Create($this->GetLibraryName());
    }

    /**
     * Registers configuration properties and timers.
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/create/
     */
    public function Create()
    {
        // Call the parent method
        /** @noinspection PhpUndefinedClassInspection */
        parent::Create();
    }

    /**
     * Performs cleanup tasks when the instance is deleted.
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/destroy/
     */
    public function Destroy()
    {
        // Call the parent method
        /** @noinspection PhpUndefinedClassInspection */
        parent::Destroy();
    }

    /**
     * Returns the file name of the class.
     * It always returns the name of the called class.
     * @return string File name of the class.
     */
    protected function GetClassFileName()
    {
        // Get the file name of the class
        $reflectionClass = new \ReflectionClass(get_called_class());
        $fileName = $reflectionClass->getFileName();

        // Return the file name
        return $fileName;
    }

    /**
     * Returns the directory name of the module class file.
     * @return string Directory name of the class.
     */
    protected function GetModuleDirectory()
    {
        // Get the file name of the class
        $fileName = $this->GetClassFileName();

        // Return the directory name of the class
        return dirname($fileName);
    }

    protected function GetModuleInfoFileName()
    {
        // Get the directory name of the module class
        $dirName = $this->GetModuleDirectory();

        // Return the file name of the module info file
        return $dirName . DIRECTORY_SEPARATOR . 'module.json';
    }

    /**
     * Returns the key of the library to which the module belongs to.
     * @return string Key of the library.
     */
    protected function GetLibraryName()
    {
        // Get the file name of the class
        $fileName = $this->GetClassFileName();

        // Strip off the modules base path
        $basePath = IPS::GetModulesDir();
        $fileName = str_replace($basePath, '', $fileName);

        // Split the path components
        $components = explode(DIRECTORY_SEPARATOR, $fileName);

        // Return the first component
        return $components[0];
    }

    /**
     * Returns the IPS module configuration form data.
     * This convenience method returns an array with the configuration form data, while IPS' default method
     * Module::GetConfigurationForm() is expected to return an JSON-encoded array.
     * An implementation of this method in concrete module classes should call the parent method and then add their
     * own form fields to the data structure returned by the base method.
     * Also note that the form data may also contain translations, which is not mentioned in the IPS documentation.
     * @return array IPS module configuration form data.
     * @see Module::GetConfigurationForm()
     */
    protected function GetConfigurationFormData()
    {
        // Return the default configuration form data
        return array(
            'elements' => array(),
            'actions' => array(),
            'status' => array(
                array(
                    'code' => self::STATUS_INSTANCE_CREATING,
                    'icon' => 'inactive',
                    'caption' => Translator::Get('patami.framework.module.form.status.instance_creating')
                ),
                array(
                    'code' => self::STATUS_INSTANCE_ACTIVE,
                    'icon' => 'active',
                    'caption' => Translator::Get('patami.framework.module.form.status.instance_active')
                ),
                array(
                    'code' => self::STATUS_INSTANCE_DELETING,
                    'icon' => 'inactive',
                    'caption' => Translator::Get('patami.framework.module.form.status.instance_deleting')
                ),
                array(
                    'code' => self::STATUS_INSTANCE_INACTIVE,
                    'icon' => 'inactive',
                    'caption' => Translator::Get('patami.framework.module.form.status.instance_inactive')
                ),
            ),
            'translations' => array()
        );
    }

    /**
     * Returns the IPS module configuration form data as a JSON-encoded array.
     * This method will call Module::GetConfigurationFormData(), which returns an array with the configuration form
     * elements, actions and translations. The array returned by the method is JSON-encoded and returned to IPS.
     * The method adds a couple of form fields to the form, including module information and copyright information and
     * two buttons which open the module documentation and release notes web pages.
     * @return string JSON-encoded configuration form data.
     * @see Module::GetConfigurationFormData()
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/getconfigurationform/
     */
    final public function GetConfigurationForm()
    {
        // Get the configuration form data
        $data = $this->GetConfigurationFormData();

        if (count($data['actions']) > 0) {
            array_push($data['actions'],
                array(
                    'type' => 'Label',
                    'label' => ''
                )
            );
        }

        // Add the create issue button
        $newIssueUrl = $this->GetNewIssueUrl();
        if ($newIssueUrl) {
            array_push($data['actions'],
                array(
                    'type' => 'Label',
                    'label' => Translator::Get('patami.framework.module.form.actions.new_issue.label')
                ),
                array(
                    'type' => 'Button',
                    'label' => Translator::Get('patami.framework.module.form.actions.new_issue.button'),
                    'onClick' => sprintf("echo '%s'", $newIssueUrl)
                )
            );
        };

        // Add support info button
        array_push($data['actions'],
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.framework.module.form.actions.support_info.label')
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.framework.module.form.actions.support_info.button'),
                'onClick' => sprintf('%s_ShowSupportInfo($id);', $this->GetModulePrefix())
            ),
            array(
                'type' => 'Label',
                'label' => ''
            )
        );

        // Add module info to the end of the actions area
        array_push($data['actions'],
            array(
                'type' => 'Label',
                'label' => Translator::Format('patami.framework.module.form.actions.about.name_label', $this->GetModuleName())
            ),
            array(
                'type' => 'Label',
                'label' => Translator::Format(
                    'patami.framework.module.form.actions.about.version_label',
                    $this->library->GetDisplayName(),
                    $this->library->GetVersion(),
                    $this->library->GetBranch(),
                    $this->library->GetCommit(),
                    date('d.m.Y', $this->library->GetUpdateTime()),
                    date('H:i', $this->library->GetUpdateTime())
                )
            ),
            array(
                'type' => 'Label',
                'label' => Translator::Format(
                    'patami.framework.module.form.actions.about.copyright_label',
                    date('Y'),
                    $this->library->GetAuthor()
                )
            )
        );

        // Add the license button
        $licenseUrl = $this->GetLicenseUrl();
        if ($licenseUrl) {
            array_push($data['actions'],
                array(
                    'type' => 'Label',
                    'label' => ''
                ),
                array(
                    'type' => 'Button',
                    'label' => Translator::Get('patami.framework.module.form.actions.about.license_button'),
                    'onClick' => sprintf("echo '%s'", $licenseUrl)
                )
            );
        };

        // Add documentation buttons
        $documentationUrl = $this->GetDocumentationUrl();
        $releaseNotesUrl = $this->GetReleaseNotesUrl();
        if ($documentationUrl || $releaseNotesUrl) {
            array_push($data['actions'],
                array(
                    'type' => 'Label',
                    'label' => ''
                ),
                array(
                    'type' => 'Label',
                    'label' => Translator::Get('patami.framework.module.form.actions.documentation.label')
                )
            );
        };
        if ($documentationUrl) {
            array_push($data['actions'],
                array(
                    'type' => 'Button',
                    'label' => Translator::Get('patami.framework.module.form.actions.documentation.documentation_button'),
                    'onClick' => sprintf("echo '%s'", $documentationUrl)
                )
            );
        };
        if ($releaseNotesUrl) {
            array_push($data['actions'],
                array(
                    'type' => 'Button',
                    'label' => Translator::Get('patami.framework.module.form.actions.documentation.release_notes_button'),
                    'onClick' => sprintf("echo '%s'", $releaseNotesUrl)
                )
            );
        };

        // Remove actions element if it is empty
        if (count($data['actions']) == 0) {
            unset($data['actions']);
        }

        return json_encode($data);
    }

    /**
     * Performs actions required when the configuration of this instance was saved.
     * It is automatically called by IPS when the use presses the apply button on the instance's configuration page and
     * calls the Module::Configure() method which can be overridden by your own modules.
     * @see Module::Configure()
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/applychanges/
     */
    final public function ApplyChanges()
    {
        // Call the parent method
        /** @noinspection PhpUndefinedClassInspection */
        parent::ApplyChanges();

        // Listen for kernel messages
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterMessage(0, IPS_KERNELSHUTDOWN);

        // Return if the IPS kernel is not yet ready
        if (! IPS::IsKernelReady()) {
            IPS::LogMessage($this->GetModuleName(), sprintf('Instance %d waiting for IPS initialization...', $this->GetId()));
            return;
        }

        // Configure the instance
        $this->Configure();
    }

    /**
     * Performs actions required when the configuration of this instance was saved.
     * It is automatically called by the Module::ApplyChanges() method, which is in turn automatically called by IPS
     * when the user presses the apply button on the instance's configuration page.
     * You should call parent::Configure() at the end of the overridden method, in case the configuration of the
     * instance is OK. Otherwise set a different instance status and do not call the parent method.
     * @see Module::ApplyChanges()
     */
    protected function Configure()
    {
        // Set the instance active
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SetStatus(self::STATUS_INSTANCE_ACTIVE);
    }

    /**
     * Returns the IPS object ID of the instance.
     * @return int IPS object ID of the instance.
     */
    final public function GetId()
    {
        // Return the IPS object ID
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->InstanceID;
    }

    /**
     * Returns an Instance object for the module instance.
     * @return Instance Instance object for the instance.
     */
    final public function GetInstance()
    {
        // Return a new instance object
        return new Instance($this->GetId());
    }

    /**
     * Returns the IPS module GUID of the module.
     * @return string IPS module GUID.
     */
    public function GetModuleId()
    {
        // Return the module GUID
        return IPS::GetInstanceModuleId($this->GetId());
    }

    /**
     * Returns the name of the module.
     * @return string Name of the module.
     */
    public function GetModuleName()
    {
        // Return the module name
        return IPS::GetInstanceModuleName($this->GetId());
    }

    /**
     * Returns information about the module from the module.json file.
     * @return array Attributes of the module.
     */
    protected function GetModuleFileInfo()
    {
        // Get the file name of the module.json file
        $fileName = $this->GetModuleInfoFileName();

        // Load the file
        $text = @file_get_contents($fileName);

        // Return an empty array if the file could not be loaded
        if ($text === false) {
            return array();
        }

        // Decode the JSON data
        $data = @json_decode($text, true);

        // Return an empty array if the JSON data could not be parsed
        if (is_null($data)) {
            return array();
        }

        // Return the information
        return $data;
    }

    /**
     * Returns the prefix used by IPS to register the public module methods / functions.
     * @return string Function prefix.
     */
    protected function GetModulePrefix()
    {
        // Return the module function prefix
        return @$this->GetModuleFileInfo()['prefix'];
    }

    /**
     * Returns a list of all IPS object IDs of all instances of the module.
     * @return array IPS object IDs of all instances of the module.
     * @see IPS::GetInstanceListByModuleId()
     */
    public function GetModuleInstances()
    {
        // Return the instance list with the module's GUID
        return IPS::GetInstanceListByModuleId($this->GetModuleId());
    }

    /**
     * Checks if the instance if valid / active.
     * @return bool True if the instance is active.
     */
    public function IsValid()
    {
        // Get the instance status
        /** @noinspection PhpUndefinedFieldInspection */
        $status = IPS::GetInstance($this->GetId())['InstanceStatus'];

        // Check if the instance is active and return the result
        return $status == self::STATUS_INSTANCE_ACTIVE;
    }

    /**
     * Checks if the module is private.
     * A module is regarded as being private if its library is not loaded anonymously.
     * @return bool True if the module is private.
     * @see Library::IsPrivate()
     */
    protected function IsPrivate()
    {
        // Check if the library is private and return the result
        return $this->library->IsPrivate();
    }

    /**
     * Returns the IPS object ID of the parent IPS instance.
     * The parent instance is not the instance above the current instance in the IPS object tree, but the instance
     * to which this instance is connected to.
     * @return int IPS object ID of the parent instance.
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/datenfluss/
     */
    public function GetParentId()
    {
        // Get the IPS object ID of the parent instance and return it
        /** @noinspection PhpUndefinedFieldInspection */
        return @IPS::GetInstance($this->GetId())['ConnectionID'];
    }

    /**
     * Checks if the parent IPS instance is active.
     * @return bool True if the parent IPS instance is active.
     * @see Module::GetParentId()
     */
    public function IsParentActive()
    {
        // Get the parent ID
        $parentId = $this->GetParentId();

        // Return false if no parent is set
        if (is_null($parentId)) {
            return false;
        }

        // Get the parent's instance status
        $status = IPS::GetInstance($parentId)['InstanceStatus'];

        // Check if the instance is active and return the result
        return $status == self::STATUS_INSTANCE_ACTIVE;
    }

    /**
     * Registers an instance to listen for reload events.
     * @param int $instanceId IPS object ID of the other instance.
     */
    protected function ListenForInstanceReloads($instanceId)
    {
        // Return if the own instance if is used
        if ($instanceId == $this->GetId()) {
            return;
        }

        // Register an event listener
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterMessage($instanceId, IM_CONNECT);
    }

    /**
     * Processes IPS messages.
     * This method is called by IPS for registered messages when they are triggered.
     * @param int $timestamp Unix timestamp when the message was triggered.
     * @param int $senderId IPS object ID of the object that that triggered the message.
     * @param int $messageId ID of the message.
     * @param array $data Event data.
     */
    final public function MessageSink($timestamp, $senderId, $messageId, $data)
    {
        // Get the ID
        $id = $this->GetId();

        switch ($messageId) {
            case IPS_KERNELSTARTED:
                // IPS kernel is ready
                // Call the event handler
                $this->OnKernelStarted($timestamp, $data);
                // Reapply settings
                IPS::LogMessage($this->GetModuleName(), sprintf('Configuring instance %d', $id));
                IPS::ApplyChanges($id);
                break;
            case IPS_KERNELSHUTDOWN:
                // IPS kernel is about to shutdown
                // Call the event handler
                IPS::LogMessage($this->GetModuleName(), 'Shutting down...');
                $this->OnKernelShutdown($timestamp, $data);
                break;
            case IM_CONNECT:
                // An instance was loaded and is ready to use
                // Call the event handler
                $this->OnInstanceConnect($timestamp, $senderId, $data);
                // Reapply settings
                IPS::LogMessage($this->GetModuleName(), sprintf('Configuring instance %d, linked instance %d was reloaded', $id, $senderId));
                IPS::ApplyChanges($id);
                break;
            default:
                // Call custom message handler
                $this->OnMessage($timestamp, $senderId, $messageId, $data);
        }
    }

    /**
     * Event handler called when the IPS kernel started.
     * @param int $timestamp Unix timestamp when the message was triggered.
     * @param array $data Event data.
     */
    protected function OnKernelStarted($timestamp, $data)
    {
    }

    /**
     * Event handler called when the IPS kernel is about to shutdown.
     * @param int $timestamp Unix timestamp when the message was triggered.
     * @param array $data Event data.
     */
    protected function OnKernelShutdown($timestamp, $data)
    {
    }

    /**
     * Event handler called when another instance interface is connected.
     * @param int $timestamp Unix timestamp when the message was triggered.
     * @param int $senderId IPS object ID of the object that that triggered the message.
     * @param array $data Event data.
     */
    protected function OnInstanceConnect($timestamp, $senderId, $data)
    {
    }

    /**
     * Processes IPS messages.
     * This method is called by the frameork for registered messages when they are triggered.
     * @param int $timestamp Unix timestamp when the message was triggered.
     * @param int $senderId IPS object ID of the object that that triggered the message.
     * @param int $messageId ID of the message.
     * @param array $data Event data.
     */
    protected function OnMessage($timestamp, $senderId, $messageId, $data)
    {
    }

    /**
     * Encodes data to be send to child or parent IPS instances.
     * @param string $dataId GUID of the data packet type.
     * @param array $data Data which should be encoded in the data packet.
     * @return string JSON-encoded data to be sent to child or parent IPS instances.
     * @see Module::SendEncodedDataToParent()
     * @see Module::SendEncodedDataToChildren()
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/receivedata/
     */
    protected function GetEncodedData($dataId, array $data)
    {
        // Add the data ID to the data
        $data['DataID'] = $dataId;

        // Return the encoded data
        return json_encode($data);
    }

    /**
     * Sends data to the parent IPS instance.
     * The data is received by the parent instance's IPSModule::ForwardData() method.
     * @param string $dataId GUID of the data packet type.
     * @param array|mixed $data Data which should be encoded in the data packet.
     * @return string|null Result returned from the IPSModule::ForwardData() method.
     * @see Module::GetEncodedData()
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/senddatatoparent/
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/forwarddata/
     */
    public function SendEncodedDataToParent($dataId, $data)
    {
        // Return if the data is not an array
        if (! is_array($data)) {
            return null;
        }

        // Encode and send data to the parent instance and return the result
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->SendDataToParent($this->GetEncodedData($dataId, $data));
    }

    /**
     * Sends data to all child IPS instances.
     * The data is received by the child instance's IPSModule::ReceiveData() method.
     * @param string $dataId GUID of the data packet type.
     * @param array|mixed $data Data which should be encoded in the data packet.
     * @see Module::GetEncodedData()
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/senddatatochildren/
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/receivedata/
     */
    public function SendEncodedDataToChildren($dataId, $data)
    {
        // Return if the data is not an array
        if (! is_array($data)) {
            return;
        }

        // Encode and send data to all child instances
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SendDataToChildren($this->GetEncodedData($dataId, $data));
    }

    /**
     * Creates a new boolean status variable below the module instance.
     * This method should be called in the ApplyChanges() or Configure() methods.
     * It will update the variable profile and the position of the variable, but not its name.
     * @param string $ident Ident of the status variable.
     * @param string $name Name of the status variable.
     * @param string $profile Variable profile name or an empty string for no variable profile.
     * @param int $position Relative position compared to the other objects below the module instance.
     * @return int IPS object ID of the new status variable.
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/registerpropertyboolean/
     */
    public function RegisterVariableBoolean($ident, $name, $profile = '', $position = 0)
    {
        /** @noinspection PhpUndefinedClassInspection */
        return parent::RegisterVariableBoolean($ident, $name, $profile, $position);
    }

    /**
     * Creates a new boolean status variable below the module instance.
     * This method should be called in the ApplyChanges() or Configure() methods.
     * It will update the name, the variable profile and the position of the variable.
     * @param string $ident Ident of the status variable.
     * @param string $name Name of the status variable.
     * @param string $profile Variable profile name or an empty string for no variable profile.
     * @param int $position Relative position compared to the other objects below the module instance.
     * @return int IPS object ID of the new status variable.
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/registerpropertyboolean/
     */
    public function RegisterVariableBooleanEx($ident, $name, $profile = '', $position = 0)
    {
        // Register the variable
        $variableId = $this->RegisterVariableBoolean($ident, $name, $profile, $position);

        // Update the variable name
        IPS::SetName($variableId, $name);

        // Return the variable ID
        return $variableId;
    }

    /**
     * Creates a new float status variable below the module instance.
     * This method should be called in the ApplyChanges() or Configure() methods.
     * It will update the variable profile and the position of the variable, but not its name.
     * @param string $ident Ident of the status variable.
     * @param string $name Name of the status variable.
     * @param string $profile Variable profile name or an empty string for no variable profile.
     * @param int $position Relative position compared to the other objects below the module instance.
     * @return int IPS object ID of the new status variable.
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/registerpropertyfloat/
     */
    public function RegisterVariableFloat($ident, $name, $profile = '', $position = 0)
    {
        /** @noinspection PhpUndefinedClassInspection */
        return parent::RegisterVariableFloat($ident, $name, $profile, $position);
    }

    /**
     * Creates a new float status variable below the module instance.
     * This method should be called in the ApplyChanges() or Configure() methods.
     * It will update the name, the variable profile and the position of the variable.
     * @param string $ident Ident of the status variable.
     * @param string $name Name of the status variable.
     * @param string $profile Variable profile name or an empty string for no variable profile.
     * @param int $position Relative position compared to the other objects below the module instance.
     * @return int IPS object ID of the new status variable.
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/registerpropertyfloat/
     */
    public function RegisterVariableFloatEx($ident, $name, $profile = '', $position = 0)
    {
        // Register the variable
        $variableId = $this->RegisterVariableFloat($ident, $name, $profile, $position);

        // Update the variable name
        IPS::SetName($variableId, $name);

        // Return the variable ID
        return $variableId;
    }

    /**
     * Creates a new integer status variable below the module instance.
     * This method should be called in the ApplyChanges() or Configure() methods.
     * It will update the variable profile and the position of the variable, but not its name.
     * @param string $ident Ident of the status variable.
     * @param string $name Name of the status variable.
     * @param string $profile Variable profile name or an empty string for no variable profile.
     * @param int $position Relative position compared to the other objects below the module instance.
     * @return int IPS object ID of the new status variable.
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/registerpropertyinteger/
     */
    public function RegisterVariableInteger($ident, $name, $profile = '', $position = 0)
    {
        /** @noinspection PhpUndefinedClassInspection */
        return parent::RegisterVariableInteger($ident, $name, $profile, $position);
    }

    /**
     * Creates a new integer status variable below the module instance.
     * This method should be called in the ApplyChanges() or Configure() methods.
     * It will update the name, the variable profile and the position of the variable.
     * @param string $ident Ident of the status variable.
     * @param string $name Name of the status variable.
     * @param string $profile Variable profile name or an empty string for no variable profile.
     * @param int $position Relative position compared to the other objects below the module instance.
     * @return int IPS object ID of the new status variable.
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/registerpropertyinteger/
     */
    public function RegisterVariableIntegerEx($ident, $name, $profile = '', $position = 0)
    {
        // Register the variable
        $variableId = $this->RegisterVariableInteger($ident, $name, $profile, $position);

        // Update the variable name
        IPS::SetName($variableId, $name);

        // Return the variable ID
        return $variableId;
    }

    /**
     * Creates a new string status variable below the module instance.
     * This method should be called in the ApplyChanges() or Configure() methods.
     * It will update the variable profile and the position of the variable, but not its name.
     * @param string $ident Ident of the status variable.
     * @param string $name Name of the status variable.
     * @param string $profile Variable profile name or an empty string for no variable profile.
     * @param int $position Relative position compared to the other objects below the module instance.
     * @return int IPS object ID of the new status variable.
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/registervariablestring/
     */
    public function RegisterVariableString($ident, $name, $profile = '', $position = 0)
    {
        /** @noinspection PhpUndefinedClassInspection */
        return parent::RegisterVariableString($ident, $name, $profile, $position);
    }

    /**
     * Creates a new string status variable below the module instance.
     * This method should be called in the ApplyChanges() or Configure() methods.
     * It will update the name, the variable profile and the position of the variable.
     * @param string $ident Ident of the status variable.
     * @param string $name Name of the status variable.
     * @param string $profile Variable profile name or an empty string for no variable profile.
     * @param int $position Relative position compared to the other objects below the module instance.
     * @return int IPS object ID of the new status variable.
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/module/registervariablestring/
     */
    public function RegisterVariableStringEx($ident, $name, $profile = '', $position = 0)
    {
        // Register the variable
        $variableId = $this->RegisterVariableString($ident, $name, $profile, $position);

        // Update the variable name
        IPS::SetName($variableId, $name);

        // Return the variable ID
        return $variableId;
    }

    /**
     * Returns support info to be displayed to the user.
     * @return array Key value pairs with support info.
     */
    protected function GetSupportInfo()
    {
        return array(
            'Library' => $this->library->GetDisplayName(),
            'Module' => $this->GetModuleName(),
            'Version' => $this->library->GetVersion(),
            'Branch' => $this->library->GetBranch(),
            'Commit' => $this->library->GetCommit(),
            'Framework Version' => Framework::GetVersion(),
            'Framework Branch' => Framework::GetBranch(),
            'Framework Commit' => Framework::GetCommit(),
            'IPS Version' => IPS::GetKernelVersion(),
            'IPS Version Date' => date('d.m.Y', IPSLive::GetVersionDate()),
            'IPS Variable Limit Exceeded' => StringHelper::GetBooleanAsYesNo(IPSLive::IsVariableLimitExceeded(), 'en-US'),
            'IPS Connect Connected' => StringHelper::GetBooleanAsYesNo(IPSConnect::IsConnected(), 'en-US'),
            'Platform' => System::GetOSName(),
        );
    }

    /**
     * Displays support info to the user.
     */
    public function ShowSupportInfo()
    {
        // Get the support information
        $info = $this->GetSupportInfo();

        // Format the output
        $text = '';
        foreach ($info as $key => $value) {
            $text .= sprintf("%s: %s\n", $key, $value);
        }
        $text = sprintf(
            Translator::Get('patami.framework.module.form.actions.support_info.text'),
            trim($text)
        );

        // Output the info
        echo $text;
    }

    /**
     * Returns the URL the the module's license web page.
     * Override this method in your module to link to your own license.
     * @return string|null URL to the module's license web page or null if the button to open the web page should not be
     * displayed in the module's configuration form.
     */
    protected function GetLicenseUrl()
    {
        return null;
    }

    /**
     * Returns the URL to open a new issue in the module's issue tracker.
     * Override this method in your module to link to your issue tracker.
     * @return string|null URL to the create issue web page of your issue tracker or null if the button to open a new
     * issue should not be displaxed in the module's configuration form.
     */
    protected function GetNewIssueUrl()
    {
        return null;
    }

    /**
     * Returns the URL to the module's documentation web page.
     * Override this method in your module to link to your own documentation.
     * @return string|null URL to the module's documentation web page or null if the button to open the web page should
     * not be displayed in the module's configuration form.
     */
    protected function GetDocumentationUrl()
    {
        return null;
    }

    /**
     * Returns the URL to the module's release notes web page.
     * Override this method in your module to link to your own release notes.
     * @return string|null URL to the module's release notes web page or null if the button to open the web page should
     * not be displayed in the module's configuration form.
     */
    protected function GetReleaseNotesUrl()
    {
        return null;
    }

    public function Debug($tag, $message, array $data = null)
    {
        // Convert a non-string message to a string by serializing it
        if (! is_string($message)) {
            $message = serialize($message);
        }

        // Send the message to the debug log of the IPS instance
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SendDebug($tag, $message, self::DEBUG_TEXT);

        // Enable fluent interface
        return $this;
    }

    /**
     * Sends an extended debug message to the log.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     * @return $this Fluent interface.
     * @see Module::Debug()
     * @see Framework::IsExtendedDebuggingEnabled()
     */
    public function DebugEx($tag, $message, array $data = null)
    {
        // Return if extended debugging is not enabled
        if (! Framework::IsExtendedDebuggingEnabled()) {
            return $this;
        }

        // Log the message
        $this->Debug($tag, $message, $data);

        // Enable fluent interface
        return $this;
    }

}