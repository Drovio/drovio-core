<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\client;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\client
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "storage::session");

use \API\Resources\storage\session;

/**
 * CAPTCHA Protocol
 * 
 * This protocol defines how all the available captcha mechanisms will interact with the server and validate themselves.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 9:38 (UTC)
 * @revised	March 7, 2013, 9:38 (UTC)
 */
class CaptchaProtocol
{
	/**
	 * Set captcha value as a session variable
	 * 
	 * @param	int	$formID
	 * 		The id of the form that contains the captcha
	 * 
	 * @param	string	$captchaValue
	 * 		The value that the captcha was initialized with
	 * 
	 * @return	void
	 */
	public static function set($formID, $captchaValue)
	{
		// Create hashed value
		$hashedValue = md5($captchaValue);
		
		// Set session value
		session::set($formID, $hashedValue, $namespace = 'captcha');
	}
	
	/**
	 * Validate captcha value
	 * 
	 * @param	int	$formID
	 * 		The id of the form that is validating
	 * 
	 * @param	string	$captchaValue
	 * 		The value that the form received and wants to validate
	 * 
	 * @return	boolean
	 */
	public static function validate($formID, $captchaValue)
	{
		// Get hashed value
		$hashedValue = md5($captchaValue);

		// Get session value
		$storedValue = session::get($formID, $default = NULL, $namespace = 'captcha');

		// Clear session value
		//session::clear($formID, $namespace = 'captcha');
		
		// Return validation
		return ($hashedValue === $storedValue);
	}
}
//#section_end#
?>