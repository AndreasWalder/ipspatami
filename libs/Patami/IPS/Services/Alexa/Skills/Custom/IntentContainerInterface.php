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


use Patami\IPS\System\Logging\DebugInterface;


/**
 * Interface used to define the required methods to retrieve information required by an intent from a WebHook or
 * WebOAuth I/O module.
 * @package IPSPATAMI
 */
interface IntentContainerInterface extends DebugInterface
{

    /**
     * Returns the IPS object ID of the instance.
     * @return int IPS object ID of the instance.
     */
    public function GetId();

    /**
     * Returns a list of FQCNs of the intent classes used to IntentRequests.
     * For WebHook requests, the user can override them or add new intents with Intent Module instances.
     * @return array FQCNs of the intent classes.
     */
    public function GetIntentClassNames();

    /**
     * Returns the value of an intent property.
     * This method is called by the intent object embedded in the configuration form,
     * @param ModuleIntent $intent Intent object.
     * @param string $name Name of the intent configuration property.
     * @return mixed Value of the property.
     */
    public function ReadIntentProperty(ModuleIntent $intent, $name);

}