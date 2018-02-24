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

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "Apps", "appSettings");
importer::import("DEV", "Apps", "views::appView");
importer::import("DEV", "Apps", "source::srcPackage");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Tools", "parsers::jsParser");
importer::import("DEV", "Resources", "paths");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Apps\appSettings;
use \DEV\Apps\views\appView;
use \DEV\Apps\source\srcPackage;
use \DEV\Version\vcs;
use \DEV\Prototype\sourceMap;
use \DEV\Projects\project;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;
use \DEV\Resources\paths;

/**
 * Redback Application
 * 
 * This class is responsible for the development of an application.
 * Extends the project class.
 * 
 * @version	{empty}
 * @created	April 6, 2014, 0:01 (EEST)
 * @revised	April 10, 2014, 9:45 (EEST)
 */
class application extends project
{
	/**
	 * The index file name.
	 * 
	 * @type	string
	 */
	const INDEX_FILE = "index.xml";
	
	/**
	 * The source index file name
	 * 
	 * @type	string
	 */
	const SOURCE_INDEX = "source.xml";
	
	/**
	 * The application source folder path.
	 * 
	 * @type	string
	 */
	const SOURCE_FOLDER = "/src";
	
	/**
	 * The application styles folder path.
	 * 
	 * @type	string
	 */
	const STYLES_FOLDER = "/styles";
	
	/**
	 * The application scripts folder path.
	 * 
	 * @type	string
	 */
	const SCRIPTS_FOLDER = "/scripts";
	
	/**
	 * The application views folder path.
	 * 
	 * @type	string
	 */
	const VIEWS_FOLDER = "/views";
	
	/**
	 * The source vcs object manager.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Initializes the application.
	 * 
	 * @param	integer	$appID
	 * 		The application project id.
	 * 
	 * @param	string	$appName
	 * 		The application unique name.
	 * 
	 * @return	void
	 */
	public function __construct($appID = "", $appName = "")
	{
		// Init project
		parent::__construct($appID, $appName);
		
		// Init vcs
		$this->vcs = new vcs($appID);
		
		// Create app structure (if first time)
		$this->createAppStructure();
	}
	
	/**
	 * Gets the vcs manager object.
	 * 
	 * @return	vcs
	 * 		The vcs manager object.
	 */
	public function getVCS()
	{
		return $this->vcs;
	}
	
	/**
	 * Creates the application structure.
	 * 
	 * @return	void
	 */
	private function createAppStructure()
	{
		// Check if structure is already there
		if (file_exists(systemRoot.$this->getRootFolder()."/".self::INDEX_FILE))
			return;
		
		// Create index file
		fileManager::create(systemRoot.$this->getRootFolder()."/".self::INDEX_FILE, "", TRUE);
		
		// Create Application Index
		$this->createAppIndex();
		$this->createSourceMap();
		
		// Create Main source package
		$sourcePackage = new srcPackage($this->id);
		$sourcePackage->init();
		$sourcePackage->create("Main");
		
		// Create Settings File
		$appSettings = new appSettings($this->id);
		$appSettings->create();
		
		// Create Main View
		$appView = new appView($this->id);
		$appView->create("MainView");
		
		// Set init settings
		$appSettings->set("STARTUP_VIEW", "MainView");
	}
	
	/**
	 * Creates the application index file.
	 * 
	 * @return	void
	 */
	private function createAppIndex()
	{
		// Create vcs item
		$itemID = $this->getItemID("appIndex");
		$itemPath = "/";
		$itemName = self::INDEX_FILE;
		$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		
		// Create Application index root
		$parser = new DOMParser();
		$root = $parser->create("app");
		$parser->append($root);
		$parser->save($itemTrunkPath, FALSE);
		
		// Populate index
		$stylesElement = $parser->create("styles");
		$parser->append($root, $stylesElement);
		
		$scriptsElement = $parser->create("scripts");
		$parser->append($root, $scriptsElement);
		
		$viewsElement = $parser->create("views");
		$parser->append($root, $viewsElement);
		
		$parser->update();
	}
	
	/**
	 * Creates the application source map index file.
	 * 
	 * @return	void
	 */
	private function createSourceMap()
	{
		// Create vcs item
		$itemID = $this->getItemID("sourceIndex");
		$itemPath = "/";
		$itemName = self::SOURCE_INDEX;
		$mapFilePath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		
		// Create source map
		$sourceMap = new sourceMap(dirname($mapFilePath), basename($mapFilePath));
		$sourceMap->createMapFile();
	}
	
	/**
	 * Adds an object's index to the application index file.
	 * 
	 * @param	string	$group
	 * 		The object group.
	 * 
	 * @param	string	$type
	 * 		The object type.
	 * 
	 * @param	string	$name
	 * 		The object name.
	 * 
	 * @return	boolean
	 * 		True on success, false if object already exist.
	 */
	public function addObjectIndex($group, $type, $name)
	{
		// Get vcs item and update
		$itemID = $this->getItemID("appIndex");
		$itemTrunkPath = $this->vcs->updateItem($itemID);
		
		// Load application index
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		
		// Check if item already exists
		$item = $parser->evaluate("//".$group."/".$type."[@name='".$name."']")->item(0);
		if (!is_null($item))
			return FALSE;
		
		// Get The root
		$root = $parser->evaluate("//".$group)->item(0);
		
		// Create object
		$obj = $parser->create($type);
		$parser->attr($obj, "name", $name);
		$parser->append($root, $obj);
		
		// Update file
		return $parser->update();
	}
	
	/**
	 * Adds an object's index to the application index file.
	 * 
	 * @param	string	$group
	 * 		The object group.
	 * 
	 * @param	string	$type
	 * 		The object type.
	 * 
	 * @param	string	$name
	 * 		The object name.
	 * 
	 * @return	boolean
	 * 		True on success, false if object already exist.
	 */
	public function removeObjectIndex($group, $type, $name)
	{
		// Get vcs item and update
		$itemID = $this->getItemID("appIndex");
		$itemTrunkPath = $this->vcs->updateItem($itemID);
		
		// Load application index
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		
		// Check if item already exists
		$item = $parser->evaluate("//".$group."/".$type."[@name='".$name."']")->item(0);
		if (is_null($item))
			return FALSE;
		
		// Remove item and update file
		$parser->replace($item, NULL);
		return $parser->update();
	}
	
	/**
	 * Gets all application styles.
	 * 
	 * @return	array
	 * 		An array of styles by value.
	 */
	public function getStyles()
	{
		return $this->getIndexObjects("styles", "style");
	}
	
	/**
	 * Gets all application scripts.
	 * 
	 * @return	array
	 * 		An array of scripts by value.
	 */
	public function getScripts()
	{
		return $this->getIndexObjects("scripts", "script");
	}
	
	/**
	 * Gets all application views.
	 * 
	 * @return	array
	 * 		An array of views by value.
	 */
	public function getViews()
	{
		return $this->getIndexObjects("views", "view");
	}
	
	/**
	 * Get objects from the application index.
	 * 
	 * @param	string	$group
	 * 		The object group.
	 * 
	 * @param	string	$name
	 * 		The object tag name.
	 * 
	 * @return	array
	 * 		An array of all object names by group.
	 */
	private function getIndexObjects($group, $name)
	{
		// Get vcs item and load index
		$itemID = $this->getItemID("appIndex");
		$itemTrunkPath = $this->vcs->getItemTrunkPath($itemID);
		
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		
		// Get The root
		$root = $parser->evaluate("//".$group)->item(0);

		// Get all packages
		$objects = array();
		$elements = $parser->evaluate($name, $root);
		foreach ($elements as $elm)
			$objects[] = $parser->attr($elm, "name");
		
		return $objects;
	}
	
	/**
	 * Get the vcs item id.
	 * 
	 * @param	string	$suffix
	 * 		The id suffix.
	 * 
	 * @return	string
	 * 		The item hash id.
	 */
	public function getItemID($suffix)
	{
		return "app".md5("app_".$this->id."_".$suffix);
	}
	
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
	public function publish($branchName = vcs::MASTER_BRANCH)
	{
		// Get current release folder
		$app = new application($this->id);
		$vcs = new vcs($this->id);
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
		$sp = new srcPackage($appID);
		$packages = $sp->getList();
		foreach ($packages as $package)
		{
			// Get object list
			$objects = $sp->getObjects($package, $namespace = NULL);
			foreach ($objects as $object)
			{
				$obj = new sourceObject($appID, $package, $object['namespace'], $object['name']);
				
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
}
//#section_end#
?>