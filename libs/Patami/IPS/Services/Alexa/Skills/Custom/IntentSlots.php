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


use Patami\IPS\Services\Alexa\Skills\Custom\Exceptions\InvalidIntentSlotsException;


/**
 * Class to handle intent slots in Alexa Custom Skill requests and responses.
 *
 * It offers attribute access ($data->key) and array access ($data['key']) as well as an iterator interface (eg. to
 * use foreach on the object to process all key-value pairs).
 *
 * @package IPSPATAMI
 */
class IntentSlots extends RequestData
{

    /**
     * IntentSlots constructor.
     * The intent slot data is taken from the session data and then overwritten with the slots from the request.
     * This makes sure that slots set by previous requests are not lost in subsequent requests, still making sure new
     * slot values have precedence if they are both specified in the request and the session data.
     * @param array $slots Intent slots from the Alexa request.
     * @param array $sessionSlots Intent slots from the session data of the Alexa request.
     * @throws InvalidIntentSlotsException if the intent slot data is invalid.
     */
    public function __construct(array $slots = [], array $sessionSlots = [])
    {
        // 1) Session-Slots in die Basisklasse laden
        parent::__construct($sessionSlots);
    
        // 2) Sicherstellen, dass data existiert
        if (!is_array($this->data)) {
            $this->data = [];
        }
    
        // 3) Default für callbackIntent setzen (verhindert Undefined-Array-Key später)
        if (!array_key_exists('callbackIntent', $this->data)) {
            $this->data['callbackIntent'] = null;
        }
    
        // 4) Eingehende Slots sauber mergen (ohne @, mit Fallbacks)
        foreach ($slots as $slot) {
            if (!is_array($slot)) {
                // tolerante Weiterverarbeitung statt Exception
                continue;
            }
    
            $slotName  = $slot['name']  ?? null;
            if ($slotName === null || $slotName === '') {
                throw new InvalidIntentSlotsException('Missing slot name');
            }
    
            $slotValue = $slot['value'] ?? null;
    
            // Optional: 'CallbackIntent' (andere Schreibweisen) normalisieren
            if (strcasecmp($slotName, 'callbackIntent') === 0) {
                $slotName = 'callbackIntent';
            }
    
            $this->data[$slotName] = $slotValue;
        }
    }

}
