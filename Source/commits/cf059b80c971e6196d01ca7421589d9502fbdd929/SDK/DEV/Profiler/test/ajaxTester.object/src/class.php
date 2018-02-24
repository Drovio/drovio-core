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
 * Ajax File Tester Manager
 * 
 * Manages ajax file tester mode.
 * 
 * @version	{empty}
 * @created	February 10, 2014, 11:49 (EET)
 * @revised	February 10, 2014, 11:49 (EET)
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
	 * 		True on success, false on failure.
	 */
	public static function status()
	{
		return parent::status("ajxTester");
	}
	
	public static function resolve($url)
	{
		if (self::status())
		{
			// Get url
			$parts = explode("?", $url);
			$testUrl = $parts[0];
			$urlVars = $parts[1];
			
			// Add testing parameter
			$requestParameters = "__AJAX[path]=".$testUrl."&".$urlVars;
			
			// Return resolved url
			$url = "/ajax/tester.php?".$requestParameters;
		}
		
		return $url;
	}
}
//#section_end#
?>