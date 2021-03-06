<?php
//#section#[header]
// Namespace
namespace DEV\Modules\test;

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
 * @package	Modules
 * @namespace	\test
 * 
 * @copyright	Copyright (C) 2015 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Modules", "modulesProject");
importer::import("DEV", "Profiler", "tester");

use \DEV\Modules\modulesProject;
use \DEV\Profiler\tester;

/**
 * Module Tester Manager
 * 
 * Manages module tester mode.
 * 
 * @version	0.1-2
 * @created	September 17, 2014, 11:01 (EEST)
 * @revised	January 1, 2015, 21:38 (EET)
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
		if (empty($modules))
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
	 * @param	integer	$moduleID
	 * 		The module id to activate. Leave empty for default value that includes all modules.
	 * 
	 * @return	boolean
	 * 		True if module is on tester mode, false otherwise.
	 */
	public static function status($moduleID = "all")
	{
		// Validate to project
		$mp = new modulesProject();
		if ($mp->validate())
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
		
		// Deactivate and return false
		self::deactivate();
		return FALSE;
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