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


namespace Patami\IPS\Services\Alexa\Skills\Custom\Intents;


use Patami\IPS\I18N\Translator;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidIntentConfigurationPropertyException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\SpeechOutputTextTooLongException;
use Patami\IPS\Services\Alexa\Skills\Custom\Intents\Configurator\Exceptions\InvalidItemClassNameException;
use Patami\IPS\Services\Alexa\Skills\Custom\ModuleIntent;
use Patami\IPS\Services\Alexa\Skills\Custom\Request;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\LocaleNotSupportedException;
use Patami\IPS\Services\Alexa\Skills\Custom\Intents\Exceptions\SpeechOutputTextNotSetException;
use Patami\IPS\Services\Alexa\Skills\Custom\Response;
use Patami\IPS\Helpers\StringHelper;
use Patami\IPS\System\Locales;


/**
 * Intent class which returns a SpeechOutput object in order to read a text to the user.
 * @package IPSPATAMI
 */
class SpeechOutputIntent extends ModuleIntent
{

    /** Interpret the text as plain text. */
    const MODE_PLAIN_TEXT = 0;

    /** Interpret the text as SSML. */
    const MODE_SSML = 1;

    protected function GetType()
    {
        return 'SpeechOutputIntent';
    }

    /**
     * Returns the configuration properties of the intent.
     * The user can configure the translated speech output texts, the mode (plain text or SSML) and session handling
     * options.
     * @return array Configuration properties.
     */
    public function GetConfigurationProperties()
    {
        // Get the list of properties from the parent method
        $properties = parent::GetConfigurationProperties();

        // Get the default speech output textx
        $defaultTexts = $this->GetDefaultSpeechOutputProperties();

        // Push our properties to the list
        array_push($properties,
            array(
                'type' => self::PROPERTY_STRING,
                'name' => 'SpeechOutputTextDEDE',
                'default' => @$defaultTexts['de-DE']
            ),
            array(
                'type' => self::PROPERTY_STRING,
                'name' => 'SpeechOutputTextENUS',
                'default' => @$defaultTexts['en-US']
            ),
            array(
                'type' => self::PROPERTY_STRING,
                'name' => 'SpeechOutputTextENGB',
                'default' => @$defaultTexts['en-GB']
            ),
            array(
                'type' => self::PROPERTY_INTEGER,
                'name' => 'SpeechOutputMode',
                'default' => $this->GetDefaultSpeechOutputModeProperty()
            ),
            array(
                'type' => self::PROPERTY_BOOLEAN,
                'name' => 'ContinueSession',
                'default' => $this->GetDefaultContinueSessionProperty()
            ),
            array(
                'type' => self::PROPERTY_BOOLEAN,
                'name' => 'Callback',
                'default' => $this->GetDefaultCallbackProperty()
            ),
            array(
                'type' => self::PROPERTY_STRING,
                'name' => 'CallbackIntentName',
                'default' => $this->GetDefaultCallbackIntentNameProperty()
            )
        );

        // Return the property list
        return $properties;
    }

    /**
     * Returns the default translated speech output texts.
     * @return array Translated speech output texts.
     */
    protected function GetDefaultSpeechOutputProperties()
    {
        return array(
            'de-DE' => 'OK',
            'en-US' => 'OK',
            'en-GB' => 'OK'
        );
    }

    /**
     * Returns the default speech output mode.
     * @return int Speech output mode.
     * @see SpeechOutputIntent::MODE_PLAIN_TEXT
     * @see SpeechOutputIntent::MODE_SSML
     */
    protected function GetDefaultSpeechOutputModeProperty()
    {
        return self::MODE_PLAIN_TEXT;
    }

    /**
     * Returns the default value for the continue session property.
     * @return bool True if the session should be continued after reading the text to the user.
     */
    protected function GetDefaultContinueSessionProperty()
    {
        return false;
    }

    /**
     * Returns the default value for the callback property.
     * @return bool True if the intent should set a callback intent name.
     */
    protected function GetDefaultCallbackProperty()
    {
        return false;
    }

    /**
     * Returns the default value for the callback name property.
     * @return string Intent name of the callback intent.
     */
    protected function GetDefaultCallbackIntentNameProperty()
    {
        return '';
    }

    /**
     * Returns the configuration form of the intent.
     * The user can configure the translated speech output texts, the mode (plain text or SSML) and session handling
     * options.
     * @return array Configuration form.
     */
    public function GetConfigurationFormData()
    {
        // Get the parent form data
        $data = parent::GetConfigurationFormData();

        // Add nothing if the intent is not enabled
        if (! $this->IsEnabled()) {
            return $data;
        }

        // Push the speech output text form fields if they're visible
        if ($this->AreSpeechOutputTextPropertiesVisible()) {
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => Translator::Get('patami.framework.services.alexa.custom.intents.speechoutputintent.form.output_text.label')
                ),
                array(
                    'type' => 'ValidationTextBox',
                    'name' => 'SpeechOutputTextDEDE',
                    'caption' => Translator::Get('patami.framework.services.alexa.custom.intents.speechoutputintent.form.output_text.de-de_label')
                ),
                array(
                    'type' => 'ValidationTextBox',
                    'name' => 'SpeechOutputTextENUS',
                    'caption' => Translator::Get('patami.framework.services.alexa.custom.intents.speechoutputintent.form.output_text.en-us_label')
                ),
                array(
                    'type' => 'ValidationTextBox',
                    'name' => 'SpeechOutputTextENGB',
                    'caption' => Translator::Get('patami.framework.services.alexa.custom.intents.speechoutputintent.form.output_text.en-gb_label')
                )
            );
        }

        // Push the speech output mode form field if it is visible
        if ($this->IsSpeechOutputModePropertyVisible()) {
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => Translator::Get('patami.framework.services.alexa.custom.intents.speechoutputintent.form.type.label')
                ),
                array(
                    'type' => 'Select',
                    'name' => 'SpeechOutputMode',
                    'caption' => '',
                    'options' => array(
                        array(
                            'label' => Translator::Get('patami.framework.services.alexa.custom.intents.speechoutputintent.form.type.plain_text_option'),
                            'value' => self::MODE_PLAIN_TEXT
                        ),
                        array(
                            'label' => Translator::Get('patami.framework.services.alexa.custom.intents.speechoutputintent.form.type.ssml_option'),
                            'value' => self::MODE_SSML
                        )
                    )
                )
            );
        }

        // Push the continue session checkbox if it is visible
        if ($this->IsContinueSessionPropertyVisible()) {
            array_push($data['elements'],
                array(
                    'type' => 'CheckBox',
                    'name' => 'ContinueSession',
                    'caption' => Translator::Get('patami.framework.services.alexa.custom.intents.speechoutputintent.form.continue_session.label')
                )
            );
        }

        // Push the callback checkbox if it is visible
        $continueSession = $this->ReadPropertyBoolean('ContinueSession');
        if ($continueSession && $this->IsCallbackPropertyVisible()) {
            array_push($data['elements'],
                array(
                    'type' => 'CheckBox',
                    'name' => 'Callback',
                    'caption' => Translator::Get('patami.framework.services.alexa.custom.intents.speechoutputintent.form.custom_callback_intent.label')
                )
            );
        }

        // Push the callback intent name input field if it is visible
        $callback = $this->ReadPropertyBoolean('Callback');
        if ($continueSession && $callback && $this->IsCallbackIntentNamePropertyVisible()) {
            array_push($data['elements'],
                array(
                    'type' => 'ValidationTextBox',
                    'name' => 'CallbackIntentName',
                    'caption' => Translator::Get('patami.framework.services.alexa.custom.intents.speechoutputintent.form.custom_callback_intent.intent_label')
                )
            );
        }

        // Return the form data
        return $data;
    }

    /**
     * Checks if the speech output text configuration fields should be displayed on the I/O module's configuration form.
     * @return bool True if the configuration fields should be displayed.
     */
    protected function AreSpeechOutputTextPropertiesVisible()
    {
        return true;
    }

    /**
     * Checks if the speech output mode configuration field should be displayed on the I/O module's configuration form.
     * @return bool True if the configuration field should be displayed.
     */
    protected function IsSpeechOutputModePropertyVisible()
    {
        return true;
    }

    /**
     * Checks if the continue session configuration field should be displayed on the I/O module's configuration form.
     * @return bool True if the configuration field should be displayed.
     */
    protected function IsContinueSessionPropertyVisible()
    {
        return true;
    }

    /**
     * Checks if the callback configuration field should be displayed on the I/O module's configuration form.
     * @return bool True if the configuration field should be displayed.
     */
    protected function IsCallbackPropertyVisible()
    {
        return true;
    }

    /**
     * Checks if the callback intent name configuration field should be displayed on the I/O module's configuration form.
     * @return bool True if the configuration field should be displayed.
     */
    protected function IsCallbackIntentNamePropertyVisible()
    {
        return true;
    }

    /**
     * Returns the translated speech output text.
     * @param string $locale Locale of the speech output text.
     * @return string|false Translated speech output text or false if the locale is not supported.
     */
    protected function GetSpeechOutputTextPropertyByLocale($locale)
    {
        switch ($locale) {
            case 'de-DE':
                $name = 'SpeechOutputTextDEDE';
                break;
            case 'en-US':
                $name = 'SpeechOutputTextENUS';
                break;
            case 'en-GB':
                $name = 'SpeechOutputTextENGB';
                break;
            default:
                return false;
        }

        return $name;
    }

    /**
     * Processes the Alexa Custom Skill Intent request by returning speech output text.
     * This method is automatically called by the Execute() method.
     * @param Request $request Request object of the incoming request.
     * @return Response Response object to be sent back to the Amazon servers generated by the action script.
     * @throws LocaleNotSupportedException if the locale requested by Alexa is not supported.
     * @throws SpeechOutputTextNotSetException if the speech output text is not configured.
     * @throws SpeechOutputTextTooLongException if the text is too long (more than 8000 characters).
     * @throws InvalidIntentConfigurationPropertyException if the speech output mode configuration is invalid.
     */
    protected function DoExecute(Request $request)
    {
        // Get the request locale
        $locale = $request->GetLocale();
        $this->Debug('Speech Output Locale', $locale);

        // Get the text property name
        $name = $this->GetSpeechOutputTextPropertyByLocale($locale);

        // Throw an exception if the locale is not supported
        if (! $name) {
            throw new LocaleNotSupportedException();
        }

        // Get the text
        $text = $this->ReadPropertyString($name);
        $this->Debug('Speech Output Text', $text);

        // Throw an exception if the text is not set
        if ($text == '') {
            throw new SpeechOutputTextNotSetException();
        }

        // Create and return the response object
        $mode = $this->ReadPropertyInteger('SpeechOutputMode');
        switch ($mode) {
            case self::MODE_PLAIN_TEXT:
                $this->Debug('Speech Output Type', 'Plain Text');
                $response = Response::CreatePlainText($text);
                break;
            case self::MODE_SSML:
                $this->Debug('Speech Output Type', 'SSML');
                $response = Response::CreateSSML($text);
                break;
            default:
                // Throw an exception if the mode property is invalid
                throw new InvalidIntentConfigurationPropertyException();
        }

        // Continue the session if necessary
        $continueSession = $this->ReadPropertyBoolean('ContinueSession');
        $this->Debug('Continue Session', StringHelper::GetBooleanAsYesNo($continueSession, Locales::EN_US));
        $response->ContinueSession($continueSession);

        // Set the callback intent if necessary
        if ($continueSession) {
            $callback = $this->ReadPropertyBoolean('Callback');
            $this->Debug('Callback', StringHelper::GetBooleanAsYesNo($callback, Locales::EN_US));
            if ($callback) {
                $callbackIntent = $this->ReadPropertyString('CallbackIntentName');
                $this->Debug('Callback Intent', $callbackIntent);
                $response->SetCallbackIntent($callbackIntent);
            }
        }

        // Return the response object
        return $response;
    }

}