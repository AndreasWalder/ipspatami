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


namespace Patami\IPS\Objects;


use Patami\IPS\Objects\Exceptions\ObjectSetFailedException;
use Patami\IPS\Objects\Exceptions\VariableInvalidDataException;


/**
 * Provides a IPS string variable object that stores data as a JSON encoded string.
 * @package IPSPATAMI
 */
class ArrayVariable extends StringVariable
{

    /**
     * @var bool Remember the class name to make sure that the data is automatically decoded when the object is created again.
     */
    protected $rememberClassName = true;

    /**
     * Returns the decoded data stored in the IPS string variable object.
     * @return mixed Data stored in the string variable.
     * @throws VariableInvalidDataException if the data could not be JSON-decoded.
     */
    public function Get()
    {
        // Get the text
        $text = parent::Get();

        // Decode the JSON string
        $data = @json_decode($text, true);

        // Throw an exception if the data could not be decoded
        if (is_null($data)) {
            throw new VariableInvalidDataException();
        }

        // Return the decoded array
        return $data;
    }

    /**
     * Encodes and sets the data in the IPS string variable object.
     * @param mixed $data New data to be stored as JSON-encoded string.
     * @return $this Fluent interface.
     * @throws ObjectSetFailedException if the value of the variable object could not be updated.
     */
    public function Set($data)
    {
        // Encode the data
        $text = @json_encode($data);

        // Set the value
        parent::Set($text);

        // Enable fluent interface
        return $this;
    }

}