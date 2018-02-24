<?php
//#section#[header]
// Namespace
namespace DEV\Tools;

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
 * @package	Tools
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

/**
 * Information/Type validator
 * 
 * Validates specific types of input like mails, usernames etc.
 * 
 * @version	0.1-1
 * @created	June 3, 2015, 17:58 (EEST)
 * @updated	June 3, 2015, 17:58 (EEST)
 */
class validator
{
	/**
	 * Email Regular Expression
	 * 
	 * @type	string
	 */
	const EMAIL_REGEXP = "/^[a-z0-9._%+-]+@(?:[a-z0-9-]+\.)+(?:[a-z]{2,})$/i";
	
	/**
	 * Username Regular Expression
	 * 
	 * @type	string
	 */
	const USERNAME_REGEXP = "/^[a-z][a-z0-9_\.]{5,19}$/i";
	
	/**
	 * Check if the given value is a valid email.
	 * 
	 * @param	string	$value
	 * 		The email to check.
	 * 
	 * @return	boolean
	 * 		True if the email is a valid one, false otherwise.
	 */
	public static function validEmail($value)
	{
		return preg_match(self::EMAIL_REGEXP, $value);
	}
	
	/**
	 * Check if the given value is a valid username.
	 * 
	 * @param	string	$value
	 * 		The username to check
	 * 
	 * @return	boolean
	 * 		True if the username is a valid one, false otherwise.
	 */
	public static function validUsername($value)
	{
		return preg_match(self::USERNAME_REGEXP, $value);
	}
}
//#section_end#
?>