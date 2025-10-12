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


namespace Patami\IPS\System;


use Patami\IPS\Framework;
use Patami\IPS\System\Network\cURL;
use Patami\IPS\System\Network\Exceptions\cURLException;


/**
 * IP Symcon Live Class.
 * Provides functions to get information about the IP Symcon license and subscription.
 * @package IPSPATAMI
 * @link https://www.symcon.de/forum/live.php
 */
class IPSLive
{

    /** URL of the IPS Forum login service. */
    const LOGIN_URL = 'https://www.symcon.de/forum/login.php?do=login';
    /** URL of the IPS Live status web page. */
    const STATUS_URL = 'https://www.symcon.de/forum/live.php';

    /** Basic IPS license. */
    const LICENSE_TYPE_BASIC = 'basic';
    /** Professional IPS license. */
    const LICENSE_TYPE_PROFESSIONAL = 'professional';
    /** Unlimited IPS license. */
    const LICENSE_TYPE_UNLIMITED = 'unlimited';

    /** @var array|null Information about the IPS license and subscription. */
    protected static $info = null;

    /**
     * Returns information about the IPS license and subscription.
     * @param bool $refresh True if the cached information should be refreshed.
     * @return array|null Attributes of the IPS license and subscription or null if the information could not be determined.
     */
    public static function GetInfo($refresh = false)
    {
        // Skip if the framework instance is still initializing
        if (Framework::IsInstanceInitializing()) {
            return null;
        }

        $tag = 'IPSLive::GetInfo()';

        // Return cached information if possible and requested
        if (! is_null(self::$info) && ! $refresh) {
            return self::$info;
        }

        // Get the login credentials for the IPS forum
        $id = Framework::GetInstanceId();
        /** @noinspection PhpUndefinedFunctionInspection */
        $username = Patami_GetIPSForumUsername($id);
        /** @noinspection PhpUndefinedFunctionInspection */
        $password = Patami_GetIPSForumPassword($id);

        try {
            // Create a new cURL object
            $curl = new cURL();

            // Log in to the forum
            $text = $curl
                ->SetUrl(self::LOGIN_URL)
                ->SetPostFields(array(
                    'vb_login_username' => $username,
                    'vb_login_password' => '',
                    's' => '',
                    'securitytoken' => 'guest',
                    'do' => 'login',
                    'vb_login_md5password' => md5($password),
                    'vb_login_md5password_utf' => md5($password)
                ))
                ->Execute();

            // Check if the login was successful
            if (! preg_match('/Danke f&uuml;r Ihre Anmeldung/', $text)) {
                // Log the error
                Framework::Debug($tag, 'Login to the IPS forum failed. Check the login credentials!');
                // Return null
                return null;
            }

            // Retrieve the live status page
            $text = $curl
                ->SetRequestType(cURL::REQUEST_TYPE_GET)
                ->SetUrl(self::STATUS_URL)
                ->Execute();

        } catch (cURLException $e) {
            // Log the error
            Framework::Debug($tag, utf8_decode($e->GetCustomMessage()));
            // Return null
            return null;
        }

        // Initialize the info data structure
        $info = array();

        // Extract the license type
        if (preg_match('/<img class="version" src="\/forum\/images\/live\/ips_([a-z]+).png">/', $text, $tokens)) {
            $licenseType = $tokens[1];
            $allowedLicenseTypes = array(
                self::LICENSE_TYPE_BASIC,
                self::LICENSE_TYPE_PROFESSIONAL,
                self::LICENSE_TYPE_UNLIMITED
            );
            if (in_array($licenseType, $allowedLicenseTypes)) {
                $info['licenseType'] = $licenseType;
            } else {
                Framework::Debug($tag, sprintf('Invalid license type: %s', $licenseType));
            }
        } else {
            Framework::Debug($tag, 'Unable to parse the license type.');
        }

        // Extract the license email address
        if (preg_match('/javascript:makePostRequest\(\'sendLicense\', \'(.*)\'\);/', $text, $tokens)) {
            $licenseEmail = $tokens[1];
            if (filter_var($licenseEmail, FILTER_VALIDATE_EMAIL)) {
                $info['licenseEmail'] = $licenseEmail;
            } else {
                Framework::Debug($tag, sprintf('Invalid license email address: %s', $licenseEmail));
            }
        } else {
            Framework::Debug($tag, 'Unable to parse the license email address.');
        }

        // Extract the version date
        if (preg_match('/<p>Version: [0-9\.]+ \(([0-9\.]+)\)<\/p>/', $text, $tokens)) {
            $versionDate = @strtotime($tokens[1]);
            if ($versionDate === false) {
                Framework::Debug($tag, sprintf('Invalid IPS version date: %s', $tokens[1]));
            } else {
                $info['versionDate'] = $versionDate;
            }
        } else {
            Framework::Debug($tag, 'Unable to parse the IPS version date.');
        }

        // Extract the install date
        if (preg_match('/<p>Installiert am: ([0-9]+)\.([0-9]+)\.([0-9]+)<\/p>/', $text, $tokens)) {
            $date = sprintf('%s.%s.20%s', $tokens[1], $tokens[2], $tokens[3]);
            $installedDate = @strtotime($date);
            if ($installedDate === false) {
                Framework::Debug($tag, sprintf('Invalid IPS installed date: %s', $date));
            } else {
                $info['installedDate'] = $installedDate;
            }
        } else {
            Framework::Debug($tag, 'Unable to parse the IPS installed date.');
        }

        // Extract the subscription end date
        if (preg_match('/<p>Subskription bis: <span class=\'[a-z]+\'>([0-9]+)\.([0-9]+)\.([0-9]+)<\/span><\/p>/', $text, $tokens)) {
            $date = sprintf('%s.%s.20%s', $tokens[1], $tokens[2], $tokens[3]);
            $subscriptionEndDate = @strtotime($date);
            if ($subscriptionEndDate === false) {
                Framework::Debug($tag, sprintf('Invalid IPS subscription end date: %s', $date));
            } else {
                $subscriptionEndDate = new \DateTime(sprintf('%s +1 day -1 second', $date));
                $info['subscriptionEndDate'] = $subscriptionEndDate->getTimestamp();
            }
        } else {
            Framework::Debug($tag, 'Unable to parse the IPS subscription end date.');
        }

        // Remember the info
        self::$info = $info;

        // Return the info
        return $info;
    }

    /**
     * Returns the IPS license type.
     * @param bool $refresh True if the cached information should be refreshed.
     * @return string|null IPS license type or null if the information could not be determined.
     */
    public static function GetLicenseType($refresh = false)
    {
        // Get the information
        $info = self::GetInfo($refresh);

        // Return the license type
        return @$info['licenseType'];
    }

    /**
     * Returns the variable limit of the current IPS license.
     * @param bool $refresh True if the cached information should be refreshed.
     * @return int|null Maximum number of variables or null if the number could not be determined.
     */
    public static function GetVariableLimit($refresh = false)
    {
        // Get the license type
        $licenseType = self::GetLicenseType($refresh);

        // Get the limit
        $limits = array(
            null => null,
            self::LICENSE_TYPE_BASIC => 250,
            self::LICENSE_TYPE_PROFESSIONAL => 1000,
            self::LICENSE_TYPE_UNLIMITED => 0
        );

        // Return the limit
        return $limits[$licenseType];
    }

    /**
     * Checks if the variable limit of the current IPS license is exceeded.
     * @param bool $refresh True if the cached information should be refreshed.
     * @return bool True if the variable limit is exceeded.
     */
    public static function IsVariableLimitExceeded($refresh = false)
    {
        // Get the variable limit
        $limit = self::GetVariableLimit($refresh);

        // Return false if the number of variables is unlimited
        if ($limit == 0) {
            return false;
        }

        // Get the variable count
        $count = IPS::GetVariableCount();

        // Return the result
        return $count > $limit;
    }

    /**
     * Returns the WebFront limit of the current IPS license.
     * @param bool $refresh True if the cached information should be refreshed.
     * @return int|null Maximum number of WebFronts or null if the number could not be determined.
     */
    public static function GetWebFrontLimit($refresh = false)
    {
        // Get the license type
        $licenseType = self::GetLicenseType($refresh);

        // Get the limit
        $limits = array(
            null => null,
            self::LICENSE_TYPE_BASIC => 1,
            self::LICENSE_TYPE_PROFESSIONAL => 5,
            self::LICENSE_TYPE_UNLIMITED => 0
        );

        // Return the limit
        return $limits[$licenseType];
    }

    /**
     * Checks if IPS Dashboards are supported by the current license.
     * @param bool $refresh True if the cached information should be refreshed.
     * @return bool|null True if dashboards are supported, false if not, or null if the information could not be determined.
     */
    public static function IsDashboardSupported($refresh = false)
    {
        // Get the license type
        $licenseType = self::GetLicenseType($refresh);

        // Return null if the information could not be determined
        if (is_null($licenseType)) {
            return null;
        }

        // Check and return if dashboards are supported
        return $licenseType != self::LICENSE_TYPE_BASIC;
    }

    /**
     * Returns the email address of the IPS license.
     * @param bool $refresh True if the cached information should be refreshed.
     * @return string|null IPS license email or null if the information could not be determined.
     */
    public static function GetLicenseEmail($refresh = false)
    {
        // Get the information
        $info = self::GetInfo($refresh);

        // Return the email address
        return @$info['licenseEmail'];
    }

    /**
     * Returns the timestamp of the IPS version.
     * @param bool $refresh True if the cached information should be refreshed.
     * @return int|null Unix timestamp of the IPS version or null if the information could not be determined.
     */
    public static function GetVersionDate($refresh = false)
    {
        // Get the information
        $info = self::GetInfo($refresh);

        // Return the email address
        return @$info['versionDate'];
    }

    /**
     * Returns the timestamp of the last IPS update.
     * @param bool $refresh True if the cached information should be refreshed.
     * @return int|null Unix timestamp of the last IPS update or null if the information could not be determined.
     */
    public static function GetInstalledDate($refresh = false)
    {
        // Get the information
        $info = self::GetInfo($refresh);

        // Return the email address
        return @$info['installedDate'];
    }

    /**
     * Returns the timestamp until when the IPS subscription is valid.
     * @param bool $refresh True if the cached information should be refreshed.
     * @return int|null Unix timestamp until when the subscription is valid or null if the information could not be determined.
     */
    public static function GetSubscriptionEndDate($refresh = false)
    {
        // Get the information
        $info = self::GetInfo($refresh);

        // Return the email address
        return @$info['subscriptionEndDate'];
    }

    /**
     * Checks if the IPS subscription is valid (active).
     * @param bool $refresh True if the cached information should be refreshed.
     * @return bool|null True if the subscription is active, false if it is expired, or null if the information could not be determined.
     */
    public static function IsSubscriptionValid($refresh = false)
    {
        // Get the expiration timestamp
        $subscriptionEndDate = self::GetSubscriptionEndDate($refresh);

        // Return null if the information could not be determined
        if (is_null($subscriptionEndDate)) {
            return null;
        }

        // Check if the subscription is expired and return the result
        return $subscriptionEndDate > time();
    }

}