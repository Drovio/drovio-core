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
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Geoloc", "locale");
importer::import("DEV", "Apps", "components::appSettings");
importer::import("DEV", "Apps", "components::appView");
importer::import("DEV", "Apps", "components::source::sourceLibrary");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Tools", "parsers::jsParser");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Geoloc\locale;
use \DEV\Apps\components\appSettings;
use \DEV\Apps\components\appView;
use \DEV\Apps\components\source\sourceLibrary;
use \DEV\Version\vcs;
use \DEV\Prototype\sourceMap;
use \DEV\Projects\project;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;

importer::import("DEV", "Profiler", "logger");
use \DEV\Profiler\logger;

/**
 * Redback Application
 * 
 * This class is responsible for the development of an application.
 * Extends the project class.
 * 
 * @version	{empty}
 * @created	April 6, 2014, 0:01 (EEST)
 * @revised	April 6, 2014, 1:58 (EEST)
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
		$repository = $this->getRepository();
		$this->vcs = new vcs($repository);
		
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
		
		// Create Main source library
		$sourceLibrary = new sourceLibrary($this->id);
		$status = $sourceLibrary->create("Main");
		
		// Create Settings File
		$appSettings = new appSettings($this->id);
		$appSettings->create();
		
		// Create Main View
		$appView = new appView($this->id);
		$appView->create("MainView");
		
		// Set init settings
		$appSettings->set("STARTUP_VIEW", "MainView");
		$appSettings->set("DEFAULT_LOCALE", locale::getDefault());
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
	 * @return	void
	 */
	public function addObjectIndex($group, $type, $name)
	{
		// Get vcs item and update
		$itemID = $this->getItemID("appIndex");
		$itemTrunkPath = $this->vcs->updateItem($itemID);
		
		// Load application index
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		
		// Get The root
		$root = $parser->evaluate("//".$group)->item(0);
		
		// Create object
		$obj = $parser->create($type);
		$parser->attr($obj, "name", $name);
		$parser->append($root, $obj);
		
		// Update file
		$parser->update();
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
	 * {description}
	 * 
	 * @param	{type}	$group
	 * 		{description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
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
	 * Publishes the application, updates the project status and follows the general project guidelines.
	 * 
	 * @param	string	$version
	 * 		The project version to publish.
	 * 
	 * @return	void
	 */
	public function publish($version)
	{
		/*
		// Create Publish Folder (if doesn't already exist)
		$publishFolder = systemRoot.appManager::getPublishedAppFolder($this->id);
		folderManager::create($publishFolder);
		
		// Clean folder
		folderManager::clean($publishFolder, $name = "", $includeHidden = TRUE);
		
		// Update the database
		$dbc = new interDbConnection();
		$q = new dbQuery("141116177", "apps");
		$attr = array();
		$attr['id'] = $this->id;
		$result = $dbc->execute($q, $attr);
		
		// Set cssContent && jsContent
		$cssContent = "";
		$jsContent = "";
		
		// Gather Application Styles
		$styles = $this->getStyles();
		foreach ($styles as $style)
		{
			$appStyle = $this->getStyle($style);
			$cssContent .= $appStyle->get()."\n";
		}
		
		// Gather Application Scripts
		$scripts = $this->getScripts();
		foreach ($scripts as $script)
		{
			$appScript = $this->getScript($script);
			$jsContent .= $appScript->get()."\n";
		}
		
		// Publish Views
		$views = $this->getViews();
		foreach ($views as $view)
		{
			// Initialize view
			$appView = $this->getView($view);
			
			// Get css and js
			$cssContent .= $appView->getCSS()."\n";
			$jsContent .= $appView->getJS()."\n";
			
			// Publish html
			$viewHTML = $appView->getHTML();
			fileManager::create($publishFolder."/views/".$view.".view/view.html", $viewHTML, TRUE);
			
			// Publish php
			$viewPHP = $appView->getPHPCode($fukk = TRUE);
			fileManager::create($publishFolder."/views/".$view.".view/view.php", $viewPHP, TRUE);
		}
		
		// Publish Source Objects
		$srcPackage = $this->getSrcPackage();
		$packages = $srcPackage->getPackages($fullNames = TRUE);
		foreach ($packages as $packageName)
		{
			$objects = $srcPackage->getObjects($packageName, $parentNs = "");
			foreach ($objects as $object)
			{
				// Initialize source object
				$appSrcObj = $this->getSrcObject($packageName, $object['namespace'], $object['name']);
				
				// Export css and js
				$cssContent .= $appSrcObj->getCSSCode()."\n";
				$jsContent .= $appSrcObj->getJSCode()."\n";
				
				// Export source
				$objectSource = $appSrcObj->getSourceCode($full = TRUE);
				$namespace = str_replace("::", "/", $object['namespace']);
				$objectPath = $packageName."/".$namespace."/".$object['name'].".php";
				fileManager::create($publishFolder."/src/".$objectPath, $objectSource, TRUE);
			}
		}
		
		// Publish css
		$cssContent = cssParser::format($cssContent);
		fileManager::create($publishFolder."/style.css", $cssContent, TRUE);
		
		// Publish js
		$jsContent = cssParser::format($jsContent);
		fileManager::create($publishFolder."/script.js", $jsContent, TRUE);
		
		// Export Settings
		$appSettings = $this->getSettings();
		fileManager::create($publishFolder."/config/Settings.xml", $appSettings->getXML(), TRUE);
		
		// Export Lilterals
		$appLiterals = $this->getLiterals();
		$active = locale::active();
		foreach ($active as $locale)
		{
			try
			{
				$appLiterals->getXML($locale['locale']);
				$filePath = $publishFolder."/content/".$locale['locale']."/literals.xml";
				fileManager::create($filePath, $appLiterals->getXML($locale['locale']), TRUE);
			}
			catch (Exception $ex)
			{
			}
		}
		
		// Export media
		folderManager::create($publishFolder."/media/");
		folderManager::copy(systemRoot.$this->getRepository()."/media/", $publishFolder."/media/", TRUE);
		
		return TRUE;*/
	}
}
//#section_end#
?>