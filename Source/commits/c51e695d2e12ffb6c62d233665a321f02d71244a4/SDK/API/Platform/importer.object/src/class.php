<?php
//#section#[header]
// Namespace
namespace API\Platform;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Platform
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "loading::classLoader", TRUE);
use \API\Platform\loading\classLoader;

importer::import("API", "Developer", "profiler::logger");
importer::import("API", "Developer", "components::sdk::sdkPackage");

use \API\Developer\profiler\logger;
use \API\Developer\components\sdk\sdkPackage;

/**
 * System Importer
 * 
 * It is used to import all files in the system.
 * Classes and resources.
 * 
 * @version	{empty}
 * @created	March 27, 2013, 12:11 (EET)
 * @revised	May 6, 2013, 12:26 (EEST)
 */
class importer
{
	/**
	 * Log messages
	 * 
	 * @type	array
	 */
	private static $log = array();
	
	/**
	 * Loaded classes
	 * 
	 * @type	array
	 */
	private static $loaded = array();
	
	/**
	 * Import an SDK Object from the given library and package.
	 * 
	 * @param	string	$library
	 * 		The library name
	 * 
	 * @param	string	$package
	 * 		The package name
	 * 
	 * @param	string	$class
	 * 		The full name of the class (including namespaces separated by "::")
	 * 
	 * @param	boolean	$strict
	 * 		Indicates whether the object will be forced to load from latest.
	 * 
	 * @return	void
	 */
	public static function import($library, $package = "", $class = "", $strict = FALSE)
	{
		// Load Entire Package
		if ($class == "")
		{
			$sdkPkg = new sdkPackage();
			return $sdkPkg->load($library, $package);
		}
		
		// Check if the class is already loaded
		if (self::checkLoaded($library, $package, $class))
			return;
			
		// Force loading from latest
		if ($strict)
		{
			$nspath = systemSDK."/".$library."/".$package."/".str_replace("::", "/", $class).".php";
			self::req($nspath, TRUE, TRUE);
		}
		else
		{
			// Load Class
			classLoader::load($library, $package, $class);
		}
		
		// Set Class as Loaded
		self::setLoaded($library, $package, $class);
		
		// Flush Log messages
		self::flushLog();
	}
	
	/**
	 * Checks if a class has already been loaded.
	 * 
	 * @param	string	$library
	 * 		The object's library name.
	 * 
	 * @param	string	$package
	 * 		The object's package name.
	 * 
	 * @param	string	$class
	 * 		The object's full name (including namespace).
	 * 
	 * @return	boolean
	 */
	private static function checkLoaded($library, $package, $class)
	{
		$fullName = $library."::".$package."::".$class;
		return in_array($fullName, self::$loaded);
	}
	
	/**
	 * Sets a object as loaded.
	 * 
	 * @param	string	$library
	 * 		The object's library name.
	 * 
	 * @param	string	$package
	 * 		The object's package name.
	 * 
	 * @param	string	$class
	 * 		The object's full name (including namespace).
	 * 
	 * @return	void
	 */
	private static function setLoaded($library, $package, $class)
	{
		$fullName = $library."::".$package."::".$class;
		self::$loaded[] = $fullName;
	}
	
	/**
	 * Include file (doesn't throw exception...)
	 * 
	 * @param	string	$path
	 * 		The filepath
	 * 
	 * @param	boolean	$root
	 * 		Indicator that defines whether the path will be normalized to system's root
	 * 
	 * @param	boolean	$once
	 * 		Include once or not
	 * 
	 * @return	boolean
	 */
	public static function incl($path, $root = TRUE, $once = FALSE)
	{
		// Normalize path
		$nspath = ($root ? systemRoot : "").$path;

		// Include File
		if (file_exists($nspath))
			return ($once ? include_once($nspath) : include($nspath));
		else
			self::log("File '".$path."' doesn't exist for inclusion...");
	}
	
	/**
	 * Require file (throws exception...)
	 * 
	 * @param	string	$path
	 * 		The filepath
	 * 
	 * @param	boolean	$root
	 * 		Indicator that defines whether the path will be normalized to system's root
	 * 
	 * @param	boolean	$once
	 * 		Require once or not
	 * 
	 * @return	boolean
	 */
	public static function req($path, $root = TRUE, $once = FALSE)
	{
		// Normalize path
		$nspath = ($root ? systemRoot : "").$path;

		// Include File
		if (file_exists($nspath))
			return ($once ? require_once($nspath) : require($nspath));
		else
		{
			self::log("File '".$path."' doesn't exist to be imported. Throwing exception...");
			throw new Exception("File '".$path."' doesn't exist for inclusion...", 2);
		}
	}
	
	/**
	 * Logs messages temporarily and then flush to logger
	 * 
	 * @param	string	$message
	 * 		The message log
	 * 
	 * @return	void
	 */
	private static function log($message)
	{
		self::$log[] = $message;
	}
	
	/**
	 * Flushes the logs to the logger
	 * 
	 * @return	void
	 */
	private static function flushLog()
	{
		foreach (self::$log as $logMessage)
			logger::log($logMessage);
		
		// Empty log array
		self::$log = array();
	}
}
//#section_end#
?>