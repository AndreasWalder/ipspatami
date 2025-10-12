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


namespace Patami\Exceptions;


use Patami\IPS\System\Locales;


/**
 * Abstract base class for all Patami Framework exceptions.
 * @package IPSPATAMI
 */
class Exception extends \Exception
{

    /**
     * @var array Translated error message strings.
     */
    protected $customMessages = array();

    /**
     * Returns a translated error message.
     * @param string|null $locale Locale code or null to use the default IPS locale.
     * @return string Translated error message for the given locale.
     * @see Locales for a list of valid locale codes.
     */
    public function GetCustomMessage($locale = null)
    {
        // Get the system locale if necessary
        if (is_null($locale)) {
            $locale = Locales::GetIPSLocale();
        }

        // Format and return the error message
        return sprintf(@$this->customMessages[$locale], $this->getMessage(), $this->getCode());
    }

}