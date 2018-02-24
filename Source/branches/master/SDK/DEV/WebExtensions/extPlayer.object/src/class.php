<?php
//#section#[header]
// Namespace
namespace DEV\WebExtensions;

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
 * @package	WebExtensions
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::RuntimeException");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "settingsManager");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "WebExtensions", "extManager");

use \ESS\Protocol\server\RuntimeException;
use \API\Resources\filesystem\fileManager;
use \API\Resources\settingsManager;
use \UI\Html\DOM;
use \DEV\WebExtensions\extManager;

/**
 * Extension Player
 * 
 * Inits and runs the extension.
 * 
 * @version	{empty}
 * @created	May 23, 2014, 10:47 (EEST)
 * @revised	May 23, 2014, 10:47 (EEST)
 */
class extPlayer
{
	/**
	 * Run the extension given view.
	 * Load the html (if any) in the given container (if not null) and then run the view php code to fill the html.
	 * 
	 * @param	integer	$extID
	 * 		The extension id.
	 * 
	 * @param	DOMElement	$container
	 * 		The DOMElement container for the extension view content (html).
	 * 
	 * @param	string	$viewName
	 * 		The view name.
	 * 
	 * @return	mixed
	 * 		The extension view return value.
	 */
	public static function play($extID, $container, $viewName = "")
	{
		try
		{
			// Load the view html
			if (!empty($container))
			{
				$viewHTMLPath = self::getViewPath($extID, $viewName).".html";
				$viewHTML =  trim(fileManager::get(systemRoot.$viewHTMLPath));
				DOM::innerHTML($container, $viewHTML);
			}
			
			$viewPHPPath = self::getViewPath($extID, $viewName).".php";
			return importer::incl($viewPHPPath, $root = TRUE, $once = FALSE);
		}
		catch (Exception $ex)
		{
			// Catches the exception thrown by the programmer's code
			return RuntimeException::get($ex);
		}
	}
	
	/**
	 * Get the published view name.
	 * 
	 * @param	integer	$extID
	 * 		The extension id.
	 * 
	 * @param	string	$viewName
	 * 		The view name.
	 * 
	 * @return	string
	 * 		The published view name (no extensions included).
	 */
	private static function getViewPath($extID, $viewName = "")
	{
		// Get Run Path
		$extPath = extManager::getPublishedFolder($extID);
		
		// If view name is empty, get startup view from app settings
		if (empty($viewName))
		{
			// Get Settings
			$extSettings = new settingsManager($extPath, $fileName = "settings", $rootRelative = TRUE);
			$viewName = $extSettings->get("STARTUP_VIEW");
		}
		
		// Return view path
		$viewName = self::getViewName($extID, $viewName);
		return $extPath."/views/".$viewName;
	}
	
	/**
	 * Gets the extension's developer folder path to the view (no extensions included).
	 * 
	 * @param	integer	$extID
	 * 		The extension id.
	 * 
	 * @param	string	$name
	 * 		The view name.
	 * 		If empty, get the extension startup view from settings.
	 * 
	 * @return	string
	 * 		The view path.
	 */
	public static function getViewName($extID, $name)
	{
		return "v".md5("view_".$extID."_".$name);
	}
}
//#section_end#
?>