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
 * Module Tester Manager
 * 
 * Manages module tester mode.
 * 
 * @version	{empty}
 * @created	December 20, 2013, 17:56 (EET)
 * @revised	December 22, 2013, 13:24 (EET)
 */
class moduleTester extends tester
{
	/**
	 * Activates the tester mode for the given modules.
	 * 
	 * @param	mixed	$modules
	 * 		An array of modules or empty for all modules.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	public static function activate($modules = "all")
	{
		if (empty($packages))
			return self::deactivate();
		
		// Set Modules for Testing
		if (is_array($modules))
		{
			$mdlList = implode(":", $modules);
			return parent::activate("mdlTester", $mdlList);
		}
		else
			return parent::activate("mdlTester", "all");
	}
	
	/**
	 * Deactivates the tester mode for the given module.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	public static function deactivate()
	{
		// Deactivate modules
		return parent::deactivate("mdlTester");
	}
	
	/**
	 * The tester status for a given module.
	 * 
	 * @param	string	$moduleID
	 * 		The module id to activate. Leave empty for default value that includes all modules.
	 * 
	 * @return	boolean
	 * 		True if module is on tester mode, false otherwise.
	 */
	public static function status($moduleID = "all")
	{
		// Get status
		$status = parent::status("mdlTester");
		
		if (empty($moduleID) || $moduleID == "all")
			return ($status == "all");
			
		// Get Modules
		$mdlList = self::getModules();
		
		// Check if module is in list or all modules are tester
		return (in_array($moduleID, $mdlList) || $status == "all");
	}
	
	/**
	 * Get all active modules.
	 * 
	 * @return	array
	 * 		An array of active modules.
	 * 		This array may be empty meaning that no module is active or all modules are active for tester mode.
	 */
	private static function getModules()
	{
		// Get Package List
		$list = parent::status("mdlTester");
		
		if (empty($list) || $list == "all")
			return array();
		
		// Search if package exists
		return explode(":", $list);
	}
}
//#section_end#
?>