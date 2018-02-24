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
 * Module Security Tester Manager
 * 
 * Manages the security testing for modules.
 * If enabled, the privileges will allow testing modules with access status 'user'.
 * 
 * @version	0.1-1
 * @created	April 22, 2015, 10:24 (EEST)
 * @updated	April 22, 2015, 10:24 (EEST)
 */
class moduleSecurityTester extends tester
{
	/**
	 * Activates the security tester mode.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	public static function activate()
	{
		return parent::activate("mdlsTester");
	}
	
	/**
	 * Deactivates the security tester mode.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	public static function deactivate()
	{
		// Deactivate modules
		return parent::deactivate("mdlsTester");
	}
	
	/**
	 * Gets the security tester mode.
	 * 
	 * @return	boolean
	 * 		True if mode is on, false otherwise.
	 */
	public static function status()
	{
		return parent::status("mdlsTester");
	}
}
//#section_end#
?>