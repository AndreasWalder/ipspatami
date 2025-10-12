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
 * Provides date and time helper functions.
 * @package IPSPATAMI
 */
class DateTime
{

    /**
     * Returns a human readable representation of a duration.
     * @param int $duration Number of seconds.
     * @return string Duration text.
     */
    public static function GetDurationAsString($duration)
    {
        // Intervals in seconds
        $intervals = array(
            'year' => 31556926,
            'month' => 2629744,
            'week' => 604800,
            'day' => 86400,
            'hour' => 3600,
            'minute'=> 60
        );

        if ($duration < 60)
        {
            return $duration == 1 ? $duration . ' Sekunde' : $duration . ' Sekunden';
        }

        if ($duration >= 60 && $duration < $intervals['hour'])
        {
            $duration = floor($duration / $intervals['minute']);
            return $duration == 1 ? $duration . ' Minute' : $duration . ' Minuten';
        }

        if ($duration >= $intervals['hour'] && $duration < $intervals['day'])
        {
            $duration = floor($duration / $intervals['hour']);
            return $duration == 1 ? $duration . ' Stunde' : $duration . ' Stunden';
        }

        if ($duration >= $intervals['day'] && $duration < $intervals['week'])
        {
            $duration = floor($duration / $intervals['day']);
            return $duration == 1 ? $duration . ' Tag' : $duration . ' Tagen';
        }

        if ($duration >= $intervals['week'] && $duration < $intervals['month'])
        {
            $duration = floor($duration / $intervals['week']);
            return $duration == 1 ? $duration . ' Woche' : $duration . ' Wochen';
        }
    }

    /**
     * Returns a human readable representation of the time since the given unix timestamp.
     * @param int $timestamp Unix timestamp used to calculate the duration.
     * @return string Duration text.
     */
    public static function GetTimePassedAsString($timestamp)
    {
        // Type cast, current time, difference in timestamps
        $timestamp = (int)$timestamp;
        $current_time = time();
        $duration = $current_time - $timestamp;

        if ($duration == 0)
        {
            return 'gerade eben';
        }

        return 'vor ' . self::GetDurationAsString($duration);
    }

}
