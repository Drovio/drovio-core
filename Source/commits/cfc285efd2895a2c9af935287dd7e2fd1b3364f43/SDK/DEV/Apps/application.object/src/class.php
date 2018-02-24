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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Environment", "url");
importer::import("ESS", "Protocol", "BootLoader");
importer::import("AEL", "Platform", "application");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("DEV", "Apps", "appSettings");
importer::import("DEV", "Apps", "appManifest");
importer::import("DEV", "Apps", "views/appView");
importer::import("DEV", "Apps", "views/appViewManager");
importer::import("DEV", "Apps", "library/appStyle");
importer::import("DEV", "Apps", "library/appScript");
importer::import("DEV", "Apps", "source/srcPackage");
importer::import("DEV", "Apps", "source/srcObject");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Tools", "parsers/jsParser");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\url;
use \ESS\Protocol\BootLoader;
use \AEL\Platform\application as appPlayer;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Apps\appSettings;
use \DEV\Apps\appManifest;
use \DEV\Apps\views\appView;
use \DEV\Apps\views\appViewManager;
use \DEV\Apps\library\appStyle;
use \DEV\Apps\library\appScript;
use \DEV\Apps\source\srcPackage;
use \DEV\Apps\source\srcObject;
use \DEV\Version\vcs;
use \DEV\Prototype\sourceMap;
use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;
use \DEV\Resources\paths;

/**
 * Redback Application
 * 
 * This class is responsible for the development of an application.
 * Extends the project class.
 * 
 * @version	2.0-15
 * @created	April 6, 2014, 0:01 (EEST)
 * @updated	April 21, 2015, 14:47 (EEST)
 */
class application extends project
{
	/**
	 * The application project type id.
	 * 
	 * @type	integer
	 */
	const PROJECT_TYPE = 4;
	
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
	 * The views index file name.
	 * 
	 * @type	string
	 */
	const VIEWS_INDEX = "views.xml";
	
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
	 * 		The application id.
	 * 		Leave empty for new application or name initialization.
	 * 		It is empty by default.
	 * 
	 * @param	string	$appName
	 * 		The application unique name.
	 * 		Leave empty for new application or id initialization.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($appID = "", $appName = "")
	{
		// Init project
		parent::__construct($appID, $appName);
		
		// Init vcs
		$this->vcs = new vcs($this->getID());
		
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
		$sourcePackage = new srcPackage($this->getID());
		$sourcePackage->init();
		$sourcePackage->create("Main");
		
		// Create Settings File
		$appSettings = new appSettings($this->getID());
		$appSettings->create();
		
		// Create Main View and update with default source code
		$appView = new appView($this->getID());
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
		$itemTrunkPath = $this->vcs->updateItem($itemID, TRUE);
		
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
	 * Remove an object from the application's index file.
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
	 * 		True on success, false on failure.
	 */
	public function removeObjectIndex($group, $type, $name)
	{
		// Get vcs item and update
		$itemID = $this->getItemID("appIndex");
		$itemTrunkPath = $this->vcs->updateItem($itemID, TRUE);
		
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
	 * Gets all application scripts.
	 * 
	 * @return	array
	 * 		An array of views by value.
	 * 
	 * @deprecated	Use getAllViews() instead.
	 */
	public function getViews()
	{
		return $this->getIndexObjects("views", "view");
	}
	
	/**
	 * Get all application views.
	 * 
	 * @return	array
	 * 		A compact array as:
	 * 		[folderName] => viewName
	 */
	public function getAllViews()
	{
		$vMan = new appViewManager($this->getID());
		$allFolders = $vMan->getFolders("", TRUE);
		
		$allViews = array();
		$allViews[""] = $vMan->getFolderViews("");
		foreach ($allFolders as $fld)
			$allViews[$fld] = $vMan->getFolderViews($fld);
		
		return $allViews;
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
		return "app".md5("app_".$this->getID()."_".$suffix);
	}
	
	/**
	 * Publish the current application to the given version.
	 * 
	 * @param	string	$version
	 * 		The version to publish the application.
	 * 
	 * @param	string	$branchName
	 * 		The source's branch name to publish.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function publish($version, $branchName = vcs::MASTER_BRANCH)
	{
		// Check branch name
		$branchName = (empty($branchName) ? vcs::MASTER_BRANCH : $branchName);
		
		// Get application id
		$appID = $this->getID();
		// Get current release folder
		$releaseFolder = $this->vcs->getCurrentRelease($branchName);
		
		// Create Publish Folder (if doesn't already exist)
		$publishFolder = projectLibrary::getPublishedPath($appID, $version);
		folderManager::create(systemRoot.$publishFolder);
		$innerFolder = "/resources";
		
		// Copy settings, source index and manifest files
		fileManager::copy($releaseFolder."/settings.xml", systemRoot.$publishFolder."/settings.xml");
		fileManager::copy($releaseFolder."/source.xml", systemRoot.$publishFolder."/source.xml");
		fileManager::copy(systemRoot.$this->getRootFolder()."/".appManifest::MANIFEST_FILE, systemRoot.$publishFolder."/".appManifest::MANIFEST_FILE);
		
		// Set cssContent && jsContent
		$cssContent = "";
		$jsContent = "";
		
		// Get Styles
		$parser = new DOMParser();
		$parser->load($releaseFolder."/".self::INDEX_FILE, FALSE);
		$root = $parser->evaluate("//styles")->item(0);
		
		$styles = array();
		$elements = $parser->evaluate("style", $root);
		foreach ($elements as $elm)
			$styles[] = $parser->attr($elm, "name");
		
		// Gather Application Styles
		foreach ($styles as $styleName)
			$cssContent .= fileManager::get($releaseFolder."/".self::STYLES_FOLDER."/".$styleName.".css")."\n";
		
		// Get Scripts
		$parser = new DOMParser();
		$parser->load($releaseFolder."/".self::INDEX_FILE, FALSE);
		$root = $parser->evaluate("//scripts")->item(0);
		
		$scripts = array();
		$elements = $parser->evaluate("script", $root);
		foreach ($elements as $elm)
			$scripts[] = $parser->attr($elm, "name");
		
		// Gather Application Scripts
		$scripts = $this->getScripts();
		foreach ($scripts as $scriptName)
			$jsContent .= fileManager::get($releaseFolder."/".self::SCRIPTS_FOLDER."/".$scriptName.".js")."\n";
		
		// Publish Views
		$allViews = $this->getReleaseViews($releaseFolder);
		foreach ($allViews as $folderName => $views)
			foreach ($views as $viewName)
			{
				$viewFolder = $releaseFolder."/".self::VIEWS_FOLDER."/".$folderName."/".$viewName.".view";
				
				// Get css and js
				$cssContent .= fileManager::get($viewFolder."/style.css")."\n";
				$jsContent .= fileManager::get($viewFolder."/script.js")."\n";
				
				// Get view name
				$viewHashName = appPlayer::getViewName($appID, $viewName);
				
				// Publish html
				$viewHTML = fileManager::get($viewFolder."/view.html");
				
				// Resolve urls in html and format
				$viewHTML = BootLoader::resolveURLs($appID, $viewHTML, $version, $protocol = NULL, $innerFolder);
				/*$parser = new DOMParser();
				$parser->loadContent($viewHTML, DOMParser::TYPE_XML);
				$viewHTML = $parser->getHTML();*/
				// Create file
				fileManager::create(systemRoot.$publishFolder."/views/".$folderName."/".$viewHashName.".html", $viewHTML, TRUE);
				
				// Publish php
				$viewPHP = fileManager::get($viewFolder."/view.php");
				fileManager::create(systemRoot.$publishFolder."/views/".$folderName."/".$viewHashName.".php", $viewPHP, TRUE);
			}
		
		// Publish Source Objects
		$sourceFolder = $releaseFolder."/".self::SOURCE_FOLDER;
		
		$sp = new srcPackage($appID);
		$packages = $sp->getList();
		foreach ($packages as $package)
		{
			// Get object list
			$objects = $sp->getObjects($package, $namespace = NULL);
			foreach ($objects as $object)
			{
				$namespace = str_replace("::", "/", $object['namespace']);
				$objectPath = srcPackage::LIB_NAME."/".$package."/".$namespace."/".$object['name'].".object";
				
				// Get css and js
				$fullObjectPath = $sourceFolder."/".$objectPath;
				$cssContent .= fileManager::get($fullObjectPath."/model/style.css");
				$jsContent .= fileManager::get($fullObjectPath."/script.js");
				
				// Export source
				$objectSource = fileManager::get($fullObjectPath."/src/class.php");
				$objectPath = srcPackage::LIB_NAME."/".$package."/".$namespace."/".$object['name'].".php";
				fileManager::create(systemRoot.$publishFolder."/".self::SOURCE_FOLDER."/".$objectPath, $objectSource, TRUE);
			}
		}
		
		// Resolve urls in css, format and export
		$cssContent = BootLoader::resolveURLs($appID, $cssContent, $version, $protocol = "https", $innerFolder);
		$cssContent = cssParser::format($cssContent, $compact = TRUE);
		$cssContent = BootLoader::resolveURLs($appID, $cssContent, $version, $protocol = NULL, $innerFolder);
		BootLoader::exportCSS($appID, "Apps", $appID, $cssContent, $version);
		
		// Resolve urls in js, format and export
		$jsContent = jsParser::format($jsContent);
		$jsContent = BootLoader::resolveURLs($appID, $jsContent, $version, $protocol = NULL, $innerFolder);
		BootLoader::exportJS($appID, "Apps", $appID, $jsContent, $version);
		
		
		// COMPATIBILITY
		fileManager::create(systemRoot.$publishFolder."/style.css", $cssContent, TRUE);
		fileManager::create(systemRoot.$publishFolder."/script.js", $jsContent, TRUE);
		
		// Export application resources
		$this->publishResources($publishFolder."/resources/");
		
		// Publish project common assets
		return parent::publish($version);
	}
	
	/**
	 * Get all application views from the release index.
	 * 
	 * @param	string	$releaseFolder
	 * 		The application release folder.
	 * 
	 * @return	void
	 */
	private function getReleaseViews($releaseFolder)
	{
		$parser = new DOMParser();
		$parser->load($releaseFolder."/".self::VIEWS_INDEX, FALSE);
		
		// Get all folders
		$allFolders = $this->getFolders($parser);
		
		// Get all views
		$allViews = array();
		$allViews[""] = $this->getFolderViews($parser, "");
		foreach ($allFolders as $fld)
			$allViews[$fld] = $this->getFolderViews($parser, $fld);
		
		return $allViews;
	}
	
	/**
	 * Get an array of all the folders under the given path.
	 * 
	 * @param	DOMParser	$parser
	 * 		The index parser object.
	 * 
	 * @param	string	$parent
	 * 		The folder to look down from.
	 * 		The default value is empty, for the root.
	 * 		Separate each folder with "/".
	 * 
	 * @return	array
	 * 		A nested array of all the folders under the given path.
	 */
	private function getFolders($parser, $parent = "")
	{
		// Get library parser
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		$expression = "/views";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$expression .= "/folder";
		
		$folders = array();
		$fes = $parser->evaluate($expression);
		foreach ($fes as $folderElement)
		{
			$folderName = $parser->attr($folderElement, "name");
			$newParent = (empty($parent) ? "" : $parent."/").$folderName;
			$libFolders = $this->getFolders($parser, $newParent);
			$folders[] = $newParent;
			foreach ($libFolders as $lf)
				$folders[] = $lf;
		}
		
		if (empty($folders))
			return "";
		return $folders;
	}
	
	/**
	 * Get all views in a given folder.
	 * 
	 * @param	DOMParser	$parser
	 * 		The index parser object.
	 * 
	 * @param	string	$parent
	 * 		The folder to look down from.
	 * 		The default value is empty, for the root.
	 * 		Separate each folder with "/".
	 * 
	 * @return	array
	 * 		An array of all views.
	 */
	private function getFolderViews($parser, $parent = "")
	{
		// Get library parser
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		$expression = "/views";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$expression .= "/view";
		
		$views = array();
		// Get document parent
		$vElements = $parser->evaluate($expression);
		foreach ($vElements as $vElement)
			$views[] = $parser->attr($vElement, "name");
		
		return $views;
	}
}
//#section_end#
?>