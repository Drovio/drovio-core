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

use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");
importer::import("API", "Resources", "filesystem/directory");
importer::import("API", "Model", "apps/application");
importer::import("AEL", "Resources", "appManager");
importer::import("AEL", "Profiler", "logger");

use \API\Profile\account;
use \API\Profile\team;
use \API\Resources\filesystem\directory as APIDirectory;
use \API\Model\apps\application;
use \AEL\Resources\appManager;
use \AEL\Profiler\logger;

/**
 * Application directory manager.
 * 
 * Handles application directories.
 * Adds extra functions like getting directory contents and other.
 * 
 * @version	0.1-2
 * @created	May 5, 2015, 11:40 (EEST)
 * @updated	August 29, 2015, 14:19 (EEST)
 */
class directory
{
	/**
	 * The account directory mode.
	 * 
	 * @type	integer
	 */
	const ACCOUNT_MODE = 1;
	
	/**
	 * The team directory mode.
	 * 
	 * @type	integer
	 */
	const TEAM_MODE = 2;
	
	/**
	 * The directory mode for the class instance.
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
	 * Create a new instance of an application directory manager.
	 * 
	 * @param	integer	$mode
	 * 		The directory mode.
	 * 		See class constants for options.
	 * 		It is in account mode by default.
	 * 
	 * @param	boolean	$shared
	 * 		If set to true, the directory class will have access to the shared application data folder.
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
	 * Get directory content list.
	 * 
	 * @param	string	$directory
	 * 		The directory we are searching.
	 * 
	 * @param	boolean	$includeHidden
	 * 		Whether to include hidden files (files that start with a dot) in the results.
	 * 
	 * @param	boolean	$includeDotFolders
	 * 		Whether to include dot folders ('.', '..') in the results.
	 * 
	 * @return	array
	 * 		An array of all the contents of a directory.
	 * 		['dirs'] for directories
	 * 		['files'] for files
	 */
	public function getContentList($directory, $includeHidden = FALSE, $includeDotFolders = FALSE)
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Normalize directory path
		$fullDirectory = APIDirectory::normalize($rootFolder."/".$directory."/");
		
		// Check directory existance
		if (!is_dir($fullDirectory))
		{
			logger::log("Folder '".$directory."' doesn't exist for listing. Aborting...", 2);
			return FALSE;
		}
		echo $fullDirectory."\n";
		$contents = array();
		$iterator = new RecursiveDirectoryIterator($fullDirectory);
		
		// Inner directories first
		foreach ($iterator as $path)
		{		
			// Get relative name
			$p = $path->getBasename();
			if ($path->isDir())
			{
				// Filter dots
				$basename = $path->getBasename();
				
				if (!$includeDotFolders && ($basename == "." || $basename == ".."))
					continue;
					
				if (!$includeHidden && (!strncmp($basename, ".", strlen("."))))
					continue;
				
				$contents['dirs'][] = $p;
			}
			else
			{
				// Filter hidden
				if (!$includeHidden && (!strncmp($path->getBasename(), ".", strlen("."))))
					continue;
				
			 	$contents['files'][] = $p;
			}
		}
		
		return $contents;
	}
	
	/**
	 * Get directory contents in details.
	 * 
	 * @param	string	$directory
	 * 		The directory we are searching.
	 * 
	 * @param	boolean	$includeHidden
	 * 		Whether to include hidden files (files that start with a dot) in the results.
	 * 
	 * @return	array
	 * 		Returns all the content details of a folder in an array:
	 * 		['dirs'] for directories
	 * 		['files'] for files
	 */
	public function getContentDetails($directory, $includeHidden = FALSE)
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Normalize directory path
		$fullDirectory = APIDirectory::normalize($rootFolder."/".$directory."/");
		
		// Check directory existance
		if (!is_dir($fullDirectory))
		{
			logger::log("Folder '".$directory."' doesn't exist for detail listing. Aborting...", 2);
			return FALSE;
		}
		
		$contents = array();
		$iterator = new RecursiveDirectoryIterator($fullDirectory);
		
		// Inner directories first
		foreach ($iterator as $path)
		{
			// Filter hidden
			if (!$includeHidden && (!strncmp($path->getBasename(), ".", strlen("."))))
				continue;
		
			$details = array();
			$details['name'] = $path->getBasename();
			$details['path'] = $path->__toString();
			$details['extension'] = $path->getExtension();
			$details['lastModified'] = $path->getMTime();
			$details['size'] = $path->getSize();
			$details['type'] = $path->getType();
			/*$details['isExecutable'] = $path->isExecutable();
			$details['isReadable'] = $path->isReadable();
			$details['isWritable'] = $path->isWritable();*/
			
			$basename = $path->getBasename();
			if ($path->isDir())
			{
				// Filter dots
				if ($basename == "." || $basename == "..")
					continue;
				$contents['dirs'][$basename] = $details;
			}
			else
			 	$contents['files'][$basename] = $details;
		}
		
		return $contents;
	}
	
	/**
	 * Checks if a directory is empty.
	 * 
	 * @param	string	$directory
	 * 		The directory path.
	 * 
	 * @return	void
	 */
	public function isEmpty($directory)
	{
		// Get root folder
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Normalize directory path
		$fullDirectory = APIDirectory::normalize($rootFolder."/".$directory."/");
		if (!is_dir($fullDirectory))
		{
			logger::log("Folder '".$directory."' doesn't exist for empty check. Aborting...", logger::ERROR);
			return NULL;
		}
		
		$handle = opendir($fullDirectory);
		while (FALSE !== ($entry = readdir($handle)))
			if ($entry != "." && $entry != "..")
				return FALSE;

		return TRUE;
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