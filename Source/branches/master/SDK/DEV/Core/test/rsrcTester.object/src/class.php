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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("DEV", "Profiler", "tester");

use \DEV\Profiler\tester;

/**
 * Core Resource Tester Manager
 * 
 * Manages the testing mode for the resources.
 * 
 * @version	0.1-1
 * @created	May 27, 2015, 10:28 (EEST)
 * @updated	May 27, 2015, 10:28 (EEST)
 */
class rsrcTester extends tester
{
	/**
	 * Activates the resource tester mode.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate()
	{
		return parent::activate("rsrcTester");
	}
	
	/**
	 * Deactivates the resource tester mode.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate()
	{
		return parent::deactivate("rsrcTester");
	}
	
	/**
	 * Gets the resource tester status.
	 * 
	 * @return	boolean
	 * 		True if activated, false otherwise.
	 */
	public static function status()
	{
		return parent::status("rsrcTester");
	}
}
//#section_end#
?>