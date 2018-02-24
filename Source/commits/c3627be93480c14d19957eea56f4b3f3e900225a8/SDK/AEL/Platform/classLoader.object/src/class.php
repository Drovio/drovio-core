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
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "appcenter::appPlayer");
importer::import("API", "Developer", "appcenter::appManager");
importer::import("API", "Resources", "DOMParser");

use \API\Developer\appcenter\appPlayer;
use \API\Developer\appcenter\appManager;
use \API\Resources\DOMParser;

/**
 * Application Engine Class Loader
 * 
 * Manager for importing all classes from the Core SDK and the application's source.
 * 
 * @version	{empty}
 * @created	January 9, 2014, 20:08 (EET)
 * @revised	January 9, 2014, 20:08 (EET)
 */
class classLoader
{
	/**
	 * The application id that is currently running.
	 * 
	 * @type	integer
	 */
	private static $appID;
	/**
	 * The allowed core object list.
	 * 
	 * @type	array
	 */
	private static $allowedObjects = NULL;
	
	/**
	 * Initializes the application id for importing the source code.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	void
	 */
	public static function initApp($appID)
	{
		self::$appID = $appID;
	}
	
	/**
	 * Imports a source object from the application.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$object
	 * 		The object name.
	 * 		It is separated with "::" from the namespace.
	 * 		ex. platform::importer.
	 * 
	 * @return	void
	 */
	public static function import($package, $object)
	{
		$tester = appPlayer::testerStatus();
		$innerObjectPath = $package."/".str_replace("::", "/", $object);
		if ($tester)
		{
			$appMan = new appManager();
			$appFolder = $appMan->getDevAppFolder(self::$appID);
			$objectPath = $appFolder."/.repository/trunk/master/src/".$innerObjectPath.".object/src/class.php";
		}
		else
		{
			$appFolder = appManager::getPublishedAppFolder(self::$appID);
			$objectPath = $appFolder."/src/".$innerObjectPath.".php";
		}
		
		// Import application object
		return importer::req($objectPath, $root = TRUE, $once = TRUE);
	}
	
	/**
	 * Import a class object from the Redback Core SDK (including the AEL objects).
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$object
	 * 		The object name.
	 * 		The same as the import() function.
	 * 
	 * @return	void
	 */
	public static function importCore($library, $package, $object = "")
	{
		// Validate object
		$valid = self::validate($library, $package, $object);
		if (!$valid)
			throw new Exception("You don't have the right privileges to import this library.");
		
		// Import object
		importer::import($library, $package, $object);
	}
	
	/**
	 * Validates if the given object is allowed to be included in the application.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$object
	 * 		The object name.
	 * 		The same as the import() function.
	 * 
	 * @return	boolean
	 * 		True is object is allowed, false otherwise.
	 */
	private function validate($library, $package, $object)
	{
		// Load core list
		if (empty(self::$allowedObjects))
			self::loadXMLList();
		
		// Check if it's the AEL library
		if ($library == "AEL")
			return TRUE;

		// Check if exists in other libraries
		if (isset(self::$allowedObjects[$library][$package][$object]))
			return TRUE;
	}
	
	/**
	 * Loads the allowed redback core sdk objects.
	 * 
	 * @return	void
	 */
	private static function loadXMLList()
	{
		$parser = new DOMParser();
		try
		{
			$parser->load("/System/Resources/appCenter/core.xml");
		}
		catch (Exception $ex)
		{
			self::$allowedObjects = array();
			return;
		}

		// Get objects
		$objects = $parser->evaluate("//object");
		foreach ($objects as $object)
		{
			// Get parents
			$package = $object->parentNode;
			$library = $package->parentNode;
			
			// Set names
			$libraryName = $parser->attr($library, "name");
			$packageName = $parser->attr($package, "name");
			$objectName = $parser->attr($object, "name");
			
			// Set core list
			self::$allowedObjects[$libraryName][$packageName][$objectName] = 1;
		}
	}
}
//#section_end#
?>