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
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Internal Access Control
 * 
 * This is the proxy class which is responsible for letting the API execute internal functions and prevent others from executing closed functions and features.
 * 
 * @version	{empty}
 * @created	March 8, 2013, 10:41 (EET)
 * @revised	July 1, 2014, 10:54 (EEST)
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
		return self::libCall("API", 3) || self::libCall("UI", 3) || self::libCall("ESS", 3) || self::libCall("INU", 3) || self::libCall("AEL", 3) || self::libCall("DEV", 3);
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