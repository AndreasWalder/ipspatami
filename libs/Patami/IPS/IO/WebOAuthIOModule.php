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


/**
 * Abstract base class for IPS WebOAuth modules.
 *
 * This class extends the functionality provided by the Module class to handle requests sent via IPS' WebOAuth interface.
 *
 * An OAuth ID must be defined for your module and registered by Symcon GmbH.
 * Please get in contact with them to request an ID: https://www.symcon.de/kontakt/#OAuth
 *
 * @see Module
 * @link https://github.com/paresy/SymconMisc/blob/master/Locative/module.php
 *
 * @package IPSPATAMI
 */
abstract class WebOAuthIOModule extends IOModule
{

    /** WebOAuth instance could not be found. */
    const STATUS_ERROR_WEBOAUTH_INSTANCE_NOT_FOUND = 801;

    /** OAuth ID is already registered by another instance. */
    const STATUS_ERROR_WEBOAUTH_ALREADY_REGISTERED = 802;

    /** IPS module GUID of the WebOAuth instance. */
    const WEBOAUTH_MODULE_ID = '{F99BF07D-CECA-438B-A497-E4B55F139D37}';

    /**
     * @var string OAuth ID used to register the endpoint.
     * This must be overriden by concrete implementations of this class.
     */
    protected $oAuthId = null;

    /**
     * Unregisters the OAuth endpoint when the instance is deleted.
     * @see WebOAuthIOModule::UnregisterOAuth()
     */
    public function Destroy()
    {
        // Unregister (remove) the OAuth endpoint
        $this->UnregisterOAuth();

        // Call the parent method
        parent::Destroy();
    }

    /**
     * Returns the IPS module configuration form data.
     * {@inheritDoc}
     * The WebOAuthIOModule implementation adds status codes, translations and a status label that indicates whether
     * the required Symcon Connect service is active (connected).
     */
    protected function GetConfigurationFormData()
    {
        // Call the parent method
        $data = parent::GetConfigurationFormData();

        // Add status codes to indicate problems with the WebOAuth
        array_unshift($data['status'],
            array(
                'code' => self::STATUS_ERROR_WEBOAUTH_INSTANCE_NOT_FOUND,
                'icon' => 'error',
                'caption' => Translator::Get('patami.framework.io.weboauthiomodule.form.status.weboauth_instance_not_found')
            ),
            array(
                'code' => self::STATUS_ERROR_WEBOAUTH_ALREADY_REGISTERED,
                'icon' => 'error',
                'caption' => Translator::Format('patami.framework.io.weboauthiomodule.form.status.weboauth_already_registered', $this->oAuthId)
            )
        );

        // Add the label that shows the IP-Symcon Connect status
        array_unshift($data['actions'],
            array(
                'type' => 'Label',
                'label' => Translator::Get(
                    $this->IsConnectActive()?
                    'patami.framework.io.weboauthiomodule.form.actions.connect.connected':
                    'patami.framework.io.weboauthiomodule.form.actions.connect.not_connected'
                )
            )
        );

        // Return the configuration form
        return $data;
    }

    /**
     * Registers the OAuth endpoint.
     */
    protected function Configure()
    {
        // Register the OAuth endpoint
        $result = $this->RegisterOAuth();
        if (!$result) {
            return;
        }

        // Call the parent function
        parent::Configure();
    }

    /**
     * Registers the OAuth endpoint.
     * The method registers the OAuth endpoint with the IPS WebOAuth instance.
     * IPS will call WebOAuthIOModule::ProcessOAuthData() when a request is received from the IPS Connect service.
     * @return bool True if the OAuth endpoint was successfully registered.
     * @see WebOAuthIOModule::$oAuthId
     * @see WebOAuthIOModule::ProcessOAuthData()
     */
    protected function RegisterOAuth()
    {
        // Remove old OAuth endpoints, if any
        $this->UnregisterOAuth();

        $this->Debug('Registering OAuth Endpoint', $this->oAuthId);

        // Find the WebOAuth instance
        $ids = IPS::GetInstanceListByModuleId(self::WEBOAUTH_MODULE_ID);

        if (sizeof($ids) == 1) {

            // Get the list of client IDs
            $clientIds = json_decode(IPS::GetProperty($ids[0], "ClientIDs"), true);

            // Loop through the client IDs
            foreach ($clientIds as $index => $clientId) {
                // The instance is already registered
                if ($clientId['ClientID'] == $this->oAuthId) {
                    // The client ID is already registered
                    /** @noinspection PhpUndefinedFieldInspection */
                    if ($clientId['TargetID'] == $this->InstanceID) {
                        // The OAuth endpoint is already registered to the current instance, we're done
                        return true;
                    }
                    // Return an error if another instance already registered the endpoint
                    $this->Debug('Registering OAuth Endpoint', sprintf('Endpoint is already registered to instance %d', $clientId['TargetID']));
                    /** @noinspection PhpUndefinedMethodInspection */
                    $this->SetStatus(self::STATUS_ERROR_WEBOAUTH_ALREADY_REGISTERED);
                    return false;
                }
            }

            // The OAuth endpoint did not exist yet, add it to the list
            /** @noinspection PhpUndefinedFieldInspection */
            $clientIds[] = array(
                "ClientID" => $this->oAuthId,
                "TargetID" => $this->InstanceID
            );

            // Save the hook list back to the WebOAuth instance
            IPS::SetProperty($ids[0], "ClientIDs", json_encode($clientIds));
            IPS::ApplyChanges($ids[0]);

        } else {
            $this->Debug('Registering OAuth Endpoint', 'Cannot find the WebOAuth instance');
            /** @noinspection PhpUndefinedMethodInspection */
            $this->SetStatus(self::STATUS_ERROR_WEBOAUTH_INSTANCE_NOT_FOUND);
            return false;
        }

        // Everything went fine
        return true;
    }

    /**
     * Unregisters the OAuth endpoint.
     * The method unregisters the OAuth endpoint from the IPS WebOAuth Control instance.
     * It is automatically called when the module is deleted.
     * @see WebOAuthIOModule::Destroy()
     */
    protected function UnregisterOAuth()
    {
        $this->Debug('Unregistering OAuth Endpoint', $this->oAuthId);

        // Find the WebOAuth instance
        $ids = IPS::GetInstanceListByModuleId(self::WEBOAUTH_MODULE_ID);
        if (sizeof($ids) == 1) {

            // Get the list of client IDs
            $clientIds = json_decode(IPS::GetProperty($ids[0], "ClientIDs"), true);

            // Repeat until all instances were removed
            $index = null;
            $found = true;
            while ($found !== false) {
                $found = false;
                // Loop through all client IDs
                foreach ($clientIds as $index => $clientId) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    if ($clientId['TargetID'] == $this->InstanceID) {
                        // The OAuth endpoint was found
                        $found = $index;
                        break;
                    }
                }
                // Remove it from the list
                if ($found !== false) {
                    array_splice($clientIds, $index, 1);
                }
            }

            // Save the hook list back to the WebOAuth instance
            IPS::SetProperty($ids[0], "ClientIDs", json_encode($clientIds));
            IPS::ApplyChanges($ids[0]);
        }
    }

    /**
     * Processes a OAuth request.
     * This method is automatically called by the IPS WebOAuth Control instance.
     * @see WebOAuthIOModule::ProcessOAuthData()
     */
    protected function ProcessOAuthData()
    {
        // Forward the request to the common method
        $this->ProcessData();
    }

    /**
     * Checks if the IP-Symcon Connect service is connected.
     * @return bool True if the IP-Symcon Connect service is active (connected).
     */
    protected function IsConnectActive()
    {
        return IPSConnect::IsConnected();
    }

}