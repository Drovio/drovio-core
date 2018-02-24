<?php
//#section#[header]
// Namespace
namespace API\Security;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Security
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

/**
 * Internal Access Control
 * 
 * This is the proxy class which is responsible for letting the API execute internal functions and prevent others from executing closed functions and features.
 * 
 * @version	{empty}
 * @created	March 8, 2013, 8:41 (UTC)
 * @revised	March 8, 2013, 8:41 (UTC)
 */
class accessControl
{
	/**
	 * Check if the call was internal (SDK Library)
	 * 
	 * @return	boolean
	 */
	public static function internalCall()
	{
		return self::libCall("API", 3) || self::libCall("UI", 3) || self::libCall("ESS", 3) || self::libCall("INU", 3) || self::libCall("ACL", 3);
	}
	
	/**
	 * Checks if the execution call is from the given library
	 * 
	 * @param	string	$lib
	 * 		The library to check
	 * 
	 * @param	string	$level
	 * 		The level of the stack trace to check.
	 * 
	 * @return	boolean
	 */
	protected static function libCall($lib, $level = 2)
	{
		// Get Backtrace
		$trace = debug_backtrace();
		
		return self::_check_class($trace[$level], $lib."\\");
	}
	
	/**
	 * Checks the trace given for the specified class
	 * 
	 * @param	array	$trace
	 * 		The execution stack trace as given by the debug_backtrace() function.
	 * 
	 * @param	string	$class
	 * 		The class library which the call must abide.
	 * 
	 * @return	boolean
	 */
	private function _check_class($trace, $class)
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