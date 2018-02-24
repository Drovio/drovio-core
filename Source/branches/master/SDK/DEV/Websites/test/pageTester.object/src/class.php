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
 * Website Page Tester
 * 
 * Manages the website page testing.
 * 
 * @version	0.1-1
 * @created	May 18, 2015, 16:39 (EEST)
 * @updated	May 18, 2015, 16:39 (EEST)
 */
class pageTester extends tester
{
	/**
	 * Activates the page tester mode for the given pages.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @param	rray	$pages
	 * 		An array of page paths to be activated.
	 * 		You can choose "all" for all pages.
	 * 		The default value is "all".
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate($websiteID, $pages = "all")
	{
		if (empty($websiteID))
			return FALSE;
			
		if (empty($pages))
			return self::deactivate($websiteID);
		
		// Set New Package List
		if (is_array($pages))
		{
			$pageList = implode(":", $pages);
			return parent::activate("webTester_ws".$websiteID."_p", $pageList);
		}
		else
			return parent::activate("webTester_ws".$websiteID."_p", "all");
	}
	
	/**
	 * Deactivates the page tester mode for the given pages.
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
		return parent::deactivate("webTester_ws".$websiteID."_p");
	}
	
	/**
	 * Gets the tester status for a given website page.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @param	string	$page
	 * 		The page path to check the tester mode.
	 * 		You can choose "all" for any page.
	 * 		The default value is "all".
	 * 
	 * @return	boolean
	 * 		True if view is on tester mode, false otherwise.
	 */
	public static function status($websiteID, $page = "all")
	{
		if (empty($websiteID))
			return FALSE;
		
		// Get status
		$status = parent::status("webTester_ws".$websiteID."_p");
		
		if (empty($view) || $view == "all")
			return ($status == "all");
		
		// Get Packages
		$pageList = self::getPages($websiteID);
		
		// Return if exists
		return (in_array($page, $pageList) || $status == "all");
	}
	
	/**
	 * Get all pages on tester mode.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @return	array
	 * 		An array of all active page paths.
	 */
	private static function getPages($websiteID)
	{
		// Get Package List
		$list = parent::status("webTester_ws".$websiteID."_p");
		
		if (empty($list))
			return array();
		
		// Search if package exists
		return explode(":", $list);
	}
}
//#section_end#
?>