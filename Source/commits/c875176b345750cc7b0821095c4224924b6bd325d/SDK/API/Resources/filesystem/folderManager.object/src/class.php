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

importer::import("API", "Developer", "profiler::logger");
importer::import("API", "Security", "accessControl");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::directory");

use \API\Developer\profiler\logger;
use \API\Security\accessControl;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\directory;

use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

/**
 * Folder Manager
 * 
 * System's folder manager
 * 
 * @version	{empty}
 * @created	April 4, 2013, 11:04 (EEST)
 * @revised	January 29, 2014, 10:15 (EET)
 */
class folderManager
{
	/**
	 * Mass Removal Permissions Index.
	 * 
	 * @type	string
	 */
	const RM_PERMS = "folderManager/editableList.xml";
	
	/**
	 * Create a new folder
	 * 
	 * @param	string	$path
	 * 		The folder parent path
	 * 
	 * @param	string	$name
	 * 		The folder's name
	 * 
	 * @param	integer	$mode
	 * 		The linux file mode
	 * 
	 * @param	boolean	$recursive
	 * 		Allows the creation of nested directories specified in the pathname.
	 * 
	 * @return	boolean
	 * 		True on success, False on failure.
	 */
	public static function create($path, $name = "", $mode = 0777, $recursive = TRUE)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		// Create Directory
		$folderPath = $path."/".($name = "" ? "" : $name."/");
		
		// Collapse redundant slashes
		$folderPath = directory::normalize($folderPath);
		
		if (!is_dir($full_path))
			$status = mkdir($folderPath, $mode, $recursive);
		else
			return TRUE;
		
		// Log
		if (!$status)
			logger::log("Folder '".$folderPath."' failed to be created.", logger::ERROR, "", logger::DISK_W);
		
		return $status;
	}
	
	/**
	 * Removes a directory
	 * 
	 * @param	string	$path
	 * 		The folder's parent path
	 * 
	 * @param	string	$name
	 * 		The folder's name
	 * 
	 * @param	boolean	$recursive
	 * 		Remove all inner contents of the folder recursively.
	 * 
	 * @return	boolean
	 * 		True on success, False on failure.
	 */
	public static function remove($path, $name = "", $recursive = FALSE)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		// Remove Directory
		$directory = ($name = "" ? $path."/" : $path."/".$name."/");
		
		// Collapse redundant slashes
		$directory = directory::normalize($directory);
		
		// Remove inner contents recursively
		if ($recursive)
		{
			if (self::checkPermissions($directory) === FALSE)
				return FALSE;

			// Remove Directory
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory), RecursiveIteratorIterator::CHILD_FIRST);
			
			// Remove inner directories first
			foreach ($iterator as $p)
			{
				if ($p->isDir())
				{
					if (!preg_match("/\/\.+$/", $p->__toString()))
						rmdir($p->__toString());
				}
				else
				 	unlink($p->__toString());
			}
		}
		
		if (is_dir($directory))
			$status = rmdir($directory);
		else
		{
			logger::log("Folder '".$directory."' doesn't exist to be removed.", logger::NOTICE, "", logger::DISK_R);
			return FALSE;
		}
		
		if (!$status)
			logger::log("Folder '".$directory."' failed to be removed.", logger::ERROR, "", logger::DISK_W);
		
		return $status;
	}
	
	/**
	 * Empties a directory
	 * 
	 * @param	string	$path
	 * 		The folder's parent path, or the folder's path, if the name is omitted.
	 * 
	 * @param	string	$name
	 * 		The folder's name
	 * 
	 * @param	boolean	$includeHidden
	 * 		Whether to include hidden files and folders.
	 * 
	 * @return	boolean
	 * 		True on success, False on failure.
	 */
	public static function clean($path, $name = "", $includeHidden = TRUE)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		// Remove Directory
		$directory = (empty($name) ? $path."/" : $path."/".$name."/");
		
		// Collapse redundant slashes
		$directory = directory::normalize($directory);
		
		// Get contents
		$contents = directory::getContentList($directory, $includeHidden);
		
		foreach ((array)$contents['dirs'] as $dir)
			self::remove($dir, $name = "", $recursive = TRUE);
		
		foreach ((array)$contents['files'] as $dir)
			unlink($dir);
		
		return directory::isEmpty($directory);
	}
	
	/**
	 * Copy a folder (recursively)
	 * 
	 * @param	string	$source
	 * 		The source folder path.
	 * 
	 * @param	string	$destination
	 * 		The destination folder path.
	 * 
	 * @param	boolean	$contents_only
	 * 		Defines whether only the contents of the folder will be copied or the folder selected also.
	 * 
	 * @return	boolean
	 * 		True on success, False on failure.
	 */
	public static function copy($source, $destination, $contents_only = FALSE)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		$source = directory::normalize(trim($source)."/");
		$destination = directory::normalize(trim($destination)."/");
		
		// Copy to subfolder is not yet supported
		if (!strncmp($destination, $source, strlen($source)))
		{
			logger::log("Copy to subfolder is not yet supported. Aborting...", logger::ERROR);
			return FALSE;
		}
		
		// If source (or sometimes destination) are not dirs, return
		if (!is_dir($source) || ($contents_only && !is_dir($destination)))
		{
			logger::log("Source or destination are not directories. Aborting...", logger::ERROR, "", logger::DISK_R);
			return FALSE;
		}
		
		$selected = basename($source)."/";
		
		// Outermost Dir
		$outmostDir = ($contents_only ? $destination : $destination.$selected);
		if (!is_dir($outmostDir))
			self::create($outmostDir);
		
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
		
		$sourceCopy = $source;
		str_replace(dirname($source), "", $sourceCopy);
		foreach ($iterator as $path)
		{
			$innerPath = $path->__toString();
			$innerPath = str_replace($sourceCopy, "", $innerPath);
			if ($path->isDir())
				$status = @mkdir($outmostDir.$innerPath."/");
			else
				$status = copy($path->__toString(), $outmostDir.$innerPath);
		}
		
		return TRUE;
	}
	
	/**
	 * Move a folder (recursively).
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
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		$source = directory::normalize(trim($source)."/");
		$destination = directory::normalize(trim($destination)."/");
		
		// Check permissions
		if (self::checkPermissions($source) === FALSE)
				return FALSE;
		
		// Move to subfolder is not yet supported
		if (!strncmp($destination, $source, strlen($source)))
		{
			logger::log("Move to subfolder is not yet supported. Aborting...", logger::NOTICE);
			return FALSE;
		}
		
		$selected = basename($source)."/";
		
		$rename = !is_dir($destination);
		if ($rename)
			self::create($destination);
			
		self::copy($source, $destination, $contents_only = $rename);
		self::remove($source, $name = "", $recursive = TRUE);
		
		return TRUE;
	}
	
	/**
	 * Checks for write permissions in the given directory.
	 * 
	 * @param	string	$directory
	 * 		The folder path to check for permissions.
	 * 
	 * @return	boolean
	 * 		True if permissions exist, false otherwise.
	 */
	private static function checkPermissions($directory)
	{
		$parser = new DOMParser();
		$perms = "/System/Resources/SDK/".self::RM_PERMS;
		try
		{
			$parser->load($perms, TRUE);
		}
		catch (Exception $ex)
		{
			return FALSE;
		}
		
		$paths = $parser->evaluate("//folders/path");
		$qSystemRoot = preg_quote(systemRoot, "/");

		$permArray = array();
		foreach ($paths as $patt)
			$permArray[] = $qSystemRoot.$patt->nodeValue;

		$permExp = implode("|", $permArray);
		$directory = directory::normalize($directory."/");
		
		if (!is_dir($directory) || empty($permExp) || !preg_match("/(".$permExp.")/", $directory))
		{
			logger::log("Outer directory doesn't exist or has restricted permissions. Aborting...", logger::ERROR);
			return FALSE;
		}
	
		return TRUE;
	}
	
	/**
	 * Copy folder (recursively)
	 * 
	 * @param	string	$source
	 * 		The source folder path
	 * 
	 * @param	string	$destination
	 * 		The destination folder path
	 * 
	 * @param	boolean	$contents_only
	 * 		Defines whether only the contents of the folder will be copied or the folder selected also.
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	Use copy() instead.
	 */
	public static function copy_folder($source, $destination, $contents_only = FALSE)
	{
		return self::copy($source, $destination, $contents_only);
	}
	
	/**
	 * Move folder (recursively)
	 * 
	 * @param	string	$source
	 * 		The source folder path
	 * 
	 * @param	string	$destination
	 * 		The destination folder path
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	Use move() instead.
	 */
	public static function move_folder($source, $destination)
	{
		return self::move($source, $destination);
	}
	
	/**
	 * Remove a directory with its contents
	 * 
	 * @param	string	$directory
	 * 		The directory path
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	Use folderManager::remove() instead.
	 */
	public static function remove_full($directory)
	{
		return self::remove($directory, "", TRUE);
	}
	
	/**
	 * Returns all the contents of a folder in an array.
	 * ['dirs'] for directories
	 * ['files'] for files
	 * 
	 * @param	string	$directory
	 * 		The directory we are searching
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Resources\filesystem\directory::getContentList() instead.
	 */
	public static function get_contentList($directory)
	{
		return directory::getContentList($directory, TRUE);
	}
}
//#section_end#
?>