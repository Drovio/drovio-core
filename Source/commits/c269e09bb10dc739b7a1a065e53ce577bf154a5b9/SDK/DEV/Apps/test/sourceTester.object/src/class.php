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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Profiler", "tester");

use \DEV\Profiler\tester;

/**
 * Application Source Tester
 * 
 * Manages the application source tester.
 * 
 * @version	0.1-1
 * @created	April 7, 2014, 11:20 (EEST)
 * @revised	August 24, 2014, 19:56 (EEST)
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
			return self::deactivate();
		
		// Set New Package List
		if (is_array($packages))
		{
			$pkgList = implode(":", $packages);
			return parent::activate("appTester_src_id".$appID, $pkgList);
		}
		else
			return parent::activate("appTester_src_id".$appID, "all");
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
		return parent::deactivate("appTester_src_id".$appID);
	}
	
	/**
	 * Gets the tester status for a given app source package.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$package
	 * 		The source package to check the tester mode.
	 * 
	 * @return	boolean
	 * 		True if package is on tester mode, false otherwise.
	 */
	public static function status($appID, $package)
	{
		if (empty($appID))
			return FALSE;
		
		// Check if status is for all packages
		if (parent::status("appTester_src_id".$appID) == "all")
			return TRUE;
		
		// Get Packages
		$pkgList = self::getPackages();
		
		// Return if exists
		return (in_array($package, $pkgList));
	}
	
	/**
	 * Get all packages on tester mode.
	 * 
	 * @return	array
	 * 		An array of all active package names.
	 */
	private static function getPackages()
	{
		// Get Package List
		$list = parent::status("appTester_src_id".$appID);
		
		if (empty($list))
			return array();
		
		// Search if package exists
		return explode(":", $list);
	}
}
//#section_end#
?>