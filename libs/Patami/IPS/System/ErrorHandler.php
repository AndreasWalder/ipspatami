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


use Patami\Helpers\Debug;
use Patami\IPS\Framework;
use Patami\IPS\System\Logging\Log;


/**
 * Error Handler Class.
 * Registers PHP error and exception handlers that log uncatched errors and exceptions to the IPS log.
 * @package IPSPATAMI
 */
class ErrorHandler
{

    /**
     * Returns the name of an error type.
     * @param int $type Error type.
     * @return string Name of the error type.
     */
    public static function GetErrorTypeAsString($type)
    {
        switch($type)
        {
            case E_ERROR: // 1 //
                return 'E_ERROR';
            case E_WARNING: // 2 //
                return 'E_WARNING';
            case E_PARSE: // 4 //
                return 'E_PARSE';
            case E_NOTICE: // 8 //
                return 'E_NOTICE';
            case E_CORE_ERROR: // 16 //
                return 'E_CORE_ERROR';
            case E_CORE_WARNING: // 32 //
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR: // 64 //
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING: // 128 //
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR: // 256 //
                return 'E_USER_ERROR';
            case E_USER_WARNING: // 512 //
                return 'E_USER_WARNING';
            case E_USER_NOTICE: // 1024 //
                return 'E_USER_NOTICE';
            case E_STRICT: // 2048 //
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR: // 4096 //
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED: // 8192 //
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED: // 16384 //
                return 'E_USER_DEPRECATED';
        }

        return $type;
    }

    /**
     * Error handler called by PHP when an error occurs.
     */
    public static function ErrorHandler()
    {
        $error = error_get_last();
        if (! is_null($error) && error_reporting() != 0 && @$error['type'] != E_NOTICE) {
            $text = sprintf(
                "Type: %s\n" .
                "Message: %s\n" .
                "File: %s\n" .
                "Line: %s",
                self::GetErrorTypeAsString(@$error['type']),
                @$error['message'],
                @$error['file'],
                @$error['line']
            );
            $callTrace = Debug::GetCallTraceAsString();
            if ($callTrace != '') {
                $text .= "\nTrace:\n" . $callTrace;
            }
            Log::Error('PHP Error', $text);
        }
    }

    public static function ExceptionHandler(\Exception $e)
    {
        $text = sprintf(
            "Class: %s\n" .
            "Message: %s\n" .
            "File: %s\n" .
            "Line: %s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        $callTrace = Debug::GetCallTraceAsString();
        if ($callTrace != '') {
            $text .= "\nTrace:\n" . $callTrace;
        }
        Log::Error('PHP Exception', $text);
    }

    /**
     * Registers error and exception handler functions.
     * This method is automatically called during framework initialization.
     * @see Framework::Bootstrap()
     */
    public static function Initialize()
    {
        // Do nothing if the error handler is disabled
        if (! Framework::IsErrorHandlerEnabled()) {
            return;
        }

        // Register shutdown handler to log uncatchable error messages
        register_shutdown_function(array(get_called_class(), 'ErrorHandler'));

        // Register exception handler to log uncatched exceptions
        set_exception_handler(array(get_called_class(), 'ExceptionHandler'));
    }

}
