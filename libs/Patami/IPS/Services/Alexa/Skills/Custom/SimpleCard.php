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


use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\CardTextTooLongException;


/**
 * Class for simple cards to be displayed in the Alexa App.
 *
 * Simple cards can contain a title and a content text.
 *
 * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/providing-home-cards-for-the-amazon-alexa-app#creating-a-basic-home-card-to-display-text
 *
 * @package IPSPATAMI
 */
class SimpleCard extends TitleCard
{

    /** @var string Text to be displayed on the card. */
    protected $content;

    /**
     * SimpleCard constructor.
     * @param string $title Title of the card.
     * @param string $content Text of the card. You can use \r or \r\n to insert line breaks.
     * @throws CardTextTooLongException if the title or the text is too long (more than 8000 characters).
     */
    public function __construct($title, $content)
    {
        // Remember the title and the content
        $this->SetTitle($title);
        $this->SetContent($content);
    }

    /**
     * Static factory method to create a new instance of the class.
     * @return $this New instance.
     * @throws CardTextTooLongException if the title or the text is too long (more than 8000 characters).
     */
    public static function Create($title, $content)
    {
        // Get the name of the called class
        $className = get_called_class();

        // Create and return a new instance of the class
        return new $className($title, $content);
    }

    /**
     * Returns the content text of the card.
     * @return string Content text of the card.
     */
    public function GetContent()
    {
        // Return the card content text
        return $this->content;
    }

    /**
     * Sets the content text of the card.
     * @param string $content Text of the card. You can use \r or \r\n to insert line breaks.
     * @return $this Fluent interface.
     * @throws CardTextTooLongException if the text is too long (more than 8000 characters).
     */
    public function SetContent($content)
    {
        // Throw an exception if the card text is too long
        if (strlen($content) > 8000) {
            throw new CardTextTooLongException();
        }

        // Remember the card text
        $this->content = $content;

        // Enable fluent interface
        return $this;
    }

    public function GetData()
    {
        return array(
            'type' => 'Simple',
            'title' => $this->title,
            'content' => $this->content
        );
    }

}