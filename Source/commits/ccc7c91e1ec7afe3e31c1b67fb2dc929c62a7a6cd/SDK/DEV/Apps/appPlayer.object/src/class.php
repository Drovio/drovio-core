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
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Apps", "application");

use \ESS\Protocol\server\RuntimeException;
use \API\Resources\filesystem\fileManager;
use \API\Resources\settingsManager;
use \UI\Html\DOM;
use \DEV\Apps\application;

/**
 * Application Player
 * 
 * Inits and runs the application.
 * 
 * @version	{empty}
 * @created	April 7, 2014, 11:14 (EEST)
 * @revised	April 7, 2014, 22:38 (EEST)
 */
class appPlayer
{
	/**
	 * Run the application given view.
	 * Load the html (if any) in the given container (if not null) and then run the view php code to fill the html.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	DOMElement	$container
	 * 		The DOMElement container for the application view content (html).
	 * 
	 * @param	string	$viewName
	 * 		The view name.
	 * 
	 * @return	mixed
	 * 		The php code return value.
	 */
	public static function play($appID, $container, $viewName = "")
	{
		try
		{
			// Load the view html
			if (!empty($container))
			{
				$viewHTMLPath = self::getViewPath($appID, $viewName).".html";
				$viewHTML =  trim(fileManager::get(systemRoot.$viewHTMLPath));
				DOM::innerHTML($container, $viewHTML);
			}
			
			$viewPHPPath = self::getViewPath($appID, $viewName).".php";
			return importer::incl($viewPHPPath, $root = TRUE, $once = FALSE);
		}
		catch (Exception $ex)
		{
			// Catches the exception thrown by the programmer's code
			return RuntimeException::get($ex);
		}
	}
	
	/**
	 * Gets the settings manager for the application.
	 * Decides whether to load the settings from the developer folder or the published folder.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	settingsManager
	 * 		The appSettings or settingsManager object.
	 */
	public static function getAppSettings($appID)
	{
		if (self::testerStatus($appID))
		{
			// Initialize application
			$app = new application($appID);
			
			// Get Settings
			return $app->getSettings();
		}
		else
		{
			$appPath = self::getApplicationRunPath($appID)."/config/";
			return new settingsManager($appPath, $fileName = "Settings", $rootRelative = TRUE);
		}
	}
	
	/**
	 * Gets the application's developer folder path to the view (no extensions included).
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$viewName
	 * 		The view name. If empty, get the application startup view from settings.
	 * 
	 * @return	string
	 * 		The view path.
	 */
	private static function getViewPath($appID, $viewName = "")
	{
		// Get Run Path
		$appPath = application::getPublishedAppFolder($appID);
		
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