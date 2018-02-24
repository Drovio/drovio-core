<?php
//#section#[header]
// Namespace
namespace AEL\Resources\filesystem;

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
 * @namespace	\filesystem
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Model", "apps/application");
importer::import("AEL", "Resources", "appManager");

use \API\Profile\account;
use \API\Profile\team;
use \API\Resources\filesystem\fileManager as APIFileManager;
use \API\Model\apps\application;
use \AEL\Resources\appManager;

/**
 * Application File manager
 * 
 * Manages all application files for the current running application.
 * 
 * NOTE: For each call it checks if there is an active application. If not, returns false every time.
 * All paths are relative to the application root folder or the application shared folder root.
 * The shared folder is one for all applications, so be careful of what you are storing there.
 * 
 * @version	1.0-1
 * @created	March 3, 2015, 15:15 (EET)
 * @updated	September 2, 2015, 15:24 (EEST)
 */
class fileManager
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
	 * Create a new instance of an application file manager.
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
	 * Creates a new file in the specified location in the account application folder.
	 * 
	 * @param	string	$file
	 * 		The file path.
	 * 
	 * @param	string	$contents
	 * 		The text file contents.
	 * 
	 * @param	boolean	$recursive
	 * 		Indicates whether the file will create the path's folders if don't exist.
	 * 		It is TRUE by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($file, $contents = "", $recursive = TRUE)
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return APIFileManager::create($rootFolder."/".$file, $contents, $recursive);
	}
	
	/**
	 * Remove a file from the specified location in the account application folder.
	 * 
	 * @param	boolean	$file
	 * 		The file path to be removed.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($file)
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return APIFileManager::remove($rootFolder."/".$file);
	}
	
	/**
	 * Get the contents of a file from the specified location in the account application folder.
	 * 
	 * @param	string	$file
	 * 		The file path to get the contents from.
	 * 
	 * @return	mixed
	 * 		The file contents or NULL if the file does not exist.
	 * 		FALSE if no active application is found.
	 */
	public function get($file)
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return APIFileManager::get($rootFolder."/".$file);
	}
	
	/**
	 * Get contents from a given url.
	 * 
	 * @param	string	$url
	 * 		The url path.
	 * 		It must be a valid url starting with "http".
	 * 
	 * @param	resource	$context
	 * 		A valid context resource created with stream_context_create().
	 * 		If you don't need to use a custom context, you can skip this parameter by NULL.
	 * 
	 * @return	mixed
	 * 		The http request result.
	 */
	public function getUrl($url, $context = NULL)
	{
		// Check if it is a valid url
		$url = trim($url);
		if (strpos($url, "http") !== 0)
			return NULL;
			
		// Fetch url using file_get_contents
		return file_get_contents($url, $use_include_path = FALSE, $context);
	}
	
	/**
	 * Put contents to a file from the specified location in the account application folder.
	 * 
	 * @param	string	$file
	 * 		The file path to put the contents to.
	 * 
	 * @param	mixed	$contents
	 * 		The contents to be written.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function put($file, $contents = "")
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return APIFileManager::put($rootFolder."/".$file, $contents);
	}
	
	/**
	 * Copy the contents of a file to another file in the specified location in the application folder.
	 * 
	 * @param	string	$from_file
	 * 		The source file path.
	 * 
	 * @param	string	$to_file
	 * 		The destination file path.
	 * 
	 * @param	boolean	$preventOverwrite
	 * 		If set to TRUE the destination file will not be overwritten.
	 * 		It is FALSE by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function copy($from_file, $to_file, $preventOverwrite = FALSE)
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return APIFileManager::put($rootFolder."/".$from_file,$rootFolder."/".$to_file, $preventOverwrite);
	}
	
	/**
	 * Move a file to another location in the specified location in the account application folder.
	 * 
	 * @param	string	$from_file
	 * 		The source file path.
	 * 
	 * @param	string	$to_file
	 * 		The destination file path.
	 * 
	 * @param	boolean	$preventOverwrite
	 * 		If set to TRUE the destination file will not be overwritten.
	 * 		It is FALSE by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function move($from_file, $to_file, $preventOverwrite = FALSE)
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return APIFileManager::put($rootFolder."/".$from_file, $rootFolder."/".$to_file, $preventOverwrite);
	}
	
	/**
	 * Get the root folder for the object.
	 * 
	 * @return	string
	 * 		The root folder, according to mode and shared variable.
	 */
	private function getPath()
	{
		return appManager::getRootFolder($this->mode, $this->shared);
	}
}
//#section_end#
?>