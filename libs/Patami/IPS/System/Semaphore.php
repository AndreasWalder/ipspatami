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


namespace Patami\IPS\System;


use Patami\IPS\System\Exceptions\SemaphoreTimeoutException;


/**
 * IPS Sempahore Wrapper Class.
 * Provides an OO interface for an IPS semaphore to handle exclusive access to a resource with multi-threading.
 * @package IPSPATAMI
 */
class Semaphore
{

    /**
     * Default number of milliseconds to wait for a locked semaphore if not time is specified.
     * @see Semaphore::Enter()
     */
    const DEFAULT_WAIT_TIME = 1000;

    /**
     * @var string Name of the semaphore.
     */
    protected $name = null;

    /**
     * Semaphore constructor.
     * @param string $name Name of the semaphore.
     */
    public function __construct($name)
    {
        // Remember the name of the semaphore
        $this->SetName($name);
    }

    /**
     * Returns the name of the semaphore.
     * @return string Name of the semaphore.
     */
    public function GetName()
    {
        // Return the name of the semaphore
        return $this->name;
    }

    /**
     * Sets the name of the semaphore.
     * This method should not be called after Semaphore::Enter().
     * @param string $name Name of the semaphore.
     * @return $this Fluent interface.
     */
    public function SetName($name)
    {
        // Remember the name of the semaphore
        $this->name = $name;

        // Enable fluent interface
        return $this;
    }

    /**
     * Locks the semaphore or waits for another thread to release the semaphore if it is already locked.
     * @param int $waitTime Number of milliseconds to wait for another thread to release the semaphore.
     * @return $this Fluent interface.
     * @throws SemaphoreTimeoutException if a timeout occurred while waiting for the semaphore to be release by another thread.
     * @see IPS::SemaphoreEnter()
     */
    public function Enter($waitTime = self::DEFAULT_WAIT_TIME)
    {
        // Do nothing if there is no semaphore name
        if (is_null($this->name)) {
            return $this;
        }

        // Try to lock the semaphore (usually works)
        $result = IPS::SemaphoreEnter($this->name, $waitTime);

        // Throw a timeout exception if the result is false (see documentation)
        if ($result === false) {
            throw new SemaphoreTimeoutException();
        }

        // Enable fluent interface
        return $this;
    }

    /**
     * Releases the semaphore.
     * @return $this Fluent interface.
     * @see IPS::SemaphoreLeave()
     */
    public function Leave()
    {
        // Do nothing if there is no semaphore name
        if (is_null($this->name)) {
            return $this;
        }

        // Unlock the semaphore
        IPS::SemaphoreLeave($this->name);

        // Enable fluent interface
        return $this;
    }

}