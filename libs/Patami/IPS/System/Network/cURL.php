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


namespace Patami\IPS\System\Network;


use Patami\IPS\Framework;
use Patami\IPS\System\IPS;
use Patami\IPS\System\Network\Exceptions\cURLConfigFailedException;
use Patami\IPS\System\Network\Exceptions\cURLInitFailedException;
use Patami\IPS\System\Network\Exceptions\cURLInvalidRequestTypeException;
use Patami\IPS\System\Network\Exceptions\cURLRequestFailedException;


/**
 * cURL class.
 * Provides functions to perform HTTP(S) requests with the cURL library.
 * @package IPSPATAMI
 * @link https://curl.haxx.se/
 */
class cURL
{

    /** Default number of seconds cURL waits for a successful connection. */
    const DEFAULT_CONNECT_TIMEOUT = 2;

    /**
     * @var resource cURL handler for the session.
     */
    protected $handle = null;

    /**
     * @var string URL to be used for the cURL request.
     */
    protected $url = null;

    /**
     * @var int Number of seconds cURL waits for a successful connection.
     */
    protected $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT;

    /**
     * @var array Custom HTTP headers.
     */
    protected $headers = array();

    /** HTTP GET request. */
    const REQUEST_TYPE_GET = 'GET';
    /** HTTP POST request. */
    const REQUEST_TYPE_POST = 'POST';

    /**
     * @var string HTTP request type.
     */
    protected $requestType = self::REQUEST_TYPE_GET;

    /**
     * @var array HTTP POST fields.
     */
    protected $postFields = array();

    /**
     * @var string User agent string.
     */
    protected $userAgent = null;

    /**
     * @var string Proxy server address and port.
     * Format: address:port
     */
    protected $proxy = null;

    /**
     * @var string Proxy server username and password.
     * Format: username:password
     */
    protected $proxyUsernamePassword = null;

    /**
     * @var bool True if SSL certificate verification is enabled.
     */
    protected $verifySSLPeer = true;

    /**
     * cURL constructor.
     * @param string $url URL used by cURL.
     * Initializes the cURL session.
     */
    public function __construct($url = null)
    {
        // Open a new cURL session
        $this->Init();

        // Set default settings
        $this->InitSettings();

        // Set the URL
        $this->SetUrl($url);
    }

    /**
     * cURL destructor.
     * Destroys the cURL session.
     */
    public function __destruct()
    {
        // Close the cURL session
        $this->Close();
    }

    /**
     * Initializes the cURL session and remembers the cURL handle.
     * @throws cURLInitFailedException if the cURL session could not be initialized.
     */
    protected function Init()
    {
        // Create a new cURL session
        $handle = @curl_init();

        // Throw an exception if there was an error
        if ($handle === false) {
            throw new cURLInitFailedException();
        }

        // Remember the handle
        $this->handle = $handle;
    }

    protected function InitSettings()
    {
        // Throw an exception if the framework instance is still initializing
        if (Framework::IsInstanceInitializing()) {
            throw new cURLConfigFailedException();
        }

        // Get the framework instance ID
        $instanceId = Framework::GetInstanceId();

        // Throw an exception if the framework configuration is not valid
        /** @noinspection PhpUndefinedFunctionInspection */
        if (! Patami_IsValid($instanceId)) {
            throw new cURLConfigFailedException();
        }

        // Set the proxy server
        /** @noinspection PhpUndefinedFunctionInspection */
        if (Patami_IsProxyEnabled($instanceId)) {
            /** @noinspection PhpUndefinedFunctionInspection */
            $this->SetProxy(
                Patami_GetProxyAddress($instanceId),
                Patami_GetProxyPort($instanceId)
            );
            // Set proxy authentication
            /** @noinspection PhpUndefinedFunctionInspection */
            if (Patami_IsProxyAuthenticationEnabled($instanceId)) {
                /** @noinspection PhpUndefinedFunctionInspection */
                $this->SetProxyAuthentication(
                    Patami_GetProxyUsername($instanceId),
                    Patami_GetProxyPassword($instanceId)
                );
            }
        } else {
            $this->SetProxy(null);
        }

        // Set SSL peer certifcate verification
        /** @noinspection PhpUndefinedFunctionInspection */
        $this->EnableSSLPeerVerification(! Patami_IsCertificateVerificationDisabled($instanceId));
    }

    /**
     * Closes the cURL session if it was initialized.
     */
    protected function Close()
    {
        // If the session is open
        if (! is_null($this->handle)) {
            // Close the cURL session
            @curl_close($this->handle);
            $this->handle = null;
        }
    }

    /**
     * Returns the URL used by cURL.
     * @return string URL.
     */
    public function GetUrl()
    {
        // Return the URL
        return $this->url;
    }

    /**
     * Sets the URL used by cURL.
     * @param string $url URL.
     * @return $this Fluent interface.
     */
    public function SetUrl($url)
    {
        // Remember the URL
        $this->url = $url;

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the number of seconds cURL waits for a successful connection.
     * @return int Number of seconds.
     */
    public function GetConnectTimeout()
    {
        // Return the timeout
        return $this->connectTimeout;
    }

    /**
     * Sets the number of seconds cURL waits for a successful connection.
     * @param int $timeout Number of seconds to wait for a successful connection or 0 to disable the timeout.
     * @return $this Fluent interface.
     */
    public function SetConnectTimeout($timeout = self::DEFAULT_CONNECT_TIMEOUT)
    {
        // Remember the timeout
        $this->connectTimeout = $timeout;

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the custom HTTP headers.
     * @return array HTTP custom headers.
     */
    public function GetHeaders()
    {
        // Return the headers
        return $this->headers;
    }

    /**
     * Sets the custom HTTP headers.
     * @param array $headers Custom HTTP headers.
     * @return $this Fluent interface.
     */
    public function SetHeaders(array $headers)
    {
        // Remember the headers
        $this->headers = $headers;

        // Enable fluent interface
        return $this;
    }

    /**
     * Sets a custom HTTP header.
     * @param string $key Name of the HTTP header entry.
     * @param string $value Value of the HTTP header entry.
     * @return $this Fluent interface.
     */
    public function SetHeader($key, $value)
    {
        // Remember the header
        $this->headers[$key] = $value;

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the HTTP request type.
     * @return string HTTP request type.
     */
    public function GetRequestType()
    {
        // Return the request type
        return $this->requestType;
    }

    /**
     * Sets the HTTP request type (GET, POST, ...).
     * @param string $requestType Request type.
     * @return $this Fluent interface.
     * @throws cURLInvalidRequestTypeException if the request type is invalid.
     */
    public function SetRequestType($requestType)
    {
        // Throw an exception if the request type is invalid
        $allowedRequestTypes = array(
            self::REQUEST_TYPE_GET,
            self::REQUEST_TYPE_POST
        );
        if (! in_array($requestType, $allowedRequestTypes)) {
            throw new cURLInvalidRequestTypeException();
        }

        // Remember the request type
        $this->requestType = $requestType;

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the HTTP POST fields.
     * @return array POST fields key-value pairs.
     */
    public function GetPostFields()
    {
        // Return the POST fields
        return $this->postFields;
    }

    /**
     * Checks if a HTTP POST request is performed.
     * @return bool True if a HTTP POST request is performed.
     */
    public function IsPostRequest()
    {
        // Return if HTTP POST is enabled
        return $this->requestType == self::REQUEST_TYPE_POST;
    }

    /**
     * Enables HTTP POST and specifies the data to be send to the server.
     * @param array $data HTTP POST fields to be sent to the server.
     * @return $this Fluent interface.
     */
    public function SetPostFields(array $data)
    {
        // Switch to HTTP POST
        $this->SetRequestType(self::REQUEST_TYPE_POST);

        // Remember the fields
        $this->postFields = $data;

        // Enable fluent interface
        return $this;
    }

    /**
     * Clears the HTTP POST data and enables HTTP GET.
     * @return $this Fluent interface.
     */
    public function ClearPostFields()
    {
        // Clear the data and enable fluent interface
        return $this->SetPostFields(array());
    }

    /**
     * Returns the user agent string used in the request.
     * @return string|null User agent string or null if the default string is used.
     */
    public function GetUserAgent()
    {
        // Return the user agent string
        return $this->userAgent;
    }

    /**
     * Sets the user agent string.
     * If null is specified, the framework will send "Patami IPS Framework X.X"
     * @param string|null $userAgent User agent string or null to use the default string.
     * @return $this Fluent interface.
     */
    public function SetUserAgent($userAgent)
    {
        // Remember the user agent
        $this->userAgent = $userAgent;

        // Enable fluent interface
        return $this;
    }

    /**
     * Sets the proxy server to be used for requests.
     * @param string|null $address Proxy server address or null to not use a proxy server.
     * $param int $port Proxy server port.
     * @return $this Fluent interface.
     */
    public function SetProxy($address, $port = 8080)
    {
        // Clear the proxy is the address is null
        if (is_null($address)) {
            $this->proxy = null;
        } else {
            $this->proxy = sprintf('%s:%d', $address, $port);
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Sets the proxy server authentication information (username and password).
     * @param string|null $username Proxy server username or null to not use proxy server authentication.
     * @param string|null $password Proxy server password.
     * @return $this Fluent interface.
     */
    public function SetProxyAuthentication($username, $password)
    {
        // Clear the credentials if the username is null
        if (is_null($username)) {
            $this->proxyUsernamePassword = null;
        } else {
            $this->proxyUsernamePassword = sprintf('%s:%s', $username, $password);
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Enables or disables SSL peer certificate verification.
     * @param bool $enabled True to enable SSL peer certificate verification.
     * @return $this Fluent interface.
     */
    public function EnableSSLPeerVerification($enabled = true)
    {
        // Remember the setting
        $this->verifySSLPeer = $enabled;

        // Enable fluent interface
        return $this;
    }

    /**
     * Directly sets a cURL option.
     * You should not use this method in your own code, since there are convenience methods available that encapsulate
     * the cURL options.
     * @param int $option cURL option ID.
     * @param mixed $value Value for the cURL option.
     * @return $this Fluent interface.
     * @internal
     * @link http://php.net/manual/de/function.curl-setopt.php
     */
    public function SetOption($option, $value)
    {
        // Set the cURL option
        curl_setopt($this->handle, $option, $value);

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the last HTTP status code.
     * @return int Last HTTP status code.
     */
    public function GetHTTPCode()
    {
        // Return the last HTTP code
        return curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
    }

    /**
     * Executes the request and returns the data returned by the remote server.
     * @return string Result of the request.
     * @throws cURLRequestFailedException if the request failed.
     */
    public function Execute()
    {
        // Set the URL
        curl_setopt($this->handle, CURLOPT_URL, $this->url);

        // Set common cURL options
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($this->handle, CURLOPT_COOKIEFILE, '');

        // Set cURL request type specific options
        curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $this->requestType);
        if ($this->IsPostRequest()) {
            // POST request
            curl_setopt($this->handle, CURLOPT_POST, 1);
            curl_setopt($this->handle, CURLOPT_POSTFIELDS, $this->postFields);
        } else {
            // Other request (GET, ...)
            curl_setopt($this->handle, CURLOPT_POST, 0);
        }

        // Set HTTP header option
        if (count($this->headers) > 0) {
            $headers = array();
            foreach ($this->headers as $key => $value) {
                $headers[] = sprintf('%s: %s', $key, $value);
            }
            IPS::LogMessage('Test', var_export($headers, true));
            curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);
        }

        // Set user agent cURL option
        curl_setopt($this->handle, CURLOPT_USERAGENT, is_null($this->userAgent)?
            sprintf('Patami IPS Framework %s', Framework::GetVersion()):
            $this->userAgent
        );

        // Set proxy cURL options
        if (is_null($this->proxy)) {
            curl_setopt($this->handle, CURLOPT_PROXY, '');
        } else {
            curl_setopt($this->handle, CURLOPT_PROXY, $this->proxy);
            if (is_null($this->proxyUsernamePassword)) {
                curl_setopt($this->handle, CURLOPT_PROXYUSERPWD, '');
            } else {
                curl_setopt($this->handle, CURLOPT_PROXYUSERPWD, $this->proxyUsernamePassword);
            }
        }

        // Set SSL peer certificate verification cURL option
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, $this->verifySSLPeer);

        // Execute the request
        $result = curl_exec($this->handle);

        // Throw an exception if the request failed
        if ($result === false) {
            throw new cURLRequestFailedException(curl_error($this->handle));
        }

        // Return the result of the request
        return $result;
    }

}