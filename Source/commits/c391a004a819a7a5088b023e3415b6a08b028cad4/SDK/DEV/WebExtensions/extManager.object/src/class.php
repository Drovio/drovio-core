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

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "WebExtensions", "extension");
importer::import("DEV", "WebExtensions", "extPlayer");
importer::import("DEV", "WebExtensions", "components::extView");
importer::import("DEV", "WebExtensions", "components::extTheme");
importer::import("DEV", "WebExtensions", "components::extScript");
importer::import("DEV", "WebExtensions", "components::source::sourceLibrary");
importer::import("DEV", "WebExtensions", "components::source::sourcePackage");
importer::import("DEV", "WebExtensions", "components::source::sourceObject");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Version", "vcs");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\WebExtensions\extension;
use \DEV\WebExtensions\extPlayer;
use \DEV\WebExtensions\components\extView;
use \DEV\WebExtensions\components\extTheme;
use \DEV\WebExtensions\components\extScript;
use \DEV\WebExtensions\components\source\sourceLibrary;
use \DEV\WebExtensions\components\source\sourcePackage;
use \DEV\WebExtensions\components\source\sourceObject;
use \DEV\Tools\parsers\cssParser;
use \DEV\Version\vcs;
/**
 * Application Manager
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	May 23, 2014, 10:57 (EEST)
 * @revised	May 23, 2014, 10:57 (EEST)
 */
class extManager
{
	/**
	 * Gets the extension's publish folder.
	 * 
	 * @param	integer	$extID
	 * 		The extension id.
	 * 
	 * @return	string
	 * 		The extension publish folder path.
	 */
	public static function getPublishedFolder($extID)
	{
		return paths::getPublishedPath()."/Extensions/ext".md5("ext_".$extID).".extension/";
	}
	
	/**
	 * Publish the given extension.
	 * It publishes the extension to the review folder.
	 * 
	 * @param	integer	$extID
	 * 		The extension id.
	 * 
	 * @param	string	$branchName
	 * 		The branch name to publish.
	 * 
	 * @return	void
	 */
	public static function publish($extID, $branchName = vcs::MASTER_BRANCH)
	{
		// Get current release folder
		$extension = new extension($extID);
		$vcs = new vcs($extID);
		$releaseFolder = $vcs->getCurrentRelease($branchName);
		
		// Create Publish Folder (if doesn't already exist)
		$publishFolder = systemRoot.self::getPublishedFolder($extID);
		folderManager::create($publishFolder);
		
		// Create folder for review
		$reviewFolder = $publishFolder."/.review";
		folderManager::create($reviewFolder);
		
		// Clean review folder
		folderManager::clean($reviewFolder, $name = "", $includeHidden = TRUE);
		
		// Set cssContent && jsContent
		$cssContent = "";
		$jsContent = "";
		
		/*
		// Gather Application Styles
		$styles = $extension->getStyles();
		foreach ($styles as $styleName)
		{
			$extTheme = new extTheme($extID, $styleName);
			$cssContent .= $extTheme->get();
		}*/
		
		// Gather Application Scripts
		$scripts = $extension->getScripts();
		foreach ($scripts as $scriptName)
		{
			$extScript = new extScript($extID, $scriptName);
			$jsContent .= $extScript->get()."\n";
		}
		
		// Publish Views
		$views = $extension->getViews();
		foreach ($views as $viewName)
		{
			// Initialize view
			$extView = new extView($extID, $viewName);
			
			// Get css and js
			$cssContent .= $extView->getCSS();
			$jsContent .= $extView->getJS()."\n";
			
			// Get view name
			$viewHashName = extPlayer::getViewName($extID, $viewName);
			
			// Publish html
			$viewHTML = $extView->getHTML();
			fileManager::create($reviewFolder."/views/".$viewHashName.".html", $viewHTML, TRUE);
			
			// Publish php
			$viewPHP = $extView->getPHPCode($fukk = TRUE);
			fileManager::create($reviewFolder."/views/".$viewHashName.".php", $viewPHP, TRUE);
		}
		
		// Publish Source Objects
		$lib = new sourceLibrary($extID);
		$libraries = $lib->getList();
		foreach ($libraries as $library)
		{
			// Get packages
			$packages = $lib->getPackageList($library);
			foreach ($packages as $package)
			{
				// Get object list
				$pkg = new sourcePackage($extID);
				$objects = $pkg->getObjects($library, $package, $namespace = NULL);
				foreach ($objects as $object)
				{
					$obj = new sourceObject($extID, $library, $package, $object['namespace'], $object['name']);
					
					// Get css and js
					$cssContent .= $obj->getCSSCode()."\n";
					$jsContent .= $obj->getJSCode()."\n";
					
					// Export source
					$objectSource = $obj->getSourceCode($full = TRUE);
					$namespace = str_replace("::", "/", $object['namespace']);
					$objectPath = $library."/".$package."/".$namespace."/".$object['name'].".php";
					fileManager::create($reviewFolder."/src/".$objectPath, $objectSource, TRUE);
				}
			}
		}
		
		// Format css
		$cssContent = cssParser::format($cssContent);
		
		// Replace resources vars in css
		$resourcePath = $publishFolder."/resources";
		$cssContent = str_replace("%resources%", $resourcePath, $cssContent);
		
		// Publish css file
		fileManager::create($reviewFolder."/style.css", $cssContent, TRUE);
		
		// Publish js
		fileManager::create($reviewFolder."/script.js", $jsContent, TRUE);
		
		// Copy settings and source index files
		fileManager::copy($releaseFolder."/settings.xml", $reviewFolder."/settings.xml");
		fileManager::copy($releaseFolder."/source.xml", $reviewFolder."/source.xml");
		
		// Export media
		folderManager::create($reviewFolder."/resources/");
		folderManager::copy(systemRoot.$extension->getResourcesFolder(), $reviewFolder."/resources/", TRUE);
		
		return TRUE;
	}
}
//#section_end#
?>