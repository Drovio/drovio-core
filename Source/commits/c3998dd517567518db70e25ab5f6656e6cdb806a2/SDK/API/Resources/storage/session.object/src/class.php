<?php
//#section#[header]
// Namespace
namespace API\Resources\storage;

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
 * @namespace	\storage
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "storage::cookies");
importer::import("API", "Resources", "url");

use \API\Resources\storage\cookies;
use \API\Resources\url;

/**
 * Session Manager
 * 
 * Handles all session storage data
 * 
 * @version	{empty}
 * @created	April 23, 2013, 13:58 (EEST)
 * @revised	March 21, 2014, 20:28 (EET)
 */
class session
{
	/**
	 * The session's expiration time (in seconds)
	 * 
	 * @type	integer
	 */
	const EXPIRE = 18000;
	
	/**
	 * Init session
	 * 
	 * @param	array	$options
	 * 		A set of options like the session_id etc.
	 * 
	 * @return	void
	 */
	public static function init($options = array())
	{
		//_____ Start session
		self::_start();
		
		// Initialise the session timers
		self::_setTimers();
		
		// Validate this session
		self::_validate();
		
		// Set options
		self::_setOptions($options);
	}
	
	/**
	 * Get a session variable value
	 * 
	 * @param	string	$name
	 * 		The name of the variable
	 * 
	 * @param	string	$default
	 * 		The value that will be returned if the variable doesn't exist
	 * 
	 * @param	string	$namespace
	 * 		The namespace of the session variable
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function get($name, $default = NULL, $namespace = 'default')
	{
		// Get SESSION Namespace
		$namespace = self::getNS($namespace);

		if (isset($_SESSION[$namespace][$name]))
			return $_SESSION[$namespace][$name];
			
		return $default;
	}
	
	/**
	 * Set a session variable value
	 * 
	 * @param	string	$name
	 * 		The name of the variable
	 * 
	 * @param	string	$value
	 * 		The value with which the variable will be set
	 * 
	 * @param	string	$namespace
	 * 		The namespace of the session variable
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	public static function set($name, $value = NULL, $namespace = 'default')
	{
		// Get SESSION Namespace
		$namespace = self::getNS($namespace);

		$old = isset($_SESSION[$namespace][$name]) ? $_SESSION[$namespace][$name] : NULL;

		if (NULL === $value)
			unset($_SESSION[$namespace][$name]);
		else
			$_SESSION[$namespace][$name] = $value;

		return $old;
	}
	
	/**
	 * Check if there is a session variable
	 * 
	 * @param	string	$name
	 * 		The variable name
	 * 
	 * @param	string	$namespace
	 * 		The namespace of the session variable
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function has($name, $namespace = 'default')
	{
		// Get SESSION Namespace
		$namespace = self::getNS($namespace);

		return isset($_SESSION[$namespace][$name]);
	}
	
	/**
	 * Deletes a session variable
	 * 
	 * @param	string	$name
	 * 		The variable name
	 * 
	 * @param	string	$namespace
	 * 		The namespace of the session variable
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function clear($name, $namespace = 'default')
	{
		// Get SESSION Namespace
		$namespace = self::getNS($namespace);

		$value = NULL;
		if (isset($_SESSION[$namespace][$name]))
		{
			$value = $_SESSION[$namespace][$name];
			unset($_SESSION[$namespace][$name]);
		}

		return $value;
	}
	
	/**
	 * Delete a set of session variables under the same namespace
	 * 
	 * @param	string	$namespace
	 * 		The namespace of the session variable
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function clear_set($namespace)
	{
		// Get SESSION Namespace
		$namespace = self::getNS($namespace);
			
		if (isset($_SESSION[$namespace]))
		{
			unset($_SESSION[$namespace]);
			return TRUE;
		}
		else
			return FALSE;
	}
	
	/**
	 * Get session name
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function get_name()
	{
		return session_name();
	}
	
	/**
	 * Get session id
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function get_id()
	{
		return session_id();
	}
	
	/**
	 * Destroy session
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function destroy()
	{
		$sessionCookie = cookies::get(session_name());
		if (!empty($sessionCookie))
			cookies::delete(session_name());

		session_unset();
		session_destroy();

		return TRUE;
	}
	
	/**
	 * Return the in-memory size of the session ($_SESSION) array.
	 * 
	 * @return	integer
	 * 		{description}
	 */
	public static function get_size()
	{
		return strlen(serialize($_SESSION));
	}
	
	/**
	 * Set the validation timers
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	protected static function _setTimers()
	{
		if (!self::has('timer.start', "session"))
		{
			$start = time();
			
			self::set('timer.start', $start, "session");
			self::set('timer.last', $start, "session");
			self::set('timer.now', $start, "session");
		}

		self::set('timer.last', self::get('timer.now', NULL, "session"), "session");
		self::set('timer.now', time(), "session");

		return TRUE;
	}
	
	/**
	 * Set the session options (session id, name etc.)
	 * 
	 * @param	array	$options
	 * 		The array of options
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	protected static function _setOptions(array $options)
	{
		// Set name
		if (isset($options['id']))
		{
			session_id(md5($options['id']));
		}

		// Sync the session maxlifetime
		//ini_set('session.gc_maxlifetime', self::EXPIRE);

		return TRUE;
	}
	
	/**
	 * Start the session
	 * 
	 * @return	void
	 */
	protected static function _start()
	{
		register_shutdown_function('session_write_close');
		session_cache_limiter('none');
		
		// Set Session cookie params
		$sessionCookieParams = session_get_cookie_params();
		$rootDomain = Url::getDomain();
		
		session_set_cookie_params(
			$sessionCookieParams["lifetime"], 
			$sessionCookieParams["path"], 
			$rootDomain, 
			$sessionCookieParams["secure"], 
			$sessionCookieParams["httponly"]
		);
		
		// Set name
		session_name("ss");

		// Session start
		@session_start();
		
		return TRUE;
	}
	
	/**
	 * Validate the session and reset if necessary
	 * 
	 * @return	void
	 */
	protected static function _validate()
	{
		// Regenerate session if gone too long
		if ((time() - self::get('timer.start', NULL, "session") > self::EXPIRE))
		{
			session_regenerate_id(true);
		}
		
		// Destroy session if expired
		if ((time() - self::get('timer.last', NULL, "session") > self::EXPIRE))
		{
			self::destroy();
		}
	}
	
	/**
	 * Create the namespace string
	 * 
	 * @param	string	$namespace
	 * 		The namespace of the session variable
	 * 
	 * @return	string
	 * 		{description}
	 */
	private static function getNS($namespace)
	{
		// Add prefix to namespace to avoid collisions.
		return "__".strtoupper($namespace);
	}
}
//#section_end#
?>