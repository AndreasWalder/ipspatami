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


use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\CardImageUrlNotSetException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\CardImageUrlTooLongException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\CardTextTooLongException;


/**
 * Class for standard cards to be displayed in the Alexa App.
 *
 * Standard cards can contain a title and a content text and an image.
 *
 * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/providing-home-cards-for-the-amazon-alexa-app#creating-a-basic-home-card-to-display-text
 *
 * @package IPSPATAMI
 */
class StandardCard extends TitleCard
{

    /** @var string Text to be displayed on the card. */
    protected $text;

    /** @var string URL of a small image (recommended size: 720x480) or null for no image. */
    protected $smallImageUrl;

    /** @var string URL of a large image (recommended size: 1200x800) or null for no image. */
    protected $largeImageUrl;

    /**
     * StandardCard constructor.
     * @param string $title Title of the card.
     * @param string $text Text of the card. You can use \r or \r\n to insert line breaks.
     * @param string|null $smallImageUrl URL of a small image (recommended size: 720x480) or null for no image.
     * @param string|null $largeImageUrl URL of a large image (recommended size: 1200x800) or null for no image.
     * @throws CardTextTooLongException if the title or the text is too long (more than 8000 characters).
     * @throws CardImageUrlTooLongException if the URL is too long (more than 8000 characters).
     */
    public function __construct($title, $text, $smallImageUrl = null, $largeImageUrl = null)
    {
        // Remember the attributes
        $this->SetTitle($title);
        $this->SetText($text);
        $this->SetSmallImageUrl($smallImageUrl);
        $this->SetLargeImageUrl($largeImageUrl);
    }

    /**
     * Static factory method to create a new instance of the card class.
     * @param string $title Title of the card.
     * @param string $text Text of the card. You can use \r or \r\n to insert line breaks.
     * @param string|null $smallImageUrl URL of a small image (recommended size: 720x480) or null for no image.
     * @param string|null $largeImageUrl URL of a large image (recommended size: 1200x800) or null for no image.
     * @return $this New card instance.
     * @throws CardTextTooLongException if the title or the text is too long (more than 8000 characters).
     * @throws CardImageUrlTooLongException if the URL is too long (more than 8000 characters).
     */
    public static function Create($title, $text, $smallImageUrl = null, $largeImageUrl = null)
    {
        // Get the called class
        $className = get_called_class();

        // Create and return a new instance of the card class
        return new $className($title, $text, $smallImageUrl, $largeImageUrl);
    }

    /**
     * Returns the text of the card.
     * @return string Text of the card.
     */
    public function GetText()
    {
        // Return the text of the card
        return $this->text;
    }

    /**
     * Sets the content text of the card.
     * @param string $text Text of the card. You can use \r or \r\n to insert line breaks.
     * @return $this Fluent interface.
     * @throws CardTextTooLongException if the text is too long (more than 8000 characters).
     */
    public function SetText($text)
    {
        // Throw an exception if the card text is too long
        if (strlen($text) > 8000) {
            throw new CardTextTooLongException();
        }

        $this->text = $text;

        return $this;
    }

    /**
     * Returns the URL of the small card image.
     * @return string|null URL of the small card image or null if none is set.
     */
    public function GetSmallImageUrl()
    {
        // Return the URL
        return $this->smallImageUrl;
    }

    /**
     * Sets the URL of the small card image.
     * @param string $url URL of the small card image.
     * @return $this Fluent interface
     * @throws CardImageUrlTooLongException if the URL is too long (more than 8000 characters).
     */
    public function SetSmallImageUrl($url)
    {
        // Throw an exception if the URL is too long
        if (strlen($url) > 8000) {
            throw new CardImageUrlTooLongException();
        }

        // Remember the URL
        $this->smallImageUrl = $url;

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the URL of the large card image.
     * @return string|null URL of the large card image or null if none is set.
     */
    public function GetLargeImageUrl()
    {
        // Return the URL
        return $this->largeImageUrl;
    }

    /**
     * Sets the URL of the large card image.
     * @param string $url URL of the large card image.
     * @return $this Fluent interface
     * @throws CardImageUrlTooLongException if the URL is too long (more than 8000 characters).
     */
    public function SetLargeImageUrl($url)
    {
        // Throw an exception if the URL is too long
        if (strlen($url) > 8000) {
            throw new CardImageUrlTooLongException();
        }

        // Remember the URL
        $this->largeImageUrl = $url;

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the data structure of the card to be included in the Response object.
     * @return array Card data structure.
     * @throws CardImageUrlNotSetException if both the small and the large URL is not set.
     */
    public function GetData()
    {
        $data = array(
            'type' => 'Standard',
            'title' => $this->title,
            'text' => $this->text
        );

        if (! $this->smallImageUrl && ! $this->largeImageUrl) {
            throw new CardImageUrlNotSetException();
        }

        if ($this->smallImageUrl) {
            $data['image']['smallImageUrl'] = $this->smallImageUrl;
        }
        if ($this->largeImageUrl) {
            $data['image']['largeImageUrl'] = $this->largeImageUrl;
        }

        return $data;
    }

}