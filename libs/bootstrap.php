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


/**
 * Initializes the PHP autoloader and the Patami framework and registers IPS object drivers.
 */

// Load IPS defines
@require_once(__DIR__ . '/ipsdefines.php');

// Initialize the auto loader
@require_once(__DIR__ . '/Patami/AutoLoader.php');
\Patami\AutoLoader::Register(__DIR__);

// Bootstrap the framework
\Patami\IPS\Framework::Bootstrap();

// Add the drivers
\Patami\IPS\Objects\Drivers\Drivers::AddDriverClassNames(array(
    'Patami\\IPS\\Objects\\Drivers\\Generic\\SwitchVariableDriver',
    'Patami\\IPS\\Objects\\Drivers\\Generic\\VariableNumberDriver',
    'Patami\\IPS\\Objects\\Drivers\\Generic\\TemperatureVariableDriver',
    'Patami\\IPS\\Objects\\Drivers\\Generic\\ScriptSetSwitchDriver',
    'Patami\\IPS\\Objects\\Drivers\\KNX\\KNXSwitchDriver',
    'Patami\\IPS\\Objects\\Drivers\\KNX\\KNXDimmerDriver',
    'Patami\\IPS\\Objects\\Drivers\\KNX\\KNXGetTemperatureDriver',
    'Patami\\IPS\\Objects\\Drivers\\KNX\\KNXFloatValueDriver',
    'Patami\\IPS\\Objects\\Drivers\\HomeMatic\\HomeMaticSwitchDriver',
    'Patami\\IPS\\Objects\\Drivers\\HomeMatic\\HomeMaticDimmerDriver',
    'Patami\\IPS\\Objects\\Drivers\\Philips\\Hue\\PhilipsHueColorDriver',
    'Patami\\IPS\\Objects\\Drivers\\Philips\\Hue\\PhilipsHueColorTemperatureDriver',
    'Patami\\IPS\\Objects\\Drivers\\Patami\\ObjectGroup\\PatamiObjectGroupStatusDriver',
    'Patami\\IPS\\Objects\\Drivers\\Patami\\ObjectGroup\\PatamiObjectGroupSceneSwitchDriver',
    'Patami\\IPS\\Objects\\Drivers\\Patami\\ObjectGroup\\PatamiObjectGroupSceneDriver',
    'Patami\\IPS\\Objects\\Drivers\\Patami\\SplitRGB\\PatamiSplitRGBDriver',
));
