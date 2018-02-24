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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
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
 * @version	0.3-2
 * @created	November 5, 2014, 15:17 (EET)
 * @updated	May 4, 2015, 19:55 (EEST)
 */
class accessControl
{
	/**
	 * The class type internal check.
	 * 
	 * @type	integer
	 */
	const CLASS_CHECK = 1;
	
	/**
	 * The arguments type internal check.
	 * 
	 * @type	integer
	 */
	const ARGS_CHECK = 2;
	
	/**
	 * Check if the call was internal (any SDK Library).
	 * 
	 * @param	integer	$level
	 * 		The level of the debug backtrace depth to check for the internal call.
	 * 		The default level is 2, for SDK functions.
	 * 
	 * @param	integer	$type
	 * 		The type of check to perform in the call trace to identify whether is an internal call or not.
	 * 		Use class constants.
	 * 		Default value is CLASS_CHECK.
	 * 
	 * @return	boolean
	 * 		True if the call is internal, false otherwise.
	 */
	public static function internalCall($level = 2, $type = self::CLASS_CHECK)
	{
		// Red SDK Libraries
		// This list is hard coded to avoid circular reference
		$libs = array();
		$libs[] = "AEL";
		$libs[] = "API";
		$libs[] = "BSS";
		$libs[] = "DEV";
		$libs[] = "ESS";
		$libs[] = "GTL";
		$libs[] = "INU";
		$libs[] = "RTL";
		$libs[] = "SYS";
		$libs[] = "UI";
		
		// Get the backtrace
		$trace = debug_backtrace();
		
		// Check if the call comes from one of the Red SDK Libraries
		foreach ($libs as $lib)
			if (self::checkClass($trace, $level, $lib, $type))
				return TRUE;

		// Log failure
		logger::log("Internal call access is denied from level $level.", logger::WARNING, $trace);
		return FALSE;
	}
	
	/**
	 * Checks whether a given class is in the given trace slice.
	 * 
	 * @param	array	$fulltrace
	 * 		The full execution stack trace slice as given by the debug_backtrace() function.
	 * 
	 * @param	integer	$level
	 * 		The level of the debug backtrace depth to check for the internal call.
	 * 
	 * @param	string	$class
	 * 		The class which the call must abide.
	 * 
	 * @param	integer	$type
	 * 		The type of check to perform in the call trace to identify whether is an internal call or not.
	 * 		Use class constants.
	 * 		Default value is CLASS_CHECK.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	private function checkClass($fulltrace, $level, $class, $type = self::CLASS_CHECK)
	{
		// Get trace level to check
		$checkTrace = $fulltrace[$level];
		
		// Get Class called
		switch ($type)
		{
			case self::CLASS_CHECK:
				$class_called = $checkTrace['class'];
				break;
			case self::ARGS_CHECK:
				// Check trace function
				if ($checkTrace['function'] == "load")
					$class_called = $checkTrace['args'][0];
				else if ($checkTrace['function'] == "includeSourceCode")
				{
					$class_called = $fulltrace[$level - 2]['args'][0];
					$class_called = str_replace(".object/src/class.php", "", $class_called);
					$pos = strpos($class_called, "SDK/");
					$class_called = substr($class_called, $pos + 4);
				}
				break;
			default:
				$class_called = $checkTrace['class'];
		}
		
		// Check is class is from core
		$internal = strpos($class_called, $class);
		return !($internal === FALSE) && $internal == 0;
	}
}
//#section_end#
?>