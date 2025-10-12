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


namespace Patami\IPS\Services\Alexa\Skills\Custom\Intents\Exceptions;


use Patami\IPS\Services\Alexa\Skills\Exceptions\DriverInternalException;


/**
 * Exception used to indicate that the action script configured in the intent configuration could not be loaded.
 * @package IPSPATAMI
 */
class ActionScriptNotLoadedException extends DriverInternalException
{

    protected $customMessages = array(
        'de-DE' => 'Der angeforderte Skill konnte nicht ausgeführt werden weil das Skript nicht geladen werden konnte.',
        'en-US' => 'The requested skill could not be executed because the script could not be loaded.',
        'en-GB' => 'The requested skill could not be executed because the script could not be loaded.',
    );

}