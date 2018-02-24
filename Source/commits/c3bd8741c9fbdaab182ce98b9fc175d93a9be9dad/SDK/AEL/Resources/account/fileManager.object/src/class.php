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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Profile", "account");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Model", "apps/application");

use \API\Profile\account;
use \API\Resources\filesystem\fileManager as APIFileManager;
use \API\Model\apps\application;

/**
 * Application File manager for accounts
 * 
 * Manages all account files for the current running application.
 * 
 * NOTE: For each call it checks if there is an active application. If not, returns false every time.
 * All paths are relative to the application root folder or the application shared folder root.
 * The shared folder is one for all applications, so be careful of what you are storing there.
 * 
 * @version	2.0-2
 * @created	December 1, 2014, 10:25 (EET)
 * @updated	January 13, 2015, 12:23 (EET)
 */
class fileManager extends APIFileManager
{
	/**
	 * Shared or private application data.
	 * 
	 * @type	boolean
	 */
	private $shared;
	
	/**
	 * Create a new instance of a fileManager
	 * 
	 * @param	boolean	$shared
	 * 		If set to true, the fileManager will have access to the shared application data folder.
	 * 		It is FALSE by default.
	 * 
	 * @return	void
	 */
	public function __construct($shared = FALSE)
	{
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
		return parent::create($rootFolder."/".$file, $contents, $recursive);
	}
	
	/**
	 * Remove a file from the specified location in the account application folder.
	 * 
	 * @param	string	$file
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
		return parent::remove($rootFolder."/".$file);
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
	public function put($file, $contents = "")
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Call parent
		return parent::put($rootFolder."/".$file, $contents);
	}
	
	/**
	 * Copy the contents of a file to another file in the specified location in the account application folder.
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
		return parent::put($rootFolder."/".$from_file,$rootFolder."/".$to_file, $preventOverwrite);
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
		return parent::put($rootFolder."/".$from_file,$rootFolder."/".$to_file, $preventOverwrite);
	}
	
	/**
	 * Get the root folder for the object.
	 * 
	 * @return	string
	 * 		The root folder, according to shared variable.
	 */
	private function getPath()
	{
		if ($this->shared)
			return account::getServicesFolder("/SharedAppData/");
		else
			return application::getAccountApplicationPath();
	}
}
//#section_end#
?>