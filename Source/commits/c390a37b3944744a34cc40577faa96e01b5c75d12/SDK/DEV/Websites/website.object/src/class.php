<?php
//#section#[header]
// Namespace
namespace DEV\Websites;

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
 * @package	Websites
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Projects", "project");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Version", "vcs");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Websites", "wsSettings");
importer::import("DEV", "Websites", "wsServer");
importer::import("DEV", "Websites", "source::srcPackage");
importer::import("DEV", "Websites", "pages::wsPageManager");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Resources", "paths");

importer::import("DEV", "Profiler", "logger");
use \DEV\Profiler\logger;

use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Version\vcs;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Prototype\sourceMap;
use \DEV\Websites\wsSettings;
use \DEV\Websites\wsServer;
use \DEV\Websites\source\srcPackage;
use \DEV\Websites\pages\wsPageManager;
use \DEV\Tools\parsers\cssParser;
use \DEV\Resources\paths;
/**
 * Website Manager Class
 * 
 * This is the main class for managing a website project.
 * 
 * @version	2.0-1
 * @created	June 30, 2014, 12:58 (EEST)
 * @revised	November 5, 2014, 22:11 (EET)
 */
class website extends project
{
	/**
	 * The project type code as it stored in database.
	 * 
	 * @type	integer
	 */
	const PROJECT_TYPE = 5;

	/**
	 * The index file name.
	 * 
	 * @type	string
	 */
	const INDEX_FILE = "index.xml";

	/**
	 * The name of the pages folder.
	 * 
	 * @type	string
	 */
	const PAGES_FOLDER = 'pages';
	
	/**
	 * The website's source folder.
	 * 
	 * @type	string
	 */
	const SOURCE_FOLDER = 'src';
	
	/**
	 * The source index file.
	 * 
	 * @type	string
	 */
	const SOURCE_INDEX = "source.xml";

	/**
	 * The vcs manager object
	 * 
	 * @type	vcs
	 */
	private $vcs;

	/**
	 * The contructor method
	 * 
	 * @param	sting	$id
	 * 		The projects' id
	 * 
	 * @param	sting	$name
	 * 		The projects' name
	 * 
	 * @return	void
	 */
	public function __construct($id = "", $name = "")
	{
		// Init project
		parent::__construct($id, $name);
		
		// Init vcs
		$this->vcs = new vcs($this->getID());
		
		// Create structure (if first time)
		$this->createStructure();
	}
	
	/**
	 * Creates the (folder / file) structure of the project at creation.
	 * 
	 * @return	boolean
	 * 		True on success, false elsewhere
	 */
	private function createStructure()
	{
		// Check if structure is already there
		if (file_exists(systemRoot.$this->getRootFolder()."/".self::INDEX_FILE))
			return FALSE;
		
		// Create index file
		fileManager::create(systemRoot.$this->getRootFolder()."/".self::INDEX_FILE, "", TRUE);
	
		// Create Index (Mapping) Files
		$this->createWebsiteIndex();
		$this->createSourceMap();
				
		// Create Server File
		$servers = new wsServer($this->getID());
		$servers->create();
		
		
		// Create Settings File
		$settings = new wsSettings($this->getID());
		$settings->create();
				
		// Set init settings
		$settings->setMetaDescription('This a site developed using Redback Web SDK');
		$settings->setMetaKeywords('redback, web.redback');		
		
		return TRUE;	
	}
	
	/**
	 * Creates the mapping / index file of the project at creation.
	 * 
	 * @return	boolean
	 * 		True on success, false elsewhere
	 */
	private function createWebsiteIndex()
	{
		// Create vcs item
		$itemID = $this->getItemID("wsIndex");
		$itemPath = "/";
		$itemName = self::INDEX_FILE;
		$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		
		// Create index root
		$parser = new DOMParser();
		$root = $parser->create("website");
		$parser->append($root);
		$parser->save($itemTrunkPath, FALSE);
		
		// Populate index
		$entry = $parser->create(self::PAGES_FOLDER);
		$parser->append($root, $entry);
		
		$parser->update();
	}
	
	/**
	 * Creates the website source map index file.
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
	 * Get objects from the website index.
	 * 
	 * @param	string	$group
	 * 		The object's group.
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @return	array
	 * 		An array of all object names by group.
	 */
	private function getIndexObjects($group, $name)
	{
		// Get vcs item and load index
		$itemID = $this->getItemID("wsIndex");
		$itemTrunkPath = $this->vcs->getItemTrunkPath($itemID);
		
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		
		// Get The root
		$root = $parser->evaluate("//".$group)->item(0);

		// Get all packages
		$objects = array();
		$elements = $parser->evaluate($name, $root);
		foreach ($elements as $elem)
			$objects[] = $parser->attr($elem, "name");
		
		return $objects;
	}
	
	/**
	 * Adds an object's index to the website index file.
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
		$itemID = $this->getItemID("wsIndex");
		$itemTrunkPath = $this->vcs->updateItem($itemID);
		
		// Load index
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
	 * Remove an object from the website's index file.
	 * 
	 * @param	string	$group
	 * 		The object group.
	 * 
	 * @param	string	$name
	 * 		The object name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeObjectIndex($group, $name)
	{
		// Get vcs item and update
		$itemID = $this->getItemID("wsIndex");
		$itemTrunkPath = $this->vcs->updateItem($itemID);
		
		// Load index
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		
		// Check if item already exists
		$item = $parser->evaluate("//".$group."[@name='".$name."']")->item(0);
		if (is_null($item))
			return FALSE;
		
		// Remove item and update file
		$parser->replace($item, NULL);
		return $parser->update();
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
		return "ws".md5("ws_".$this->getID()."_".$suffix);
	}
	
	/**
	 * Publish the current website to the given version.
	 * 
	 * @param	string	$version
	 * 		The version to publish the website.
	 * 
	 * @param	string	$branchName
	 * 		The source's branch name to publish.
	 * 
	 * @return	void
	 */
	public function publish($version, $branchName = vcs::MASTER_BRANCH, $innerSitePath = NULL)
	{
		$wsID = $this->getID();
		$vcs = new vcs($wsID);
		$releaseFolder = $vcs->getCurrentRelease($branchName);
		
		// Create Publish Folder (if doesn't already exist)
		$publishFolder = projectLibrary::getPublishedPath($this->getID(), $version);
		folderManager::create(systemRoot.$publishFolder);
		
		
		// Copy settings and source index files
		fileManager::copy($releaseFolder."/settings.xml", systemRoot.$publishFolder."/settings.xml");
		fileManager::copy($releaseFolder."/source.xml", systemRoot.$publishFolder."/source.xml");
		
		
		// Set cssContent && jsContent
		$cssContent = "";
		$jsContent = "";
		
		/*
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
			$cssContent .= fileManager::get($releaseFolder."/".self::STYLES_FOLDER."/".$styleName.".css");
		
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
			$jsContent .= fileManager::get($releaseFolder."/".self::SCRIPTS_FOLDER."/".$scriptName.".js");
		*/
		
		// Set Page cssContent && jsContent
		$cssContent = "";
		$jsContent = "";
		
		// Publish Pages
		$wPageManager = new wsPageManager($this->getID());
		$folders = $wPageManager->getFolders("", TRUE);
		// Push also root folder
		array_push($folders, "/");
		foreach ($folders as $folderPath)
		{
			$pages = $wPageManager->getFolderPages($folderPath);
			foreach ($pages as $pageName)
			{
				$pageFolder = $releaseFolder."/".self::PAGES_FOLDER."/".$folderPath."/".$pageName.".page";
				logger::log($pageFolder, logger::DEBUG);
				
				// Get css and js
				$cssContent .= fileManager::get($pageFolder."/style.css");
				$jsContent .= fileManager::get($pageFolder."/script.js");
				
				// Create destination directory
				// since copy does not create them
				folderManager::create(systemRoot.$publishFolder."/.website/pages/".$folderPath."/".$pageName.".page");
				
				// Copy html				
				$path = $pageFolder."/page.html";
				$pageHTML = systemRoot.$publishFolder."/.website/pages/".$folderPath."/".$pageName.".page"."/page.html";
				fileManager::copy($path, $pageHTML, TRUE);
				
				// Copy php				
				$path = $pageFolder."/page.php";
				$pagePHP = systemRoot.$publishFolder."/.website/pages/".$folderPath."/".$pageName.".page"."/page.php";
				fileManager::copy($path, $pagePHP, TRUE);
				
				// Create Published Page
				// Get contents from template 
				$contents = fileManager::get(systemRoot.paths::getDevRsrcPath()."/websites/pageTemplate.inc");
				// Alter the needed values
				if(!is_null($innerSitePath))
				{
					$contents  = str_replace('{siteInnerPath}', $innerSitePath, $contents);					
				}
				else
				{
					// For future updates
				}
				
				
				// Create the file
				fileManager::create(systemRoot.$publishFolder."/".$folderPath."/".$pageName.".php", $contents, TRUE);
			}
		}
		
		//  Publish Page cssContent && jsContent
		// Format css
		$cssContent = cssParser::format($cssContent);
		
		// Replace resources vars in css
		$resourcePath = $publishFolder."library/resources";
		$resourceUrl = "/library/resources";
		$cssContent = str_replace("%resources%", $resourceUrl, $cssContent);		
		
		fileManager::create(systemRoot.$publishFolder."/library/css/p/style.css", $cssContent, TRUE);
		fileManager::create(systemRoot.$publishFolder."/library/js/p/script.js", $jsContent, TRUE);
		
		
		// Publish Source Objects
		$sourceFolder = $releaseFolder."/".self::SOURCE_FOLDER;
				
		// Set Source cssContent && jsContent
		$cssContent = "";
		$jsContent = "";
		
		$sp = new srcPackage($wsID);
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
				fileManager::create(systemRoot.$publishFolder."/.website/src/".self::SOURCE_FOLDER."/".$objectPath, $objectSource, TRUE);
			}
		}
		
		// Publish Source cssContent && jsContent
		// Format css
		$cssContent = cssParser::format($cssContent);
		
		// Replace resources vars in css
		$resourcePath = $publishFolder."/library/resources";
		$resourceUrl = "/library/resources";
		$cssContent = str_replace("%resources%", $resourceUrl, $cssContent);
		
		fileManager::create(systemRoot.$publishFolder."/library/css/s/style.css", $cssContent, TRUE);
		fileManager::create(systemRoot.$publishFolder."/library/js/s/script.js", $jsContent, TRUE);
		
		// Export application resources
		$this->publishResources($publishFolder."/library/resources/");
	}
}
//#section_end#
?>