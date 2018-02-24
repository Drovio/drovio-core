<?php
//#section#[header]
// Namespace
namespace DEV\Apps\test;

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
 * @package	Apps
 * @namespace	\test
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("DEV", "Profiler", "tester");

use \DEV\Profiler\tester;

/**
 * Application Source Tester
 * 
 * Manages the application source testing.
 * 
 * @version	0.2-4
 * @created	April 7, 2014, 11:20 (EEST)
 * @updated	May 13, 2015, 17:52 (EEST)
 */
class sourceTester extends tester
{
	/**
	 * Activates the source tester mode for the given source packages.
	 * 
	 * @param	integer	$appID
	 * 		The application id for the source.
	 * 
	 * @param	mixed	$packages
	 * 		An array of source packages to be activated.
	 * 		You can choose "all" for all packages.
	 * 		The default value is "all".
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate($appID, $packages = "all")
	{
		if (empty($appID))
			return FALSE;
			
		if (empty($packages))
			return self::deactivate($appID);
		
		// Set New Package List
		if (is_array($packages))
		{
			$pkgList = implode(":", $packages);
			return parent::activate("appTester_app".$appID."_src", $pkgList);
		}
		else
			return parent::activate("appTester_app".$appID."_src", "all");
	}
	
	/**
	 * Deactivates the source tester mode for the application.
	 * 
	 * @param	integer	$appID
	 * 		The application id to deactivate the source tester mode for.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate($appID)
	{
		if (empty($appID))
			return FALSE;
			
		// Deactivate packages
		return parent::deactivate("appTester_app".$appID."_src");
	}
	
	/**
	 * Gets the tester status for a given app source package.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$package
	 * 		The source package to check the tester mode.
	 * 		You can choose "all" for all packages.
	 * 		The default value is "all".
	 * 
	 * @return	boolean
	 * 		True if package is on tester mode, false otherwise.
	 */
	public static function status($appID, $package = "all")
	{
		if (empty($appID))
			return FALSE;
		
		// Get status
		$status = parent::status("appTester_app".$appID."_src");
		
		if (empty($package) || $package == "all")
			return ($status == "all");
		
		// Get Packages
		$pkgList = self::getPackages($appID);

		// Return if exists
		return (in_array($package, $pkgList) || $status == "all");
	}
	
	/**
	 * Get all packages on tester mode.
	 * 
	 * @param	integer	$appID
	 * 		The application id to get the packages for.
	 * 
	 * @return	array
	 * 		An array of all active package names.
	 */
	private static function getPackages($appID)
	{
		// Get Package List
		$list = parent::status("appTester_app".$appID."_src");
		
		if (empty($list))
			return array();
		
		// Search if package exists
		return explode(":", $list);
	}
}
//#section_end#
?>