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

// Import logger
importer::import("API", "Developer", "profiler::logger");
use \API\Developer\profiler\logger as loggr;
use \API\Developer\profiler\logger;
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
 * @revised	December 20, 2013, 17:57 (EET)
 */
class moduleTester extends tester
{
	/**
	 * Activates the tester mode for the given module.
	 * 
	 * @param	string	$moduleID
	 * 		The module id to activate. Leave empty for default value that includes all modules.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	public static function activate($moduleID = "all")
	{
		// See if package already exists
		$status = self::status($moduleID);
		if ($status)
			return TRUE;
			
		// Activate Package
		$mdlList = self::getModules();
		$mdlList[] = $moduleID;

		// Set Packages
		return self::setModules($mdlList);
	}
	
	/**
	 * Deactivates the tester mode for the given module.
	 * 
	 * @param	string	$moduleID
	 * 		The module id to deactivate. Leave empty to deactivate all modules.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	public static function deactivate($moduleID = "")
	{
		// If module id is empty, deactivate tester mode
		if (empty($moduleID))
			return self::setModules();
			
		// See if module already exists
		$status = self::status($moduleID);
		if (!$status)
			return TRUE;
			
		// Deactivate Module
		$mdlList = self::getModules();
		$key = array_search($moduleID, $mdlList);
		unset($mdlList[$key]);
		
		// Set Modules
		return self::setModules($mdlList);
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
	
	/**
	 * Set all the modules given for the tester mode.
	 * 
	 * @param	array	$modules
	 * 		An array of modules to be activated.
	 * 		If empty, the tester mode will be deactivated.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private static function setModules($modules = array())
	{
		// Choose whether to activate or deactivate
		if (!empty($modules))
		{
			// Set New Package List
			$mdlList = implode(":", $modules);
			return parent::activate("mdlTester", $mdlList);
		}
		else
			return parent::deactivate("mdlTester");
	}
}
//#section_end#
?>