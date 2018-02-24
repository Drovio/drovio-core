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
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Platform", "accessControl");
importer::import("DEV", "Profiler", "logger");

use \API\Platform\accessControl;
use \DEV\Profiler\logger;

use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

/**
 * Directory
 * 
 * System's directory object. Used to acquire a directory's details
 * 
 * @version	2.0-3
 * @created	April 9, 2013, 14:03 (EEST)
 * @updated	August 29, 2015, 18:21 (EEST)
 */
class directory
{
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
	 * @param	boolean	$relativeNames
	 * 		Return content names, instead of full paths.
	 * 
	 * @return	array
	 * 		An array of all the contents of a directory.
	 * 		['dirs'] for directories
	 * 		['files'] for files
	 */
	public static function getContentList($directory, $includeHidden = FALSE, $includeDotFolders = FALSE, $relativeNames = FALSE)
	{
		$directory = self::normalize($directory."/");
		
		// Check directory existance
		if (!is_dir($directory))
		{
			logger::log("Folder '".$directory."' doesn't exist for listing. Aborting...", logger::ERROR);
			return FALSE;
		}
		
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
	public static function getContentDetails($directory, $includeHidden = FALSE)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		$directory = self::normalize($directory."/");
		
		// Check directory existance
		if (!is_dir($directory))
		{
			logger::log("Folder '".$directory."' doesn't exist for detail listing. Aborting...", logger::ERROR);
			return FALSE;
		}
		
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
	 * 		The path of the folder.
	 * 
	 * @param	string	$name
	 * 		The name of the folder.
	 * 
	 * @return	mixed
	 * 		True if the directory is empty, false otherwise.
	 * 		It returns NULL if an error occurred or the directory doesn't exist.
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
		{
			logger::log("Folder '".$directory."' doesn't exist for empty check. Aborting...", logger::ERROR);
			return NULL;
		}
		
		$handle = opendir($directory);
		while (FALSE !== ($entry = readdir($handle)))
			if ($entry != "." && $entry != "..")
				return FALSE;

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
	
	/**
	 * Formats a given size in bytes.
	 * 
	 * @param	float	$bytes
	 * 		The size in bytes.
	 * 
	 * @param	integer	$precision
	 * 		The precision of the rounded sizes in digits.
	 * 
	 * @return	string
	 * 		The size formatted in the highest metric possible.
	 */
	public static function formatBytes($bytes, $precision = 2)
	{ 
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 
	
		$bytes /= (1 << (10 * $pow)); 
	
		return round($bytes, $precision).' '.$units[$pow]; 
	} 
}
//#section_end#
?>