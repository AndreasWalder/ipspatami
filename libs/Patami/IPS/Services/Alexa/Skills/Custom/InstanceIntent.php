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


use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\IntentNotFoundException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidIntentInstanceException;
use Patami\IPS\System\IPS;


/**
 * Base class for intents implemented using IPS intent instances.
 *
 * It basically forwards Alexa Custom Skill requests to IPS intent instances, which - depending on their configuration -
 * process the request using different module intent classes.
 *
 * @package IPSPATAMI
 */
class InstanceIntent extends Intent
{

    /** IPS module GUID of the intent instance module. */
    const INTENT_MODULE_ID = '{F14921A2-405D-4576-A389-95FB9A5CD730}';

    /** @var int IPS object ID of the intent instance. */
    protected $instanceId = null;

    /**
     * Static factory method that creates a new instance intent object by searching the instance that provides an intent with the specified name.
     * @param IntentContainerInterface $container WebHook I/O module object which uses the intent.
     * @param string $name Intent name.
     * @return InstanceIntent|Intent Instance intent object that represents the IPS intent instance for the specified name.
     * @throws IntentNotFoundException if no IPS intent instance with the specified intent name could be found.
     */
    public static function CreateByName(IntentContainerInterface $container, $name)
    {
        // Get the IPS object ID of the WebHook I/O
        $webHookId = $container->GetId();

        // Get the list of intent instances
        $instanceIds = IPS::GetInstanceListByModuleId(self::INTENT_MODULE_ID);

        // Loop through the instances
        foreach ($instanceIds as $instanceId) {
            // Skip if connected to a different WebHook
            /** @noinspection PhpUndefinedFunctionInspection */
            if ($webHookId != ACSIntent_GetParentId($instanceId)) {
                continue;
            }
            // Create and return the object if the name matches
            /** @noinspection PhpUndefinedFunctionInspection */
            if ($name == ACSIntent_GetIntentName($instanceId)) {
                // Get the name of the called class
                /** @var InstanceIntent $className */
                $className = get_called_class();
                // Create and return the intent object
                /** @var InstanceIntent $intent */
                return $className::CreateByInstanceId($container, $instanceId);
            }
        }

        // Throw an error if no matching class was found
        throw new IntentNotFoundException();
    }

    /**
     * Static factory method that creates a new instance intent object from the IPS object ID of the intent instance.
     * @param IntentContainerInterface $container WebHook I/O module object which uses the intent.
     * @param int $instanceId IPS object ID of the intent instance.
     * @return InstanceIntent|Intent Intent instance object that represents the IPS intent instance for the specified name.
     * @throws InvalidIntentInstanceException if the specified IPS object is of the wrong type or connected to the
     * wrong Alexa I/O module.
     */
    public static function CreateByInstanceId(IntentContainerInterface $container, $instanceId)
    {
        // Get the name of the called class
        /** @var InstanceIntent $className */
        $className = get_called_class();

        // Create the intent object
        /** @var InstanceIntent $intent */
        $intent = $className::Create($container);

        // Set the instance ID
        $intent->SetInstanceId($instanceId);

        // Return the object
        return $intent;
    }

    /**
     * Returns the IPS object ID of the intent instance.
     * @return int IPS object ID.
     */
    public function GetInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * Sets the IPS object ID of the associated intent instance.
     * @param int $instanceId IPS object ID of the associated intent instance.
     * @throws InvalidIntentInstanceException if the specified IPS object is of the wrong type or connected to the
     * wrong Alexa I/O module.
     */
    public function SetInstanceId($instanceId)
    {
        // Throw an exception if the instance is not an AlexaCustomSkillIntent
        $moduleId = @IPS::GetInstance($instanceId)['ModuleInfo']['ModuleID'];
        if ($moduleId != self::INTENT_MODULE_ID) {
            throw new InvalidIntentInstanceException();
        }

        // Throw an exception if the instance is connected to a different WebHook
        /** @noinspection PhpUndefinedFunctionInspection */
        if ($this->container->GetId() != ACSIntent_GetParentId($instanceId)) {
            throw new InvalidIntentInstanceException();
        }

        $this->instanceId = $instanceId;
    }

    /**
     * Validates the IPS object ID.
     * @throws IntentNotFoundException if no valid IPS intent instance is associated.
     */
    protected function ValidateInstanceId()
    {
        // Throw an exception if the instance ID is not set
        if (is_null($this->instanceId)) {
            throw new IntentNotFoundException();
        }
    }

    /**
     * Returns the intent name.
     * @return string Intent name.
     * @throws IntentNotFoundException if no valid IPS intent instance is associated.
     */
    public function GetName()
    {
        // Validate the instance ID
        $this->ValidateInstanceId();

        // Return the intent name
        /** @noinspection PhpUndefinedFunctionInspection */
        return ACSIntent_GetIntentName($this->instanceId);
    }

    protected function GetType()
    {
        return 'InstanceIntent';
    }

    protected function DoExecute(Request $request)
    {
        // Log instance ID
        $this->Debug('Intent Instance ID', $this->GetInstanceId());

        // Execute the intent instance and return the result
        /** @noinspection PhpUndefinedFunctionInspection */
        return ACSIntent_Execute($this->GetInstanceId(), $request);
    }

}