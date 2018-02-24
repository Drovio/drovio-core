<?php
//#section#[header]
// Namespace
namespace API\Platform\loading;

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
 * @package	Platform
 * @namespace	\loading
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

/**
 * Class Loader
 * 
 * Loads SDK classes from repositories or from latest.
 * 
 * @version	{empty}
 * @created	April 2, 2013, 13:49 (EEST)
 * @revised	December 20, 2013, 19:14 (EET)
 */
class classLoader
{
	/**
	 * The repository path.
	 * 
	 * @type	string
	 */
	private static $vcsPath = "/.developer/Repository/Core/trunk/master/SDK/";
	/**
	 * The inner trunk's path.
	 * 
	 * @type	string
	 */
	private static $trunkPath = "/";
	/**
	 * The object's inner class path.
	 * 
	 * @type	string
	 */
	private static $innerClassPath = ".object/src/class.php";
	
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
	 * 		The full name (including namespace) of the object.
	 * 
	 * @return	void
	 */
	public static function load($library, $package, $class)
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
			$nspath = self::$vcsPath.$library."/".$package."/".$namespace.self::$trunkPath.$className.self::$innerClassPath;
		}
		else
			$nspath = systemSDK."/".$library."/".$package."/".str_replace("::", "/", $class).".php";

		// Require
		importer::req($nspath, TRUE, TRUE);
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
	 * 		{description}
	 */
	public static function getTesterStatus($libName, $packageName)
	{
		return self::packageStatus($libName."_".$packageName);
	}
	
	/**
	 * Returns whether the user has set the given package for testing.
	 * 
	 * @param	string	$package
	 * 		The merged package name.
	 * 
	 * @return	boolean
	 * 		{description}
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
	 * 		{description}
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
	 * 		{description}
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
	 * 		{description}
	 */
	private static function getCookie($name)
	{
		return (isset($_COOKIE[$name]) ? $_COOKIE[$name] : NULL);
	}
}
//#section_end#
?>