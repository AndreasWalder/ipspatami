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


require_once(__DIR__ . "/../bootstrap.php");


use Patami\IPS\Services\Alexa\Skills\Custom\WebHookIOModule;


class AlexaSystemInfoDemoSkill extends WebHookIOModule
{

    protected function GetDefaultWebHookSubPath()
    {
        return 'alexa/tutorial/info';
    }

    protected function IsLaunchIntentIdPropertyVisible()
    {
        return false;
    }

    public function GetLaunchIntentClassName()
    {
        return 'Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\Demo\\SystemInfo\\Intents\\GetInformation';
    }

    public function GetIntentClassNames()
    {
        return array(
            'Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\Demo\\SystemInfo\\Intents\\GetInformation',
            'Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\Demo\\SystemInfo\\Intents\\HelpIntent',
            'Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\Intents\\Amazon\\RepeatIntent',
            'Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\Intents\\Amazon\\CancelIntent',
            'Patami\\IPS\\Services\\Alexa\\Skills\\Custom\\Intents\\Amazon\\StopIntent'
        );
    }

    protected function GetNewIssueUrl()
    {
        return 'https://bitbucket.org/patami/ipspatami/issues/new';
    }

    protected function GetLicenseUrl()
    {
        return 'https://docs.braintower.de/display/IPSPATAMI/Lizenz';
    }

    protected function GetDocumentationUrl()
    {
        return 'https://docs.braintower.de/display/IPSPATAMI/System+Information+Custom+Skill';
    }

    protected function GetReleaseNotesUrl()
    {
        return 'https://docs.braintower.de/display/IPSPATAMI/Versionshinweise';
    }

}