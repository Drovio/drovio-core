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

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "Apps", "application");
importer::import("DEV", "Apps", "appPlayer");
importer::import("DEV", "Apps", "components::appView");
importer::import("DEV", "Apps", "components::appStyle");
importer::import("DEV", "Apps", "components::appScript");
importer::import("DEV", "Apps", "components::source::sourceLibrary");
importer::import("DEV", "Apps", "components::source::sourcePackage");
importer::import("DEV", "Apps", "components::source::sourceObject");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Version", "vcs");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Apps\application;
use \DEV\Apps\appPlayer;
use \DEV\Apps\components\appView;
use \DEV\Apps\components\appStyle;
use \DEV\Apps\components\appScript;
use \DEV\Apps\components\source\sourceLibrary;
use \DEV\Apps\components\source\sourcePackage;
use \DEV\Apps\components\source\sourceObject;
use \DEV\Tools\parsers\cssParser;
use \DEV\Version\vcs;
/**
 * Application Manager
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	April 6, 2014, 21:26 (EEST)
 * @revised	April 8, 2014, 22:32 (EEST)
 */
class appManager
{
	/**
	 * Gets the application's publish folder.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	string
	 * 		The application publish folder path.
	 */
	public static function getPublishedAppFolder($appID)
	{
		return paths::getPublishedPath()."/Apps/app".md5("app_".$appID).".app/";
	}
	
	/**
	 * Gets the redback's shared library list.
	 * 
	 * @return	array
	 * 		The shared library in a $library => $object array.
	 */
	public static function getSharedLibraryList()
	{
		$libs = array();
		$parser = new DOMParser();
		try
		{
			$parser->load("/System/Resources/SDK/AEL/privileges.xml");
		}
		catch (Exception $ex)
		{
			return $libs;
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
			
			$libs[$libraryName][$packageName][] = $objectName;
		}
		
		return $libs;
	}
	
	/**
	 * Publish the given application.
	 * It publishes the application to the review folder.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$branchName
	 * 		The branch name to publish.
	 * 
	 * @return	void
	 */
	public static function publishApp($appID, $branchName = vcs::MASTER_BRANCH)
	{
		// Get current release folder
		$app = new application($appID);
		$repository = $app->getRepository();
		$vcs = new vcs($repository);
		$releaseFolder = $vcs->getCurrentRelease($branchName);
		
		// Create Publish Folder (if doesn't already exist)
		$publishFolder = systemRoot.self::getPublishedAppFolder($appID);
		folderManager::create($publishFolder);
		
		// Create folder for review
		$reviewFolder = $publishFolder."/.review";
		folderManager::create($reviewFolder);
		
		// Clean review folder
		folderManager::clean($reviewFolder, $name = "", $includeHidden = TRUE);
		
		// Set cssContent && jsContent
		$cssContent = "";
		$jsContent = "";
		
		// Gather Application Styles
		$styles = $app->getStyles();
		foreach ($styles as $styleName)
		{
			$appStyle = new appStyle($appID, $styleName);
			$cssContent .= $appStyle->get();
		}
		
		// Gather Application Scripts
		$scripts = $app->getScripts();
		foreach ($scripts as $scriptName)
		{
			$appScript = new appScript($appID, $scriptName);
			$jsContent .= $appScript->get()."\n";
		}
		
		// Publish Views
		$views = $app->getViews();
		foreach ($views as $viewName)
		{
			// Initialize view
			$appView = new appView($appID, $viewName);
			
			// Get css and js
			$cssContent .= $appView->getCSS();
			$jsContent .= $appView->getJS()."\n";
			
			// Get view name
			$viewHashName = appPlayer::getViewName($appID, $viewName);
			
			// Publish html
			$viewHTML = $appView->getHTML();
			fileManager::create($reviewFolder."/views/".$viewHashName.".html", $viewHTML, TRUE);
			
			// Publish php
			$viewPHP = $appView->getPHPCode($fukk = TRUE);
			fileManager::create($reviewFolder."/views/".$viewHashName.".php", $viewPHP, TRUE);
		}
		
		// Publish Source Objects
		$lib = new sourceLibrary($appID);
		$libraries = $lib->getList();
		foreach ($libraries as $library)
		{
			// Get packages
			$packages = $lib->getPackageList($library);
			foreach ($packages as $package)
			{
				// Get object list
				$pkg = new sourcePackage($appID);
				$objects = $pkg->getObjects($library, $package, $namespace = NULL);
				foreach ($objects as $object)
				{
					$obj = new sourceObject($appID, $library, $package, $object['namespace'], $object['name']);
					
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
		folderManager::copy(systemRoot.$app->getResourcesFolder(), $reviewFolder."/resources/", TRUE);
		
		return TRUE;
	}
	
	/**
	 * Get all applications for app center (with a projectStatus of published or published under review).
	 * 
	 * @return	array
	 * 		An array of all apps (projects) available for the appcenter.
	 */
	public static function getAppCenterApps()
	{
		$dbc = new interDbConnection();
		$q = new dbQuery("657431943", "apps");
		
		$result = $dbc->execute($q);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>