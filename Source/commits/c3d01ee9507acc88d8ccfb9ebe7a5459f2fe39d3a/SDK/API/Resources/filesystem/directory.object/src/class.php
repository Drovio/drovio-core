<?php
//#section#[header]
// Namespace
namespace API\Resources\filesystem;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Resources
 * @namespace	\filesystem
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

importer::import("API", "Platform", "accessControl");
importer::import("DEV", "Profiler", "logger");

use \API\Platform\accessControl;
use \DEV\Profiler\logger;

/**
 * Directory
 * 
 * System's directory object. Used to acquire a directory's details
 * 
 * @version	1.0-1
 * @created	April 9, 2013, 14:03 (EEST)
 * @revised	November 10, 2014, 9:59 (EET)
 */
class directory
{
	/**
	 * Returns all the contents of a folder in an array.
	 * ['dirs'] for directories
	 * ['files'] for files
	 * 
	 * @param	string	$directory
	 * 		The directory we are searching
	 * 
	 * @param	boolean	$includeHidden
	 * 		Include hidden files (files that start with a dot) in the results
	 * 
	 * @param	boolean	$includeDotFolders
	 * 		Include dot folders ('.', '..') in the results
	 * 
	 * @param	boolean	$relativeNames
	 * 		Return content names, instead of paths
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function getContentList($directory, $includeHidden = FALSE, $includeDotFolders = FALSE, $relativeNames = FALSE)
	{
		// Check core functionality
		//if (!accessControl::internalCall())
		//	return FALSE;
		
		$directory = self::normalize($directory."/");
		
		// Check directory existance
		if (!is_dir($directory))
		{
			logger::log("Folder '".$directory."' doesn't exist for listing. Aborting...", 2);
			return FALSE;
		}
		
		// Log activity
		logger::log("Getting folder '".$directory."' contents...");
		
		$contents = array();
		$iterator = new RecursiveDirectoryIterator($directory);
		
		// Inner directories first
		foreach ($iterator as $path)
		{		
			// Full path or relative name
			$p = ($relativeNames ? $path->getBasename() : $path->__toString());
		
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
	 * Returns all the content details of a folder in an array:
	 * ['dirs'] for directories
	 * ['files'] for files
	 * 
	 * Each elements holds the following details (keys):
	 * 	name		-> File's name
	 * 	path		-> File's path
	 * 	extension	-> File's Extnsion
	 * 	lastModified	-> Last Modified Date (unformated)
	 * 	size		-> File's size
	 * 	type		-> File's type
	 * 
	 * @param	string	$directory
	 * 		The directory we are searching
	 * 
	 * @param	boolean	$includeHidden
	 * 		Include hidden files (files that start with a dot) in the results
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function getContentDetails($directory, $includeHidden = FALSE)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		$directory = self::normalize($directory."/");
		
		// Check directory existance
		if (!is_dir($directory))
		{
			logger::log("Folder '".$directory."' doesn't exist for detail listing. Aborting...", 2);
			return FALSE;
		}
		
		// Log activity
		logger::log("Getting folder '".$directory."' content details...");
		if (!$includeHidden)
			logger::log("Hidden files will be ignored...");
		
		$contents = array();
		$iterator = new RecursiveDirectoryIterator($directory);
		
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
	 * @param	string	$path
	 * 		The path of the folder
	 * 
	 * @param	string	$name
	 * 		The name of the folder
	 * 
	 * @return	mixed
	 * 		Returns if the given directory is empty [TRUE] or not [FALSE]. Returns NULL if an error occurs.
	 */
	public static function isEmpty($path, $name = "")
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return NULL;
		
		// Remove Directory
		$directory = ($name = "" ? $path."/" : $path."/".$name."/");
		
		// Collapse redundant slashes
		$directory = self::normalize($directory);
		
		if (!is_dir($directory))
			return NULL;
		
		// Log activity
		logger::log("Checking folder '".$directory."' ...");
		
		$handle = opendir($directory);
		while (FALSE !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				logger::log("Folder '".$directory."' is not empty!");
				return FALSE;
			}
		}
		logger::log("Folder '".$directory."' appears to be empty!");
		return TRUE;
	}
	
	/**
	 * Normalizes a path by collapsing redundant slashes.
	 * 
	 * @param	string	$path
	 * 		The path to be normalized.
	 * 
	 * @return	string
	 * 		The normalized path.
	 */
	public static function normalize($path)
	{
		return preg_replace("/\/{2,}/", "/", $path);
	}
	
	/**
	 * Check if the given name (file or folder) contains one of the following characters and thus it is not valid.
	 * 
	 * @param	string	$name
	 * 		The file/folder name to check.
	 * 
	 * @return	boolean
	 * 		TRUE if the name is valid FALSE otherwise.
	 */
	public static function validate($name)
	{
		$valid = (strpbrk($name, '\/?%*:|"<>') === FALSE);
		return $valid;
	}
	
}
//#section_end#
?>