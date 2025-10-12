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


/**
 * Abstract base class for cards to be displayed in the Alexa App.
 * @package IPSPATAMI
 */
abstract class Card
{

    /**
     * Simple card (Basic Home Card).
     * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/providing-home-cards-for-the-amazon-alexa-app#creating-a-basic-home-card-to-display-text
     */
    const TYPE_SIMPLE = 'Simple';

    /**
     * Standard card (Home Card).
     * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/providing-home-cards-for-the-amazon-alexa-app#creating-a-home-card-to-display-text-and-an-image
     */
    const TYPE_STANDARD = 'Standard';

    /**
     * Account linking card.
     * @link https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/providing-home-cards-for-the-amazon-alexa-app#defining-a-card-for-use-with-account-linking
     */
    const TYPE_LINK_ACCOUNT = 'LinkAccount';

    /**
     * Returns the type of the card.
     * @return string Card type.
     * @see Card::TYPE_SIMPLE
     * @see Card::TYPE_STANDARD
     * @see Card::TYPE_LINK_ACCOUNT
     */
    public function GetType()
    {
        return $this->GetData()['type'];
    }

    /**
     * Returns the data structure of the card to be included in the Response object.
     * @return array Card data structure.
     */
    abstract public function GetData();

}