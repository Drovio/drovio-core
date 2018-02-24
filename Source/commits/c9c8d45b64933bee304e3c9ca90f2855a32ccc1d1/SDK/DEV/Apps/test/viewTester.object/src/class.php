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
 * Application View Tester
 * 
 * Manages the application view testing.
 * 
 * @version	0.1-4
 * @created	August 25, 2014, 11:50 (EEST)
 * @updated	May 13, 2015, 17:52 (EEST)
 */
class viewTester extends tester
{
	/**
	 * Activates the view tester mode for the given source views.
	 * 
	 * @param	integer	$appID
	 * 		The application id for the views.
	 * 
	 * @param	mixed	$views
	 * 		An array of app views to be activated.
	 * 		You can choose "all" for all views.
	 * 		The default value is "all".
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate($appID, $views = "all")
	{
		if (empty($appID))
			return FALSE;
			
		if (empty($views))
			return self::deactivate($appID);
		
		// Set New Package List
		if (is_array($views))
		{
			$viewList = implode(":", $views);
			return parent::activate("appTester_app".$appID."_v", $viewList);
		}
		else
			return parent::activate("appTester_app".$appID."_v", "all");
	}
	
	/**
	 * Deactivates the view tester mode for the application.
	 * 
	 * @param	integer	$appID
	 * 		The application id to deactivate the view tester mode for.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate($appID)
	{
		if (empty($appID))
			return FALSE;
			
		// Deactivate packages
		return parent::deactivate("appTester_app".$appID."_v");
	}
	
	/**
	 * Gets the tester status for a given app view.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$view
	 * 		The view to check the tester mode.
	 * 		You can choose "all" for all views.
	 * 		The default value is "all".
	 * 
	 * @return	boolean
	 * 		True if view is on tester mode, false otherwise.
	 */
	public static function status($appID, $view = "all")
	{
		if (empty($appID))
			return FALSE;
		
		// Get status
		$status = parent::status("appTester_app".$appID."_v");
		
		if (empty($view) || $view == "all")
			return ($status == "all");
		
		// Get Packages
		$viewsList = self::getViews($appID);
		
		// Return if exists
		return (in_array($view, $viewsList) || $status == "all");
	}
	
	/**
	 * Get all views on tester mode.
	 * 
	 * @param	integer	$appID
	 * 		The application id to get the views for.
	 * 
	 * @return	array
	 * 		An array of all active view names.
	 */
	private static function getViews($appID)
	{
		// Get Package List
		$list = parent::status("appTester_app".$appID."_v");
		
		if (empty($list))
			return array();
		
		// Search if package exists
		return explode(":", $list);
	}
}
//#section_end#
?>