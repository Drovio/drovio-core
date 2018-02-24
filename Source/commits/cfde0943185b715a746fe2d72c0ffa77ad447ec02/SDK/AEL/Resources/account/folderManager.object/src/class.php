<?php
//#section#[header]
// Namespace
namespace AEL\Resources\account;

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
 * @namespace	\account
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Profile", "account");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("AEL", "Platform", "application");

use \API\Profile\account;
use \API\Resources\filesystem\folderManager as APIFolderManager;
use \AEL\Platform\application;

/**
 * Application Folder manager for accounts
 * 
 * Manages all account folders for the current running application.
 * 
 * NOTE: For each call it checks if there is an active application. If not, returns false every time.
 * All paths are relative to the application root folder.
 * 
 * @version	1.0-2
 * @created	December 1, 2014, 10:50 (EET)
 * @revised	December 2, 2014, 12:37 (EET)
 */
class folderManager extends APIFolderManager
{
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
	 * @param	integer	$mode
	 * 		The linux file mode for this folder.
	 * 		It is 0777 by default.
	 * 
	 * @param	string	$recursive
	 * 		Allows the creation of nested directories specified in the pathname.
	 * 		It is TRUE by default.
	 * 
	 * @return	boolean
	 * 		True on success, False on failure.
	 */
	public static function create($path, $name = "", $mode = 0777, $recursive = TRUE)
	{
		// Get root folder
		$rootFolder = systemRoot.self::getApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return parent::create($rootFolder."/".$path, $name, $mode, $recursive);
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
	public static function remove($path, $name = "", $recursive = FALSE)
	{
		// Get root folder
		$rootFolder = systemRoot.self::getApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		parent::remove($rootFolder."/".$path, $name, $recursive);
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
	public static function clean($path, $name = "", $includeHidden = TRUE)
	{
		// Get root folder
		$rootFolder = systemRoot.self::getApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		parent::clean($rootFolder."/".$path, $name, $includeHidden);
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
	public static function copy($source, $destination, $contents_only = FALSE)
	{
		// Get root folder
		$rootFolder = systemRoot.self::getApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		parent::copy($rootFolder."/".$source, $rootFolder."/".$destination, $contents_only);
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
	public static function move($source, $destination)
	{
		// Get root folder
		$rootFolder = systemRoot.self::getApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		parent::move($rootFolder."/".$source, $rootFolder."/".$destination);
	}
	
	/**
	 * Get the application service path inside the account folder.
	 * 
	 * @return	mixed
	 * 		The application path or NULL if there is no active application.
	 */
	public static function getApplicationPath()
	{
		// Get application id
		$applicationID = application::init();
		if (empty($applicationID))
			return NULL;
		
		// Get 'service' folder inside the account foler
		$serviceName = "app".md5("app_service_".$applicationID);
		return account::getServicesFolder($serviceName);
	}
}
//#section_end#
?>