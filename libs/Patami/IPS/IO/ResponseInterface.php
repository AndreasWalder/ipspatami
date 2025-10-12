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


namespace Patami\IPS\IO;


/**
 * Interface for the required methods to retrieve information from a response object in order to send a response
 * to the remote client.
 * @package IPSPATAMI
 */
interface ResponseInterface
{

    /**
     * Returns the data of the response.
     * The method may return any data type.
     * @return mixed Response data.
     */
    public function GetData();

    /**
     * Returns the text of the response.
     * The method should typically return the data returned by ResponseInterface::GetData() as a JSON-encoded string.
     * @return string Response text.
     * @see ResponseInterface::GetData()
     */
    public function GetText();

    /**
     * Returns the MIME content type of the response.
     * @return string MIME type of the response.
     * @see ResponseInterface::GetText()
     */
    public function GetContentType();

}