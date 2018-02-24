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
 * Ajax File Tester Manager
 * 
 * Manages ajax file tester mode.
 * 
 * @version	{empty}
 * @created	December 20, 2013, 16:43 (EET)
 * @revised	December 20, 2013, 17:01 (EET)
 */
class ajaxTester extends tester
{
	/**
	 * Activates the ajax tester mode.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate()
	{
		return parent::activate("ajxTester");
	}
	
	/**
	 * Deactivates the ajax tester mode.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate()
	{
		return parent::deactivate("ajxTester");
	}
	
	/**
	 * Gets the ajax tester status.
	 * 
	 * @return	boolean
	 * 		True if tester is on, false otherwise.
	 */
	public static function status()
	{
		return parent::status("ajxTester");
	}
}
//#section_end#
?>