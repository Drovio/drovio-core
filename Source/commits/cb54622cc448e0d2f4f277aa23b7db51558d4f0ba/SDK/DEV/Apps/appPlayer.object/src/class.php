<?php
//#section#[header]
// Namespace
namespace DEV\Apps;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Apps
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::RuntimeException");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "settingsManager");

use \ESS\Protocol\server\RuntimeException;
use \API\Resources\filesystem\fileManager;
use \API\Resources\settingsManager;

/**
 * Application Player
 * 
 * Inits and runs the application.
 * 
 * @version	{empty}
 * @created	April 7, 2014, 11:14 (EEST)
 * @revised	April 7, 2014, 11:14 (EEST)
 */
class appPlayer
{
	/**
	 * Initializes the application importer.
	 * 
	 * @return	void
	 */
	public static function init()
	{
		$importerPath = "/System/Library/Core/SDK/AEL/Platform/classLoader.php";
		importer::req($importerPath, $root = TRUE, $once = TRUE);
	}
	
	/**
	 * Gets the html content of the requested view of the running application.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$viewName
	 * 		The view name.
	 * 
	 * @return	string
	 * 		The view html content.
	 */
	public static function getView($appID, $viewName = "")
	{
		$viewPath = self::getViewPath($appID, $viewName);
		$viewHTML = $viewPath.".html";

		return trim(fileManager::get(systemRoot.$viewHTML));
	}
	
	/**
	 * Runs the requested view php code for the running application.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$viewName
	 * 		The view name.
	 * 
	 * @return	mixed
	 * 		The php code return value.
	 */
	public static function play($appID, $viewName = "")
	{
		try
		{
			$viewPath = self::getViewPath($appID, $viewName);
			$appPath = $viewPath.".php";
	
			return importer::incl($appPath, $root = TRUE, $once = FALSE);
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
	 * @param	{type}	$viewName
	 * 		The view name. If empty, get the application startup view from settings.
	 * 
	 * @return	string
	 * 		The view path.
	 */
	private static function getViewPath($appID, $viewName = "")
	{
		// Get Run Path
		$appPath = appManager::getPublishedAppFolder($appID);
		
		// If view name is empty, get startup view from app settings
		if (empty($viewName))
		{
			// Get Settings
			$appSettings = new settingsManager($appPath, $fileName = "settings", $rootRelative = TRUE);
			$viewName = $appSettings->get("STARTUP_VIEW");
		}
		
		// Return view path
		$viewName = self::getViewName($appID, $viewName);
		return $appPath."/views/".$viewName;
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
}
//#section_end#
?>