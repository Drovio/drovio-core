<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\client\environment;

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
 * @namespace	\client\environment
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::environment::Url");

use \ESS\Protocol\client\environment\Url;

/**
 * Cookie's Manager
 * 
 * Manages all cookie interaction for the system
 * 
 * @version	{empty}
 * @created	March 7, 2013, 11:41 (EET)
 * @revised	October 23, 2013, 10:48 (EEST)
 */
class cookies
{
	/**
	 * Create a new cookie.
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
	 * @return	boolean
	 * 		True on success, false on failure.
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
	 * @return	string
	 * 		The cookie value.
	 */
	public static function get($name)
	{
		return (isset($_COOKIE[$name]) ? $_COOKIE[$name] : NULL);
	}
	
	/**
	 * Delete a cookie
	 * 
	 * @param	string	$name
	 * 		The cookie's name
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
	 */
	public static function clear()
	{
		foreach ($_COOKIE as $name => $value)
			self::delete($name);
	}
	
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