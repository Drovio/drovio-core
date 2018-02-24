<?php
//#section#[header]
// Namespace
namespace DEV\Profiler;

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
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "storage::cookies");

use \API\Resources\storage\cookies;

/**
 * Abstract Tester Profile
 * 
 * Manages all the testing configuration.
 * 
 * @version	{empty}
 * @created	February 10, 2014, 11:15 (EET)
 * @revised	February 10, 2014, 11:15 (EET)
 */
abstract class tester
{
	/**
	 * Activate the tester mode for the given mode.
	 * 
	 * @param	string	$name
	 * 		The tester mode name.
	 * 
	 * @param	mixed	$value
	 * 		The value to store as activated (True or another value).
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate($name = "", $value = TRUE)
	{
		if (!empty($name))
			return cookies::set($name, $value, $expiration = 0);
		
		return FALSE;
	}
	
	/**
	 * Deactivate the tester mode for the given mode.
	 * 
	 * @param	string	$name
	 * 		The tester mode name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate($name = "")
	{
		if (!empty($name))
			return cookies::set($name, FALSE, $expiration = -1);
		
		return FALSE;
	}
	
	/**
	 * Gets the tester mode status.
	 * 
	 * @param	string	$name
	 * 		The tester mode name.
	 * 
	 * @return	mixed
	 * 		Returns the tester mode status balu
	 */
	public static function status($name = "")
	{
		if (!empty($name))
			return (is_null(cookies::get($name)) ? FALSE : cookies::get($name));
		
		return FALSE;
	}
}
//#section_end#
?>