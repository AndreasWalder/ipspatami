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


use Patami\IPS\IO\ResponseInterface;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\AlexaApiRequestFailedException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\AlexaApiRequestForbiddenException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\CallbackIntentNotSetException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\IntentNotFoundException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidAddressException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidApiEndpointUrlException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidConsentTokenException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidCountryAndPostalCodeException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidDeviceIdException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidIntentSlotsException;
use Patami\IPS\System\Locales;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\RedirectLoopException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\RedirectNotAllowedException;
use Patami\IPS\IO\IOModule;
use Patami\IPS\Services\Alexa\Skills\Request as BaseRequest;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidRequestVersionException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\LocaleNotSupportedException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidRequestIdException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidSessionIdException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\ApplicationIdNotAllowedException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\RequestTypeNotSupportedException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\LaunchRequestIntentNotSetException;
use Patami\IPS\System\Network\cURL;
use Patami\IPS\System\Network\Exceptions\cURLRequestFailedException;


/**
 * Abstract base class for Amazon Alexa Custom Skill requests.
 * @package IPSPATAMI
 */
abstract class Request extends BaseRequest
{

    /**
     * LaunchRequest
     * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/custom-standard-request-types-reference#launchrequest
     */
    const TYPE_LAUNCH_REQUEST = 'LaunchRequest';

    /**
     * IntentRequest
     * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/custom-standard-request-types-reference#intentrequest
     */
    const TYPE_INTENT_REQUEST = 'IntentRequest';
	
		/**
	 * APL UserEvent
	 */
	public const TYPE_APL_USER_EVENT = 'Alexa.Presentation.APL.UserEvent';

	/**
	 * System.ExceptionEncountered (Fehler-Callback von Alexa)
	 * -> nur protokollieren und neutral antworten
	 */
	public const TYPE_SYSTEM_EXCEPTION_ENCOUNTERED = 'System.ExceptionEncountered';

    /**
     * SessionEndedRequest
     * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/custom-standard-request-types-reference#sessionendedrequest
     */
    const TYPE_SESSION_ENDED_REQUEST = 'SessionEndedRequest';
	

    /** @var IOInterface I/O module object processing the request. */
    protected $io;

    /** @var string Unique identifier for the specific request. */
    protected $requestId;

    /** @var string Unique identifier of the Alexa device. */
    protected $deviceId;

    /** @var string URL of the Amazon Alexa API endpoint. */
    protected $apiEndpointUrl;

    /** @var string Authorization token for calling the Amazon Alexa API endpoint. */
    protected $consentToken;

    /** @var array Country and postal code where the Alexa device is located. */
    protected $countryAndPostalCode;

    /** @var array Address where the Alexa device is located. */
    protected $address;

    /**
     * @var string Type of the request.
     * @see Request::TYPE_LAUNCH_REQUEST
     * @see Request::TYPE_INTENT_REQUEST
     * @see Request::TYPE_SESSION_ENDED_REQUEST
     */
    protected $requestType;

    /** @var bool True if the request was sent from the Alaexa Service Simulator. */
    protected $isServiceSimulatorRequest;

    /** @var string Unique identifier per a user’s active session. */
    protected $sessionId;

    /** @var SessionAttributes Object with key-value pairs stored in the session. */
    public $attributes;

    /**
     * @var IntentSlots Object with key-value pairs based on the intent schema.
     * @see https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/custom-standard-request-types-reference#slot-object
     */
    public $slots;

    /** @var string Name of the intent that asked the last question in the user's active session. */
    protected $callbackIntent;

    /** @var string Name of the intent which has redirected back to the callback intent. */
    protected $callbackSourceIntent;

    /** @var bool True if the request is currently being processed by an intent. */
    protected $isProcessing = false;

    /** @var array List of intent names used to find out where a redirected intent request came from. */
    protected $intentStack = array();

    /**
     * Request constructor.
     * @param IOModule $io I/O module object processing the request.
     * @param array $data Decoded request body received from the Amazon servers.
     * @param string $defaultLocale Locale code to be used before it can be extracted from request.
     */
    public function __construct(IOModule $io, array $data, $defaultLocale)
    {
        // Call the parent constructor
        parent::__construct($io, $data, $defaultLocale);

        // Create empty objects for the intent slots and session attributes
        $this->slots = new IntentSlots();
        $this->attributes = new SessionAttributes();

        // Normalize/ensure basic session structure to avoid undefined-key warnings later
        if (!isset($this->data) || !is_array($this->data)) {
            $this->data = [];
        }
        if (!isset($this->data['session']) || !is_array($this->data['session'])) {
            $this->data['session'] = [];
        }
        if (!isset($this->data['session']['attributes']) || !is_array($this->data['session']['attributes'])) {
            $this->data['session']['attributes'] = [];
        }
        if (!isset($this->data['session']['attributes']['attributes']) || !is_array($this->data['session']['attributes']['attributes'])) {
            $this->data['session']['attributes']['attributes'] = [];
        }
        if (!isset($this->data['session']['attributes']['slots']) || !is_array($this->data['session']['attributes']['slots'])) {
            $this->data['session']['attributes']['slots'] = [];
        }
        if (!array_key_exists('callbackIntent', $this->data['session']['attributes'])) {
            $this->data['session']['attributes']['callbackIntent'] = null;
        }

        // --- Request-Struktur defensiv normalisieren ---
        if (!isset($this->data['request']) || !is_array($this->data['request'])) {
            $this->data['request'] = [];
        }
        if (!isset($this->data['request']['intent']) || !is_array($this->data['request']['intent'])) {
            $this->data['request']['intent'] = [];
        }
        if (!isset($this->data['request']['intent']['slots']) || !is_array($this->data['request']['intent']['slots'])) {
            $this->data['request']['intent']['slots'] = [];
        }
        if (!array_key_exists('type', $this->data['request'])) {
            $this->data['request']['type'] = null;
        }
        if (!array_key_exists('locale', $this->data['request'])) {
            $this->data['request']['locale'] = null;
        }
        if (!array_key_exists('name', $this->data['request']['intent'])) {
            $this->data['request']['intent']['name'] = null;
        }
    }
	
	/** @var array APL UserEvent arguments (from request.arguments) */
	protected $aplArguments = [];

	public function GetAplArguments()
	{
		return is_array($this->aplArguments) ? $this->aplArguments : [];
	}


    /**
     * Returns the unique identifier for the request.
     * @return string Unique identifier for the specific request.
     */
    public function GetRequestId()
    {
        return $this->requestId;
    }

    /**
     * Returns the unique identifier of the Alexa device.
     * @return string Unique identifier for the device.
     */
    public function GetDeviceId()
    {
        return $this->deviceId;
    }

    /**
     * Returns the URL of the Alexa API endpoint.
     * @return string URL of the Alexa API endpoint.
     */
    public function GetApiEndpointUrl()
    {
        return $this->apiEndpointUrl;
    }

    /**
     * Returns the consent token that can be used to call the Alexa API.
     * @return string Consent token.
     */
    public function GetConsentToken()
    {
        return $this->consentToken;
    }
	
	

    /**
     * Returns the country and postal code of the Alexa device if permitted.
     * @return array Country and postal code of the Alexa device.
     * @throws AlexaApiRequestFailedException if the request to the Alexa API failed.
     * @throws AlexaApiRequestForbiddenException if the request to the Alexa API was denied. Make sure the user gives
     * consent to to disclose the information.
     * @throws InvalidApiEndpointUrlException if the Alexa API endpoint is unknown.
     * @throws InvalidConsentTokenException if the consent token is unknown. Make sure the user gives consent to
     * disclose the information.
     * @throws InvalidCountryAndPostalCodeException if the Alexa API returns invalid information.
     * @throws InvalidDeviceIdException if the device ID is unknown.
     * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/device-address-api#getCountryAndPostalCode
     */
    public function GetCountryAndPostalCode()
    {
        $tag = 'Retrieve Country and Postal Code';

        // Return the cached value if possible
        if (! is_null($this->countryAndPostalCode)) {
            return $this->countryAndPostalCode;
        }

        // Throw an exception if the device ID is unknown
        if (is_null($this->apiEndpointUrl)) {
            throw new InvalidDeviceIdException();
        }

        // Throw an exception if the API endpoint URL is unknown
        if (is_null($this->apiEndpointUrl)) {
            throw new InvalidApiEndpointUrlException();
        }

        // Throw an exception if the consent token is not set
        if (is_null($this->consentToken)) {
            throw new InvalidConsentTokenException();
        }

        // Generate the URL
        $url = sprintf('%s/v1/devices/%s/settings/address/countryAndPostalCode', $this->apiEndpointUrl, $this->deviceId);
        $this->Debug($tag, sprintf('URL: %s', $url));

        // Create a new cURL object
        $curl = new cURL();

        // Retrieve the information
        try {
            $text = $curl
                ->SetUrl($url)
                ->SetHeader('Accept', 'application/json')
                ->SetHeader('Authorization', sprintf('Bearer %s', $this->consentToken))
                ->Execute();
        } catch (cURLRequestFailedException $e) {
            throw new AlexaApiRequestFailedException();
        }

        // Check the result code
        switch ($curl->GetHTTPCode()) {
            case 200:
                // All fine
                break;
            case 403:
                // The request was denied
                throw new AlexaApiRequestForbiddenException();
            default:
                // Another error occurred
                throw new AlexaApiRequestFailedException();
        }

        // Decode the information
        $data = @json_decode($text, true);

        // Throw an exception if the information is not a valid JSON structure
        if (is_null($data)) {
            throw new InvalidCountryAndPostalCodeException();
        }

        // Remember the information
        $this->countryAndPostalCode = $data;

        // Return the data
        return $data;
    }

    /**
     * Returns the address of the Alexa device if permitted.
     * @return array Address of the Alexa device.
     * @throws AlexaApiRequestFailedException if the request to the Alexa API failed.
     * @throws AlexaApiRequestForbiddenException if the request to the Alexa API was denied. Make sure the user gives
     * consent to to disclose the information.
     * @throws InvalidApiEndpointUrlException if the Alexa API endpoint is unknown.
     * @throws InvalidConsentTokenException if the consent token is unknown. Make sure the user gives consent to
     * disclose the information.
     * @throws InvalidAddressException if the Alexa API returns invalid information.
     * @throws InvalidDeviceIdException if the device ID is unknown.
     * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/device-address-api#getAddress
     */
    public function GetAddress()
    {
        $tag = 'Retrieve Address';

        // Return the cached value if possible
        if (! is_null($this->address)) {
            return $this->address;
        }

        // Throw an exception if the device ID is unknown
        if (is_null($this->apiEndpointUrl)) {
            throw new InvalidDeviceIdException();
        }

        // Throw an exception if the API endpoint URL is unknown
        if (is_null($this->apiEndpointUrl)) {
            throw new InvalidApiEndpointUrlException();
        }

        // Throw an exception if the consent token is not set
        if (is_null($this->consentToken)) {
            throw new InvalidConsentTokenException();
        }

        // Generate the URL
        $url = sprintf('%s/v1/devices/%s/settings/address', $this->apiEndpointUrl, $this->deviceId);
        $this->Debug($tag, sprintf('URL: %s', $url));

        // Create a new cURL object
        $curl = new cURL();

        // Retrieve the information
        try {
            $text = $curl
                ->SetUrl($url)
                ->SetHeader('Accept', 'application/json')
                ->SetHeader('Authorization', sprintf('Bearer %s', $this->consentToken))
                ->Execute();
        } catch (cURLRequestFailedException $e) {
            throw new AlexaApiRequestFailedException();
        }

        // Check the result code
        switch ($curl->GetHTTPCode()) {
            case 200:
                // All fine
                break;
            case 403:
                // The request was denied
                throw new AlexaApiRequestForbiddenException();
            default:
                // Another error occurred
                throw new AlexaApiRequestFailedException();
        }

        // Decode the information
        $data = @json_decode($text, true);

        // Throw an exception if the information is not a valid JSON structure
        if (is_null($data)) {
            throw new InvalidAddressException();
        }

        // Remember the information
        $this->address = $data;

        // Return the data
        return $data;
    }

    /**
     * Returns the type of the request.
     * @return string Request type.
     * @see Request::TYPE_LAUNCH_REQUEST
     * @see Request::TYPE_INTENT_REQUEST
     * @see Request::TYPE_SESSION_ENDED_REQUEST
     */
    public function GetRequestType()
    {
        return $this->requestType;
    }

    /**
     * Checks if the request is a LaunchRequest.
     * @return bool True if the request is a LaunchRequest.
     */
    public function IsLaunchRequest()
    {
        return $this->requestType == self::TYPE_LAUNCH_REQUEST;
    }

    /**
     * Checks if the request is an IntentRequest.
     * @return bool True if the request is an IntentRequest.
     */
    public function IsIntentRequest()
    {
        return $this->requestType == self::TYPE_INTENT_REQUEST;
    }

    /**
     * Checks if the request is an APL UserEvent.
     * @return bool
     */
    public function IsAPLUserEvent()
    {
        return $this->requestType == self::TYPE_APL_USER_EVENT;
    }

    /**
     * Checks if the request was sent by the Alexa Service Simulator.
     * @return bool True if the request was sent from the service simulator.
     */
    public function IsServiceSimulatorRequest()
    {
        return $this->isServiceSimulatorRequest;
    }

    /**
     * Returns the unique identifier for the user's active session.
     * @return string Unique identifier per a user’s active session.
     */
    public function GetSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Checks if the request is currently processing.
     * @return bool True if the request is processing.
     */
    public function IsProcessing()
    {
        return $this->isProcessing;
    }

    /**
     * Returns the current intent name.
     * This is the name of the intent currently processed by the request, regardless of other intents that may have been
     * processed before this and which may have redirected to the current intent.
     * @return string|null Intent name or null if the intent name is not known yet.
     */
    public function GetIntentName()
    {
        if (count($this->intentStack) == 0) {
            // The intent name is not known yet
            return null;
        } else {
            // Return the last intent name from the stack
            return array_values(array_slice($this->intentStack, -1))[0];
        }
    }

    /**
     * Returns the first intent name.
     * This is the name of the intent invoked by the request, regardless of which other intents have been
     * invoked due to callback or redirection.
     * @return string|null Intent name or null if the intent name is not known yet.
     */
    public function GetFirstIntentName()
    {
        if (count($this->intentStack) == 0) {
            // The intent name is not known yet
            return null;
        } else {
            // Return the first intent name from the stack
            return $this->intentStack[0];
        }
    }

    /**
     * Returns the list of intent names in the callback/redirect stack.
     * @return array List of intent names used to find out where a redirected intent request came from.
     */
    public function GetIntentStack()
    {
        return $this->intentStack;
    }

    /**
     * Checks if the request has been redirected to the current intent.
     * @return bool True if other intents have redirected the request to the current intent.
     */
    public function IsRedirected()
    {
        return count($this->intentStack) > 1;
    }

    /**
     * Checks if the current intent was called as a callback, eg. to process collected data.
     * @return bool True if the current intent was called as a callback.
     */
    public function IsCallback()
    {
        return ! is_null($this->callbackSourceIntent);
    }

    /**
     * Returns the name of the intent that called the current intent as a callback.
     * @return string|null Intent name or null if no callback was called.
     */
    public function GetCallbackSourceIntentName()
    {
        return $this->callbackSourceIntent;
    }

    /**
     * Validates the current request by checking various request data fields.
     * @throws InvalidRequestVersionException if the version is invalid.
     * @throws LocaleNotSupportedException if the locale is not known or supported.
     * @throws InvalidRequestIdException if the request ID is invalid.
     * @throws InvalidSessionIdException if the session ID is invalid.
     * @throws ApplicationIdNotAllowedException if the application ID is not allowed.
     * @throws RequestTypeNotSupportedException if the request type is not supported.
     */
    protected function Validate()
    {
        // Validate the request data
        $this->ValidateVersion();
        $this->ValidateLocale();
        $this->ValidateRequestId();
        $this->ValidateSessionId();
        $this->ValidateApplicationId();
        $this->ValidateDeviceId();
        $this->ValidateApiEndpointUrl();
        $this->ValidateConsentToken();
        $this->ValidateRequestType();
    }

    /**
     * Validates the version field of the request.
     * @throws InvalidRequestVersionException if the version is invalid.
     */
    protected function ValidateVersion()
    {
        // Validate version
        $version = @$this->data['version'];

        // Check if the version field exists
        if (is_null($version)) {
            // It does not exist
            // This will be (temporarily) ignored since there seems to be a bug on the text tab of the Alexa Service
            // Simulator
            $this->Debug('Version Validation', 'Version field does not exist; ignoring as a workaround for Alexa Service Simulator bug');
        } else {
            // Is exists
            $this->Debug('Version Validation', $version);
            // Check if we know the version
            if ($version != '1.0') {
                $this->Debug('Version Validation', 'Version is not supported');
                throw new InvalidRequestVersionException();
            }
        }
    }

    /**
     * Validates the locale field of the request.
     * @throws LocaleNotSupportedException if the locale is not known or supported.
     */
    protected function ValidateLocale()
    {
        // Validate locale
        $locale = @$this->data['request']['locale'];
        $this->Debug('Locale Validation', $locale);
        $localeName = Locales::GetNameByCode($locale);
        if ($localeName === false) {
            $this->Debug('Locale Validation', 'Locale is not supported');
            throw new LocaleNotSupportedException();
        }
        $this->Debug('Locale Validation', $localeName);
        $this->locale = $locale;
    }

    /**
     * Validates the unique identifier of the request.
     * @throws InvalidRequestIdException if the request ID is invalid.
     */
    protected function ValidateRequestId()
    {
        // Validate Request ID
        $id = @$this->data['request']['requestId'];
        $this->Debug('Request ID Validation', $id);
        if (! preg_match('/^(EdwRequestId|amzn1\.echo\-api\.request)\.[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/', $id)) {
            $this->Debug('Request ID Validation', 'Request ID is invalid');
            throw new InvalidRequestIdException();
        }
        $this->requestId = $id;
    }

    /**
     * Validates the unique identifier of the user's active session.
     * @throws InvalidSessionIdException if the session ID is invalid.
     */
    protected function ValidateSessionId()
    {
        // Validate Session ID
        $id = @$this->data['session']['sessionId'];
        $this->Debug('Session ID Validation', $id);
        if (! preg_match('/^(SessionId|amzn1\.echo\-api\.session)\.[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/', $id)) {
            $this->Debug('Session ID Validation', 'Session ID is invalid');
            throw new InvalidSessionIdException();
        }
        $this->sessionId = $id;
    }

    /**
     * Validates the application ID of the request against the allowed application ID.
     * For WebHook requests, the Application ID needs to be configured on the I/O's configuration page.
     * @throws ApplicationIdNotAllowedException if the application ID is not allowed.
     * @see IOInterface::GetAllowedApplicationId()
     */
    protected function ValidateApplicationId()
    {
        // Validate Application ID
        $id = @$this->data['session']['application']['applicationId'];
        $this->Debug('Application ID Validation', $id);
        if ($id != $this->io->GetAllowedApplicationId()) {
            $this->Debug('Application ID Validation', 'Application ID is not allowed');
            throw new ApplicationIdNotAllowedException();
        }
    }

    /**
     * Validates the unique identifier of the Alexa device.
     * @throws InvalidDeviceIdException if the device ID is invalid.
     */
    protected function ValidateDeviceId()
    {
        // Validate Device ID
        $id = @$this->data['context']['System']['device']['deviceId'];

        // Check if the device ID is set
        $this->deviceId = null;
        if (is_null($id)) {
            // It is not set
            $this->Debug('Device ID Validation', 'Not set');
        } else {
            // It is set
            $this->Debug('Device ID Validation', $id);
            if ($id == 'deviceId') {
                // Request was sent from the service simulator
                $this->Debug('Device ID Validation', 'Ignoring, request is coming from the Alexa Service Simulator');
                $this->isServiceSimulatorRequest = true;
            } else {
                if (! preg_match('/^amzn1\.ask\.device\.[0-9A-Z]{156}$/', $id)) {
                    //$this->Debug('Device ID Validation', 'Device ID is invalid');
                    //throw new InvalidDeviceIdException();
                }
                $this->deviceId = $id;
                $this->isServiceSimulatorRequest = false;
            }
        }
    }

    /**
     * Validates the Amazon Alexa API endpoint URL.
     * @throws InvalidDeviceIdException if the device ID is invalid.
     */
    protected function ValidateApiEndpointURL()
    {
        // Validate API endpoint URL
        $url = @$this->data['context']['System']['apiEndpoint'];

        // Check if the API endpoint URL is set
        $this->apiEndpointUrl = null;
        if (is_null($url)) {
            // It is not set
            $this->Debug('API Endpoint URL Validation', 'Not set');
        } else {
            // It is set
            $this->Debug('API Endpoint URL Validation', $url);
            if ($url == 'apiEndpoint') {
                // Request was sent from the service simulator
                $this->Debug('API Endpoint URL Validation', 'Ignoring, request is coming from the Alexa Service Simulator');
            } else {
                if (! filter_var($url, FILTER_VALIDATE_URL)) {
                    $this->Debug('API Endpoint URL Validation', 'URL is invalid');
                    throw new InvalidApiEndpointUrlException();
                }
                $this->apiEndpointUrl = $url;
            }
        }
    }

    /**
     * Validates the consent token to be used for Alexa API requests.
     * @throws InvalidDeviceIdException if the device ID is invalid.
     */
    protected function ValidateConsentToken()
    {
        // Validate consent token
        $token = @$this->data['context']['System']['user']['permissions']['consentToken'];

        // Check if consent token is set
        $this->consentToken = null;
        if (is_null($token)) {
            // It is not set
            $this->Debug('Consent Token Validation', 'Not set');
        } else {
            // It is set
            $this->Debug('Consent Token Validation', $token);
            if ($token == 'consentToken') {
                // Request was sent from the service simulator
                $this->Debug('Consent Token Validation', 'Ignoring, request is coming from the Alexa Service Simulator');
            } else {
                if (! preg_match('/^Atza\|[0-9A-Za-z_-]{519}$/', $token)) {
                    $this->Debug('Consent Token Validation', 'Consent Token is invalid');
                    throw new InvalidConsentTokenException();
                }
                $this->consentToken = $token;
            }
        }
    }

    /**
     * Validates the request type.
     * @throws RequestTypeNotSupportedException if the request type is not supported.
     */
    protected function ValidateRequestType()
	{
		$validRequestTypes = array(
			self::TYPE_LAUNCH_REQUEST,
			self::TYPE_INTENT_REQUEST,
			self::TYPE_SESSION_ENDED_REQUEST,
			self::TYPE_APL_USER_EVENT,
			self::TYPE_SYSTEM_EXCEPTION_ENCOUNTERED
		);

		$requestType = @$this->data['request']['type'];
		$this->Debug('Request Type Validation', $requestType);

		if (!in_array($requestType, $validRequestTypes)) {
			throw new RequestTypeNotSupportedException();
		}
		$this->requestType = $requestType;
	}



    /**
     * Loads the intent slot key value pairs from the request data and creates a new IntentSlots object from the data.
     * @throws InvalidIntentSlotsException if the intents slots in the request data are invalid.
     * @see IntentSlots
     * @see Request::$intentSlots
     */
    protected function LoadSlots()
	{
	    // Request/Intent defensiv lesen
	    $req    = $this->data['request'] ?? null;
	    $intent = is_array($req) ? ($req['intent'] ?? null) : null;
	    $slots  = is_array($intent) ? ($intent['slots'] ?? []) : [];
	
	    $this->Debug('Slots from Request', json_encode($slots));
	
	    // Session-Slots defensiv lesen
	    $session      = $this->data['session'] ?? null;
	    $sessionAttrs = is_array($session) ? ($session['attributes'] ?? null) : null;
	    $sessionSlots = is_array($sessionAttrs) ? ($sessionAttrs['slots'] ?? []) : [];
	
	    $this->Debug('Slots from Session', json_encode($sessionSlots));
	
	    // Zusammenführen
	    $this->slots = new IntentSlots($slots, $sessionSlots);
	    $this->Debug('Merged Slots', $this->slots->GetAsJSON());
	}

    /**
     * Loads the session data key value pairs from the request data and creates a new SessionAttributes object from the data.
     * @see SessionAttributes
     * @see Request::$sessionAttributes
     */
    protected function LoadSessionAttributes()
    {
        // Load Session Attributes
        $sessionAttributes = @$this->data['session']['attributes']['attributes'];
        if (! is_array($sessionAttributes)) {
            $sessionAttributes = array();
        }

        // Create the attributes object
        $this->attributes = new SessionAttributes($sessionAttributes);
        $this->Debug('Session Attributes', $this->attributes->GetAsJSON());
    }

    /**
     * Loads the name of the callback intent from the request data.
     * @see Request::$callbackIntentName
     */
    protected function LoadCallbackIntent()
	{
	    $attrs = [];
	    if (is_array($this->data)) {
	        $session = $this->data['session'] ?? null;
	        if (is_array($session)) {
	            $attrs = $session['attributes'] ?? [];
	        }
	    }
	    $cb = $attrs['callbackIntent'] ?? ($attrs['CallbackIntent'] ?? null);
	    $this->callbackIntent = is_string($cb) && $cb !== '' ? $cb : null;

	    if ($this->callbackIntent !== null) {
	        $this->Debug('Callback Intent', $this->callbackIntent);
	    }
	}
	
	protected function LoadAplUserEvent()
	{
	    if (($this->data['request']['type'] ?? null) === self::TYPE_APL_USER_EVENT) {
	        $args = $this->data['request']['arguments'] ?? [];
	        if (!is_array($args)) { $args = []; }
	        $this->aplArguments = $args;   // <-- hier war das fehlende $
	        $this->Debug('APL.UserEvent Arguments', json_encode($args));
	    } else {
	        $this->aplArguments = [];
	    }
	}


    /**
     * Processes the incoming Alexa Custom Skill request and returns the response.
     * The method validates various request fields, creates objects for the intent slots and the session attributes,
     * calls the specialized processing methods for the different request types and returns a Response object.
     * @throws InvalidRequestVersionException if the version is invalid.
     * @throws LocaleNotSupportedException if the locale is not known or supported.
     * @throws InvalidRequestIdException if the request ID is invalid.
     * @throws InvalidSessionIdException if the session ID is invalid.
     * @throws ApplicationIdNotAllowedException if the application ID is not allowed.
     * @throws RequestTypeNotSupportedException if the request type is not supported.
     * @throws InvalidIntentSlotsException if the intents slots in the request data are invalid.
     * @return Response Response object generated by the intent.
     * @internal
     * @see Request::ProcessLaunchRequest()
     * @see Request::ProcessIntentRequest()
     * @see Request::ProcessSessionEndedRequest()
     */
     public function Process()
	{
		// --- Validieren & Grunddaten laden ---
		$this->Debug('LOADED FILE', __FILE__);
		$this->Validate();
		$this->LoadSlots();
        $this->LoadSessionAttributes();
		$this->LoadCallbackIntent();

		// ============================================================
		// EARLY: APL.UserEvent → in einen normalen IntentRequest wandeln
		// ============================================================
		if (($this->data['request']['type'] ?? null) === self::TYPE_APL_USER_EVENT) {
			$this->Debug('DISPATCH', 'EARLY APL.UserEvent handler!');

			$args = (isset($this->data['request']['arguments']) && is_array($this->data['request']['arguments']))
				? $this->data['request']['arguments'] : [];
			$this->Debug('APL_UserEvent Arguments', json_encode($args));
            $this->aplArguments = $args;

			if (isset($this->attributes) && method_exists($this->attributes, 'Set')) {
				$this->attributes->Set('APL_ARGS', $args);
			}

			$intentCandidate = (isset($args[0]) && is_string($args[0]) && $args[0] !== '') ? $args[0] : null;

			try {
				$intentName = $intentCandidate ?: 'GetHaus';
				$this->Debug('APL_Dispatch target', $intentName);
				$intent = \Patami\IPS\Services\Alexa\Skills\Custom\ModuleIntent::CreateByName($this->io, $intentName);

				$this->intentStack[] = $intent->GetName();
				$response = $intent->Execute($this);

				if (method_exists($response, 'SetShouldEndSession')) {
					$response->SetShouldEndSession(false);
				}
				return $response;

			} catch (\Throwable $e) {
				$this->Debug('APL_ERROR', '@ '.$e->getFile().':'.$e->getLine());
				$this->Debug('APL_TRACE', $e->getTraceAsString());

				try {
					$this->Debug('APL_FALLBACK target', 'GetHaus');
					$intent = \Patami\IPS\Services\Alexa\Skills\Custom\ModuleIntent::CreateByName($this->io, 'GetHaus');

					$this->intentStack[] = $intent->GetName();
					$response = $intent->Execute($this);

					if (method_exists($response, 'SetShouldEndSession')) {
						$response->SetShouldEndSession(false);
					}
					return $response;

				} catch (\Throwable $e2) {
					return $this->ProcessLaunchRequest();
				}
			}
		}
		// --- /EARLY --------------------------------------------------------



		// ==================================================================
		// Sonderfall: Alexa meldet einen Fehler-Callback (kein User-Problem)
		// ==================================================================
		if ($this->requestType === self::TYPE_SYSTEM_EXCEPTION_ENCOUNTERED) {
			$this->Debug('SYSTEM.ExceptionEncountered:error', json_encode(@$this->data['request']['error']));
			$this->Debug('SYSTEM.ExceptionEncountered:cause', json_encode(@$this->data['request']['cause']));

			$resp = Response::Create();
			$resp->AddPlainText('OK');
			if (method_exists($resp, 'SetShouldEndSession')) {
				$resp->SetShouldEndSession(false);
			}
			return $resp;
		}

		// ----------------- ab hier normaler Flow -----------------
		$this->isProcessing = true;

		switch ($this->requestType) {
			case self::TYPE_LAUNCH_REQUEST:
				$response = $this->ProcessLaunchRequest();
				break;

			case self::TYPE_INTENT_REQUEST:
			    $req    = $this->data['request'] ?? null;
			    $intent = is_array($req) ? ($req['intent'] ?? null) : null;
			    $name   = is_array($intent) ? ($intent['name'] ?? null) : null;
			    $this->Debug('Intent Name Validation', $name);
			    if ($name === null) {
			        return $this->ProcessLaunchRequest();
			    }
			    $response = $this->ProcessIntentRequest($name);
			    break;

			case self::TYPE_SESSION_ENDED_REQUEST:
				$response = $this->ProcessSessionEndedRequest();
				break;
		}

		$this->isProcessing = false;
		/** @noinspection PhpUndefinedVariableInspection */
		return $response;
	}







    /**
     * Processes Alexa Custom Skill LaunchRequests and returns the response.
     * @return Response Response object generated by the intent.
     * @throws LaunchRequestIntentNotSetException if the I/O does not return an intent class that handles LaunchRequests.
     */
    protected function ProcessLaunchRequest()
    {
        // Get the LaunchRequest intent class name
        /** @var ModuleIntent $className */
        $className = $this->io->GetLaunchIntentClassName();

        // Throw an error if the launch request intent is not set
        if (is_null($className)) {
            throw new LaunchRequestIntentNotSetException();
        }

        // Create the intent object
        /** @var ModuleIntent $intent */
        /** @noinspection PhpParamsInspection */
        $intent = $className::Create($this->io);

        // Push the intent name to the stack
        $this->intentStack[] = $intent->GetName();

        // Execute the intent
        return $intent->Execute($this);
    }

    /**
     * Processes Alexa Custom Skill IntentRequests and returns the response.
     * @param string $intentName Intent name as received from the Amazon servers.
     * @return Response Response object generated by the intent.
     * @throws IntentNotFoundException if no matching intent built-in intent class.
     */
    protected function ProcessIntentRequest($intentName)
    {
        // Create the intent object
        /** @noinspection PhpParamsInspection */
        $intent = ModuleIntent::CreateByName($this->io, $intentName);
        
        // Push the intent name to the stack
        $this->intentStack[] = $intent->GetName();
        // Execute the intent
        return $intent->Execute($this);
    }
	
    /**
     * Redirects to another intent.
     * This method is supposed to be called from within an intent's DoExecute() method or the aciton script of an IPS
     * intent instance.
     * @param string $intentName Intent name of the intent to which the request should be redirected.
     * @return Response Response object generated by the intent.
     * @throws IntentNotFoundException if no matching intent could be found either configured as IPS intent instance or
     * configured as a built-in intent class.
     * @throws RedirectLoopException if a loop was detected while processing a chain of redirects.
     * @throws RedirectNotAllowedException if processing of the request has not yet begun.
     */
    public function RedirectToIntent($intentName)
    {
        // Log the new intent name
        $this->Debug('Redirect to Intent', $intentName);

        // Throw an exception if redirection is now allowed
        if (! $this->isProcessing) {
            throw new RedirectNotAllowedException();
        }

        // Throw an exception if a redirection loop was detected
        if (in_array($intentName, $this->intentStack)) {
            $this->Debug('Detected Intent Loop', implode(', ', $this->intentStack) . ', ' . $intentName);
            throw new RedirectLoopException();
        }

        // Process the "new" intent request
        return $this->ProcessIntentRequest($intentName);
    }

    /**
     * Redirects to the callback intent specified in the user's active session.
     * This method is supposed to be called from within an intent's DoExecute() method or the aciton script of an IPS
     * intent instance.
     * @return Response Response object generated by the intent.
     * @throws CallbackIntentNotSetException if no callback intent name is set in the session.
     * @throws IntentNotFoundException if no matching intent could be found either configured as IPS intent instance or
     * configured as a built-in intent class.
     * @throws RedirectLoopException if a loop was detected while processing a chain of redirects.
     * @throws RedirectNotAllowedException if processing of the request has not yet begun.
     * @see Request::GetCallbackIntentName()
     */
    public function RedirectToCallbackIntent()
    {
        // Throw an exception if the callback intent is not set
        if (! $this->callbackIntent) {
            throw new CallbackIntentNotSetException();
        }

        // Log the new intent name
        $this->Debug('Redirect to Callback Intent', $this->callbackIntent);

        // Remember the callback source intent name
        $this->callbackSourceIntent = $this->GetIntentName();

        // Redirect
        return $this->RedirectToIntent($this->callbackIntent);
    }

    /**
     * Processes Alexa Custom Skill SessionEndedRequests, closes the session and returns an empty response.
     * This request type only occurs if a problem occurred or if the user stopped responding to questions from the
     * custom skill. The reason for ending the request if logged.
     * @return Response Empty response object.
     */
    protected function ProcessSessionEndedRequest()
    {
        // Log reason
        $reason = @$this->data['request']['reason'];
        $this->Debug('Session Ended Reason', $reason);

        // Send an empty response
        return Response::Create();
    }

    /**
     * Returns the response returned by the last request in the user's active session.
     * @return ResponseInterface Response object returned by the last request in the user's active session.
     */
    public function GetLastResponse()
    {
        // Return the last response object
        return $this->io->GetLastResponse();
    }

}
