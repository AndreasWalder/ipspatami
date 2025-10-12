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


use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\IntentNotFoundException;


/**
 * Interface used to define the required methods to create a new intent object by using the class that provides an intent
 * with the specified name.
 * @package IPSPATAMI
 */
interface IntentInterface
{

    /**
     * Static factory method that creates a new module intent object by using the class that provides an intent with the specified name.
     * @param IntentContainerInterface $container WebHook or WebOAuth I/O module object which uses the intent.
     * @param string $name Intent name.
     * @return Intent Module intent class that provides an intent implementation for the specified intent name.
     * @throws IntentNotFoundException if no module intent class implementing the intent name could be found.
     */
    public static function CreateByName(IntentContainerInterface $container, $name);

}