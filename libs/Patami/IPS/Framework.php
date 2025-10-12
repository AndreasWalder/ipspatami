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


namespace Patami\IPS;


use Patami\IPS\Exceptions\FrameworkInstanceNotFoundException;
use Patami\IPS\Exceptions\FrameworkTooManyInstancesFoundException;
use Patami\IPS\I18N\Translator;
use Patami\IPS\Libraries\Exceptions\InvalidLibraryException;
use Patami\IPS\Libraries\Libraries;
use Patami\IPS\Libraries\Library;
use Patami\IPS\Objects\Instance;
use Patami\IPS\Objects\Script;
use Patami\IPS\Services\KNX\KNX;
use Patami\IPS\System\ErrorHandler;
use Patami\IPS\System\IPS;
use Patami\IPS\System\Logging\Log;


/**
 * Patami Framework Helper Class.
 * @package IPSPATAMI
 */
class Framework
{

    /**
     * IPS-internal key of the library required for the MC_* IPS functions.
     * Also used as the directory name of the library under the IPS modules directory.
     */
    const FRAMEWORK_MODULE_KEY = 'ipspatami';

    /**
     * IPS GUID of the module.
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/bibliotheken/
     */
    const FRAMEWORK_MODULE_ID = '{F82A758B-3F08-4E06-BBF9-A4055033611D}';

    /**
     * File name of the default log file.
     */
    const LOG_FILE_NAME = 'patami.log';

    /**
     * Creates a new IPS core instance object for the Framework.
     * @return int IPS object ID of the framework instance.
     */
    protected static function Create()
    {
        // Create a new instance
        $instance = new Instance(null, self::FRAMEWORK_MODULE_ID);

        // Set the name of the instance
        $instance->SetName('Patami Framework');

        // Return the new instance
        return $instance->GetId();
    }

    /**
     * Checks if the framework's IPS core instance exists.
     * @return bool True if the framework's IPS core instance exists.
     */
    public static function InstanceExists()
    {
        // Get the list of framework instances
        $ids = IPS::GetInstanceListByModuleId(self::FRAMEWORK_MODULE_ID);

        // Return true if at least one instance exists
        return count($ids) > 0;
    }

    /**
     * Checks if the framework's IPS core instance is initializing.
     * This can be used to check if the instance is being created during IPS startup or module updates.
     * @return bool True if the framework's IPS core instance is initializing.
     */
    public static function IsInstanceInitializing()
    {
        return ! function_exists('Patami_Debug');
    }

    /**
     * Returns the IPS object ID of the framework instance.
     * @param bool $autoCreate True if the framework's IPS core instance should be created if it does not exists.
     * @return int IPS object ID of the framework instance.
     * @throws FrameworkInstanceNotFoundException if the IPS instance of the framework was not found.
     * @throws FrameworkTooManyInstancesFoundException if more than one framework instance was found.
     */
    public static function GetInstanceId($autoCreate = true)
    {
        // Get the list of framework instances
        $ids = IPS::GetInstanceListByModuleId(self::FRAMEWORK_MODULE_ID);

        // Check if an object was found
        if (count($ids) == 0) {
            // No, none
            // Check if one should be auto-created
            if ($autoCreate) {
                // Create and return a new instance
                return self::Create();
            } else {
                // No, throw an exception
                throw new FrameworkInstanceNotFoundException();
            }
        }

        // Throw an exception if more than one instance was found
        if (count($ids) > 1) {
            throw new FrameworkTooManyInstancesFoundException();
        }

        // Return the ID
        return $ids[0];
    }

    /**
     * Returns the framework instance.
     * @param bool $autoCreate True if the framework's IPS core instance should be created if it does not exists.
     * @return Instance Framework instance.
     */
    public static function GetInstance($autoCreate = true)
    {
        // Create and return the instance object
        return new Instance(self::GetInstanceId($autoCreate));
    }

    /**
     * Returns a new instance of the PatamiFramework IPS core instance.
     * @param bool $autoCreate True if the framework's IPS core instance should be created if it does not exists.
     * @return \PatamiFramework
     */
    public static function GetModule($autoCreate = true)
    {
        // Get the instance ID
        $instanceId = self::GetInstanceId($autoCreate);

        // Include the module file
        $fileName = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Framework' . DIRECTORY_SEPARATOR . 'module.php';
        /** @noinspection PhpIncludeInspection */
        @require_once($fileName);

        // Create and return a new instance of the module
        return new \PatamiFramework($instanceId);
    }

    /**
     * Returns the version of the framework.
     * @return string Version number of the framework.
     */
    public static function GetVersion()
    {
        $library = new Library(self::FRAMEWORK_MODULE_KEY);
        return $library->GetVersion();
    }

    /**
     * Returns the currently active Git branch of the framework.
     * @return string Git branch name.
     */
    public static function GetBranch()
    {
        $library = new Library(self::FRAMEWORK_MODULE_KEY);
        return $library->GetBranch();
    }

    /**
     * Returns the current Git commit has of the framework.
     * @return string Git commit hash.
     */
    public static function GetCommit()
    {
        $library = new Library(self::FRAMEWORK_MODULE_KEY);
        return $library->GetCommit();
    }

    /**
     * Returns the last update unix timestamp of the framework Git repository.
     * @return int Unix timestamp of the last Git update.
     */
    public static function GetUpdateTime()
    {
        $library = new Library(self::FRAMEWORK_MODULE_KEY);
        return $library->GetUpdateTime();
    }

    /**
     * Returns the name of the framework log file.
     * @return string Name of the frame work log file.
     */
    public static function GetLogFileName()
    {
        return IPS::GetLogFileName(self::LOG_FILE_NAME);
    }

    /**
     * Returns the IPS object ID of the custom autoload script or 0 if none is set on the configuration page.
     * @return int IPS object ID of the autoload script.
     */
    public static function GetCustomAutoLoadScriptId()
    {
        // Get and return the setting
        return self::GetInstance()->GetProperty('CustomAutoLoadScriptID');
    }

    /**
     * Loads the custom autoload script.
     * @return bool True if the autoload script was successfully loaded.
     */
    public static function LoadCustomAutoLoadScript()
    {
        // Get the script ID
        $scriptId = self::GetCustomAutoLoadScriptId();

        // Return if no script is set
        if ($scriptId == 0) {
            return false;
        }

        // Create the script object
        $script = new Script($scriptId);

        // Include the script ands return the result
        return $script->Load();
    }

    /**
     * Returns true if the framework logs extended debug messages.
     * @return bool True if extended debugging is enabled.
     */
    public static function IsExtendedDebuggingEnabled()
    {
        // Get and return the setting
        return self::GetInstance()->GetProperty('ExtendedDebuggingEnabled');
    }

    /**
     * Returns true if the framework global error handler is enabled on the configuration page.
     * @return bool True if the global error handler is enabled.
     */
    public static function IsErrorHandlerEnabled()
    {
        // Get and return the setting
        return self::GetInstance()->GetProperty('ErrorHandlerEnabled');
    }

    /**
     * Initializes the Patami Framework and its components. This method is automatically called when the framework is bootstrapped.
     */
    public static function Bootstrap()
    {
        // Do nothing if the IPS kernel not ready yet
        if (! IPS::IsKernelReady()) {
            return;
        }

        // Do nothing if the framework instance is still initializing
        if (self::IsInstanceInitializing()) {
            return;
        }

        // Initialize translator
        Translator::Initialize();

        // Initialize logging
        Log::Initialize();

        // Initialize error handler
        ErrorHandler::Initialize();

        // Load the custom auto-load script
        self::LoadCustomAutoLoadScript();
    }

    /**
     * Sends a debug message to the log of the framework instance.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     */
    public static function Debug($tag, $message, array $data = null)
    {
        // Skip if the framework instance is still initializing
        if (self::IsInstanceInitializing()) {
            return;
        }

        // Get the Framework instance ID
        $id = self::GetInstanceId();

        // Send the log entry to the Framework instance
        /** @noinspection PhpUndefinedFunctionInspection */
        @Patami_Debug($id, $tag, $message, $data);
    }

    /**
     * Sends an extended debug message to the log of the framework instance.
     * @param string $tag Name of the sender of the log (can be used as a category or similar).
     * @param string $message The log message.
     * @param array|null $data Optional data to be added to the log entry.
     */
    public static function DebugEx($tag, $message, array $data = null)
    {
        // Skip if the framework instance is still initializing
        if (self::IsInstanceInitializing()) {
            return;
        }

        // Get the Framework instance ID
        $id = self::GetInstanceId();

        // Send the log entry to the Framework instance
        /** @noinspection PhpUndefinedFunctionInspection */
        @Patami_DebugEx($id, $tag, $message, $data);
    }

}