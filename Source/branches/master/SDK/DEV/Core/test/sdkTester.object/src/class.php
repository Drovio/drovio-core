<?php
//#section#[header]
// Namespace
namespace DEV\Core\test;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Core
 * @namespace	\test
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("DEV", "Profiler", "tester");

use \DEV\Profiler\tester;

/**
 * SDK Package Tester Manager
 * 
 * Manages sdk packages tester mode.
 * 
 * @version	0.1-4
 * @created	September 17, 2014, 10:54 (EEST)
 * @updated	January 2, 2015, 14:45 (EET)
 */
class sdkTester extends tester
{
	/**
	 * Activates the sdk tester mode for the given sdk package.
	 * 
	 * @param	array	$packages
	 * 		An array of all packages with the mixed package name or empty ("all") for all packages.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate($packages = "all")
	{
		if (empty($packages))
			return self::deactivate();
		
		// Set New Package List
		if (is_array($packages))
		{
			$pkgList = implode(":", $packages);
			return parent::activate("sdkTester", $pkgList);
		}
		else
			return parent::activate("sdkTester", "all");
	}
	
	/**
	 * Deactivates the sdk tester mode for the given sdk package.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate()
	{
		// Deactivate packages
		return parent::deactivate("sdkTester");
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
	public static function status($package)
	{
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
}
//#section_end#
?>