<?php
//#section#[header]
// Namespace
namespace DEV\Websites\test;

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
 * @package	Websites
 * @namespace	\test
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("DEV", "Profiler", "tester");

use \DEV\Profiler\tester;

/**
 * Website Source Tester
 * 
 * Manages the website source testing.
 * 
 * @version	0.1-1
 * @created	May 18, 2015, 16:39 (EEST)
 * @updated	May 18, 2015, 16:39 (EEST)
 */
class sourceTester extends tester
{
	/**
	 * Activates the website source tester mode for the given source packages.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @param	array	$packages
	 * 		An array of source packages to be activated.
	 * 		You can choose "all" for all packages.
	 * 		The default value is "all".
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate($websiteID, $packages = "all")
	{
		if (empty($websiteID))
			return FALSE;
			
		if (empty($packages))
			return self::deactivate($websiteID);
		
		// Set New Package List
		if (is_array($packages))
		{
			$pkgList = implode(":", $packages);
			return parent::activate("webTester_ws".$websiteID."_src", $pkgList);
		}
		else
			return parent::activate("webTester_ws".$websiteID."_src", "all");
	}
	
	/**
	 * Deactivates the website source tester.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate($websiteID)
	{
		if (empty($websiteID))
			return FALSE;
			
		// Deactivate packages
		return parent::deactivate("webTester_ws".$websiteID."_src");
	}
	
	/**
	 * Gets the tester status for a given website source package.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @param	string	$package
	 * 		The source package to check the tester mode.
	 * 		You can choose "all" for any package.
	 * 		The default value is "all".
	 * 
	 * @return	boolean
	 * 		True if package is on tester mode, false otherwise.
	 */
	public static function status($websiteID, $package = "all")
	{
		if (empty($websiteID))
			return FALSE;
		
		// Get status
		$status = parent::status("webTester_ws".$websiteID."_src");
		
		if (empty($package) || $package == "all")
			return ($status == "all");
		
		// Get Packages
		$pkgList = self::getPackages($websiteID);

		// Return if exists
		return (in_array($package, $pkgList) || $status == "all");
	}
	
	/**
	 * Gets the tester status for a given website source package.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
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
	public static function libPackageStatus($websiteID, $library, $package)
	{
		$packageName = $library."_".$package;
		return self::status($websiteID, $packageName);
	}
	
	/**
	 * Get all packages on tester mode.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @return	array
	 * 		An array of all active package.
	 */
	private static function getPackages($websiteID)
	{
		// Get Package List
		$list = parent::status("webTester_ws".$websiteID."_src");
		
		if (empty($list))
			return array();
		
		// Search if package exists
		return explode(":", $list);
	}
}
//#section_end#
?>