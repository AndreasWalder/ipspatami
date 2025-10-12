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


use Patami\IPS\I18N\Translator;
use Patami\IPS\IO\ResponseInterface;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\LastResponseNotFoundException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidIntentResponseException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidIntentConfigurationPropertyException;
use Patami\IPS\System\IPS;
use Patami\IPS\System\Semaphore;


/**
 * Provides common methods for Amazon Alexa Custom Skill I/O modules.
 * Eliminates the need for a common base class, since both WebHook and WebOAuth I/Os are being used.
 * @package IPSPATAMI
 */
trait IOModuleTrait
{

    // Include the common code for Smart Home and Custom skills
    use \Patami\IPS\Services\Alexa\Skills\IOModuleTrait;

    /**
     * Registers configuration properties for Alexa Custom Skill I/O modules required by the intents of skill.
     * Called by WebHookIOModule::Create() or WebOAuthIOModule::Create().
     * Calls the SkillCreate() method of the included IOModuleTrait, which calls the IOModule::Create() method.
     * @throws InvalidIntentConfigurationPropertyException if the type of an intent configuration property is invalid.
     * @see WebHookIOModule
     * @see WebOAuthIOModule
     * @see \Patami\IPS\Services\Alexa\Skills\IOModuleTrait
     */
    protected function CustomSkillCreate()
    {
        // Call the parent method
        $this->SkillCreate();

        // Loop through intent classes
        $classNames = $this->GetIntentClassNames();
        foreach ($classNames as $className) {
            // Create the intent object
            /** @var ModuleIntent $className */
            /** @var ModuleIntent $intent */
            /** @noinspection PhpParamsInspection */
            $intent = $className::Create($this);
            // Get the properties
            $properties = $intent->GetConfigurationProperties();
            // Loop through the intent properties
            foreach ($properties as $property) {
                // Get the values
                $type = @$property['type'];
                $name = @$property['name'];
                $default = @$property['default'];
                // Check the name and throw an exception if somthing is wrong
                if (! $name) {
                    throw new InvalidIntentConfigurationPropertyException();
                }
                // Register the property
                $name = $this->GetTranslatedPropertyName($className, $name);
                switch ($type) {
                    case ModuleIntent::PROPERTY_BOOLEAN:
                        /** @noinspection PhpUndefinedMethodInspection */
                        $this->RegisterPropertyBoolean($name, $default);
                        break;
                    case ModuleIntent::PROPERTY_INTEGER:
                        /** @noinspection PhpUndefinedMethodInspection */
                        $this->RegisterPropertyInteger($name, $default);
                        break;
                    case ModuleIntent::PROPERTY_FLOAT:
                        /** @noinspection PhpUndefinedMethodInspection */
                        $this->RegisterPropertyFloat($name, $default);
                        break;
                    case ModuleIntent::PROPERTY_STRING:
                        /** @noinspection PhpUndefinedMethodInspection */
                        $this->RegisterPropertyString($name, $default);
                        break;
                    default:
                        // Unknown or invalid type, throw an exception
                        throw new InvalidIntentConfigurationPropertyException();
                }
            }
        }
    }

    /**
     * Adds sections specified by the intents of an Alexa Custom Skill I/O module to its configuration page.
     * Called by WebHookIOModule::GetConfigurationFormData() or WebOAuthIOModule::GetConfigurationFormData().
     * Calls the GetSkillConfigurationFormData() method of the included IOModuleTrait, which calls the
     * IOModule::GetConfigurationFormData() method.
     * @return array IPS module configuration form data.
     * @see Module
     */
    protected function GetCustomSkillConfigurationFormData()
    {
        // Call the parent method
        $data = $this->GetSkillConfigurationFormData();

        // Loop through intent classes
        $classNames = $this->GetIntentClassNames();
        foreach ($classNames as $className) {
            // Create the intent object
            /** @var ModuleIntent $className */
            /** @var ModuleIntent $intent */
            /** @noinspection PhpParamsInspection */
            $intent = $className::Create($this);
            // Get the configuration form data
            $intentData = $intent->GetConfigurationFormData();
            // Merge the elements
            $elements = @$intentData['elements'];
            if ($elements) {
                // Add header labels
                array_push($data['elements'],
                    array(
                        'type' => 'Label',
                        'label' => ''
                    ),
                    array(
                        'type' => 'Label',
                        'label' => $intent->GetLabel()
                    ),
                    array(
                        'type' => 'Label',
                        'label' => '---------------------------------------------------------------------------------------------------------------------------------'
                    )
                );
                // Translate the property names
                foreach ($elements as &$element) {
                    if (@$element['name']) {
                        $element['name'] = $this->GetTranslatedPropertyName($className, $element['name']);
                    }
                }
                // Merge in elements
                $data['elements'] = array_merge($data['elements'], $elements);
            }
            // Merge the translations
            $translations = @$intentData['translations'];
            if ($translations) {
                // Loop through the languages
                foreach ($translations as $language => $messages) {
                    // Loop through the messages
                    foreach ($messages as $originalMessage => $translatedMessage) {
                        // Set the translation text (possibly overwriting translations with the same original
                        // but different translated message texts)
                        $data['translations'][$language][$originalMessage] = $translatedMessage;
                    }
                }
            }
        }

        // Return the merged data
        return $data;
    }

    /**
     * Returns the label of the default locale configuration field.
     * @return string Translated label of the configuration field.
     */
    protected function GetLocaleLabel()
    {
        return Translator::Get('patami.framework.services.alexa.custom.iomoduletrait.form.default_locale.label');
    }

    /**
     * Returns the FQCN of the request class.
     * This needs to be overridden by the concrete Custom Skill I/O class to use the matching request class.
     * @return string FQCN of the request class.
     */
    abstract protected function GetRequestClassName();

    /**
     * Returns an Alexa response object with the translated text from the given exception.
     * @param \Exception $e Throws exception from which the exception message should be retrieved.
     * @param string $locale Locale code used for the translation of the message.
     * @return TellResponse Response object to be returned to the Amazon servers.
     */
    protected function CreateExceptionResponse(\Exception $e, $locale)
    {
        // Create a plain test response from the exception
        return TellResponse::CreateFromException($e, $locale);
    }

    /**
     * Creates a new request object from the supplied request text, processes the request and returns the response.
     * If an exception occurs while processing the request, if is caught and Alexa will speak the exception message.
     * @param string $text JSON-encoded request body received from the Amazon servers.
     * @return Response Response object to be sent back to the Amazon servers.
     * @throws InvalidIntentResponseException if the Request::Process() method did not return a Response object.
     * @see WebHookRequest
     * @see WebOAuthRequest
     * @see Response
     */
    protected function ProcessRequest($text)
    {
        try {
            // Process the request
            /** @var Request $className */
            $className = $this->GetRequestClassName();
            /** @var Request $request */
            /** @noinspection PhpParamsInspection */
            $request = $className::CreateFromText($this, $text, $this->GetLocale());
            $response = $request->Process();
            // Throw an request if the intent did not return a valid response
            if (! $response instanceof Response) {
                throw new InvalidIntentResponseException();
            }
        } catch (\Exception $e) {
            // Create an error response if an exception occurred
            $this->Debug('Caught Exception', get_class($e));
            $locale = isset($request)? $request->GetLocale(): $this->GetLocale();
            $response = $this->CreateExceptionResponse($e, $locale);
        }

        // Return the response
        return $response;
    }

    /**
     * Returns the FQCN of the intent class used for LaunchRequests.
     * For WebHook requests, the user can override the LaunchRequest with an Intent Module instance.
     * @return string FQCN of the intent class.
     * @see Intent
     */
    public function GetLaunchIntentClassName()
    {
        // Return no LaunchRequest intent class name
        return null;
    }

    /**
     * Returns a list of FQCNs of the intent classes used to IntentRequests.
     * For WebHook requests, the user can override them or add new intents with Intent Module instances.
     * @return array FQCNs of the intent classes.
     */
    public function GetIntentClassNames()
    {
        // Return an empty list of intent class names
        return array();
    }

    /**
     * Returns a translated configuration form property name.
     * This is used to make sure the properties required by the built-in intents have unique names by prefixing the
     * property name with the FQCN of the intent class.
     * @param string $className FQCN of the intent class.
     * @param string $name Name of the property.
     * @return string Translated property name.
     */
    protected function GetTranslatedPropertyName($className, $name)
    {
        return sprintf('%s_%s', str_replace('\\', '_', $className), $name);
    }

    /**
     * Returns the value of an intent property.
     * This method is called by the intent object embedded in the configuration form,
     * @param ModuleIntent $intent Intent object.
     * @param string $name Name of the intent configuration property.
     * @return mixed Value of the property.
     */
    public function ReadIntentProperty(ModuleIntent $intent, $name)
    {
        // Get the property name
        $propertyName = $this->GetTranslatedPropertyName(get_class($intent), $name);

        // Return the property value
        /** @noinspection PhpUndefinedFieldInspection */
        return IPS::GetProperty($this->InstanceID, $propertyName);
    }

    /**
     * Saves the response on the IPS object buffer and calls the parent method to send the response to the Amazon servers.
     * This is required to allow the AMAZON.RepeatIntent intent to speak the last response again.
     * @param ResponseInterface $response Response to be sent to the Amazon servers.
     */
    protected function SendResponse(ResponseInterface $response)
    {
        // Remember the response
        $this->SetLastResponse($response);

        // Call the parent method
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        parent::SendResponse($response);
    }

    /**
     * Returns the name of the semaphore used to make sure the IPS object buffer is locked for exclusive access.
     * @return string Name of the semaphore.
     * @see Semaphore
     */
    protected function GetLastResponseSemaphoreName()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        return sprintf('%d_buffer', $this->InstanceID);
    }

    /**
     * Returns the number of milliseconds to wait for exclusive access to the IPS object buffer.
     * @return int Milliseconds to wait for semaphore timeout.
     */
    protected function GetLastResponseSemaphoreTimeout()
    {
        return Semaphore::DEFAULT_WAIT_TIME;
    }

    /**
     * Returns the name of the IPS object buffer used to store the last response.
     * @return string Name of the IPS object buffer.
     */
    protected function GetLastResponseBufferName()
    {
        return 'response';
    }

    /**
     * Returns the last response sent to the Amazon servers from the IPS object buffer.
     * @return ResponseInterface Last response object.
     * @throws LastResponseNotFoundException if no last response was stored in the IPS object buffer.
     */
    public function GetLastResponse()
    {
        // Wait for exclusive buffer access
        $result = IPS::SemaphoreEnter($this->GetLastResponseSemaphoreName(), $this->GetLastResponseSemaphoreTimeout());
        if (! $result) {
            $this->Debug('Get Last Response', 'Timeout waiting for exclusive buffer access');
            throw new LastResponseNotFoundException();
        }

        // Get the buffer contents
        /** @noinspection PhpUndefinedMethodInspection */
        $buffer = $this->GetBuffer($this->GetLastResponseBufferName());

        // Release the semaphore (and the exclusive buffer access)
        IPS::SemaphoreLeave($this->GetLastResponseSemaphoreName());

        // Unserialize the Response object
        $response = @unserialize($buffer);

        // Return false if deserialization failed
        if (! $response instanceof ResponseInterface) {
            $this->Debug('Get Last Response', sprintf('Unserialized response is not an object that implements Patami\IPS\IO\ResponseInterface: %s', $buffer));
            throw new LastResponseNotFoundException();
        }

        // Return the response object (or false)
        return $response;
    }

    /**
     * Remembers the response sent to the Amazon servers in the IPS object buffer.
     * @param ResponseInterface $response Last response object.
     * @return bool
     */
    protected function SetLastResponse(ResponseInterface $response)
    {
        // Wait for exclusive buffer access
        $result = IPS::SemaphoreEnter($this->GetLastResponseSemaphoreName(), $this->GetLastResponseSemaphoreTimeout());
        if (! $result) {
            $this->Debug('Set Last Response', 'Timeout waiting for exclusive buffer access');
            return false;
        }

        // Serialize the response object into the buffer
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SetBuffer($this->GetLastResponseBufferName(), @serialize($response));

        // Release the semaphore (and the exclusive buffer access)
        IPS::SemaphoreLeave($this->GetLastResponseSemaphoreName());

        // Return true
        return true;
    }

}