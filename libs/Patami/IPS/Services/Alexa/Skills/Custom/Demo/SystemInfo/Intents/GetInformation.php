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


namespace Patami\IPS\Services\Alexa\Skills\Custom\Demo\SystemInfo\Intents;


use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\SlotValueNotSetException;
use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\SlotValueNotSupportedException;
use Patami\IPS\Services\Alexa\Skills\Custom\ModuleIntent;
use Patami\IPS\Services\Alexa\Skills\Custom\Request;
use Patami\IPS\Services\Alexa\Skills\Custom\Demo\SystemInfo\SlotTypes\Subject;
use Patami\IPS\Services\Alexa\Skills\Custom\AskResponse;
use Patami\IPS\Services\Alexa\Skills\Custom\TellResponse;
use Patami\IPS\System\IPS;


class GetInformation extends ModuleIntent
{

    protected function DoExecute(Request $request)
    {
        try {
            $subject = Subject::GetKey($request, 'subject');
            switch ($subject) {
                case Subject::OBJECTS:
                    $text = sprintf('Es sind %d Objekte vorhanden.', IPS::GetObjectCount());
                    break;
                case Subject::LIBRARIES:
                    $text = sprintf('Es sind %d Bibliotheken vorhanden.', IPS::GetVariableCount());
                    break;
                case Subject::MODULES:
                    $text = sprintf('Es sind %d Module vorhanden.', IPS::GetModuleCount());
                    break;
                case Subject::INSTANCES:
                    $text = sprintf('Es sind %d Instanzen vorhanden.', IPS::GetInstanceCount());
                    break;
                case Subject::VARIABLES:
                    $text = sprintf('Es sind %d Variablen vorhanden.', IPS::GetVariableCount());
                    break;
                case Subject::SCRIPTS:
                    $text = sprintf('Es sind %d Skripte vorhanden.', IPS::GetScriptCount());
                    break;
                case Subject::FUNCTIONS:
                    $text = sprintf('Es sind %d Funktionen vorhanden.', IPS::GetFunctionCount(0));
                    break;
                case Subject::EVENTS:
                    $text = sprintf('Es sind %d Ereignisse vorhanden.', IPS::GetEventCount());
                    break;
                case Subject::MEDIAS:
                    $text = sprintf('Es sind %d Medien vorhanden.', IPS::GetMediaCount());
                    break;
                case Subject::LINKS:
                    $text = sprintf('Es sind %d Links vorhanden.', IPS::GetLinkCount());
                    break;
                case Subject::CATEGORIES:
                    $text = sprintf('Es sind %d Kategorien vorhanden.', IPS::GetCategoryCount());
                    break;
            }
            /** @noinspection PhpUndefinedVariableInspection */
            return TellResponse::CreatePlainText(
                $text
            )->SetSimpleCard(
                'IP-Symcon Informationen',
                $text
            );
        } catch (SlotValueNotSupportedException $e) {
            return AskResponse::CreatePlainText(
                'Diese Objektart kenne ich nicht. Was meintest Du?'
            )->SetRepromptPlainText(
                'Wie bitte?'
            );
        } catch (SlotValueNotSetException $e) {
            return AskResponse::CreatePlainText(
                'Ich kann Dir sagen wie viele Objekte unterschiedlicher Typen im System vorhanden sind. ' .
                'Zu welcher Art von Objekt willst Du Informationen haben?'
            )->SetRepromptPlainText(
                'Wie bitte?'
            );
        }
    }

}