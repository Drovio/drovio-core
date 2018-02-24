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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("DEV", "Profiler", "tester");

use \DEV\Profiler\tester;

/**
 * Modules Resource Tester Manager
 * 
 * Manages the testing mode for the resources.
 * 
 * @version	0.1-1
 * @created	May 27, 2015, 10:35 (EEST)
 * @updated	May 27, 2015, 10:35 (EEST)
 */
class mrsrcTester extends tester
{
	/**
	 * Activates the resource tester mode.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate()
	{
		return parent::activate("mrsrcTester");
	}
	
	/**
	 * Deactivates the resource tester mode.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate()
	{
		return parent::deactivate("mrsrcTester");
	}
	
	/**
	 * Gets the resource tester status.
	 * 
	 * @return	boolean
	 * 		True if activated, false otherwise.
	 */
	public static function status()
	{
		return parent::status("mrsrcTester");
	}
}
//#section_end#
?>