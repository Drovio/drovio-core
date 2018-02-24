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

importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::directory");
importer::import("API", "Platform", "accessControl");
importer::import("DEV", "Profiler", "logger");

use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\directory;
use \API\Platform\accessControl;
use \DEV\Profiler\logger;

/**
 * File Manager
 * 
 * System's file manager.
 * 
 * @version	1.1-2
 * @created	April 4, 2013, 11:04 (EEST)
 * @revised	December 1, 2014, 10:58 (EET)
 */
class fileManager
{
	/**
	 * Creates a new text file.
	 * This function has access control, and it can be executed only from the SDK.
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
			logger::log("File '".$file."' failed to be created.", logger::WARNING);
			
		return $status;
	}
	
	/**
	 * Remove a file.
	 * This function has access control, and it can be executed only from the SDK.
	 * 
	 * @param	string	$file
	 * 		The file path
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
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
			logger::log("File '".$file."' doesn't exist to be removed.", logger::NOTICE);
			$status = FALSE;
		}
		
		if (!$status)
			logger::log("File '".$file."' failed to be removed.", logger::WARNING);
		
		return $status;
	}
	
	/**
	 * Returns the contents of a given file.
	 * 
	 * @param	string	$file
	 * 		The file path.
	 * 
	 * @return	string
	 * 		The file contents or NULL if the file does not exist.
	 */
	public static function get($file)
	{
		$file = directory::normalize($file);
		if (file_exists($file))
			return file_get_contents($file);
		else
		{
			logger::log("File '".$file."' doesn't exist to load contents...", logger::ERROR);
			return NULL;
		}
	}
	
	/**
	 * Puts contents to the given file.
	 * This function has access control, and it can be executed only from the SDK.
	 * 
	 * @param	string	$path
	 * 		The file path.
	 * 
	 * @param	string	$contents
	 * 		The contents to be written.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function put($path, $contents = "")
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		// Normalize path
		$path = directory::normalize($path);
		
		// Log and write
		$saveFlag = file_put_contents($path, $contents);
		if (is_bool($saveFlag))
		{
			// Log error
			logger::log("Error writing to '".$file."'.", logger::ERROR);
			
			// Return FALSE flag
			return $saveFlag;
		}
		else
			return TRUE;
	}
	
	/**
	 * Copies a file.
	 * This function has access control, and it can be executed only from the SDK.
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
			return copy($from_file, $to_file);
		else
			logger::log("File '".$from_file."' doesn't exist to be copied...", logger::ERROR);
	}
	
	/**
	 * Moves a file.
	 * This function has access control, and it can be executed only from the SDK.
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
			return rename($from_file, $to_file);
		else
			logger::log("File '".$from_file."' doesn't exist to be moved.", logger::ERROR);
	}
	
	/**
	 * Returns the size of a file, or FALSE in case of an error.
	 * 
	 * @param	string	$file
	 * 		The path to the file.
	 * 
	 * @param	boolean	$formated
	 * 		If set to TRUE, then the size will be formatted.
	 * 		It is FALSE by default.
	 * 
	 * @return	mixed
	 * 		The file size, formated [not in bytes] or not [in bytes], or FALSE in case of an error.
	 */
	public static function getSize($file, $formated = FALSE)
	{
		if (!file_exists($file))
		{
			logger::log("File '".$file."' doesn't exist...", logger::ERROR);
			return FALSE;
		}
		
		$size = filesize($file);
		if ($formated)
			$size = self::formatBytes($size, $precision = 2);
		
		return $size;
	}
	
	/**
	 * Check if the given name contains one of the following characters and thus it is not valid
	 * Char list: \ / ? % * : | " < >
	 * 
	 * @param	string	$name
	 * 		The file name to check
	 * 
	 * @return	boolean
	 * 		TRUE if the name is valid FALSE if it is invalid
	 * 
	 * @deprecated	Use \API\Resources\filesystem\directory::validate() instead.
	 */
	public static function validateName($name)
	{
		return directory::validate($name);
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
	private static function formatBytes($bytes, $precision = 2)
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