<?php
//#section#[header]
// Namespace
namespace API\Developer\profiler;

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
 * @package	Developer
 * @namespace	\profiler
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "profiler::tester");

use \API\Developer\profiler\tester;

/**
 * SDK Query Tester Manager
 * 
 * Manages sdk class tester mode.
 * 
 * @version	{empty}
 * @created	December 20, 2013, 17:34 (EET)
 * @revised	December 20, 2013, 17:47 (EET)
 */
class sdkTester extends tester
{
	/**
	 * Activates the sdk tester mode for the given sdk package
	 * 
	 * @param	string	$package
	 * 		The mixed package name to activate.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate($package)
	{
		// See if package already exists
		$status = self::status($package);
		if ($status)
			return TRUE;
			
		// Activate Package
		$pkgList = self::getPackages();
		$pkgList[] = $package;

		// Set Packages
		return self::setPackages($pkgList);
	}
	
	/**
	 * Deactivates the sdk tester mode for the given sdk package.
	 * 
	 * @param	string	$package
	 * 		The mixed package name to deactivate.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate($package = "")
	{
		// If package name is empty, deactivate tester mode
		if (empty($package))
			return self::setPackages();
			
		// See if package already exists
		$status = self::status($package);
		if (!$status)
			return TRUE;
			
		// Deactivate Package
		$pkgList = self::getPackages();
		$key = array_search($package, $pkgList);
		unset($pkgList[$key]);
		
		// Set Packages
		return self::setPackages($pkgList);
	}
	
	/**
	 * Gets the tester status for a given sdk package.
	 * 
	 * @param	string	$package
	 * 		The mixed package name to check.
	 * 
	 * @return	boolean
	 * 		True if package is on tester mode, false otherwise.
	 */
	public static function status($package = "")
	{
		if (empty($package))
			return parent::status("sdkTester");
			
		// Get Packages
		$pkgList = self::getPackages();
		
		// Return if exists
		return (in_array($package, $pkgList));
	}
	
	/**
	 * Gets the tester status for a given sdk package.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @return	boolean
	 * 		True if package is on tester mode, false otherwise.
	 */
	public static function libPackageStatus($library, $package)
	{
		$packageName = $library."_".$package;
		return self::status($packageName);
	}
	
	/**
	 * Get all packages on tester mode.
	 * 
	 * @return	array
	 * 		An array of all active mixed package names.
	 */
	private static function getPackages()
	{
		// Get Package List
		$list = parent::status("sdkTester");
		
		if (empty($list))
			return array();
		
		// Search if package exists
		return explode(":", $list);
	}
	
	/**
	 * Set all the packages for the tester mode.
	 * 
	 * @param	array	$packages
	 * 		An array of all mixed package names or empty for deactivation.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	private static function setPackages($packages = array())
	{
		// Choose whether to activate or deactivate
		if (!empty($packages))
		{
			// Set New Package List
			$pkgList = implode(":", $packages);
			return parent::activate("sdkTester", $pkgList);
		}
		else
			return parent::deactivate("sdkTester");
	}
}
//#section_end#
?>