<?php
//#section#[header]
// Namespace
namespace AEL\Resources;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	AEL
 * @package	Resources
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Model", "apps/application");

use \API\Profile\account;
use \API\Profile\team;
use \API\Resources\filesystem\folderManager as APIFolderManager;
use \API\Model\apps\application;

/**
 * Application Folder manager
 * 
 * Manages all application folders for the current running application.
 * 
 * NOTE: For each call it checks if there is an active application. If not, returns false every time.
 * All paths are relative to the application root folder or the application shared folder root.
 * The shared folder is one for all applications, so be careful of what you are storing there.
 * 
 * @version	0.1-1
 * @created	January 14, 2015, 10:15 (EET)
 * @updated	January 14, 2015, 10:15 (EET)
 */
class folderManager
{
	/**
	 * The account file mode.
	 * 
	 * @type	integer
	 */
	const ACCOUNT_MODE = 1;
	
	/**
	 * The team file mode.
	 * 
	 * @type	integer
	 */
	const TEAM_MODE = 2;
	
	/**
	 * The file mode for the class instance.
	 * 
	 * @type	integer
	 */
	private $mode;
	
	/**
	 * Shared or private application data.
	 * 
	 * @type	boolean
	 */
	private $shared;
	
	/**
	 * Create a new instance of an application folder manager.
	 * 
	 * @param	integer	$mode
	 * 		The file mode.
	 * 		See class constants for options.
	 * 		It is in account mode by default.
	 * 
	 * @param	boolean	$shared
	 * 		If set to true, the fileManager will have access to the shared application data folder.
	 * 		It is FALSE by default.
	 * 
	 * @return	void
	 */
	public function __construct($mode = self::ACCOUNT_MODE, $shared = FALSE)
	{
		$this->mode = $mode;
		$this->shared = $shared;
	}
	
	/**
	 * Creates a new folder in the specified location in the account application folder.
	 * 
	 * @param	string	$path
	 * 		The folder's parent path, or the folder's path, if the name is omitted.
	 * 
	 * @param	string	$name
	 * 		The folder's name.
	 * 		You can leave it empty if the folder name is included in the path value.
	 * 		It is empty by default.
	 * 
	 * @param	boolean	$recursive
	 * 		Allows the creation of nested directories specified in the pathname.
	 * 		It is TRUE by default.
	 * 
	 * @return	boolean
	 * 		True on success, False on failure.
	 */
	public function create($path, $name = "", $recursive = TRUE)
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return APIFolderManager::create($rootFolder."/".$path, $name, 0777, $recursive);
	}
	
	/**
	 * Remove a folder from the specified location in the account application folder.
	 * 
	 * @param	string	$path
	 * 		The folder's parent path, or the folder's path, if the name is omitted.
	 * 
	 * @param	string	$name
	 * 		The folder's name.
	 * 		You can leave it empty if the folder name is included in the path value.
	 * 		It is empty by default.
	 * 
	 * @param	boolean	$recursive
	 * 		Remove all inner contents of the folder recursively.
	 * 		It is FALSE by default.
	 * 
	 * @return	boolean
	 * 		True on success, False on failure.
	 */
	public function remove($path, $name = "", $recursive = FALSE)
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return APIFolderManager::remove($rootFolder."/".$path, $name, $recursive);
	}
	
	/**
	 * Empties a directory of all files and folders.
	 * 
	 * @param	string	$path
	 * 		The folder's parent path, or the folder's path, if the name is omitted.
	 * 
	 * @param	string	$name
	 * 		The folder's name.
	 * 		You can leave it empty if the folder name is included in the path value.
	 * 		It is empty by default.
	 * 
	 * @param	boolean	$includeHidden
	 * 		Whether to include hidden files and folders.
	 * 		It is TRUE by default.
	 * 
	 * @return	boolean
	 * 		True on success, False on failure.
	 */
	public function clean($path, $name = "", $includeHidden = TRUE)
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return APIFolderManager::clean($rootFolder."/".$path, $name, $includeHidden);
	}
	
	/**
	 * Copy a folder (recursively) in the specified location in the account application folder.
	 * 
	 * @param	string	$source
	 * 		The source folder path.
	 * 
	 * @param	string	$destination
	 * 		The destination folder path.
	 * 
	 * @param	boolean	$contents_only
	 * 		Defines whether only the contents of the folder will be copied or the folder selected also.
	 * 		It is FALSE by default.
	 * 
	 * @return	boolean
	 * 		True on success, False on failure.
	 */
	public function copy($source, $destination, $contents_only = FALSE)
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return APIFolderManager::copy($rootFolder."/".$source, $rootFolder."/".$destination, $contents_only);
	}
	
	/**
	 * Move a folder in the specified location in the account application folder.
	 * 
	 * @param	string	$source
	 * 		The source folder path.
	 * 
	 * @param	string	$destination
	 * 		The destination folder path.
	 * 
	 * @return	boolean
	 * 		True on success, False on failure.
	 */
	public function move($source, $destination)
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return APIFolderManager::move($rootFolder."/".$source, $rootFolder."/".$destination);
	}
	
	/**
	 * Get the root folder for the object.
	 * 
	 * @return	string
	 * 		The root folder, according to mode and shared variable.
	 */
	private function getPath()
	{
		if ($this->mode == self::ACCOUNT_MODE)
		{
			if ($this->shared)
				return account::getServicesFolder("/SharedAppData/");
			else
				return application::getAccountApplicationPath();
		}
		else
		{
			if ($this->shared)
				return team::getServicesFolder("/SharedAppData/");
			else
				return application::getTeamApplicationPath();
		}
	}
}
//#section_end#
?>