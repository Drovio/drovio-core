<?php
//#section#[header]
// Namespace
namespace API\Developer\profiler;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\profiler
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Profile", "tester");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "storage::cookies");

use \API\Profile\tester as profileTester;
use \API\Resources\filesystem\folderManager;
use \API\Resources\storage\cookies;

/**
 * Tester Profile
 * 
 * Manages all the testing configuration.
 * 
 * @version	{empty}
 * @created	March 28, 2013, 13:45 (EET)
 * @revised	August 9, 2013, 15:15 (EEST)
 */
class tester
{
	/**
	 * Activate the tester mode.
	 * 
	 * @return	void
	 */
	public static function activate()
	{
		// Activate testing server
		cookies::set("tester", TRUE, $expiration = 0);
	}
	
	/**
	 * Deactivate the tester mode.
	 * 
	 * @return	void
	 */
	public static function deactivate()
	{
		cookies::set("tester", FALSE, $expiration = 0);
	}
	
	/**
	 * Get the tester mode status.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function status()
	{
		return (is_null(cookies::get("tester")) ? FALSE : cookies::get("tester"));
	}
	
	/**
	 * Activate the tester mode for a given package.
	 * 
	 * @param	string	$package
	 * 		The package's name.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function activatePackage($package)
	{
		// Check for active testing server
		if (!self::status())
			return FALSE;
			
		// See if package already exists
		$status = self::packageStatus($package);
		if ($status)
			return TRUE;
			
		// Activate Package
		$pkgList = self::getPackages();
		$pkgList[] = $package;

		// Set Packages
		return self::setPackages($pkgList);
	}
	
	/**
	 * Deactivate the tester mode for a given package.
	 * 
	 * @param	string	$package
	 * 		The package's name.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function deactivatePackage($package)
	{
		// Check for active testing server
		if (!self::status())
			return FALSE;
			
		// See if package already exists
		$status = self::packageStatus($package);
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
	 * Get the tester mode status for a given package.
	 * 
	 * @param	string	$package
	 * 		The package's name.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function packageStatus($package)
	{
		// Get Packages
		$pkgList = self::getPackages();
		
		// Return if exists
		return (self::status() && in_array($package, $pkgList));
	}
	
	/**
	 * Get all the active packages for the tester mode.
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function getPackages()
	{
		// Get Package List
		$list = cookies::get("sdkList");
		
		if (empty($list))
			return array();
		
		// Search if package exists
		return explode(":", $list);
	}
	
	/**
	 * Set all the active packages for the tester mode.
	 * 
	 * @param	array	$packages
	 * 		The list of packages to be set.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function setPackages($packages = array())
	{
		// Set New Package List
		$pkgList = implode(":", $packages);
		return cookies::set("sdkList", $pkgList, $expiration = 0);
	}
	
	/**
	 * Gets the user's trunk folder file. This is used for file editing testing.
	 * 
	 * @return	string
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Profile\tester::getTrunk() instead.
	 */
	public static function getTrunk()
	{
		return profileTester::getTrunk();
		
	}
	
	/**
	 * Activate the tester mode for modules.
	 * 
	 * @return	void
	 */
	public static function activateModules()
	{
		cookies::set("mdlTester", TRUE);
	}
	
	/**
	 * Deactivate the tester mode for modules.
	 * 
	 * @return	void
	 */
	public static function deactivateModules()
	{
		cookies::delete("mdlTester");
	}
	
	/**
	 * Gets the module tester status.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function ModulesStatus()
	{
		return (self::status() && (is_null(cookies::get("mdlTester")) ? FALSE : cookies::get("mdlTester")));
	}
	
	/**
	 * Activate the tester mode for SQL.
	 * 
	 * @return	void
	 */
	public static function activateSQL()
	{
		cookies::set("sqlTester", TRUE);
	}
	
	/**
	 * Deactivate the tester mode for SQL.
	 * 
	 * @return	void
	 */
	public static function deactivateSQL()
	{
		cookies::delete("sqlTester");
	}
	
	/**
	 * Gets the SQL tester status.
	 * 
	 * @return	void
	 */
	public static function SQLStatus()
	{
		return (self::status() && (is_null(cookies::get("sqlTester")) ? FALSE : cookies::get("sqlTester")));
	}
	
	/**
	 * Activate the tester mode for widgets.
	 * 
	 * @return	void
	 */
	public static function activateWidgets()
	{
		cookies::set("wdgTester", TRUE);
	}
	
	/**
	 * Dectivate the tester mode for widgets.
	 * 
	 * @return	void
	 */
	public static function deactivateWidgets()
	{
		cookies::delete("wdgTester");
	}
	
	/**
	 * Gets the widget tester status.
	 * 
	 * @return	void
	 */
	public static function WidgetsStatus()
	{
		return (self::status() && (is_null(cookies::get("wdgTester")) ? FALSE : cookies::get("wdgTester")));
	}
}
//#section_end#
?>