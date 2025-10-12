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


namespace Patami\IPS\System\Logging;


use Patami\IPS\System\Logging\Exceptions\LoggingFileCloseFailedException;
use Patami\IPS\System\Logging\Exceptions\LoggingFileOpenFailedException;
use Patami\IPS\System\Logging\Exceptions\LoggingFileWriteFailedException;
use Patami\IPS\System\Semaphore;


/**
 * Logger implementation that sends log entries to a log file.
 * @package IPSPATAMI
 * @see Logger
 */
class FileLogger extends Logger
{

    /**
     * @var string File name of the log file.
     */
    protected $fileName = null;

    /**
     * FileLogger constructor.
     * @param string $fileName File name of the log file.
     */
    public function __construct($fileName)
    {
        // Remember the file name
        $this->fileName = $fileName;
    }

    /**
     * Returns the file name of the log file.
     * @return string File name of the log file.
     */
    public function GetFileName()
    {
        // Return the file name
        return $this->fileName;
    }

    /**
     * Sets the file name of the log file.
     * @param string $fileName File name of the log file.
     * @return $this Fluent interface.
     */
    public function SetFileName($fileName)
    {
        // Remember the file name
        $this->fileName = $fileName;

        // Enable fluent interface
        return $this;
    }

    /**
     * Returns the log entry formatted as a string, prefixed with a timestamp, the PHP thread ID and the log level.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param int $level Log level.
     * @param array|null $data Optional data to be added to the log entry.
     * @return string Formatted log message.
     */
    protected function FormatMessage($tag, $message, $level, $data)
    {
        // Generate the log prefix
        $prefix = sprintf(
            '%s  %s  %s  [%s] ',
            date('r'),
            str_pad(@$_IPS['THREAD'], 2, ' ', STR_PAD_LEFT),
            Log::GetLogLevelName($level, true),
            $tag
        );

        // Format the message
        $text = $this->FormatMessageAsString($message, $data);

        // Get the individual lines
        $lines = explode("\n", $text);

        // Loop through the lines and create the log entries
        $formattedText = '';
        foreach ($lines as $line) {
            $formattedText .= $prefix . rtrim($line) . "\n";
        }

        // Return the formatted log entries
        return $formattedText;
    }

    /**
     * Sends a log entry to the log file.
     * This method is called internally after formatting the message.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param string $formattedMessage The formatted log entry.
     * @param int $level Log level.
     * @param array|null $data Optional data to be added to the log entry.
     * @throws LoggingFileOpenFailedException if the log file could not be opened.
     * @throws LoggingFileWriteFailedException if the log entry could not be written to the log file.
     * @throws LoggingFileCloseFailedException if the log file could not be closed.
     */
    protected function SendLog($tag, $message, $formattedMessage, $level, $data)
    {
        // Create a new semaphore
        $semaphore = new Semaphore($this->fileName);

        try {

            // Enter the semaphore
            //$semaphore->Enter();

            // Open the log file for appending
            $fileHandle = @fopen($this->fileName, 'a');

            // Throw an exception if the file could not be opened
            if ($fileHandle === false) {
                throw new LoggingFileOpenFailedException();
            }

            try {

                // Append the message to the log file
                $numWritten = @fwrite($fileHandle, $formattedMessage);

                // Throw an exception if the log entry could not be written
                if ($numWritten === false) {
                    throw new LoggingFileWriteFailedException();
                }

            } finally {

                // Close the file
                $result = @fclose($fileHandle);

                // Throw an exception if the file could not be closed
                if ($result === false) {
                    throw new LoggingFileCloseFailedException();
                }

            }

        } finally {

            // Leave the semaphore
            //$semaphore->Leave();

        }
    }

}