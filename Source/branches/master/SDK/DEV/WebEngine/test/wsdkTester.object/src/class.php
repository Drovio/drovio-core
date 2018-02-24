<?php
//#section#[header]
// Namespace
namespace DEV\WebEngine\test;

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
 * @package	WebEngine
 * @namespace	\test
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("DEV", "Profiler", "tester");

use \DEV\Profiler\tester;

/**
 * Web Engine Core SDK Package Tester Manager
 * 
 * Manages web core sdk packages tester mode.
 * 
 * @version	0.1-1
 * @created	May 18, 2015, 16:47 (EEST)
 * @updated	May 18, 2015, 16:47 (EEST)
 */
class wsdkTester extends tester
{
	/**
	 * Activates the sdk tester mode for the given web core sdk package.
	 * 
	 * @param	string	$packages
	 * 		An array of all packages with the mixed package name or empty ("all") for all packages.
	 * 		It is "all" by default.
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
			return parent::activate("wsdkTester", $pkgList);
		}
		else
			return parent::activate("wsdkTester", "all");
	}
	
	/**
	 * Dectivates the web core sdk tester mode.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate()
	{
		// Deactivate packages
		return parent::deactivate("wsdkTester");
	}
	
	/**
	 * Gets the tester status for a given web core sdk package.
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
	 * Gets the tester status for a given web core sdk package.
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
	 * Get all web core sdk packages on tester mode.
	 * 
	 * @return	array
	 * 		An array of all active mixed package names.
	 */
	private static function getPackages()
	{
		// Get Package List
		$list = parent::status("wsdkTester");
		
		if (empty($list))
			return array();
		
		// Search if package exists
		return explode(":", $list);
	}
}
//#section_end#
?>