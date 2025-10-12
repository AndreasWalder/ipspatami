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


use Patami\IPS\Modules\SingletonModule;
use Patami\IPS\Libraries\Library;
use Patami\IPS\Framework;
use Patami\IPS\System\IPS;
use Patami\IPS\System\IPSConnect;
use Patami\IPS\System\IPSLive;
use Patami\IPS\System\System;
use Patami\IPS\Objects\StringVariable;
use Patami\IPS\Objects\IntegerVariable;
use Patami\IPS\Objects\BooleanVariable;
use Patami\IPS\Objects\Variable;
use Patami\IPS\Objects\VariableProfiles;
use Patami\IPS\Objects\IntegerVariableProfile;
use Patami\IPS\Libraries\Exceptions\LibraryUpdateFailedException;
use Patami\Helpers\PHP;
use Patami\Helpers\Number;
use Patami\IPS\Services\KNX\KNX;
use Patami\IPS\I18N\Translator;
use Patami\IPS\System\Color;


Translator::AddDir(__DIR__ . DIRECTORY_SEPARATOR . 'translations');


/**
 * PatamiFramework IPS Module.
 * The GUID of this module is {F82A758B-3F08-4E06-BBF9-A4055033611D}.
 * @package IPSPATAMI
 */
class PatamiFramework extends SingletonModule
{

    /** The minimum interval for an update timer. */
    const MIN_TIMER_INTERVAL = 0;
    /** The maximum interval for an update timer. */
    const MAX_TIMER_INTERVAL = 3600;

    /** A timer interval is invalid. */
    const STATUS_ERROR_INVALID_TIMER_INTERVAL = 201;

    /** The cURL proxy server address is invalid. */
    const STATUS_ERROR_INVALID_CURL_PROXY_ADDRESS = 202;
    /** The cURL proxy server port is invalid. */
    const STATUS_ERROR_INVALID_CURL_PROXY_PORT = 203;
    /** The cURL proxy server username is invalid. */
    const STATUS_ERROR_INVALID_CURL_PROXY_USERNAME = 204;

    /** Updating the autoload file failed. */
    const STATUS_ERROR_UPDATE_AUTOLOAD_FAILED = 206;

    /** Property name for configuration page. */
    const PROPERTY_CONFIG_PAGE = 'ConfigPage';

    /** General configuration page. */
    const CONFIG_PAGE_GENERAL = 0;
    /** Status variables configuration page. */
    const CONFIG_PAGE_STATUS_VARIABLES = 1;
    /** HTTP request configuration page. */
    const CONFIG_PAGE_HTTP_REQUEST = 2;
    /** Debugging configuration page. */
    const CONFIG_PAGE_DEBUGGING = 3;

    /** Property name for the IPS forum username. */
    const PROPERTY_IPS_FORUM_USERNAME = 'IPSForumUsername';
    /** Property name for the IPS forum password. */
    const PROPERTY_IPS_FORUM_PASSWORD = 'IPSForumPassword';

    /** Property name for the update interval of the IPS license and subscription status. */
    const PROPERTY_UPDATE_IPS_LIVE_STATUS_INTERVAL = 'UpdateIPSLiveStatusInterval';
    /** Property name for the update interval of the IPS Connect service status. */
    const PROPERTY_UPDATE_IPS_CONNECT_STATUS_INTERVAL = 'UpdateIPSConnectStatusInterval';
    /** Property name of the update interval of the IPS object count. */
    const PROPERTY_UPDATE_IPS_OBJECT_COUNTS_INTERVAL = 'UpdateIPSObjectCountsInterval';
    /** Property name of the update interval of the public IP. */
    const PROPERTY_UPDATE_PUBLIC_IP_INTERVAL = 'UpdatePublicIPInterval';

    /** Property name for the checkbox to enable using a proxy server for cURL requests. */
    const PROPERTY_CURL_USE_PROXY = 'cURLUseProxy';
    /** Property name for the proxy server address used by cURL. */
    const PROPERTY_CURL_PROXY_ADDRESS = 'cURLProxyAddress';
    /** Property name for the proxy server port used by cURL. */
    const PROPERTY_CURL_PROXY_PORT = 'cURLProxyPort';
    /** Property name for the checkbox to enable proxy authentication for cURL requests. */
    const PROPERTY_CURL_PROXY_USE_AUTHENTICATION = 'cURLProxyUseAuthentication';
    /** Property name for the proxy username used by cURL. */
    const PROPERTY_CURL_PROXY_USERNAME = 'cURLProxyUsername';
    /** Property name for the proxy password used by cURL. */
    const PROPERTY_CURL_PROXY_PASSWORD = 'cURLProxyPassword';
    /** Property name for the checkbox to disable SSL certificate verification for cURL requests. */
    const PROPERTY_CURL_DISABLE_CERTIFICATE_VERIFICATION = 'cURLDisableCertificateVerification';

    /** Property name for the IPS object ID of the custom autoload script. */
    const PROPERTY_CUSTOM_AUTOLOAD_SCRIPT_ID = 'CustomAutoLoadScriptID';
    /** Property name for the checkbox to enable global autoloading of the framework. */
    const PROPERTY_GLOBAL_AUTOLOAD_ENABLED = 'GlobalAutoLoadEnabled';

    /** Property name for the checkbox to enable extended debugging. */
    const PROPERTY_EXTENDED_DEBUGGING_ENABLED = 'ExtendedDebuggingEnabled';
    /** Property name for the checkbox to enable the error handler. */
    const PROPERTY_ERROR_HANDLER_ENABLED = 'ErrorHandlerEnabled';

    /** Ident of the Framework Version status variable. */
    const VAR_FRAMEWORK_VERSION = 'FrameworkVersion';
    /** Ident of the Framework Branch status variable. */
    const VAR_FRAMEWORK_BRANCH = 'FrameworkBranch';
    /** Ident of the Framework Commit status variable. */
    const VAR_FRAMEWORK_COMMIT = 'FrameworkCommit';
    /** Ident of the Framework Update Timestamp status variable. */
    const VAR_FRAMEWORK_UPDATE_TIMESTAMP = 'FrameworkUpdateTimestamp';

    /** Ident of the IPS Version status variable. */
    const VAR_IPS_VERSION = 'IPSVersion';
    /** Ident of the IPS Version date status variable. */
    const VAR_IPS_VERSION_DATE = 'IPSVersionDate';
    /** Ident of the IPS 64-bit status variable. */
    const VAR_IPS_64BIT = 'IPS64Bit';
    /** Ident of the IPS installed date status variable. */
    const VAR_IPS_INSTALLED_DATE = 'IPSInstalledDate';
    /** Ident of the IPS started timestamp status variable. */
    const VAR_IPS_STARTED_TIMESTAMP = 'IPSStartedTimestamp';

    /** Ident of the IPS license type status variable. */
    const VAR_IPS_LICENSE_TYPE = 'IPSLicenseType';
    /** Ident of the IPS license email status variable. */
    const VAR_IPS_LICENSE_EMAIL = 'IPSLicenseEmail';
    /** Ident of the IPS license variable limit status variable. */
    const VAR_IPS_LICENSE_VARIABLE_LIMIT = 'IPSLicenseVariableLimit';
    /** Ident of the IPS license variable limit exceeded status variable. */
    const VAR_IPS_LICENSE_VARIABLE_LIMIT_EXCEEDED = 'IPSLicenseVariableLimitExceeded';
    /** Ident of the IPS license WebFront limit status variable. */
    const VAR_IPS_LICENSE_WEBFRONT_LIMIT = 'IPSLicenseWebFrontLimit';
    /** Ident of the IPS license dashboard support status variable. */
    const VAR_IPS_LICENSE_DASHBOARD_SUPPORT = 'IPSLicenseDashboardSupport';

    /** Ident of the IPS subscription valid status variable. */
    const VAR_IPS_SUBSCRIPTION_VALID = 'IPSSubscriptionValid';
    /** Ident of the IPS subscription expiration status variable. */
    const VAR_IPS_SUBSCRIPTION_EXPIRE_TIMESTAMP = 'IPSSubscriptionExpireTimestamp';

    /** Ident of the IPS 64-bit status variable. */
    const VAR_IPS_CONNECT_CONNECTED = 'IPSConnectConnected';
    /** Ident of the IPS 64-bit status variable. */
    const VAR_IPS_CONNECT_HEALTHY = 'IPSConnectHealthy';

    /** Ident of the PHP Version status variable. */
    const VAR_PHP_VERSION = 'PHPVersion';

    /** Ident of the OS name and version status variable. */
    const VAR_SYSTEM_OS_NAME = 'OSName';
    /** Ident of the system public IP status variable. */
    const VAR_SYSTEM_PUBLIC_IP = 'PublicIP';
    /** Ident of the system public IP status variable. */
    const VAR_SYSTEM_PUBLIC_IP_HOSTNAME = 'PublicIPHostname';

    /** Ident of the Object Count status variable. */
    const VAR_IPS_OBJECT_COUNT = 'IPSObjectCount';
    /** Ident of the Available Object Count status variable. */
    const VAR_IPS_AVAILABLE_OBJECT_COUNT = 'IPSAvailableObjectCount';
    /** Ident of the Library Count status variable. */
    const VAR_IPS_LIBRARY_COUNT = 'IPSLibraryCount';
    /** Ident of the Module Count status variable. */
    const VAR_IPS_MODULE_COUNT = 'IPSModuleCount';
    /** Ident of the Instance Count status variable. */
    const VAR_IPS_INSTANCE_COUNT = 'IPSInstanceCount';
    /** Ident of the Variable Count status variable. */
    const VAR_IPS_VARIABLE_COUNT = 'IPSVariableCount';
    /** Ident of the Script Count status variable. */
    const VAR_IPS_SCRIPT_COUNT = 'IPSScriptCount';
    /** Ident of the Function Count status variable. */
    const VAR_IPS_FUNCTION_COUNT = 'IPSFunctionCount';
    /** Ident of the Event Count status variable. */
    const VAR_IPS_EVENT_COUNT = 'IPSEventCount';
    /** Ident of the Media Count status variable. */
    const VAR_IPS_MEDIA_COUNT = 'IPSMediaCount';
    /** Ident of the Link Count status variable. */
    const VAR_IPS_LINK_COUNT = 'IPSLinkCount';
    /** Ident of the Category Count status variable. */
    const VAR_IPS_CATEGORY_COUNT = 'IPSCategoryCount';

    /** Ident of the database size status variable. */
    const VAR_IPS_DATABASE_SIZE = 'IPSDatabaseSize';
    /** Ident of the logs size status variable. */
    const VAR_IPS_LOGS_SIZE = 'IPSLogsSize';
    /** Ident of the scripts size status variable. */
    const VAR_IPS_SCRIPTS_SIZE = 'IPSScriptsSize';

    /** Maximum number of objects supported by IPS. */
    const MAX_OBJECT_COUNT = 50000;

    /** Name of the timer that updates the IPS subscription and license status. */
    const UPDATE_IPS_LIVE_STATUS_TIMER_NAME = 'UpdateIPSLiveStatus';
    /** Name of the timer that updates the IPS Connect status. */
    const UPDATE_IPS_CONNECT_STATUS_TIMER_NAME = 'UpdateIPSConnectStatus';
    /** Name of the timer that updates the IPS object count. */
    const UPDATE_IPS_OBJECT_COUNTS_TIMER_NAME = 'UpdateIPSObjectCounts';
    /** Name of the timer that updates the public IP. */
    const UPDATE_PUBLIC_IP_TIMER_NAME = 'UpdatePublicIP';

    /**
     * Registers configuration properties and timers.
     * {@inheritDoc}
     */
    public function Create()
    {
        // Call the parent method
        parent::Create();

        // Add properties
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_CONFIG_PAGE, 0);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString(self::PROPERTY_IPS_FORUM_USERNAME, '');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString(self::PROPERTY_IPS_FORUM_PASSWORD, '');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_UPDATE_IPS_LIVE_STATUS_INTERVAL, 3600);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_UPDATE_IPS_CONNECT_STATUS_INTERVAL, 300);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_UPDATE_IPS_OBJECT_COUNTS_INTERVAL, 300);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_UPDATE_PUBLIC_IP_INTERVAL, 60);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_CUSTOM_AUTOLOAD_SCRIPT_ID, 0);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyBoolean(self::PROPERTY_CURL_USE_PROXY, false);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString(self::PROPERTY_CURL_PROXY_ADDRESS, '');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_CURL_PROXY_PORT, 8080);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyBoolean(self::PROPERTY_CURL_PROXY_USE_AUTHENTICATION, false);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString(self::PROPERTY_CURL_PROXY_USERNAME, '');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString(self::PROPERTY_CURL_PROXY_PASSWORD, '');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyBoolean(self::PROPERTY_CURL_DISABLE_CERTIFICATE_VERIFICATION, false);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyBoolean(self::PROPERTY_GLOBAL_AUTOLOAD_ENABLED, true);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyBoolean(self::PROPERTY_EXTENDED_DEBUGGING_ENABLED, false);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyBoolean(self::PROPERTY_ERROR_HANDLER_ENABLED, true);

        // Register timers to update the status variables
        $id = $this->GetId();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterTimer(self::UPDATE_IPS_LIVE_STATUS_TIMER_NAME, 0, sprintf('Patami_UpdateIPSLiveStatus(%d);', $id));
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterTimer(self::UPDATE_IPS_CONNECT_STATUS_TIMER_NAME, 0, sprintf('Patami_UpdateIPSConnectStatus(%d);', $id));
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterTimer(self::UPDATE_IPS_OBJECT_COUNTS_TIMER_NAME, 0, sprintf('Patami_UpdateIPSObjectCounts(%d);', $id));
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterTimer(self::UPDATE_PUBLIC_IP_TIMER_NAME, 0, sprintf('Patami_UpdatePublicIP(%d);', $id));
    }

    /**
     * Performs cleanup tasks when the instance is deleted.
     * {@inheritDoc}
     * The PatamiFramework implementation removes the framework bootstrap code from the IPS autoload file.
     */
    public function Destroy()
    {
        // Remove auto include of the Patami Framework
        $this->UnregisterAutoInclude();

        // Call the parent method
        parent::Destroy();
    }

    /**
     * Returns the IPS module configuration form data.
     * {@inheritDoc}
     * The PatamiFramework implementation adds a couple of configuration fields and a list that allows users to
     * load/unload optional libraries of the Patami Framework.
     */
    protected function GetConfigurationFormData()
    {
        // Call the parent method
        $data = parent::GetConfigurationFormData();

        // Add configuration page dropdown
        array_push($data['elements'],
            array(
                'type' => 'Select',
                'name' => 'ConfigPage',
                'caption' => Translator::Get('patami.patamiframework.form.config_page.label'),
                'options' => array(
                    array(
                        'label' => Translator::Get('patami.patamiframework.form.config_page.general'),
                        'value' => self::CONFIG_PAGE_GENERAL
                    ),
                    array(
                        'label' => Translator::Get('patami.patamiframework.form.config_page.status_variables'),
                        'value' => self::CONFIG_PAGE_STATUS_VARIABLES
                    ),
                    array(
                        'label' => Translator::Get('patami.patamiframework.form.config_page.http_requests'),
                        'value' => self::CONFIG_PAGE_HTTP_REQUEST
                    ),
                    array(
                        'label' => Translator::Get('patami.patamiframework.form.config_page.debugging'),
                        'value' => self::CONFIG_PAGE_DEBUGGING
                    )
                )
            ),
            array(
                'type' => 'Label',
                'label' => ''
            )
        );

        // Get the configuration page
        $configPage = $this->GetConfigPage();

        if ($configPage == self::CONFIG_PAGE_GENERAL) {

            // Add settings
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => Translator::Get('patami.patamiframework.form.auto_load.script_label')
                ),
                array(
                    'type' => 'SelectScript',
                    'name' => self::PROPERTY_CUSTOM_AUTOLOAD_SCRIPT_ID,
                    'caption' => ''
                ),
                array(
                    'type' => 'CheckBox',
                    'name' => self::PROPERTY_GLOBAL_AUTOLOAD_ENABLED,
                    'caption' => Translator::Get('patami.patamiframework.form.auto_load.framework_label')
                )
            );

        } elseif ($configPage == self::CONFIG_PAGE_STATUS_VARIABLES) {

            // Add status variable update interval settings
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => Translator::Get('patami.patamiframework.form.update_intervals.label')
                ),
                array(
                    'type' => 'NumberSpinner',
                    'name' => self::PROPERTY_UPDATE_IPS_LIVE_STATUS_INTERVAL,
                    'caption' => Translator::Get('patami.patamiframework.form.update_intervals.ips_live_status_label')
                ),
                array(
                    'type' => 'NumberSpinner',
                    'name' => self::PROPERTY_UPDATE_IPS_CONNECT_STATUS_INTERVAL,
                    'caption' => Translator::Get('patami.patamiframework.form.update_intervals.ips_connect_status_label')
                ),
                array(
                    'type' => 'NumberSpinner',
                    'name' => self::PROPERTY_UPDATE_IPS_OBJECT_COUNTS_INTERVAL,
                    'caption' => Translator::Get('patami.patamiframework.form.update_intervals.ips_object_counts_label')
                ),
                array(
                    'type' => 'NumberSpinner',
                    'name' => self::PROPERTY_UPDATE_PUBLIC_IP_INTERVAL,
                    'caption' => Translator::Get('patami.patamiframework.form.update_intervals.public_ip_label')
                )
            );

            // Add IPS live status update settings
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => ''
                ),
                array(
                    'type' => 'Label',
                    'label' => Translator::Get('patami.patamiframework.form.live_status.label')
                ),
                array(
                    'type' => 'ValidationTextBox',
                    'name' => self::PROPERTY_IPS_FORUM_USERNAME,
                    'caption' => Translator::Get('patami.patamiframework.form.live_status.username_label')
                ),
                array(
                    'type' => 'PasswordTextBox',
                    'name' => self::PROPERTY_IPS_FORUM_PASSWORD,
                    'caption' => Translator::Get('patami.patamiframework.form.live_status.password_label')
                )
            );

        } elseif ($configPage == self::CONFIG_PAGE_HTTP_REQUEST) {

            // Add cURL settings
            array_push($data['elements'],
                array(
                    'type' => 'CheckBox',
                    'name' => self::PROPERTY_CURL_USE_PROXY,
                    'caption' => Translator::Get('patami.patamiframework.form.curl.use_proxy_label')
                )
            );
            if ($this->IsProxyEnabled()) {
                array_push($data['elements'],
                    array(
                        'type' => 'ValidationTextBox',
                        'name' => self::PROPERTY_CURL_PROXY_ADDRESS,
                        'caption' => Translator::Get('patami.patamiframework.form.curl.proxy_address_label')
                    ),
                    array(
                        'type' => 'NumberSpinner',
                        'name' => self::PROPERTY_CURL_PROXY_PORT,
                        'caption' => Translator::Get('patami.patamiframework.form.curl.proxy_port_label')
                    ),
                    array(
                        'type' => 'CheckBox',
                        'name' => self::PROPERTY_CURL_PROXY_USE_AUTHENTICATION,
                        'caption' => Translator::Get('patami.patamiframework.form.curl.proxy_use_authentication_label')
                    )
                );
                if ($this->IsProxyAuthenticationEnabled()) {
                    array_push($data['elements'],
                        array(
                            'type' => 'ValidationTextBox',
                            'name' => self::PROPERTY_CURL_PROXY_USERNAME,
                            'caption' => Translator::Get('patami.patamiframework.form.curl.proxy_username_label')
                        ),
                        array(
                            'type' => 'PasswordTextBox',
                            'name' => self::PROPERTY_CURL_PROXY_PASSWORD,
                            'caption' => Translator::Get('patami.patamiframework.form.curl.proxy_password_label')
                        )
                    );
                }
            }
            array_push($data['elements'],
                array(
                    'type' => 'CheckBox',
                    'name' => self::PROPERTY_CURL_DISABLE_CERTIFICATE_VERIFICATION,
                    'caption' => Translator::Get('patami.patamiframework.form.curl.proxy_disable_certificate_validation_label')
                )
            );

        } elseif ($configPage == self::CONFIG_PAGE_DEBUGGING) {

            // Add extended settings
            array_push($data['elements'],
                array(
                    'type' => 'CheckBox',
                    'name' => self::PROPERTY_EXTENDED_DEBUGGING_ENABLED,
                    'caption' => Translator::Get('patami.patamiframework.form.extended_debugging.label')
                ),
                array(
                    'type' => 'CheckBox',
                    'name' => self::PROPERTY_ERROR_HANDLER_ENABLED,
                    'caption' => Translator::Get('patami.patamiframework.form.error_handler.label')
                )
            );

        }

        // Add a button to update all Patami libraries
        array_unshift($data['actions'],
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.patamiframework.form.actions.update_framework.label')
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.patamiframework.form.actions.update_framework.button'),
                'onClick' => 'Patami_UpdateLibrary($id);'
            )
        );

        // Add a button to update the status
        array_unshift($data['actions'],
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.patamiframework.form.actions.update_status.label')
            ),
            array(
                'type' => 'Button',
                'label' => Translator::Get('patami.patamiframework.form.actions.update_status.button'),
                'onClick' => 'Patami_UpdateStatus($id);'
            )
        );

        // Add status messages
        array_push($data['status'],
            array(
                'code' => self::STATUS_ERROR_INVALID_TIMER_INTERVAL,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiframework.form.status.invalid_timer_interval')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_CURL_PROXY_ADDRESS,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiframework.form.status.invalid_curl_proxy_address')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_CURL_PROXY_PORT,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiframework.form.status.invalid_curl_proxy_port')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_CURL_PROXY_USERNAME,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiframework.form.status.invalid_curl_proxy_username')
            ),
            array(
                'code' => self::STATUS_ERROR_UPDATE_AUTOLOAD_FAILED,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiframework.form.status.update_autoload_failed')
            )
        );

        // Return the configuration form
        return $data;
    }

    /**
     * Performs actions required when the configuration of this instance was saved.
     * {@inheritDoc}
     * The PatamiFramework implementation registers the framework in the IPS autoload file.
     */
    protected function Configure()
    {
        // Check the cURL proxy settings
        if ($this->IsProxyEnabled()) {
            // cURL uses a proxy
            $address = $this->GetProxyAddress();
            if ($address == '') {
                // The proxy server address is empty
                /** @noinspection PhpUndefinedMethodInspection */
                $this->SetStatus(self::STATUS_ERROR_INVALID_CURL_PROXY_ADDRESS);
                return;
            }
            $port = $this->GetProxyPort();
            if (! Number::IsInRange($port, 0, 65535)) {
                // The proxy server port is out of range
                /** @noinspection PhpUndefinedMethodInspection */
                $this->SetStatus(self::STATUS_ERROR_INVALID_CURL_PROXY_PORT);
                return;
            }
            if ($this->IsProxyAuthenticationEnabled()) {
                // cURL uses proxy authentication
                $username = $this->GetProxyUsername();
                if ($username == '') {
                    // The proxy server username is empty
                    /** @noinspection PhpUndefinedMethodInspection */
                    $this->SetStatus(self::STATUS_ERROR_INVALID_CURL_PROXY_USERNAME);
                    return;
                }
            }
        }

        // Register auto include of the Patami Framework
        if ($this->IsGlobalAutoLoadEnabled()) {
            $result = $this->RegisterAutoInclude();
        } else {
            $result = $this->UnregisterAutoInclude();
        }
        if (! $result) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->SetStatus(self::STATUS_ERROR_UPDATE_AUTOLOAD_FAILED);
            return;
        }

        // Create variable profiles
        VariableProfiles::CreateYesNo();
        VariableProfiles::CreateYesNoIC();
        VariableProfiles::CreateYesNoNC();
        VariableProfiles::CreateOnOff();
        VariableProfiles::CreateOnOffIC();
        VariableProfiles::CreateOnOffNC();

        // Create license limit variable profile
        $profile = new IntegerVariableProfile(VariableProfiles::TYPE_PATAMI_LICENSE_LIMIT);
        $profile->AddAssociationByValue(0, Translator::Get('patami.patamiframework.profiles.license_limit.unlimited'), '', new Color(Color::TRANSPARENT));

        // Create Megabytes variable profile
        $profile = new IntegerVariableProfile(VariableProfiles::TYPE_PATAMI_MEGABYTES);
        $profile->SetSuffix(' MB');

        // Remove obsolete variables (removed in earlier versions of the module)
        /** @noinspection PhpUndefinedMethodInspection */
        $this->UnregisterVariable('UpdateStatusTimestamp');

        // Add framework status variables
        $index = 0;
        $variable = new StringVariable($this->RegisterVariableStringEx(self::VAR_FRAMEWORK_VERSION, Translator::Get('patami.patamiframework.vars.framework_version')));
        $variable->Set(Framework::GetVersion())->SetPosition($index++);
        $variable = new StringVariable($this->RegisterVariableStringEx(self::VAR_FRAMEWORK_BRANCH, Translator::Get('patami.patamiframework.vars.framework_branch')));
        $variable->Set(Framework::GetBranch())->SetPosition($index++);
        $variable = new StringVariable($this->RegisterVariableStringEx(self::VAR_FRAMEWORK_COMMIT, Translator::Get('patami.patamiframework.vars.framework_commit')));
        $variable->Set(Framework::GetCommit())->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_FRAMEWORK_UPDATE_TIMESTAMP, Translator::Get('patami.patamiframework.vars.framework_update_timestamp'), VariableProfiles::TYPE_UNIX_TIMESTAMP));
        $variable->Set(Framework::GetUpdateTime())->SetPosition($index++);

        // Add IPS status variables
        $variable = new StringVariable($this->RegisterVariableStringEx(self::VAR_IPS_VERSION, Translator::Get('patami.patamiframework.vars.ips_version')));
        $variable->Set(IPS::GetKernelVersion())->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_VERSION_DATE, Translator::Get('patami.patamiframework.vars.ips_version_date'), VariableProfiles::TYPE_UNIX_TIMESTAMP_DATE));
        $variable->SetPosition($index++);
        $variable = new BooleanVariable($this->RegisterVariableBooleanEx(self::VAR_IPS_64BIT, Translator::Get('patami.patamiframework.vars.ips_64bit'), VariableProfiles::TYPE_PATAMI_YESNO_NC));
        $variable->Set(IPS::Is64Bit())->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_INSTALLED_DATE, Translator::Get('patami.patamiframework.vars.ips_installed_date'), VariableProfiles::TYPE_UNIX_TIMESTAMP_DATE));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_STARTED_TIMESTAMP, Translator::Get('patami.patamiframework.vars.ips_started_timestamp'), VariableProfiles::TYPE_UNIX_TIMESTAMP));
        $variable->Set(IPS::GetKernelStartTime())->SetPosition($index++);

        // Add IPS license status variables
        $variable = new StringVariable($this->RegisterVariableStringEx(self::VAR_IPS_LICENSE_TYPE, Translator::Get('patami.patamiframework.vars.ips_license_type')));
        $variable->SetPosition($index++);
        $variable = new StringVariable($this->RegisterVariableStringEx(self::VAR_IPS_LICENSE_EMAIL, Translator::Get('patami.patamiframework.vars.ips_license_email')));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_LICENSE_VARIABLE_LIMIT, Translator::Get('patami.patamiframework.vars.ips_license_variable_limit'), VariableProfiles::TYPE_PATAMI_LICENSE_LIMIT));
        $variable->SetPosition($index++);
        $variable = new BooleanVariable($this->RegisterVariableBooleanEx(self::VAR_IPS_LICENSE_VARIABLE_LIMIT_EXCEEDED, Translator::Get('patami.patamiframework.vars.ips_license_variable_limit_exceeded'), VariableProfiles::TYPE_PATAMI_YESNO_IC));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_LICENSE_WEBFRONT_LIMIT, Translator::Get('patami.patamiframework.vars.ips_license_webfront_limit'), VariableProfiles::TYPE_PATAMI_LICENSE_LIMIT));
        $variable->SetPosition($index++);
        $variable = new BooleanVariable($this->RegisterVariableBooleanEx(self::VAR_IPS_LICENSE_DASHBOARD_SUPPORT, Translator::Get('patami.patamiframework.vars.ips_license_dashboard_support'), VariableProfiles::TYPE_PATAMI_YESNO_NC));
        $variable->SetPosition($index++);

        // Add IPS subscription status variables
        $variable = new BooleanVariable($this->RegisterVariableBooleanEx(self::VAR_IPS_SUBSCRIPTION_VALID, Translator::Get('patami.patamiframework.vars.ips_subscription_valid'), VariableProfiles::TYPE_PATAMI_YESNO));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_SUBSCRIPTION_EXPIRE_TIMESTAMP, Translator::Get('patami.patamiframework.vars.ips_subscription_expire_timestamp'), VariableProfiles::TYPE_UNIX_TIMESTAMP_DATE));
        $variable->SetPosition($index++);

        // Add IPS Connect status variables
        $variable = new BooleanVariable($this->RegisterVariableBooleanEx(self::VAR_IPS_CONNECT_CONNECTED, Translator::Get('patami.patamiframework.vars.ips_connect_connected'), VariableProfiles::TYPE_PATAMI_YESNO));
        $variable->SetPosition($index++);
        $variable = new BooleanVariable($this->RegisterVariableBooleanEx(self::VAR_IPS_CONNECT_HEALTHY, Translator::Get('patami.patamiframework.vars.ips_connect_healthy'), VariableProfiles::TYPE_PATAMI_YESNO));
        $variable->SetPosition($index++);

        // Add IPS object status variables
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_OBJECT_COUNT, Translator::Get('patami.patamiframework.vars.ips_object_count')));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_AVAILABLE_OBJECT_COUNT, Translator::Get('patami.patamiframework.vars.ips_available_object_count')));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_LIBRARY_COUNT, Translator::Get('patami.patamiframework.vars.ips_library_count')));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_MODULE_COUNT, Translator::Get('patami.patamiframework.vars.ips_module_count')));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_INSTANCE_COUNT, Translator::Get('patami.patamiframework.vars.ips_instance_count')));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_VARIABLE_COUNT, Translator::Get('patami.patamiframework.vars.ips_variable_count')));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_SCRIPT_COUNT, Translator::Get('patami.patamiframework.vars.ips_script_count')));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_FUNCTION_COUNT, Translator::Get('patami.patamiframework.vars.ips_function_count')));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_EVENT_COUNT, Translator::Get('patami.patamiframework.vars.ips_event_count')));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_MEDIA_COUNT, Translator::Get('patami.patamiframework.vars.ips_media_count')));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_LINK_COUNT, Translator::Get('patami.patamiframework.vars.ips_link_count')));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_CATEGORY_COUNT, Translator::Get('patami.patamiframework.vars.ips_category_count')));
        $variable->SetPosition($index++);

        // Add IPS size status variables
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_DATABASE_SIZE, Translator::Get('patami.patamiframework.vars.ips_database_size'), VariableProfiles::TYPE_PATAMI_MEGABYTES));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_LOGS_SIZE, Translator::Get('patami.patamiframework.vars.ips_logs_size'), VariableProfiles::TYPE_PATAMI_MEGABYTES));
        $variable->SetPosition($index++);
        $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_IPS_SCRIPTS_SIZE, Translator::Get('patami.patamiframework.vars.ips_scripts_size'), VariableProfiles::TYPE_PATAMI_MEGABYTES));
        $variable->SetPosition($index++);

        // Add PHP status variables
        $variable = new StringVariable($this->RegisterVariableStringEx(self::VAR_PHP_VERSION, Translator::Get('patami.patamiframework.vars.php_version')));
        $variable->Set(PHP::GetVersion())->SetPosition($index++);

        // Add system status variables
        $variable = new StringVariable($this->RegisterVariableStringEx(self::VAR_SYSTEM_OS_NAME, Translator::Get('patami.patamiframework.vars.system_os_name')));
        $variable->Set(System::GetOSName())->SetPosition($index++);
        $variable = new StringVariable($this->RegisterVariableStringEx(self::VAR_SYSTEM_PUBLIC_IP, Translator::Get('patami.patamiframework.vars.system_public_ip')));
        $variable->SetPosition($index++);
        $variable = new StringVariable($this->RegisterVariableStringEx(self::VAR_SYSTEM_PUBLIC_IP_HOSTNAME, Translator::Get('patami.patamiframework.vars.system_public_ip_hostname')));
        $variable->SetPosition($index);

        // Update the other status variables
        $this->UpdateStatus();

        // Check if one of the intervals is invalid
        $ipsLiveInterval = $this->GetUpdateIPSLiveStatusInterval();
        $ipsConnectInterval = $this->GetUpdateIPSConnectStatusInterval();
        $objectCountsInterval = $this->GetUpdateIPSObjectCountsInterval();
        $publicIPInterval = $this->GetUpdatePublicIPInterval();
        if (! (
            $this->IsTimerIntervalValid($ipsLiveInterval) &&
            $this->IsTimerIntervalValid($ipsConnectInterval) &&
            $this->IsTimerIntervalValid($objectCountsInterval) &&
            $this->IsTimerIntervalValid($publicIPInterval)
        )) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->SetStatus(self::STATUS_ERROR_INVALID_TIMER_INTERVAL);
            return;
        }

        // Update the timer intervals
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SetTimerInterval(self::UPDATE_IPS_LIVE_STATUS_TIMER_NAME, $ipsLiveInterval * 1000);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SetTimerInterval(self::UPDATE_IPS_CONNECT_STATUS_TIMER_NAME, $ipsConnectInterval * 1000);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SetTimerInterval(self::UPDATE_IPS_OBJECT_COUNTS_TIMER_NAME, $objectCountsInterval * 1000);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SetTimerInterval(self::UPDATE_PUBLIC_IP_TIMER_NAME, $publicIPInterval * 1000);

        // Call the parent method
        parent::Configure();
    }

    /**
     * Checks if the given timer interval is within the allowed range.
     * @param int $interval Timer interval in seconds.
     * @return bool True if the timer interval is valid.
     */
    protected function IsTimerIntervalValid($interval)
    {
        return Number::IsInRange($interval, self::MIN_TIMER_INTERVAL, self::MAX_TIMER_INTERVAL, true);
    }

    /**
     * Updates the Patami Framework library.
     * This method is called when the user clicks on the Updates Libraries button on the configuration page of the instance.
     */
    public function UpdateLibrary()
    {
        $tag = 'Framework Update';

        // Create the library object
        $lib = Library::Create(Framework::FRAMEWORK_MODULE_KEY);
        if ($lib->IsUpdateAvailable()) {
            // An update is available
            try {
                $lib->Update();
                // Reload the library information
                $lib = Library::Create(Framework::FRAMEWORK_MODULE_KEY);
                $this->Debug($tag, sprintf('Updated to version %s commit %s', $lib->GetVersion(), $lib->GetCommit()));
                // Output the result message
                echo Translator::Get('patami.patamiframework.form.actions.update_framework.updated');
            } catch (LibraryUpdateFailedException $e) {
                $this->Debug($tag, 'Updated failed');
                // Output the result message
                echo Translator::Get('patami.patamiframework.form.actions.update_framework.failed');
            }
        } else {
            // No update available
            $this->Debug($tag, 'No update available');
            // Output the result message
            echo Translator::Get('patami.patamiframework.form.actions.update_framework.uptodate');
        }
    }

    /**
     * Returns the current config page.
     * @return int Index of the config page.
     */
    protected function GetConfigPage()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_CONFIG_PAGE);
    }

    /**
     * Returns the IPS forum username.
     * @return string IPS forum username.
     */
    public function GetIPSForumUsername()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyString(self::PROPERTY_IPS_FORUM_USERNAME);
    }

    /**
     * Returns the IPS forum password.
     * @return string IPS forum password.
     */
    public function GetIPSForumPassword()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyString(self::PROPERTY_IPS_FORUM_PASSWORD);
    }

    /**
     * Returns the number of seconds between updates of the IPS Live status.
     * @return int Number of seconds between updates.
     */
    public function GetUpdateIPSLiveStatusInterval()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_UPDATE_IPS_LIVE_STATUS_INTERVAL);
    }

    /**
     * Returns the number of seconds between updates of the IPS Connect status.
     * @return int Number of seconds between updates.
     */
    public function GetUpdateIPSConnectStatusInterval()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_UPDATE_IPS_CONNECT_STATUS_INTERVAL);
    }

    /**
     * Returns the number of seconds between updates of the IPS object count.
     * @return int Number of seconds between updates.
     */
    public function GetUpdateIPSObjectCountsInterval()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_UPDATE_IPS_OBJECT_COUNTS_INTERVAL);
    }

    /**
     * Returns the number of seconds between updates of the public IP.
     * @return int Number of seconds between updates.
     */
    public function GetUpdatePublicIPInterval()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_UPDATE_PUBLIC_IP_INTERVAL);
    }

    /**
     * Checks if a proxy server should be used for cURL requests.
     * @return bool True if cURL should use a proxy server.
     */
    public function IsProxyEnabled()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyBoolean(self::PROPERTY_CURL_USE_PROXY);
    }

    /**
     * Returns the proxy server address used for cURL requests.
     * @return string Proxy server address.
     */
    public function GetProxyAddress()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyString(self::PROPERTY_CURL_PROXY_ADDRESS);
    }

    /**
     * Returns the proxy server port used for cURL requests.
     * @return int Proxy server port.
     */
    public function GetProxyPort()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_CURL_PROXY_PORT);
    }

    /**
     * Checks if proxy authentcation should be used for cURL requests.
     * @return bool True if cURL uses authentication for proxy requests.
     */
    public function IsProxyAuthenticationEnabled()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyBoolean(self::PROPERTY_CURL_PROXY_USE_AUTHENTICATION);
    }

    /**
     * Returns the username used by cURL for proxy requests.
     * @return string Proxy username.
     */
    public function GetProxyUsername()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyString(self::PROPERTY_CURL_PROXY_USERNAME);
    }

    /**
     * Returns the password used by cURL for proxy requests.
     * @return string Proxy password.
     */
    public function GetProxyPassword()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyString(self::PROPERTY_CURL_PROXY_PASSWORD);
    }

    /**
     * Checks if SSL certification verification is disabled for cURL requests.
     * @return bool True if certificate verification is disabled.
     */
    public function IsCertificateVerificationDisabled()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyBoolean(self::PROPERTY_CURL_DISABLE_CERTIFICATE_VERIFICATION);
    }

    /**
     * Returns the IPS object ID of the custom autoload script.
     * @return int IPS object ID of the custom autoload script.
     */
    public function GetCustomAutoLoadScriptId()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_CUSTOM_AUTOLOAD_SCRIPT_ID);
    }

    /**
     * Checks if extended debugging messages are logged.
     * @return bool True if extended debugging is enabled.
     */
    public function IsExtendedDebuggingEnabled()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyBoolean(self::PROPERTY_EXTENDED_DEBUGGING_ENABLED);
    }

    /**
     * Checks if the framework is autoloaded in all PHP scripts.
     * @return bool True if the framework is autoloaded in all PHP scripts.
     */
    public function IsGlobalAutoLoadEnabled()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyBoolean(self::PROPERTY_GLOBAL_AUTOLOAD_ENABLED);
    }

    /**
     * Returns the file name of the IPS autoload script.
     * @return string File name of the IPS autoload script.
     */
    protected function GetAutoIncludeFileName()
    {
        return IPS::GetScriptDir() . '__autoload.php';
    }

    /**
     * Returns the PHP code that is inserted into the IPS autoload file.
     * @return string PHP code.
     */
    protected function GetAutoIncludeCode()
    {
        $fileName = IPS::GetModulesDir() . 'ipspatami' . DIRECTORY_SEPARATOR . 'bootstrap.php';

        return "// Patami Framework\n" .
            "// The next four lines are auto-generated, don't touch them!\n" .
            "\$fileName = '${fileName}';\n" .
            "if (file_exists(\$fileName)) {\n" .
            "\t@require_once(\$fileName);\n" .
            "}";
    }

    /**
     * Inserts the framework autoload code into the IPS autoload file.
     * @return bool True if the autoload code was successfully inserted.
     */
    protected function RegisterAutoInclude()
    {
        // Get the name of the include file
        $fileName = $this->GetAutoIncludeFileName();

        // Get the code
        $code = $this->GetAutoIncludeCode();

        // Check if the file exists
        if (! file_exists($fileName)) {
            // No, create a new one and return the result
            $result = @file_put_contents($fileName, "<?php\n" . $code);
            // Return an error if the file could not be written
            if ($result === false) {
                return false;
            }
        } else {
            // Yes, add the code if necessary
            $text = @file_get_contents($fileName);
            // Return an error if the file was not loaded
            if ($text === false) {
                return false;
            }
            // Find the code in the file
            $pos = strpos($text, $code);
            // If not found, add it
            if ($pos === false) {
                // Add the code to the beginning of the file
                $newText = preg_replace('/^<\?(php){0,1}/', "<?php\n" . $code, $text);
                // Return an error if the code could not be inserted
                if (is_null($newText) || $text == $newText) {
                    return false;
                }
                // Write the modified code to the file
                $result = @file_put_contents($fileName, $newText);
                // Return an error if the file could not be written
                if ($result === false) {
                    return false;
                }
            }
        }

        // Everything went fine
        return true;
    }

    /**
     * Removed the framework autoload code from the IPS autoload file.
     * @return bool True if the autoload code was successfully removed.
     */
    protected function UnregisterAutoInclude()
    {
        // Get the name of the include file
        $fileName = $this->GetAutoIncludeFileName();

        // Get the code
        $code = $this->GetAutoIncludeCode();

        // Check if the file exists
        if (file_exists($fileName)) {
            // Yes, remove the code if necessary
            $text = @file_get_contents($fileName);
            // Return an error if the file was not loaded
            if ($text === false) {
                return false;
            }
            // Replace the code with an empty string
            $text = str_replace($code . "\n", '', $text);
            // Write the modified code to the file
            $result = @file_put_contents($fileName, $text);
            // Return an error if the file could not be written
            if ($result === false) {
                return false;
            }
        }

        // Everything went fine
        return true;
    }

    /**
     * Checks if the error and exception handler is registered.
     * @return bool True if the error handler is enabled.
     */
    public function IsErrorHandlerEnabled()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyBoolean(self::PROPERTY_ERROR_HANDLER_ENABLED);
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
        return 'https://docs.braintower.de/display/IPSPATAMI';
    }

    protected function GetReleaseNotesUrl()
    {
        return 'https://docs.braintower.de/display/IPSPATAMI/Versionshinweise';
    }

    /**
     * Updates the status variables of the instance.
     */
    public function UpdateStatus()
    {
        // Run background updates
        $id = $this->GetId();
        IPS::RunScriptText(sprintf('Patami_UpdateIPSLiveStatus(%d);', $id));
        IPS::RunScriptText(sprintf('Patami_UpdateIPSConnectStatus(%d);', $id));
        IPS::RunScriptText(sprintf('Patami_UpdateIPSObjectCounts(%d);', $id));
        IPS::RunScriptText(sprintf('Patami_UpdatePublicIP(%d);', $id));
    }

    /**
     * Updates the IPS subscription and license status variables of the instance
     */
    public function UpdateIPSLiveStatus()
    {
        // Log the invocation
        $this->Debug('IPS Live Status', 'Updating status variables');

        // Get the instance
        $instance = Framework::GetInstance();

        // Update the IPS version date
        /** @var Variable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_IPS_VERSION_DATE);
        $variable->Set(IPSLive::GetVersionDate());

        // Update the IPS installed date
        $variable = $instance->GetChildByIdent(self::VAR_IPS_INSTALLED_DATE);
        $variable->Set(IPSLive::GetInstalledDate());

        // Update the IPS license type
        $variable = $instance->GetChildByIdent(self::VAR_IPS_LICENSE_TYPE);
        $variable->Set(IPSLive::GetLicenseType());

        // Update the IPS license email
        $variable = $instance->GetChildByIdent(self::VAR_IPS_LICENSE_EMAIL);
        $variable->Set(IPSLive::GetLicenseEmail());

        // Update the IPS variable limit
        $variable = $instance->GetChildByIdent(self::VAR_IPS_LICENSE_VARIABLE_LIMIT);
        $variable->Set(IPSLive::GetVariableLimit());

        // Update the IPS variable limit exceeded status
        $variable = $instance->GetChildByIdent(self::VAR_IPS_LICENSE_VARIABLE_LIMIT_EXCEEDED);
        $variable->Set(IPSLive::IsVariableLimitExceeded());

        // Update the IPS WebFront limit
        $variable = $instance->GetChildByIdent(self::VAR_IPS_LICENSE_WEBFRONT_LIMIT);
        $variable->Set(IPSLive::GetWebFrontLimit());

        // Update the IPS dashboard support
        $variable = $instance->GetChildByIdent(self::VAR_IPS_LICENSE_DASHBOARD_SUPPORT);
        $variable->Set(IPSLive::IsDashboardSupported());

        // Update the IPS subscription status
        $variable = $instance->GetChildByIdent(self::VAR_IPS_SUBSCRIPTION_VALID);
        $variable->Set(IPSLive::IsSubscriptionValid());

        // Update the IPS subscription expiration
        $variable = $instance->GetChildByIdent(self::VAR_IPS_SUBSCRIPTION_EXPIRE_TIMESTAMP);
        $variable->Set(IPSLive::GetSubscriptionEndDate());
    }

    /**
     * Updates the IPS Connect status variables of the instance.
     */
    public function UpdateIPSConnectStatus()
    {
        // Log the invocation
        $this->Debug('IPS Connect Status', 'Updating status variables');

        // Get the instance
        $instance = Framework::GetInstance();

        // Update the IPS Connect connection status
        /** @var Variable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_IPS_CONNECT_CONNECTED);
        $variable->Set(IPSConnect::IsConnected());

        // Update the IPS Connect health status
        $variable = $instance->GetChildByIdent(self::VAR_IPS_CONNECT_HEALTHY);
        $variable->Set(IPSConnect::IsHealthy());
    }

    /**
     * Updates the IPS object counts of the instance.
     */
    public function UpdateIPSObjectCounts()
    {
        // Log the invocation
        $this->Debug('IPS Object Counts', 'Updating status variables');

        // Get the instance
        $instance = Framework::GetInstance();

        // Update the object count
        /** @var Variable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_IPS_OBJECT_COUNT);
        $count = IPS::GetObjectCount();
        $variable->Set($count);

        // Update the available (remaining) object count
        $variable = $instance->GetChildByIdent(self::VAR_IPS_AVAILABLE_OBJECT_COUNT);
        $variable->Set(self::MAX_OBJECT_COUNT - $count);

        // Update the library count
        $variable = $instance->GetChildByIdent(self::VAR_IPS_LIBRARY_COUNT);
        $variable->Set(IPS::GetLibraryCount());

        // Update the module count
        $variable = $instance->GetChildByIdent(self::VAR_IPS_MODULE_COUNT);
        $variable->Set(IPS::GetModuleCount());

        // Update the instance count
        $variable = $instance->GetChildByIdent(self::VAR_IPS_INSTANCE_COUNT);
        $variable->Set(IPS::GetInstanceCount());

        // Update the variable count
        $variable = $instance->GetChildByIdent(self::VAR_IPS_VARIABLE_COUNT);
        $variable->Set(IPS::GetVariableCount());

        // Update the script count
        $variable = $instance->GetChildByIdent(self::VAR_IPS_SCRIPT_COUNT);
        $variable->Set(IPS::GetScriptCount());

        // Update the function count
        $variable = $instance->GetChildByIdent(self::VAR_IPS_FUNCTION_COUNT);
        $variable->Set(IPS::GetFunctionCount(0));

        // Update the event count
        $variable = $instance->GetChildByIdent(self::VAR_IPS_EVENT_COUNT);
        $variable->Set(IPS::GetEventCount());

        // Update the media count
        $variable = $instance->GetChildByIdent(self::VAR_IPS_MEDIA_COUNT);
        $variable->Set(IPS::GetMediaCount());

        // Update the link count
        $variable = $instance->GetChildByIdent(self::VAR_IPS_LINK_COUNT);
        $variable->Set(IPS::GetLinkCount());

        // Update the category count
        $variable = $instance->GetChildByIdent(self::VAR_IPS_CATEGORY_COUNT);
        $variable->Set(IPS::GetCategoryCount());

        // Update the database size
        $variable = $instance->GetChildByIdent(self::VAR_IPS_DATABASE_SIZE);
        $variable->Set(round(Number::BytesToMegabytes(IPS::GetDatabaseDirSize())));

        // Update the logs size
        $variable = $instance->GetChildByIdent(self::VAR_IPS_LOGS_SIZE);
        $variable->Set(round(Number::BytesToMegabytes(IPS::GetLogDirSize())));

        // Update the scripts size
        $variable = $instance->GetChildByIdent(self::VAR_IPS_SCRIPTS_SIZE);
        $variable->Set(round(Number::BytesToMegabytes(IPS::GetScriptsDirSize())));
    }

    /**
     * Updates the public IP status variables of the instance.
     */
    public function UpdatePublicIP()
    {
        // Log the invocation
        $this->Debug('Public IP', 'Updating status variables');

        // Get the instance
        $instance = Framework::GetInstance();

        // Update the system public IP
        /** @var Variable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_SYSTEM_PUBLIC_IP);
        $variable->Set(System::GetPublicIP(true));

        // Update the system public IP hostname
        $variable = $instance->GetChildByIdent(self::VAR_SYSTEM_PUBLIC_IP_HOSTNAME);
        $variable->Set(System::GetPublicIPHostname());
    }

}