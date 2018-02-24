<?php
//#section#[header]
// Namespace
namespace API\Resources\forms;

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
 * @package	Resources
 * @namespace	\forms
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

/**
 * Input Validator
 * 
 * Validates form inputs according to inline rules.
 * 
 * @version	0.1-1
 * @created	April 23, 2013, 14:02 (EEST)
 * @updated	June 3, 2015, 17:58 (EEST)
 * 
 * @deprecated	Use DEV\Tools\validator instead.
 */
class inputValidator
{
	/**
	 * Email Regular Expression
	 * 
	 * @type	string
	 */
	const EMAIL_REGEXP = "/^[a-z0-9._%+-]+@(?:[a-z0-9-]+\.)+(?:[a-z]{2}|com|xxx|cat|org|tel|net|int|pro|edu|gov|mil|biz|coop|info|mobi|name|aero|arpa|asia|jobs|travel|museum)$/i";
	/**
	 * Username Regular Expression
	 * 
	 * @type	string
	 */
	const USERNAME_REGEXP = "/^[a-z][a-z0-9_\.]{5,19}$/i";
	/**
	 * Password Regular Expression
	 * 
	 * @type	string
	 */
	const PASSWORD_REGEXP = "/^.*(?=.*\d)((?=.*[a-z])|(?=.*[A-Z]))((?=.*\W)|(?=.*\_)).*$/";
	
	/**
	 * Check if the given value is empty
	 * 
	 * @param	string	$value
	 * 		The value to check
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function checkEmpty($value)
	{
		return empty($value);
	}
	
	/**
	 * Check if the given value is not set and empty
	 * 
	 * @param	string	$value
	 * 		The value to check
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function checkNotset($value)
	{
		return (!isset($value)) || self::checkEmpty($value);
	}
	
	/**
	 * Check if the given value is a valid password
	 * 
	 * @param	string	$value
	 * 		The password to check
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function checkPassword($value)
	{
		return preg_match(self::PASSWORD_REGEXP, $value);
	}
	
	/**
	 * Check if the given value is a valid email
	 * 
	 * @param	string	$value
	 * 		The email to check
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function checkEmail($value)
	{
		return preg_match(self::EMAIL_REGEXP, $value);
	}
	
	/**
	 * Check if the given value is a valid username
	 * 
	 * @param	string	$value
	 * 		The username to check
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function checkUsername($value)
	{
		return preg_match(self::USERNAME_REGEXP, $value);
	}
}
//#section_end#
?>