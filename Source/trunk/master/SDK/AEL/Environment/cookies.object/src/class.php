<?php
//#section#[header]
// Namespace
namespace AEL\Environment;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	AEL
 * @package	Environment
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Environment", "cookies");
importer::import("AEL", "Platform", "application");

use \ESS\Environment\cookies as APICookies;
use \AEL\Platform\application;

/**
 * Application cookie manager.
 * 
 * Manages cookies for applications.
 * Cookies are accessible to the entire platform, so there is a specific onomatology for the application cookies.
 * 
 * Pattern: __APP[id]_[name]
 * Where:
 * - id: application id
 * - name: cookie name
 * 
 * @version	0.1-2
 * @created	January 21, 2015, 16:02 (EET)
 * @updated	August 29, 2015, 11:48 (EEST)
 */
class cookies extends APICookies
{
	/**
	 * Create a new cookie or update an existing one.
	 * 
	 * @param	string	$name
	 * 		The cookie's name.
	 * 
	 * @param	string	$value
	 * 		The cookie's value.
	 * 
	 * @param	integer	$expiration
	 * 		The expiration of the cookie in seconds.
	 * 		If set to 0, the cookie will expire at the end of the session.
	 * 		If set to <0 the cookie will be removed. You can use remove() instead.
	 * 
	 * @param	boolean	$httpOnly
	 * 		When TRUE the cookie will be made accessible only through the HTTP protocol.
	 * 		This means that the cookie won't be accessible by scripting languages, such as JavaScript.
	 * 
	 * @param	boolean	$secure
	 * 		Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function set($name, $value, $expiration = 0, $httpOnly = FALSE, $secure = FALSE)
	{
		// Get application relative cookie name
		$name = self::getCookieName($name);
		
		// Set cookie
		return parent::set($name, $value, $expiration, $httpOly, $secure);
	}
	
	/**
	 * Get the value of a cookie.
	 * 
	 * @param	string	$name
	 * 		The cookie's name.
	 * 
	 * @return	mixed
	 * 		The cookie value or NULL if cookie doesn't exist.
	 */
	public static function get($name)
	{
		// Get application relative cookie name
		$name = self::getCookieName($name);
		
		// Set cookie
		return parent::get($name);
	}
	
	/**
	 * Remove a cookie.
	 * 
	 * @param	string	$name
	 * 		The cookie's name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($name)
	{
		// Get application relative cookie name
		$name = self::getCookieName($name);
		
		// Set cookie
		return parent::remove($name);
	}	
	
	/**
	 * Get an application relative cookie name.
	 * 
	 * @param	string	$name
	 * 		The initial cookie name.
	 * 
	 * @return	string
	 * 		The cookie full name.
	 */
	private static function getCookieName($name)
	{
		// Get application id
		$applicationID = application::init();
		if (empty($applicationID))
			return NULL;
		
		return "__APP".$applicationID."_".$name;
	}
}
//#section_end#
?>