<?php
//#section#[header]
// Namespace
namespace API\Platform;

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
 * @package	Platform
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Core", "sdk::sdkPackage");
importer::import("DEV", "Profiler", "logger");

use \API\Resources\DOMParser;
use \DEV\Core\sdk\sdkPackage;
use \DEV\Profiler\logger;

/**
 * System Importer
 * 
 * This class is the main handler for importing all classes from the Red SDK.
 * It also provides functionality for the general include and require php functions along with the proper exception handling.
 * 
 * @version	{empty}
 * @created	March 27, 2013, 12:11 (EET)
 * @revised	July 7, 2014, 11:12 (EEST)
 */
class importer
{
	/**
	 * The core's repository path.
	 * 
	 * @type	string
	 */
	private static $vcsPath = "/.developer/Repository/p915f1513765cabf391430fbd5fe858c9.project/Source/trunk/master/SDK/";
	
	/**
	 * The object's inner class path.
	 * 
	 * @type	string
	 */
	private static $innerClassPath = ".object/src/class.php";
	
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
	 * The allowed core object list.
	 * 
	 * @type	array
	 */
	private static $allowedObjects = NULL;
	
	/**
	 * If TRUE, the importer loads only classes from the open package list.
	 * 
	 * @type	boolean
	 */
	private static $secure = FALSE;
	
	/**
	 * Set the security status for the importer.
	 * Enabling this option, the importer can import everything from the Red SDK.
	 * Otherwise it can import only the secure classes for Application Development.
	 * 
	 * @param	boolean	$status
	 * 		The secure option status. FALSE by default.
	 * 		
	 * 		NOTE: To Application Developers, if this option is set to TRUE once, it cannot be changed back.
	 * 		It set as TRUE for all Applications.
	 * 
	 * @return	boolean
	 * 		The option status.
	 */
	public static function secure($status = FALSE)
	{
		if (self::$secure === FALSE)
			self::$secure = $status;
		
		return self::$secure;
	}
	
	/**
	 * Import an SDK Object from the Red SDK library.
	 * 
	 * @param	string	$library
	 * 		The object's library.
	 * 
	 * @param	string	$package
	 * 		The object's package name.
	 * 
	 * @param	string	$class
	 * 		The full name of the class (including namespaces separated by "::")
	 * 
	 * @param	boolean	$strict
	 * 		This option enables the testing mode for the Red SDK.
	 * 		It is used only by Redback Core Developers.
	 * 		
	 * 		NOTE: To Application Developers, this option is not available for Applications.
	 * 
	 * @return	void
	 */
	public static function import($library, $package = "", $class = "", $strict = FALSE)
	{
		// Validate if importer is in secure mode
		if (self::$secure === TRUE)
			if (!self::validate($library, $package))
			{
				self::log("Package '".$library.":".$package."' is not allowed in secure mode.", logger::WARNING);
				return FALSE;
			}
			
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
			self::load($library, $package, $class);
		}
		
		// Set Class as Loaded
		self::setLoaded($library, $package, $class);
		
		// Flush Log messages
		self::flushLog();
	}
	
	/**
	 * Loads the SDK object.
	 * 
	 * @param	string	$library
	 * 		The object's library.
	 * 
	 * @param	string	$package
	 * 		The object's package.
	 * 
	 * @param	string	$class
	 * 		The full name of the class (including namespaces separated by "::")
	 * 
	 * @return	void
	 */
	private static function load($library, $package, $class)
	{
		// Get Path
		if (self::getTesterStatus($library, $package))
		{
			// Break classname
			$classParts = explode("::", $class);
			
			// Get Class Name
			$className = $classParts[count($classParts)-1];
			unset($classParts[count($classParts)-1]);
			
			// Get namespace
			$namespace = implode("/", $classParts);
			
			// Form nspath
			$nspath = self::$vcsPath.$library."/".$package."/".$namespace."/".$className.self::$innerClassPath;
		}
		else
			$nspath = systemSDK."/".$library."/".$package."/".str_replace("::", "/", $class).".php";

		// Require
		importer::req($nspath, TRUE, TRUE);
	}
	
	/**
	 * Validates if the given package is secure to be loaded when the importer is in secure mode.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @return	boolean
	 * 		True is package is allowed, false otherwise.
	 */
	private function validate($library, $package)
	{
		// Load core list
		if (empty(self::$allowedObjects))
			self::loadXMLList();

		// Check if exists in other libraries
		if (isset(self::$allowedObjects[$library][$package]))
			return TRUE;
	}
	
	/**
	 * Loads the secure list of Red SDK packages.
	 * 
	 * @return	void
	 */
	private static function loadXMLList()
	{
		$parser = new DOMParser();
		try
		{
			$parser->load("/System/Resources/SDK/privileges.xml");
		}
		catch (Exception $ex)
		{
			self::$allowedObjects = array();
			return;
		}

		// Get packages
		$packages = $parser->evaluate("//package");
		foreach ($packages as $package)
		{
			// Get library
			$library = $package->parentNode;
			
			// Set names
			$libraryName = $parser->attr($library, "name");
			$packageName = $parser->attr($package, "name");
			
			// Set core list
			self::$allowedObjects[$libraryName][$packageName] = 1;
		}
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
	 * 		True if loaded, false otherwise.
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
	 * Include a file from a given path.
	 * It only works in non-secure mode.
	 * 
	 * @param	string	$path
	 * 		The filepath to include.
	 * 
	 * @param	boolean	$root
	 * 		Indicator that defines whether the path will be normalized to system's root.
	 * 
	 * @param	boolean	$once
	 * 		Option to use the include_once or include function.
	 * 
	 * @return	mixed
	 * 		The file's return result data.
	 */
	public static function incl($path, $root = TRUE, $once = FALSE)
	{
		// Check security
		if (self::$secure === TRUE)
			return FALSE;
			
		// Normalize path
		$nspath = ($root ? systemRoot : "").$path;

		// Include File
		if (file_exists($nspath))
			return ($once ? include_once($nspath) : include($nspath));
		else
			self::log("File '".$path."' doesn't exist for inclusion...", logger::ERROR);
	}
	
	/**
	 * Require a file from a given path.
	 * It only works in non-secure mode.
	 * 
	 * @param	string	$path
	 * 		The filepath to require.
	 * 
	 * @param	boolean	$root
	 * 		Indicator that defines whether the path will be normalized to system's root.
	 * 
	 * @param	boolean	$once
	 * 		Option to use the require_once or require function.
	 * 
	 * @return	boolean
	 * 		The file's return result data.
	 * 
	 * @throws	Exception
	 */
	public static function req($path, $root = TRUE, $once = FALSE)
	{
		// Check security
		if (self::$secure === TRUE)
			return FALSE;
			
		// Normalize path
		$nspath = ($root ? systemRoot : "").$path;

		// Include File
		if (file_exists($nspath))
			return ($once ? require_once($nspath) : require($nspath));
		else
		{
			self::log("File '".$path."' doesn't exist to be imported. Throwing exception...", logger::ERROR);
			throw new Exception("File '".$path."' doesn't exist for inclusion...", 2);
		}
	}
	
	/**
	 * Returns whether the user has set the given package for testing.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	boolean
	 * 		True if package is in tester mode, false otherwise.
	 */
	private static function getTesterStatus($libName, $packageName)
	{
		return self::packageStatus($libName."_".$packageName);
	}
	
	/**
	 * Returns whether the user has set the given package for testing.
	 * The given package is in more compact format.
	 * 
	 * @param	string	$package
	 * 		The compact package name.
	 * 
	 * @return	boolean
	 * 		True if package is in tester mode, false otherwise.
	 */
	private static function packageStatus($package)
	{
		// Get Packages
		$pkgList = self::getTesterPackages();
		
		// Return if exists
		return (self::testerStatus() && in_array($package, $pkgList));
	}
	
	/**
	 * Get all tester packages.
	 * 
	 * @return	array
	 * 		An array of all packages in tester mode in compact format.
	 */
	private static function getTesterPackages()
	{
		// Get Package List
		$list = self::getCookie("sdkTester");
		
		// Search if package exists
		return explode(":", $list);
	}
	
	/**
	 * Returns the global tester's status.
	 * 
	 * @return	boolean
	 * 		True if tester mode is on, false otherwise.
	 */
	private static function testerStatus()
	{
		return (is_null(self::getCookie("sdkTester")) ? FALSE : self::getCookie("sdkTester"));
	}
	
	/**
	 * Get the value of a cookie with the given name.
	 * 
	 * @param	string	$name
	 * 		The cookie's name.
	 * 
	 * @return	mixed
	 * 		The cookie value or NULL if the cookie doesn't exist.
	 */
	private static function getCookie($name)
	{
		return (isset($_COOKIE[$name]) ? $_COOKIE[$name] : NULL);
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