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


namespace Patami\IPS\IO;


use Patami\IPS\I18N\Translator;
use Patami\IPS\System\IPS;
use Patami\IPS\System\IPSConnect;
use Patami\IPS\System\Locales;


/**
 * Abstract base class for IPS WebHook modules.
 *
 * This class extends the functionality provided by the Module class to handle requests sent via IPS' WebHook interface.
 *
 * @see Module
 * @link https://www.symcon.de/service/dokumentation/modulreferenz/webhook-control/
 *
 * @package IPSPATAMI
 */
abstract class WebHookIOModule extends IOModule
{

    /** WebHook path is invalid. */
    const STATUS_ERROR_WEBHOOK_PATH_INVALID       = 801;

    /** WebHook instance could not be found. */
    const STATUS_ERROR_WEBHOOK_INSTANCE_NOT_FOUND = 802;

    /** The same WebHook path is already registered by another instance or script. */
    const STATUS_ERROR_WEBHOOK_ALREADY_REGISTERED = 803;

    /** IPS module GUID of the WebHook instance. */
    const WEBHOOK_MODULE_ID = '{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}';

    public function Create()
    {
        // Call the parent method
        parent::Create();

        // Register the property for the WebHook path
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString('WebHookSubPath', $this->GetDefaultWebHookSubPath());
    }

    public function Destroy()
    {
        // Unregister (remove) the WebHook path
        // Doesn't touch the summary text because it cannot be set when the instance is being destroyed
        $this->UnregisterHook(false);

        // Call the parent method
        parent::Destroy();
    }

    /**
     * Returns the IPS module configuration form data.
     * {@inheritDoc}
     * The WebHookIOModule implementation adds status codes, translations and a configuration form field to configure
     * the WebHook path.
     */
    protected function GetConfigurationFormData()
    {
        // Call the parent method
        $data = parent::GetConfigurationFormData();

        // Add the WebHook path form field
        array_unshift($data['elements'],
            array(
                'type' => 'Label',
                'label' => $this->GetWebHookLabel()
            ),
            array(
                'type' => 'ValidationTextBox',
                'name' => 'WebHookSubPath',
                'caption' => '/hook/'
            )
        );

        // Add a button to display the WebHook URL
        array_unshift($data['actions'],
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.framework.io.webhookiomodule.form.actions.show_url.label')
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.framework.io.webhookiomodule.form.actions.show_url.button'),
                'onClick' => sprintf('%s_ShowURL($id);', $this->GetModulePrefix())
            ),
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.framework.io.webhookiomodule.form.actions.show_url.notice')
            )
        );

        // Add status codes to indicate problems with the WebHook
        array_unshift($data['status'],
            array(
                'code' => self::STATUS_ERROR_WEBHOOK_PATH_INVALID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.framework.io.webhookiomodule.form.status.webhook_path_invalid')
            ),
            array(
                'code' => self::STATUS_ERROR_WEBHOOK_INSTANCE_NOT_FOUND,
                'icon' => 'error',
                'caption' => Translator::Get('patami.framework.io.webhookiomodule.form.status.webhook_instance_not_found')
            ),
            array(
                'code' => self::STATUS_ERROR_WEBHOOK_ALREADY_REGISTERED,
                'icon' => 'error',
                'caption' => Translator::Get('patami.framework.io.webhookiomodule.form.status.webhook_already_registered')
            )
        );

        // Return the configuration form
        return $data;
    }

    /**
     * Validates the WebHook path and registers it.
     */
    protected function Configure()
    {
        // Validate WebHook path
        /** @noinspection PhpUndefinedMethodInspection */
        $path = strtolower($this->ReadPropertyString('WebHookSubPath'));
        if (! preg_match('/^[a-z0-9_-]+(\/[a-z0-9_-]+)*$/', $path)) {
            $this->Debug('WebHook Validation', 'Path is invalid');
            /** @noinspection PhpUndefinedMethodInspection */
            $this->SetStatus(self::STATUS_ERROR_WEBHOOK_PATH_INVALID);
            // Unregister the WebHook
            $this->UnregisterHook();
            return;
        }
        $this->Debug('WebHook Validation', 'Path is valid');

        // Register the WebHook
        $result = $this->RegisterHook();
        if (!$result) {
            return;
        }

        // Call the parent function
        parent::Configure();
    }

    /**
     * Returns the translated label for the WebHook configuration field.
     * @return string WebHook configuration field label.
     * @see Locales
     */
    abstract protected function GetWebHookLabel();

    /**
     * Returns the default WebHook sub path.
     * @return string Default WebHook sub path.
     */
    abstract protected function GetDefaultWebHookSubPath();

    /**
     * Returns the configured WebHook sub path.
     * @return string WebHook sub path.
     */
    public function GetWebHookSubPath()
    {
        // Return the WebHook subpath
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyString('WebHookSubPath');
    }

    /**
     * Returns the WebHook path.
     * All custom IPS WebHook paths are prefixed with /hook/.
     * @return string WebHook path.
     */
    public function GetWebHookPath()
    {
        // Return the WebHook path.
        return '/hook/' . $this->GetWebHookSubPath();
    }

    /**
     * Registers the WebHook.
     * The method uses the configured WebHook sub path to register the WebHook with the IPS WebHook Control instance.
     * IPS will call WebHookIOModule::ProcessHookData() when a request is received from a remote client.
     * @return bool True if the WebHook was successfully registered.
     * @see WebHookIOModule::ProcessHookData()
     * @link https://www.symcon.de/service/dokumentation/modulreferenz/webhook-control/
     */
    protected function RegisterHook()
    {
        // Remove old hooks, if any
        $this->UnregisterHook();

        // Get the WebHook path
        $path = $this->GetWebHookPath();

        $this->Debug('Registering WebHook', $path);

        // Find the WebHook instance
        $ids = IPS::GetInstanceListByModuleId(self::WEBHOOK_MODULE_ID);
        if (sizeof($ids) == 1) {

            // Get the list of hooks
            $hooks = json_decode(IPS::GetProperty($ids[0], "Hooks"), true);

            // Loop through the hooks
            foreach ($hooks as $index => $hook) {
                if ($hook['Hook'] == $path) {
                    // The hook is already registered
                    /** @noinspection PhpUndefinedFieldInspection */
                    if ($hook['TargetID'] == $this->InstanceID) {
                        // The hook is already registered to the current instance, we're done
                        return true;
                    }
                    // Return an error if another instance already registered the hook
                    $this->Debug('Registering WebHook', sprintf('Hook is already registered to instance %d', $hook['TargetID']));
                    /** @noinspection PhpUndefinedMethodInspection */
                    $this->SetStatus(self::STATUS_ERROR_WEBHOOK_ALREADY_REGISTERED);
                    return false;
                }
            }

            // The hook did not exist yet, add it to the list
            /** @noinspection PhpUndefinedFieldInspection */
            $hooks[] = array(
                "Hook" => $path,
                "TargetID" => $this->InstanceID
            );

            // Save the hook list back to the WebHook instance
            IPS::SetProperty($ids[0], "Hooks", json_encode($hooks));
            IPS::ApplyChanges($ids[0]);

            // Set the instance summary to differentiate it from other hooks
            /** @noinspection PhpUndefinedMethodInspection */
            $this->SetSummary($path);

        } else {
            $this->Debug('Registering WebHook', 'Cannot find the WebHook instance');
            /** @noinspection PhpUndefinedMethodInspection */
            $this->SetStatus(self::STATUS_ERROR_WEBHOOK_INSTANCE_NOT_FOUND);
            return false;
        }

        // Everything went fine
        return true;
    }

    /**
     * Unregisters the WebHook.
     * The method unregisters the WebHook from the IPS WebHook Control instance.
     * It is automatically called when the module is deleted.
     * @param bool $setSummary True if the summary text should be set.
     * @see WebHookIOModule::Destroy()
     * @link https://www.symcon.de/service/dokumentation/modulreferenz/webhook-control/
     */
    protected function UnregisterHook($setSummary = true)
    {
        $this->Debug('Unregistering WebHook', '');

        // Find the WebHook instance
        $ids = IPS::GetInstanceListByModuleId(self::WEBHOOK_MODULE_ID);
        if (sizeof($ids) == 1) {

            // Get the list of hooks
            $hooks = json_decode(IPS::GetProperty($ids[0], "Hooks"), true);

            // Repeat until all instances were removed
            $index = null;
            $found = true;
            while ($found !== false) {
                $found = false;
                // Loop through all hooks
                foreach ($hooks as $index => $hook) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    if ($hook['TargetID'] == $this->InstanceID) {
                        // The hook was found
                        $found = $index;
                        break;
                    }
                }
                // Remove it from the list
                if ($found !== false) {
                    array_splice($hooks, $index, 1);
                }
            }

            // Save the hook list back to the WebHook instance
            IPS::SetProperty($ids[0], "Hooks", json_encode($hooks));
            IPS::ApplyChanges($ids[0]);
        }

        // Clear the instance summary
        if ($setSummary) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->SetSummary('');
        }
    }

    /**
     * Processes a WebHook request.
     * This method is automatically called by the IPS WebHook Control instance.
     * @see WebHookIOModule::ProcessData()
     * @link https://www.symcon.de/service/dokumentation/modulreferenz/webhook-control/
     */
    protected function ProcessHookData()
    {
        // Forward the request to the common method
        $this->ProcessData();
    }

    /**
     * Processes a request.
     * {@inheritDoc}
     * The WebHookIOModule implementation of the method logs the client's IP address found in the HTTP_X_FORWARDED_FOR
     * header, which is set by the IP-Symcon Connect reverse proxy.
     */
    protected function ProcessData()
    {
        // Log original source IP address (as the request seems to be coming from the IP-Symcon Connect reverse proxy)
        // The reverse proxy sets the HTTP_X_FORWARDED_FOR header with the original IP address.
        $sourceIP = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $this->Debug('Incoming Request Source IP', $sourceIP);

        // Call the parent method
        parent::ProcessData();
    }

    /**
     * Returns the IP Symcon Connect URL of the WebHook instance.
     * @return string URL of the instance.
     */
    public function GetURL()
    {
        // Return the IP Symcon Connect URL of the instance
        return IPSConnect::GetURL() . $this->GetWebHookPath();
    }

    /**
     * Displays the IP Symcon Connect URL of the WebHook instance.
     */
    public function ShowURL()
    {
        // Output the URL which IPS will use to open a browser
        echo $this->GetURL();
    }

}