<?php
//#section#[header]
// Namespace
namespace ESS\Environment;

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
 * @package	Environment
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Platform", "engine");
importer::import("DEV", "Profiler", "logger");
importer::import("ESS", "Environment", "url");

use \API\Platform\engine;
use \DEV\Profiler\logger;
use \ESS\Environment\url;

/**
 * System Cookie Manager
 * 
 * This is the system's cookie manager.
 * Creates, deletes and reads framework cookies.
 * 
 * @version	0.1-8
 * @created	October 23, 2014, 14:03 (BST)
 * @updated	December 16, 2015, 23:07 (GMT)
 */
class cookies
{
	/**
	 * Stores runtime cookies until they are available at the page.
	 * 
	 * @type	array
	 */
	private static $runtimeCookies = array();
	
	/**
	 * Create a new cookie or update an existing one.
	 * It uses the php's setcookie function with preset values for domain and paths.
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
		// Check cookie
		if (empty($name))
		{
			logger::log("Trying to set cookie with in empty name.", logger::WARNING);
			return FALSE;
		}
			
		// Set cookie params
		$expiration = ($expiration == 0 ? $expiration : time() + $expiration);
		$path = "/";
		$domain = ".".url::getDomain();
		
		// Set cookie
		if (setcookie($name, $value, $expiration, $path, $domain, ($secure ? 1 : 0), ($httpOnly ? 1 : 0)))
		{
			// Set engine var
			if ($expiration >= 0)
				self::$runtimeCookies[$name] = $value;
			else
				unset(self::$runtimeCookies[$name]);
			
			// Return
			return TRUE;
		}
		
		return FALSE;
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
		// Get runtime and page cookie values
		$runtimeCookie = self::$runtimeCookies[$name];
		$pageCookie = (isset($_COOKIE[$name]) ? $_COOKIE[$name] : NULL);
		
		// Return the newest
		return (empty($runtimeCookie) ? $pageCookie : $runtimeCookie);
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
		// Set cookie and return TRUE
		if (self::set($name, NULL, - 3600))
			return TRUE;
		
		// Return FALSE
		return FALSE;
	}	
}
//#section_end#
?>