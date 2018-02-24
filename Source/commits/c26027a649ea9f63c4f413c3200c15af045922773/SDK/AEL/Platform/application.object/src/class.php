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

importer::import("ESS", "Protocol", "debug::RuntimeException");
importer::import("DEV", "Apps", "test::appTester");
importer::import("DEV", "Apps", "test::sourceTester");
importer::import("DEV", "Apps", "application");
importer::import("DEV", "Apps", "appSettings");
importer::import("DEV", "Apps", "views::appView");
importer::import("DEV", "Apps", "source::srcObject");
importer::import("DEV", "Apps", "source::srcPackage");
importer::import("AEL", "Literals", "appLiteral");

use \ESS\Protocol\debug\RuntimeException;
use \DEV\Apps\test\appTester;
use \DEV\Apps\test\sourceTester;
use \DEV\Apps\application as devApplication;
use \DEV\Apps\appSettings;
use \DEV\Apps\views\appView;
use \DEV\Apps\source\srcObject;
use \DEV\Apps\source\srcPackage;
use \AEL\Literals\appLiteral;

/**
 * Application Manager
 * 
 * This is the application manager class.
 * It imports application source objects and load application views.
 * 
 * @version	2.0-1
 * @created	August 23, 2014, 16:53 (EEST)
 * @revised	August 24, 2014, 20:06 (EEST)
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
	public static function import($package, $class = "")
	{
		if (sourceTester::status(self::$applicationID, $package))
		{
			if (empty($class))
				return self::importPackage($package);
			
			// Break classname
			$classParts = explode("::", $class);
			
			// Get Class Name
			$className = $classParts[count($classParts)-1];
			unset($classParts[count($classParts)-1]);
			
			// Get namespace
			$namespace = implode("/", $classParts);
			
			$obj = new srcObject(self::$applicationID, $package, $namespace, $className);
			return $obj->loadSourceCode();
		}
		else
		{
			// Load application source class from latest approved published version
		}
	}
	
	/**
	 * Import an entire application source package.
	 * 
	 * @param	string	$package
	 * 		The application source package name.
	 * 
	 * @return	void
	 */
	private function importPackage($package)
	{
		$srcp = new srcPackage(self::$applicationID);
		$objects = $srcp->getObjects($package);
		foreach ($objects as $object)
		{
			$obj = new srcObject(self::$applicationID, $package, $object["namespace"], $object["name"]);
			$obj->loadSourceCode();
		}
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
		// Get view name
		// If view name is empty, get startup view from app settings
		if (empty($viewName))
		{
			// Get Settings
			$appSettings = self::getAppSettings();
			$viewName = $appSettings->get("STARTUP_VIEW");
		}

		try
		{
			if (appTester::status(self::$applicationID))
			{
				// Initialize application
				$appView = new appView(self::$applicationID, $viewName);
				return $appView->run();
			}
			else
			{
				$viewPHPPath = self::getViewPath(self::$applicationID, $viewName).".php";
				return importer::incl($viewPHPPath, $root = TRUE, $once = FALSE);
			}
		}
		catch (Exception $ex)
		{
			// Catches the exception thrown by the programmer's code
			return RuntimeException::get($ex);
		}
	}
	
	/**
	 * Gets the application's developer folder path to the view (no extensions included).
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$viewName
	 * 		The view name.
	 * 
	 * @return	string
	 * 		The view path.
	 */
	private static function getViewPath($appID, $viewName)
	{
		// Get Run Path
		$appPath = devApplication::getPublishedAppFolder(self::$applicationID);
		
		// Return view path
		$viewName = self::getViewName(self::$applicationID, $viewName);
		return $appPath."/views/".$viewName;
	}
	
	/**
	 * Gets the settings manager for the application.
	 * Decides whether to load the settings from the developer folder or the published folder.
	 * 
	 * @return	settingsManager
	 * 		The appSettings or settingsManager object.
	 */
	private static function getAppSettings()
	{
		if (appTester::status(self::$applicationID))
		{
			// Initialize application
			return new appSettings(self::$applicationID);
		}
		else
		{
			$appPath = self::getApplicationRunPath(self::$applicationID)."/config/";
			return new settingsManager($appPath, $fileName = "Settings", $rootRelative = TRUE);
		}
	}
	
	/**
	 * Get the published view name.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$name
	 * 		The view name.
	 * 
	 * @return	string
	 * 		The published view name (no extensions included).
	 */
	public static function getViewName($appID, $name)
	{
		return "v".md5("view_".$appID."_".$name);
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
	 * @return	integer
	 * 		The application view loading depth.
	 */
	public static function getLoadingDepth()
	{
		return self::$loadingDepth;
	}
}
//#section_end#
?>