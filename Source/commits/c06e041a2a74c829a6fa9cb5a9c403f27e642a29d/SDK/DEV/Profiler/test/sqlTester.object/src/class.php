<?php
//#section#[header]
// Namespace
namespace DEV\Profiler\test;

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
 * @package	Profiler
 * @namespace	\test
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Profiler", "tester");

use \DEV\Profiler\tester;

/**
 * SQL Query Tester Manager
 * 
 * Manages sql query tester mode.
 * 
 * @version	{empty}
 * @created	February 10, 2014, 11:54 (EET)
 * @revised	February 10, 2014, 11:54 (EET)
 */
class sqlTester extends tester
{
	/**
	 * Activates the sql tester mode.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate()
	{
		return parent::activate("sqlTester");
	}
	
	/**
	 * Deactivates the sql tester mode.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate()
	{
		return parent::deactivate("sqlTester");
	}
	
	/**
	 * Gets the sql tester status.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function status()
	{
		return parent::status("sqlTester");
	}
}
//#section_end#
?>