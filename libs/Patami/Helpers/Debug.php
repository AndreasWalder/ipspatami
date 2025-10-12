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


namespace Patami\Helpers;


/**
 * Provides debugging helper functions.
 * @package IPSPATAMI
 */
class Debug
{

    /**
     * Returns a string that contains the PHP call trace.
     * The output of this method is typically used in error messages to help developers track down the reason of an error.
     * @param int $numStrip Number of entries to strip from the trace.
     * @return string Formatted PHP call trace.
     */
    public static function GetCallTraceAsString($numStrip = 1)
    {
        $result = '';

        // Get the call trace
        $callTrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);

        // Remove the first item (as this is this method)
        array_shift($callTrace);

        // Remove the given number of entries
        for ($index = 0; $index < $numStrip; $index++) {
            array_shift($callTrace);
        }

        // Loop through the entries
        $index = 0;
        foreach ($callTrace as $entry) {
            // Get the elements
            $function = @$entry['function'];
            $line = @$entry['line'];
            $file = @$entry['file'];
            $class = @$entry['class'];
            $object = @$entry['object'];
            $type = @$entry['type'];
            $args = @$entry['args'];
            // Generate the string
            $text = sprintf(
                '#%d [%s:%d] %s%s%s()',
                $index,
                $file,
                $line,
                $class,
                $type,
                $function
            );
            if (!is_null($args)) {
                foreach ($args as $arg) {
                    $text .= "\n- " . var_export($arg, true);
                }
            }
            if (!is_null($object)) {
                $text .= "\n" . var_export($object, true);
            }
            $result .= $text . "\n";
            $index++;
        }

        // Return the result
        return trim($result);
    }

}
