<?php
//#section#[header]
// Namespace
namespace API\Security;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Security
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Internal Access Control
 * 
 * This is the proxy class which is responsible for letting the API execute internal functions and prevent others from executing closed functions and features.
 * 
 * @version	0.2-1
 * @created	March 8, 2013, 10:41 (EET)
 * @revised	August 24, 2014, 19:37 (EEST)
 */
class accessControl
{
	/**
	 * Check if the call was internal (SDK Library)
	 * 
	 * @param	integer	$level
	 * 		The level of the debug backtrace depth to check for the internal call.
	 * 		The default level is 2, for SDK functions.
	 * 
	 * @return	boolean
	 * 		True if the call is internal, false otherwise.
	 */
	public static function internalCall($level = 2)
	{
		// Red SDK Libraries
		$libs = array();
		$libs[] = "API";
		$libs[] = "UI";
		$libs[] = "ESS";
		$libs[] = "INU";
		$libs[] = "AEL";
		$libs[] = "DEV";
		$libs[] = "SYS";
		
		// Get the backtrace
		$trace = debug_backtrace();
		
		// Check if the call comes from one of the Red SDK Libraries
		foreach ($libs as $lib)
			if (self::checkClass($trace[$level], $lib."\\"))
				return TRUE;

		return FALSE;
	}
	
	/**
	 * Checks the trace given for the specified class.
	 * 
	 * @param	array	$trace
	 * 		The execution stack trace as given by the debug_backtrace() function.
	 * 
	 * @param	string	$class
	 * 		The class library which the call must abide.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	private function checkClass($trace, $class)
	{
		// Get Class called
		$class_called = $trace['class'];
		
		// Check is class is from core
		$internal = strpos($class_called, $class);
		return !($internal === FALSE) && $internal == 0;
	}
}
//#section_end#
?>