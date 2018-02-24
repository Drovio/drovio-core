<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\environment;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\environment
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "environment::Url");

use \ESS\Protocol\environment\Url;

/**
 * System Cookie's Manager
 * 
 * This is the system's cookie manager.
 * Creates, deletes and reads red cookies.
 * 
 * @version	0.1-1
 * @created	July 29, 2014, 20:28 (EEST)
 * @revised	July 29, 2014, 20:28 (EEST)
 */
class Cookies
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
	 * 
	 * @param	boolean	$secure
	 * 		Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client.
	 * 
	 * @param	boolean	$httpOly
	 * 		When TRUE the cookie will be made accessible only through the HTTP protocol.
	 * 		This means that the cookie won't be accessible by scripting languages, such as JavaScript.
	 * 
	 * @return	void
	 */
	public static function set($name, $value, $expiration = 0, $secure = FALSE, $httpOly = FALSE)
	{
		// Set cookie params
		$expiration = ($expiration == 0 ? $expiration : time() + $expiration);
		$path = "/";
		$domain = ".".Url::getDomain();
		
		// Set cookie
		return self::setCookie($name, $value, $expiration, $path, $domain, $secure, $httpOnly);
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
		return (isset($_COOKIE[$name]) ? $_COOKIE[$name] : NULL);
	}
	
	/**
	 * Delete a cookie
	 * 
	 * @param	string	$name
	 * 		The cookie's name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function delete($name)
	{
		// Set cookie and return TRUE
		if (self::set($name, NULL, - 3600))
			return TRUE;
		
		// Return FALSE
		return FALSE;
	}
	
	/**
	 * Delete all cookies
	 * 
	 * @return	void
	 * 
	 * @deprecated	This function is forbidden and won't do anything.
	 */
	public static function clear() {}
	
	/**
	 * Create a new cookie with the full parameters.
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
	 * 
	 * @param	string	$path
	 * 		The path on the server in which the cookie will be available on.
	 * 
	 * @param	string	$domain
	 * 		The domain that the cookie is available to.
	 * 
	 * @param	boolean	$secure
	 * 		Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client.
	 * 
	 * @param	boolean	$httpOly
	 * 		When TRUE the cookie will be made accessible only through the HTTP protocol.
	 * 		This means that the cookie won't be accessible by scripting languages, such as JavaScript.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private static function setCookie($name, $value, $expiration = 0, $path = "/", $domain = "", $secure = FALSE, $httpOly = FALSE)
	{
		if (setcookie($name, $value, $expiration, $path, $domain, ($secure ? 1 : 0), ($httpOly ? 1 : 0)))
			return TRUE;
	}
}
//#section_end#
?>