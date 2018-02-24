<?php
//#section#[header]
// Namespace
namespace AEL\Resources\team;

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
 * @namespace	\team
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Profile", "team");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("AEL", "Platform", "application");

use \API\Profile\team;
use \API\Resources\filesystem\fileManager as APIFileManager;
use \AEL\Platform\application;

/**
 * Application File manager for teams
 * 
 * Manages all team files for the current running application.
 * 
 * NOTE: For each call it checks if there is an active application. If not, returns false every time.
 * All paths are relative to the application root folder.
 * 
 * @version	0.1-3
 * @created	December 1, 2014, 10:32 (EET)
 * @revised	December 1, 2014, 10:36 (EET)
 */
class fileManager extends APIFileManager
{
	/**
	 * Creates a new file in the specified location in the team application folder.
	 * 
	 * @param	string	$file
	 * 		The file path.
	 * 
	 * @param	string	$contents
	 * 		The text file contents.
	 * 
	 * @param	boolean	$recursive
	 * 		Indicates whether the file will create the path's folders if don't exist.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function create($file, $contents = "", $recursive = TRUE)
	{
		// Get root folder
		$rootFolder = self::getApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return parent::create($rootFolder."/".$file, $contents, $recursive);
	}
	
	/**
	 * Remove a file from the specified location in the team application folder.
	 * 
	 * @param	string	$file
	 * 		The file path to be removed.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($file)
	{
		// Get root folder
		$rootFolder = self::getApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return parent::remove($rootFolder."/".$file);
	}
	
	/**
	 * Get the contents of a file from the specified location in the team application folder.
	 * 
	 * @param	string	$file
	 * 		The file path to get the contents from.
	 * 
	 * @return	mixed
	 * 		The file contents or NULL if the file does not exist.
	 * 		FALSE if no active application is found.
	 */
	public static function get($file)
	{
		// Get root folder
		$rootFolder = self::getApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return parent::get($rootFolder."/".$file);
	}
	
	/**
	 * Put contents to a file from the specified location in the account application folder.
	 * 
	 * @param	string	$file
	 * 		The file path to put the contents to.
	 * 
	 * @param	string	$contents
	 * 		The contents to be written.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function put($file, $contents = "")
	{
		// Get root folder
		$rootFolder = self::getApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return parent::put($rootFolder."/".$file, $contents);
	}
	
	/**
	 * Copy the contents of a file to another file in the specified location in the team application folder.
	 * 
	 * @param	string	$from_file
	 * 		The source file path.
	 * 
	 * @param	string	$to_file
	 * 		The destination file path.
	 * 
	 * @param	string	$preventOverwrite
	 * 		If set to TRUE the destination file will not be overwritten.
	 * 
	 * @return	void
	 */
	public static function copy($from_file, $to_file, $preventOverwrite = FALSE)
	{
		// Get root folder
		$rootFolder = self::getApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return parent::put($rootFolder."/".$from_file,$rootFolder."/".$to_file, $preventOverwrite);
	}
	
	/**
	 * Move a file to another location in the specified location in the team application folder.
	 * 
	 * @param	string	$from_file
	 * 		The source file path.
	 * 
	 * @param	string	$to_file
	 * 		The destination file path.
	 * 
	 * @param	boolean	$preventOverwrite
	 * 		If set to TRUE the destination file will not be overwritten.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function move($from_file, $to_file, $preventOverwrite = FALSE)
	{
		// Get root folder
		$rootFolder = self::getApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return parent::put($rootFolder."/".$from_file,$rootFolder."/".$to_file, $preventOverwrite);
	}
	
	/**
	 * Get the application service path inside the team folder.
	 * 
	 * @return	string
	 * 		The application path or NULL if there is no active application.
	 */
	private static function getApplicationPath()
	{
		// Get application id
		$applicationID = application::init();
		if (empty($applicationID))
			return NULL;
		
		// Get 'service' folder inside the account foler
		$serviceName = "app".md5("app_service_".$applicationID);
		return systemRoot.team::getServicesFolder($serviceName);
	}
}
//#section_end#
?>