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


use Patami\IPS\Services\Alexa\Exceptions\Exception;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\CardTextTooLongException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidCallbackIntentException;
use Patami\IPS\IO\ResponseInterface;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\SpeechOutputTextTooLongException;


/**
 * Class for responses to Amazon Alexa Custom Skill requests.
 *
 * You should use the TellResponse child class to speak text to the user without continuing the user's active session.
 * In order to ask questions and continue the session to wait for the user's response after speaking to him, use the
 * AskResponse class.
 *
 * @see TellResponse
 * @see AskResponse
 *
 * @package IPSPATAMI
 */
class Response implements ResponseInterface
{

    /** @var SpeechOutput Object for the speech output. */
    protected $speechOutput = null;

    /** @var SpeechOutput Object for the reprompt speech output. */
    protected $repromptSpeechOutput = null;

    /** @var Card Object for the card that should be display in the Alexa App. */
    protected $card = null;

    /** @var string|null Name of the callback intent that should be called upon subsequent requests. */
    protected $callbackIntent = null;

    /**
     * @var bool True if the session should be terminated after sending the response.
     * The session is terminated by default.
     */
    protected $shouldEndSession = true;

    /**
     * @var Request Request object used to remember the intent slots and session attributes of the current request.
     */
    protected $request = null;
	
	  /** @var array[] Alexa directives (APL, VideoApp, AudioPlayer, Hint, …) */
    protected $directives = array();

    /**
     * Static factory method to create an empty Response object.
     * @return Response New empty response object.
     */
    public static function Create()
    {
        // Get the called class
        /** @var Response $className */
        $className = get_called_class();

        // Create the object
        /** @var Response $response */
        $response = new $className();

        // Return the object
        return $response;
    }

    /**
     * Static factory method to create a Response object from a SpeechOutput object.
     * @param SpeechOutput $speechOutput Speech output text object.
     * @return Response New response object with speech output.
     */
    public static function CreateSpeechOutput(SpeechOutput $speechOutput)
    {
        // Get the called class
        /** @var Response $className */
        $className = get_called_class();

        // Create the object
        /** @var Response $response */
        $response = $className::Create();

        // Set the SpeechOutput object
        $response->SetSpeechOutput($speechOutput);

        // Return the object
        return $response;
    }

    /**
     * Static factory method to create a Response object with plain text speech output from a string.
     * @param string $text Plain text speech output.
     * @return Response New response object with speech output.
     * @throws SpeechOutputTextTooLongException if the text is too long (more than 8000 characters).
     */
    public static function CreatePlainText($text)
    {
        // Get the called class
        /** @var Response $className */
        $className = get_called_class();

        // Create the object
        /** @var Response $response */
        $response = $className::Create();

        // Set the SpeechOutput object
        $response->SetPlainText($text);

        // Return the object
        return $response;
    }

    /**
     * Static factory method to create a Response object with SSML speech output from a string.
     * @param string $text SSML speech output.
     * @return Response New response object with speech output.
     * @throws SpeechOutputTextTooLongException if the text is too long (more than 8000 characters).
     */
    public static function CreateSSML($text)
    {
        // Get the called class
        /** @var Response $className */
        $className = get_called_class();

        // Create the object
        /** @var Response $response */
        $response = $className::Create();

        // Set the SpeechOutput object
        $response->SetSSML($text);

        // Return the object
        return $response;
    }

    /**
     * Static factory method to create a Response object from an Exception object.
     * This method is called by the I/O when an exception is thrown while processing the request.
     * @param \Exception $e Thrown exception.
     * @param string $locale Locale to be used when translating the exception message.
     * @return Response New response object with speech output of the Exception message.
     * @internal
     */
    public static function CreateFromException(\Exception $e, $locale)
    {
        $formatStrings = array(
            'de-DE' => 'Der angeforderte Skill konnte nicht ausgeführt werden da eine unerwartete Ausnahme vom Typ %s aufgetreten ist.',
            'en-US' => 'The requested skill could not be executed because An unexpected %s exception occurred.',
            'en-GB' => 'The requested skill could not be executed because An unexpected %s exception occurred.',
        );

        if ($e instanceof Exception) {
            $text = $e->GetCustomMessage($locale);
        } else {
            $text = $e->getMessage();
            if ($text == '') {
                $text = sprintf($formatStrings[$locale], get_class($e));
            }
        }

        // Get the called class
        /** @var Response $className */
        $className = get_called_class();

        // Create and return the object
        return $className::CreatePlainText($text);
    }

    /**
     * Updates the speech output of the response with a new SpeechOutput object.
     * @param SpeechOutput $speechOutput Speech output text object.
     * @return $this Fluent interface.
     */
    public function SetSpeechOutput(SpeechOutput $speechOutput)
    {
        $this->speechOutput = $speechOutput;
        return $this;
    }

    /**
     * Updates the speech output of the response with plain text speech output from a string.
     * @param string $text Plain text speech output.
     * @return $this Fluent interface.
     */
    public function SetPlainText($text)
    {
        $this->SetSpeechOutput(PlainTextSpeechOutput::Create($text));
        return $this;
    }

    /**
     * Updates the speech output of the response with SSML speech output from a string.
     * @param string $text SSML speech output.
     * @return $this Fluent interface.
     */
    public function SetSSML($text)
    {
        $this->SetSpeechOutput(SSMLSpeechOutput::Create($text));
        return $this;
    }

    /**
     * Clears the speech output (= no speech output).
     * @return $this Fluent interface.
     */
    public function ClearSpeechOutput()
    {
        $this->speechOutput = null;
        return $this;
    }

    /**
     * Returns the speech output object.
     * @return SpeechOutput|null Speech output object or null if no speech output was set.
     */
    public function GetSpeechOutput()
    {
        return $this->speechOutput;
    }

    /**
     * Updates the reprompt speech output of the response with a new SpeechOutput object.
     * @param SpeechOutput $speechOutput Reprompt speech output text object.
     * @return $this Fluent interface.
     */
    public function SetRepromptSpeechOutput(SpeechOutput $speechOutput)
    {
        $this->repromptSpeechOutput = $speechOutput;
        return $this;
    }

    /**
     * Updates the reprompt speech output of the response with plain text speech output from a string.
     * @param string $text Reprompt plain text speech output.
     * @return $this Fluent interface.
     */
    public function SetRepromptPlainText($text)
    {
        $this->SetRepromptSpeechOutput(PlainTextSpeechOutput::Create($text));
        return $this;
    }

    /**
     * Updates the reprompt speech output of the response with SSML speech output from a string.
     * @param string $text Reprompt SML speech output.
     * @return $this Fluent interface.
     */
    public function SetRepromptSSML($text)
    {
        $this->SetRepromptSpeechOutput(SSMLSpeechOutput::Create($text));
        return $this;
    }

    /**
     * Clears the reprompt speech output (= no speech output).
     * @return $this Fluent interface.
     */
    public function ClearRepromptSpeechOutput()
    {
        $this->repromptSpeechOutput = null;
        return $this;
    }

    /**
     * Returns the reprompt speech output object.
     * @return SpeechOutput|null Reprompt speech output object or null if no reprompt speech output was set.
     */
    public function GetRepromptSpeechOutput()
    {
        return $this->repromptSpeechOutput;
    }

    /**
     * Updates the card that is displayed in the Alex App with a Card object.
     * @param Card $card Card object to be displayed in the Alexa App.
     * @return $this Fluent interface.
     * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/providing-home-cards-for-the-amazon-alexa-app
     */
    public function SetCard(Card $card)
    {
        $this->card = $card;
        return $this;
    }
	
	
	/**
     * Adds a directive to the response (e.g. APL RenderDocument).
     * @param array $directive Alexa directive as associative array.
     * @return $this Fluent interface.
     */
    public function AddDirective(array $directive)
    {
        $this->directives[] = $directive;
        return $this;
    }

    /**
     * Clears all directives.
     * @return $this Fluent interface.
     */
    public function ClearDirectives()
    {
        $this->directives = array();
        return $this;
    }
	
	
	/**
     * Convenience: add an APL RenderDocument directive.
     * @param array $document  APL document
     * @param array $datasources APL datasources
     * @param string $token ASCII token (no umlauts/spaces)
     * @return $this
     */
    public function AddAPLRenderDocument(array $document, array $datasources = array(), $token = 'doc1')
    {
        return $this->AddDirective(array(
            'type'       => 'Alexa.Presentation.APL.RenderDocument',
            'token'      => $token,
            'document'   => $document,
            'datasources'=> $datasources
        ));
    }

    /**
     * Updates the card that is displayed in the Alex App with a SimpleCard object created from title and content texts.
     * A simple card is also called a "Basic Home Card".
     * @param string $title Title of the card.
     * @param string $content Text of the card. You can use \r or \r\n to insert line breaks.
     * @return $this Fluent interface.
     * @throws CardTextTooLongException if the text is too long (more than 8000 characters).
     * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/providing-home-cards-for-the-amazon-alexa-app#creating-a-basic-home-card-to-display-text
     */
    public function SetSimpleCard($title, $content)
    {
        $this->SetCard(SimpleCard::Create($title, $content));
        return $this;
    }

    /**
     * Updates the card that is displayed in the Alex App with a StandardCard object created from title and content texts and image URLs.
     * A standard card is also called a "Home Card".
     * @param string $title Title of the card.
     * @param string $text Text of the card. You can use \r or \r\n to insert line breaks.
     * @param string|null $smallImageUrl URL of a small image (recommended size: 720x480) or null for no image.
     * @param string|null $largeImageUrl URL of a large image (recommended size: 1200x800) or null for no image.
     * @return $this Fluent interface.
     * @throws CardTextTooLongException if the text is too long (more than 8000 characters).
     * TODO: Implement Media objects that return the desired URLs.
     */
    public function SetStandardCard($title, $text, $smallImageUrl = null, $largeImageUrl = null)
    {
        $this->SetCard(StandardCard::Create($title, $text, $smallImageUrl, $largeImageUrl));
        return $this;
    }

    /**
     * Clears the card (= no card will be displayed in the Alexa App).
     * @return $this Fluent interface.
     */
    public function ClearCard()
    {
        $this->card = null;
        return $this;
    }

    /**
     * Returns the Card object to be displayed in the Alexa App.
     * @return Card|null Card object to be displayed or null if no card will be displayed.
     */
    public function GetCard()
    {
        return $this->card;
    }

    /**
     * Sets the callback intent that should be called by an intent in a subsequent request in the user's active session.
     * @param Intent|string $intent Intent object or intent name to be called in the subsequent request.
     * @return $this Fluent interface.
     * @throws InvalidCallbackIntentException if the type of the $intent parameter is invalid.
     */
    public function SetCallbackIntent($intent)
    {
        if ($intent instanceof ModuleIntent) {
            // Remember the intent name
            $this->callbackIntent = $intent->GetName();
        } elseif (is_string($intent)) {
            // Remember the intent name
            $this->callbackIntent = $intent;
        } else {
            // Throw an exception if the given parameter is invalid
            throw new InvalidCallbackIntentException();
        }
        return $this;
    }

    /**
     * Clears the callback intent.
     * @return $this Fluent interface.
     */
    public function ClearCallbackIntent()
    {
        $this->callbackIntent = null;
        return $this;
    }

    /**
     * Returns the name of the callback intent.
     * @return string|null Name of the callback intent or null if none is set.
     */
    public function GetCallbackIntent()
    {
        return $this->callbackIntent;
    }

    /**
     * Sets a flag to continue or terminate the user's active session after the current request.
     * @param bool $continue True to continue the session or false to terminate it.
     * @return $this Fluent interface.
     */
    public function ContinueSession($continue = true)
    {
        if ($continue) {
            $this->shouldEndSession = false;
        } else {
            $this->shouldEndSession = true;
            $this->request = null;
        }

        return $this;
    }

    /**
     * Sets a flag to terminate the user's active session after the current request.
     * @return $this Fluent interface.
     */
    public function EndSession()
    {
        return $this->ContinueSession(false);
    }

    /**
     * Sets the request object that is used to send the current intent slots and session attributes back to the Amazon servers.
     * This method is called internally by the request and should not be called directly.
     * @param Request $request Request object of the current request.
     * @internal
     */
    public function SetRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Returns the response as an array data structure required by the Amazon servers to process the response.
     * It contains some (optional) elements such as the speech output, the reprompt speech output, a card that will be
     * displayed in the Alexa app, session data, the intent slots passed to the current request and so on.
     * @return array Alexa Custom Skill response data structure.
     * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/alexa-skills-kit-interface-reference#response-format
     * @internal
     */
    public function GetData()
    {
        // Initialize the data structure
        $data = array();

        // Add speech output if set
        if (! is_null($this->speechOutput)) {
            $data['outputSpeech'] = $this->speechOutput->GetData();
        }

        // Set the flag whether to end the session
        $shouldEndSession = is_null($this->request) || $this->shouldEndSession;
        $data['shouldEndSession'] = $shouldEndSession;

        // Set the session attributes
        if ($shouldEndSession) {
            $sessionAttributes = array();
        } else {
            $sessionAttributes = array(
                'slots' => $this->request->slots->Get(),
                'attributes' => $this->request->attributes->Get(),
                'callbackIntent' => is_null($this->callbackIntent)? $this->request->GetIntentName(): $this->callbackIntent
            );
        }

        // Add reprompt speech output if set and the session should not end
        if (! is_null($this->repromptSpeechOutput) && ! $shouldEndSession) {
            $data['reprompt']['outputSpeech'] = $this->repromptSpeechOutput->GetData();
        }

        // Add card output if set
        if (! is_null($this->card)) {
            $data['card'] = $this->card->GetData();
        }
		
		// Add directives if any
        if (!empty($this->directives)) {
            $data['directives'] = $this->directives;
        }

        // Create the response data structure
        $data = array(
            'version' => '1.0',
            'response' => $data,
            'sessionAttributes' => $sessionAttributes
        );

        // Return the data structure
        return $data;
    }

    /**
     * Returns the response as a JSON-encoded data structure.
     * This method is called by the I/O module after the request has been processed.
     * @return string JSON-encoded Alexa Custom Skill response data structure.
     * @see Response::GetData()
     */
    public function GetText()
    {
        // JSON encode the data and return it
        return json_encode($this->GetData());
    }

    /**
     * Returns the MIME content type of the response send to the Amazon servers.
     * @return string MIME content type.
     * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/alexa-skills-kit-interface-reference#http-header-1
     */
    public function GetContentType()
    {
        return 'application/json;charset=UTF-8';
    }

}