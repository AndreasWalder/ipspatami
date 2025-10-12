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


namespace Patami\IPS\Libraries;


use Patami\IPS\Libraries\Exceptions\InvalidLibraryBranchException;
use Patami\IPS\Libraries\Exceptions\InvalidLibraryInfoFileException;
use Patami\IPS\Libraries\Exceptions\LibraryInfoFileNotFoundException;
use Patami\IPS\Libraries\Exceptions\LibraryNotLoadedException;
use Patami\IPS\Libraries\Exceptions\LibraryUpdateFailedException;
use Patami\IPS\Libraries\Exceptions\ModuleControlNotFoundException;
use Patami\IPS\System\IPS;


/**
 * Provides an OO implementation of an IPS library.
 * @package IPSPATAMI
 * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/bibliotheken/
 */
class Library
{

    /**
     * @var string Key of the library.
     * This is equivalent to the directory name of the library under IPS' modules folder and the key required to use
     * the IPS MC_* functions.
     */
    protected $name;

    /** @var array Cached repository info. */
    protected $repositoryInfo = null;

    /** @var array Cached library.json info */
    protected $fileInfo = null;

    /**
     * Library constructor.
     * Initializes the object and loads library info.
     * @param string $name Key of the library.
     * @throws ModuleControlNotFoundException if the IPS Module Control instance was not found.
     * @throws LibraryInfoFileNotFoundException if the library.json file could not be found in the library's base directory.
     * @throws InvalidLibraryInfoFileException if the library.json file could not be JSON-decoded.
     * @see Library::LoadInfo()
     */
    public function __construct($name)
    {
        // Remember the name
        $this->name = $name;

        // Load the library info if the library is loaded
        if ($this->IsLoaded()) {
            $this->LoadInfo();
        }
    }

    /**
     * Factory method to create a new instance of the Library class.
     * @param string $name Key of the library.
     * @return Library New instance of the library class for the given library.
     * @throws ModuleControlNotFoundException if the IPS Module Control instance was not found.
     * @throws LibraryInfoFileNotFoundException if the library.json file could not be found in the library's base directory.
     * @throws InvalidLibraryInfoFileException if the library.json file could not be JSON-decoded.
     */
    public static function Create($name)
    {
        // Get the name of the called class
        $className = get_called_class();

        // Create and return a new Library object
        return new $className($name);
    }

    /**
     * Returns the key of the library.
     * @return string Key of the library.
     */
    public function GetName()
    {
        return $this->name;
    }

    /**
     * Checks if the library is loaded.
     * @return bool True if the library is loaded.
     */
    public function IsLoaded()
    {
        return Libraries::IsLibraryLoaded($this->name);
    }

    /**
     * Returns the base directory of the library.
     * @return string Base directory of the library.
     */
    protected function GetBasePath()
    {
        // Generate the path
        $path = IPS::GetModulesDir() . $this->name;

        // Return the path
        return $path;
    }

    /**
     * Loads information about the library.
     * The method retrieves information about the library from the IPS Module Control instance and the library.jdon
     * file in the library's base directory.
     * @throws ModuleControlNotFoundException if the IPS Module Control instance was not found.
     * @throws LibraryNotLoadedException if the library is not loaded.
     * @throws LibraryInfoFileNotFoundException if the library.json file could not be found in the library's base directory.
     * @throws InvalidLibraryInfoFileException if the library.json file could not be JSON-decoded.
     */
    protected function LoadInfo()
    {
        // Throw an error if the library is not loaded
        if (! $this->IsLoaded()) {
            throw new LibraryNotLoadedException();
        }

        // Retrieve information from the IPS Module Control instance
        $this->LoadRepositoryInfo();

        // Load information from the library's library.json file
        $this->LoadLibraryFileInfo();
    }

    /**
     * Retrieves information about the library repository from the IPS Module Control instance.
     * The information is cached to make sure the IPS Module Control instance is only queried once.
     * @link https://www.symcon.de/service/dokumentation/modulreferenz/module-control/
     */
    protected function LoadRepositoryInfo()
    {
        // Get the library info
        /** @noinspection PhpUndefinedFunctionInspection */
        $info = @MC_GetModuleRepositoryInfo(Libraries::GetModuleControlInstanceId(), $this->name);

        // Cache the library info
        $this->repositoryInfo = $info;
    }

    /**
     * Loads information about the library from the library.json metadata file.
     * The file is loaded from the library's base directory and the information is cached to make sure the file is
     * only read once.
     * @throws ModuleControlNotFoundException if the IPS Module Control instance was not found.
     * @throws LibraryInfoFileNotFoundException if the library.json file could not be found in the library's base directory.
     * @throws InvalidLibraryInfoFileException if the library.json file could not be JSON-decoded.
     * @link https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/bibliotheken/
     */
    protected function LoadLibraryFileInfo()
    {
        // Generate the file name
        $fileName = $this->GetBasePath() . DIRECTORY_SEPARATOR . 'library.json';

        // Load the file
        $text = @file_get_contents($fileName);

        // Throw an error if the file could not be loaded
        if ($text === false) {
            throw new LibraryInfoFileNotFoundException();
        }

        // Decode the JSON data
        $data = @json_decode($text, true);

        // Throw an exception if the JSON could not be decoded
        if (is_null($data)) {
            throw new InvalidLibraryInfoFileException();
        }

        // Cache the data
        $this->fileInfo = $data;
    }

    /**
     * Returns the display name of the library.
     * This information is retrieved from the library.json file.
     * @return string|null Display name of the library or null if the information does not exist or can not be loaded.
     * @see Library::LoadLibraryFileInfo()
     */
    public function GetDisplayName()
    {
        return @$this->fileInfo['name'];
    }

    /**
     * Returns the GUID of the library.
     * This information is retrieved from the library.json file.
     * @return string|null GUID of the library or null if the information does not exist or can not be loaded.
     * @see Library::LoadLibraryFileInfo()
     */
    public function GetGuid()
    {
        return @$this->fileInfo['id'];
    }

    /**
     * Returns the author of the library.
     * This information is retrieved from the library.json file.
     * @return string|null Name of the library's author or null if the information does not exist or can not be loaded.
     * @see Library::LoadLibraryFileInfo()
     */
    public function GetAuthor()
    {
        return @$this->fileInfo['author'];
    }

    /**
     * Returns the version of the library.
     * This information is retrieved from the library.json file.
     * @return string|null Version of the library or null if the information does not exist or can not be loaded.
     * @see Library::LoadLibraryFileInfo()
     */
    public function GetVersion()
    {
        return @$this->fileInfo['version'];
    }

    /**
     * Returns the unix timestamp of the last commit to the library's Git repository.
     * This information is retrieved from the information about the library's Git repository.
     * @return int|null Unix timestamp of the last commit to the Git repository or null if the information does not
     * exist or can not be loaded.
     * @see Library::LoadRepositoryInfo()
     */
    public function GetUpdateTime()
    {
        return @$this->repositoryInfo['ModuleTime'];
    }

    /**
     * Returns the Git commit hash of the last commit to the library's Git repository.
     * This information is retrieved from the information about the library's Git repository.
     * @return string|null Git commit hash of the last commit to the Git repository or null if the information does not
     * exist or can not be loaded.
     * @see Library::LoadRepositoryInfo()
     */
    public function GetCommit()
    {
        return @$this->repositoryInfo['ModuleCommit'];
    }

    /**
     * Switches the Git repository of the library to the given branch.
     * @param string $branchName Name of the Git branch.
     * @throws InvalidLibraryBranchException if the new branch does not exist or cannot be switched to.
     * @link https://www.symcon.de/service/dokumentation/modulreferenz/module-control/
     */
    public function SetBranch($branchName)
    {
        // Do nothing if the new branch is already the current branch
        $currentBranchName = $this->GetBranch();
        if ($branchName == $currentBranchName) {
            return;
        }

        // Change the branch
        /** @noinspection PhpUndefinedFunctionInspection */
        $result = @MC_UpdateModuleRepositoryBranch(Libraries::GetModuleControlInstanceId(), $this->name, $branchName);

        // Reload repository info
        $this->LoadRepositoryInfo();

        // Throw an exception if the branch change returned an error or if the branch was not changed
        if (! $result || $branchName != $this->name) {
            throw new InvalidLibraryBranchException();
        }
    }

    /**
     * Returns the current Git branch name of the library's Git repository.
     * This information is retrieved from the information about the library's Git repository.
     * @return string Git branch name.
     * @see Library::LoadRepositoryInfo()
     */
    public function GetBranch()
    {
        return @$this->repositoryInfo['ModuleBranch'];
    }

    /**
     * Returns the URL of the library's Git repository.
     * Please note that the URL may contain credentials if the library is private (requires Git login).
     * This information is retrieved from the information about the library's Git repository.
     * @return string Git repository URL.
     * @see Library::LoadRepositoryInfo()
     */
    public function GetUrl()
    {
        return @$this->repositoryInfo['ModuleURL'];
    }

    /**
     * Returns the Git credentials of the library's Git repository.
     * The credentials are retrieved from the Git repository URL.
     * @return string|null Git credentials (in the format username:password) or null if the Git URL does not contain credentials.
     * @see Library::GetUrl()
     */
    public function GetGitCredentials()
    {
        // Get the URL
        $url = $this->GetUrl();

        // Extract the credentials
        if (preg_match('/^.*:\/\/(.*)@/', $url, $tokens)) {
            // Return the credentials
            return $tokens[1];
        }

        // No credentials
        return null;
    }

    /**
     * Checks if the library is private.
     * A library is private if the URL contains Git credentials (requires Git login).
     * @return bool True if the library is private.
     * @see Library::GetGitCredentials()
     */
    public function IsPrivate()
    {
        return ! is_null($this->GetGitCredentials());
    }

    /**
     * Checks if an update is available for the library.
     * @return bool True if an update is available for the library.
     * @link https://www.symcon.de/service/dokumentation/modulreferenz/module-control/
     */
    public function IsUpdateAvailable()
    {
        // Check if a newer Git commit is available and return the result
        /** @noinspection PhpUndefinedFunctionInspection */
        return @MC_IsModuleUpdateAvailable(Libraries::GetModuleControlInstanceId(), $this->name);
    }

    /**
     * Updates the library.
     * @throws LibraryUpdateFailedException if the update failed.
     * @link https://www.symcon.de/service/dokumentation/modulreferenz/module-control/
     */
    public function Update()
    {
        // Update the module
        /** @noinspection PhpUndefinedFunctionInspection */
        $result = @MC_UpdateModule(Libraries::GetModuleControlInstanceId(), $this->name);

        // Throw an exception if the update failed
        if (! $result) {
            throw new LibraryUpdateFailedException();
        }
    }

}