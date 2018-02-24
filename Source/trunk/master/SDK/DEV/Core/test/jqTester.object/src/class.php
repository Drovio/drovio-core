<?php
//#section#[header]
// Namespace
namespace DEV\Core\test;

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
 * @package	Core
 * @namespace	\test
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("DEV", "Profiler", "tester");

use \DEV\Profiler\tester;

/**
 * jQUery Tester Manager
 * 
 * Manages jQuery version tester mode.
 * 
 * @version	0.1-1
 * @created	September 13, 2015, 15:44 (EEST)
 * @updated	September 13, 2015, 15:44 (EEST)
 */
class jqTester extends tester
{
	/**
	 * Activates the jQuery tester mode in the given version (filename).
	 * 
	 * @param	string	$file
	 * 		The jQuery filename inside the cdn library: CDN/js/jquery/
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate($file = "")
	{
		// If file is empty, deactivate tester status
		if (empty($file))
			return self::deactivate();
		
		// Activate jQuery tester to the given file
		return parent::activate("jqTester", $file);
	}
	
	/**
	 * Deactivates the jQuery tester mode.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate()
	{
		// Deactivate packages
		return parent::deactivate("jqTester");
	}
	
	/**
	 * Get the status for the jQuery tester.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function status()
	{
		// Get current status
		$status = parent::status("jqTester");
		if (empty($status))
		{
			// Deactivate and return FALSE
			self::deactivate();
			return FALSE;
		}
		
		// Return the filename of the jquery script
		return $status;
	}
}
//#section_end#
?>