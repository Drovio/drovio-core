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
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Environment", "session");
importer::import("AEL", "Platform", "application");
importer::import("DEV", "Profiler", "logger");

use \ESS\Environment\session as APISession;
use \AEL\Platform\application;
use \DEV\Profiler\logger;

/**
 * Application Session Manager
 * 
 * Manages session storage on behalf of the application.
 * All the variables are an a specific application namespace prefix.
 * 
 * @version	1.0-1
 * @created	October 19, 2015, 17:43 (BST)
 * @updated	October 20, 2015, 11:08 (BST)
 */
class session extends APISession
{
	/**
	 * Get a session variable value for the application.
	 * 
	 * @param	string	$name
	 * 		The name of the variable.
	 * 
	 * @param	mixed	$default
	 * 		The value that will be returned if the variable doesn't exist.
	 * 
	 * @param	string	$namespace
	 * 		The namespace of the session variable.
	 * 
	 * @return	string
	 * 		The session value.
	 */
	public static function get($name, $default = NULL, $namespace = 'default')
	{
		// Get application namespace
		$namespace = self::getApplicationNamespace($namespace);
		
		// Get variable
		return parent::get($name, $default, $namespace);
	}
	
	/**
	 * Set a session variable value.
	 * 
	 * @param	string	$name
	 * 		The name of the variable.
	 * 
	 * @param	mixed	$value
	 * 		The value with which the variable will be set.
	 * 
	 * @param	string	$namespace
	 * 		The namespace of the session variable.
	 * 
	 * @return	mixed
	 * 		The old value of the variable, or NULL if not set.
	 */
	public static function set($name, $value = NULL, $namespace = 'default')
	{
		// Get application namespace
		$namespace = self::getApplicationNamespace($namespace);
		
		// Set variable
		return parent::set($name, $value, $namespace);
	}
	
	/**
	 * Check if there is a session variable.
	 * 
	 * @param	string	$name
	 * 		The variable name.
	 * 
	 * @param	string	$namespace
	 * 		The namespace of the session variable.
	 * 
	 * @return	boolean
	 * 		True if the variable is set, false otherwise.
	 */
	public static function has($name, $namespace = 'default')
	{
		// Get application namespace
		$namespace = self::getApplicationNamespace($namespace);
		
		// Check variable
		return parent::has($name, $namespace);
	}
	
	/**
	 * Deletes a session variable
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$namespace
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use remove() instead.
	 */
	public static function clear($name, $namespace = 'default')
	{
		return self::remove($name, $namespace);
	}
	
	/**
	 * Removes a session variable.
	 * 
	 * @param	string	$name
	 * 		The variable name.
	 * 
	 * @param	string	$namespace
	 * 		The namespace of the session variable.
	 * 
	 * @return	mixed
	 * 		The old value of the variable, or NULL if not set.
	 */
	public static function remove($name, $namespace = 'default')
	{
		// Get application namespace
		$namespace = self::getApplicationNamespace($namespace);
		
		// Remove variable
		return parent::remove($name, $namespace);
	}
	
	/**
	 * Delete a set of session variables under the same namespace.
	 * 
	 * @param	string	$namespace
	 * 		The namespace to be cleared.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function clearSet($namespace)
	{
		// Get application namespace
		$namespace = self::getApplicationNamespace($namespace);
		
		// Clear namespace
		return parent::clearSet($namespace);
	}
	
	/**
	 * Get the session namespace with application identifier prefix.
	 * 
	 * @param	string	$namespace
	 * 		The namespace name.
	 * 
	 * @return	string
	 * 		The application-specific namespace.
	 */
	private static function getApplicationNamespace($namespace)
	{
		// Get application id
		$applicationID = application::init();
		
		// Return application namespace
		return "APP".$applicationID."_".$namespace;
	}
}
//#section_end#
?>