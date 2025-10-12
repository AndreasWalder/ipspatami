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


namespace Patami\IPS\Services\Alexa\Skills\Custom\Demo\SystemInfo\SlotTypes;


use Patami\IPS\Services\Alexa\Skills\Custom\SlotType;


class Subject extends SlotType
{

    const OBJECTS               = 'objects';
    const LIBRARIES             = 'libraries';
    const MODULES               = 'modules';
    const INSTANCES             = 'instances';
    const VARIABLES             = 'variables';
    const SCRIPTS               = 'scripts';
    const FUNCTIONS             = 'functions';
    const EVENTS                = 'events';
    const MEDIAS                = 'medias';
    const LINKS                 = 'links';
    const CATEGORIES            = 'categories';

    public static function GetAliasMap()
    {
        return array(
            'de-DE' => array(

                // Objects
                'objekte'       => self::OBJECTS,
                'objekten'      => self::OBJECTS,

                // Libraries
                'bibliothek'    => self::LIBRARIES,
                'bibliotheken'  => self::LIBRARIES,

                // Modules
                'module'        => self::MODULES,
                'modulen'       => self::MODULES,

                // Instances
                'instanzen'     => self::INSTANCES,

                // Variables
                'variablen'     => self::VARIABLES,

                // Scripts
                'skripte'       => self::SCRIPTS,
                'skripten'      => self::SCRIPTS,

                // Functions
                'funktionen'    => self::FUNCTIONS,

                // Events
                'ereignisse'    => self::EVENTS,
                'ereignissen'   => self::EVENTS,

                // Medias
                'medien'        => self::MEDIAS,

                // Links
                'links'         => self::LINKS,

                // Categories
                'kategorien'    => self::CATEGORIES

            )
        );
    }

}