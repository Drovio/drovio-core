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
 * @version	0.1-1
 * @created	March 8, 2013, 10:41 (EET)
 * @revised	July 28, 2014, 10:39 (EEST)
 */
class accessControl
{
	/**
	 * Check if the call was internal (SDK Library)
	 * 
	 * @return	boolean
	 * 		True if the call is internal, false otherwise.
	 */
	public static function internalCall()
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
		
		// Check if the call comes from one of the Red SDK Libraries
		foreach ($libs as $lib)
			if (self::libCall($lib, 3))
				return TRUE;
	}
	
	/**
	 * Checks if the execution call is from the given library
	 * 
	 * @param	string	$lib
	 * 		The library to check.
	 * 
	 * @param	string	$level
	 * 		The level of the stack trace to check.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	protected static function libCall($lib, $level = 2)
	{
		// Get Backtrace
		$trace = debug_backtrace();
		
		return self::checkClass($trace[$level], $lib."\\");
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