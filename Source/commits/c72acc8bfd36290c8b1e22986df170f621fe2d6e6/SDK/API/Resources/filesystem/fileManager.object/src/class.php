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

// Import logger
importer::import("API", "Developer", "profiler::logger");
use \API\Developer\profiler\logger as loggr;
use \API\Developer\profiler\logger;
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Resources
 * @namespace	\filesystem
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::directory");
importer::import("API", "Security", "accessControl");

use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\directory;
use \API\Security\accessControl;

/**
 * File Manager
 * 
 * System's file manager.
 * 
 * @version	{empty}
 * @created	April 4, 2013, 11:04 (EEST)
 * @revised	September 21, 2013, 14:43 (EEST)
 */
class fileManager
{
	/**
	 * Creates a new text file
	 * 
	 * @param	string	$file
	 * 		The file path
	 * 
	 * @param	string	$contents
	 * 		The text file contents
	 * 
	 * @param	boolean	$recursive
	 * 		Indicates whether the file will create the path's folders if don't exist.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function create($file, $contents = "", $recursive = TRUE)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		$file = directory::normalize($file);
		
		// Create directories if don't exist
		if (!file_exists(dirname($file)))
		{
			if ($recursive)
				folderManager::create(dirname($file), "", 0777, TRUE);
			else
				return FALSE;
		}
			
		// Create File
		$status = self::put($file, $contents);
		
		if (!$status)
			loggr::log("File '".$file."' failed to be created.", loggr::WARNING);
		else
			loggr::log("File '".$file."' created.", loggr::INFO, "", loggr::DISK_W);
			
		return $status;
	}
	
	/**
	 * Remove a file
	 * 
	 * @param	string	$file
	 * 		The file path
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function remove($file)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		$file = directory::normalize($file);

		if (file_exists($file))
			$status = unlink($file);
		else
		{
			loggr::log("File '".$file."' doesn't exist to be removed.", loggr::NOTICE);
			$status = FALSE;
		}
		
		if (!$status)
			loggr::log("File '".$file."' failed to be removed.", loggr::WARNING);
		else
			loggr::log("File '".$file."' removed.", loggr::INFO, "", loggr::DISK_W);
		
		return $status;
	}
	
	/**
	 * Returns the contents of a given file.
	 * 
	 * @param	string	$file
	 * 		The file path.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function get($file)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;

		$file = directory::normalize($file);
		
		if (file_exists($file))
		{
			loggr::log("Reading file '".$file."'.", loggr::INFO, "", loggr::DISK_R);
			return file_get_contents($file);
		}
		else
			loggr::log("File '".$file."' doesn't exist to load contents...", loggr::ERROR);
	}
	
	/**
	 * Puts contents to the given file.
	 * 
	 * @param	string	$path
	 * 		The file path.
	 * 
	 * @param	string	$contents
	 * 		The contents to be written.
	 * 
	 * @return	boolean
	 * 		True on success (logs bytes written)
	 * 		False elsewhere
	 */
	public static function put($path, $contents = "")
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		// Normalize path
		$path = directory::normalize($path);
		
		// Log and write
		loggr::log("Writing file '".$file."'.", loggr::INFO, "", loggr::DISK_W);
		$saveFlag = file_put_contents($path, $contents);
		if (is_bool($saveFlag))
		{
			// Log error
			loggr::log("Error writing to '".$file."'.", loggr::ERROR);
			
			// Return FALSE flag
			return $saveFlag;
		}
		else
		{
			// Log file size
			loggr::log($saveFlag." Bytes written to '".$file."'.", loggr::INFO);
			
			// return true
			return TRUE;
		}
	}
	
	/**
	 * Copies a file.
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
	 * 		{description}
	 */
	public static function copy($from_file, $to_file, $preventOverwrite = FALSE)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		$from_file = directory::normalize($from_file);
		$to_file = directory::normalize($to_file);
		
		if ($preventOverwrite && file_exists($to_file))
			return FALSE;
		else if (file_exists($from_file))
		{
			loggr::log("Copying file '".$from_file."' to '".$to_file."'.", loggr::INFO, "", loggr::DISK_W);
			return copy($from_file, $to_file);
		}
		else
			loggr::log("File '".$from_file."' doesn't exist to be copied...", loggr::ERROR);
	}
	
	/**
	 * Moves a file
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
	 * 		{description}
	 */
	public static function move($from_file, $to_file, $preventOverwrite = FALSE)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		$from_file = directory::normalize($from_file);
		$to_file = directory::normalize($to_file);
		
		if ($preventOverwrite && file_exists($to_file))
			return FALSE;
		else if (file_exists($from_file))
		{
			loggr::log("Renaming file '".$from_file."' to '".$to_file."'.", loggr::INFO, "", loggr::DISK_W);
			return rename($from_file, $to_file);
		}
		else
			loggr::log("File '".$from_file."' doesn't exist to be moved.", loggr::ERROR);
	}
	
	/**
	 * Returns the contents of a text file.
	 * 
	 * @param	string	$file
	 * 		The file path
	 * 
	 * @return	string
	 * 		{description}
	 * 
	 * @deprecated	Use get() instead.
	 */
	public static function get_contents($file)
	{
		return self::get($file);
	}
	
	/**
	 * Writes contents to a text file.
	 * 
	 * @param	string	$path
	 * 		The file path
	 * 
	 * @param	string	$contents
	 * 		The file contents
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	Use put() instead.
	 */
	public static function put_contents($path, $contents)
	{
		return self::put($path, $contents);
	}
	
	/**
	 * Copy a file
	 * 
	 * @param	string	$from_file
	 * 		The source file path
	 * 
	 * @param	string	$to_file
	 * 		The destination file path
	 * 
	 * @param	boolean	$preventOverwrite
	 * 		If set to FALSE the destination file will be overwritten
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	Use copy() instead.
	 */
	public static function copy_file($from_file, $to_file, $preventOverwrite = FALSE)
	{
		return self::copy($from_file, $to_file, $preventOverwrite);
	}
	
	/**
	 * Move a file
	 * 
	 * @param	string	$from_file
	 * 		The source file path
	 * 
	 * @param	string	$to_file
	 * 		The destination file path
	 * 
	 * @param	boolean	$preventOverwrite
	 * 		If set to FALSE the destination file will be overwritten
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	Use move() instead.
	 */
	public static function move_file($from_file, $to_file, $preventOverwrite = FALSE)
	{
		return self::move($from_file, $to_file, $preventOverwrite);
	}
	
	/**
	 * Returns the size of a file, or FALSE in case of an error.
	 * 
	 * @param	string	$file
	 * 		The path to the file.
	 * 
	 * @param	boolean	$formated
	 * 		If set to TRUE, then the size will be formated.
	 * 
	 * @return	mixed
	 * 		The file size, formated [not in bytes] or not [in bytes], or FALSE in case of an error.
	 */
	public static function getSize($file, $formated = FALSE)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		if (!file_exists($file))
		{
			loggr::log("File '".$file."' doesn't exist...", loggr::ERROR);
			return FALSE;
		}
		
		$size = filesize($file);
		if ($formated)
			$size = self::formatBytes($size, $precision = 2);
		
		return $size;
	}
	
	/**
	 * Takes and formats a size in bytes.
	 * 
	 * @param	integer	$bytes
	 * 		The size in bytes
	 * 
	 * @param	integer	$precision
	 * 		The precision of the rounded sizes in digits.
	 * 
	 * @return	integer
	 * 		The size formated in the highest value possible.
	 */
	private function formatBytes($bytes, $precision = 2) { 
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