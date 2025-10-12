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


namespace Patami\IPS\Objects\Exceptions;


use Patami\IPS\Objects\VariableProfile;


/**
 * Exception used to indicate that a property of the IPS variable profile could not be set.
 * @package IPSPATAMI
 * @see VariableProfile
 */
class VariableProfileSetFailedException extends VariableProfileException
{

    protected $customMessages = array(
        'de-DE' => 'Eine Eigenschaft des Variablenprofils kann nicht gesetzt werden.',
        'en-US' => 'A variable profile property cannot be set.',
        'en-GB' => 'A variable profile property cannot be set.',
    );

}