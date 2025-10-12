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
 * Class for ask responses to Amazon Alexa Custom Skill requests.
 *
 * Ask responses say something (typically a question) to the user via the Alexa device and continue the session, so the
 * user can answer to what Alexa said. This is normally used to collect more information.
 *
 * If you want to terminate the session though, you can call the AskResponse::EndSession() method.
 *
 * @package IPSPATAMI
 */
class AskResponse extends Response
{

    /**
     * @var bool True if the session should be terminated after sending the response.
     * The default is overridden to make sure the session is continued by default.
     */
    protected $shouldEndSession = false;

}