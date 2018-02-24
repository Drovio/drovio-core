<?php
//#section#[header]
// Namespace
namespace AEL\Platform;

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
 * @package	Platform
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Apps", "test::appTester");
importer::import("AEL", "Literals", "appLiteral");

use \DEV\Apps\test\appTester;
use \AEL\Literals\appLiteral;

/**
 * Application Manager
 * 
 * This is the application manager class.
 * It imports application source objects and load application views.
 * 
 * @version	1.0-1
 * @created	August 23, 2014, 16:53 (EEST)
 * @revised	August 23, 2014, 17:02 (EEST)
 */
class application
{
	/**
	 * The application view loading depth.
	 * 
	 * @type	integer
	 */
	private static $loadingDepth = 0;
	
	/**
	 * The application id currently running.
	 * 
	 * @type	integer
	 */
	private static $applicationID = NULL;
	
	/**
	 * Init the literal manager for the application that is currently running.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id currently running.
	 * 		
	 * 		NOTE: To application developers, this will be set only once the first time and cannot be changed after.
	 * 
	 * @return	integer
	 * 		The application id currently running.
	 */
	public static function init($applicationID = "")
	{
		// Set the application id if not set before
		if (empty(self::$applicationID))
			self::$applicationID = $applicationID;
	
		// Return the current application id (project id)
		return self::$applicationID;
	}
	
	/**
	 * Import an application source object
	 * 
	 * @param	string	$package
	 * 		The object's package name.
	 * 
	 * @param	string	$class
	 * 		The full name of the class (including namespaces separated by "::")
	 * 
	 * @return	void
	 */
	public static function import($package, $class)
	{
	}
	
	/**
	 * Load an application view content.
	 * 
	 * @param	string	$viewName
	 * 		The application view name.
	 * 		If empty, get the default/startup application view name.
	 * 		It is empty by default.
	 * 
	 * @return	mixed
	 * 		The application view content object.
	 * 		If can be a server report string or the DOMElement of the view.
	 */
	public static function loadView($viewName = "")
	{
		return appTester::loadView(self::$applicationID, $viewName);
	}
	
	/**
	 * Increase the loading depth of app views by one.
	 * 
	 * @return	void
	 */
	public static function incLoadingDepth()
	{
		self::$loadingDepth++;
	}
	
	/**
	 * Decrease the loading depth of app views by one.
	 * 
	 * @return	void
	 */
	public static function decLoadingDepth()
	{
		self::$loadingDepth--;
	}
	
	/**
	 * Get the current app view's loading depth, starting from 0.
	 * 
	 * @return	void
	 */
	public static function getLoadingDepth()
	{
		return self::$loadingDepth;
	}
}
//#section_end#
?>