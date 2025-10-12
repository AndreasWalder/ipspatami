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
 * Abstract base class for simple and standard cards to be displayed in the Alexa App.
 *
 * @package IPSPATAMI
 */
abstract class TitleCard extends Card
{

    /** @var string Title to be displayed on the card. */
    protected $title;

    /**
     * Returns the title text of the card.
     * @return string Title text of the card.
     */
    public function GetTitle()
    {
        // Return the title text
        return $this->title;
    }

    /**
     * Sets the title text of the card.
     * @param string $title Title of the card.
     * @return $this Fluent interface.
     * @throws CardTextTooLongException if the text is too long (more than 8000 characters).
     */
    public function SetTitle($title)
    {
        // Throw an exception if the title is too long
        if (strlen($title) > 8000) {
            throw new CardTextTooLongException();
        }

        // Remember the title
        $this->title = $title;

        // Enable fluent interface
        return $this;
    }

}