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


use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\SpeechOutputTextTooLongException;


/**
 * Abstract base class for plain text and SSML speech output sent as response or reprompt to the Amazon servers.
 * @package IPSPATAMI
 */
abstract class SpeechOutput
{

    /** Plain text speech output. */
    const TYPE_PLAIN_TEXT = 'PlainText';

    /** SSML speech output. */
    const TYPE_SSML = 'SSML';

    /** @var string Speech output plain text or SSML. */
    protected $text;

    /**
     * SpeechOutput constructor.
     * @param string $text Speech output text.
     */
    public function __construct($text)
    {
        // Remember the speech output text
        $this->SetText($text);
    }

    /**
     * Static factory method to create a new instance of the concrete speech output class.
     * @param string $text Speech output text.
     * @return $this
     */
    public static function Create($text)
    {
        // Get the called class
        $className = get_called_class();

        // Create and return a new instance of the class
        return new $className($text);
    }

    /**
     * Returns the speech output text.
     * @return string Speech output text.
     */
    public function GetText()
    {
        // Return the speech output text
        return $this->text;
    }

    /**
     * Sets the speech output text.
     * @param string $text Speech output text.
     * @throws SpeechOutputTextTooLongException if the text is too long (more than 8000 characters).
     */
    public function SetText($text)
    {
        // Throw an exception if the supplied speech output is too long
        if (strlen($text) > 8000) {
            throw new SpeechOutputTextTooLongException();
        }

        // Remember the speech output text
        $this->text = $text;
    }

    /**
     * Returns the type of the speech output.
     * @return string Speech output type.
     * @see SpeechOutput::TYPE_PLAIN_TEXT
     * @see SpeechOutput::TYPE_SSML
     */
    public function GetType()
    {
        // Return the type of response
        return $this->GetData()['type'];
    }

    /**
     * Returns the speech output data used as a part of the response sent to the Amazon servers.
     * This method need to be overridden by the concrete child class.
     * @return array Speech output data.
     */
    abstract public function GetData();

}