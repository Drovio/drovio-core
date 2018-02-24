<?php
//#section#[header]
// Namespace
namespace API\Platform;

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
 * @package	Platform
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Profiler", "logger");

use \DEV\Profiler\logger;

/**
 * Internal Access Control
 * 
 * This is the proxy class which is responsible for letting the API execute internal functions and prevent others from executing closed functions and features.
 * 
 * It has no effect to developers. It just gives valuable (not sensitive) information for controlling the flow and the hierarchy of execution.
 * 
 * @version	0.1-2
 * @created	November 5, 2014, 15:17 (EET)
 * @revised	November 5, 2014, 15:18 (EET)
 */
class accessControl
{
	/**
	 * Check if the call was internal (any SDK Library).
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
		$libs[] = "BSS";
		
		// Get the backtrace
		$trace = debug_backtrace();
		
		// Check if the call comes from one of the Red SDK Libraries
		foreach ($libs as $lib)
			if (self::checkClass($trace[$level], $lib."\\"))
				return TRUE;

		// Log failure
		logger::log("Internal call access is denied from level $level.", logger::WARNING, $trace);
		return FALSE;
	}
	
	/**
	 * Checks whether a given class is in the given trace slice.
	 * 
	 * @param	array	$trace
	 * 		The execution stack trace slice as given by the debug_backtrace() function.
	 * 
	 * @param	string	$class
	 * 		The class which the call must abide.
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