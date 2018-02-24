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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "filesystem/directory");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Prototype", "pageMap");
importer::import("DEV", "Profiler", "logger");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Resources", "paths");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Websites", "source/srcPackage");
importer::import("DEV", "Websites", "settings/wsSettings");
importer::import("DEV", "Websites", "wsServer");
importer::import("DEV", "WebEngine", "resources/resourceLoader");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\directory;
use \DEV\Prototype\sourceMap;
use \DEV\Prototype\pageMap;
use \DEV\Profiler\logger;
use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Resources\paths;
use \DEV\Tools\parsers\cssParser;
use \DEV\Version\vcs;
use \DEV\Websites\source\srcPackage;
use \DEV\Websites\settings\wsSettings;
use \DEV\Websites\wsServer;
use \DEV\WebEngine\resources\resourceLoader;

/**
 * Website Manager Class
 * 
 * This is the main class for managing a website project.
 * 
 * @version	4.0-2
 * @created	June 30, 2014, 12:58 (EEST)
 * @updated	January 4, 2015, 1:50 (EET)
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
	 * The website mapping folder (source, pages).
	 * 
	 * @type	string
	 */
	const MAP_FOLDER = 'map';
	
	/**
	 * The website settings folder.
	 * 
	 * @type	string
	 */
	const SETTINGS_FOLDER = 'Settings';
	
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
	 * Initialize a website project.
	 * 
	 * @param	sting	$id
	 * 		The website's id.
	 * 		Leave empty for new website or name initialization.
	 * 		It is empty by default.
	 * 
	 * @param	sting	$name
	 * 		The website's unique name.
	 * 		Leave empty for new website or id initialization.
	 * 		It is empty by default.
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
			return TRUE;
		
		// Create index file
		fileManager::create(systemRoot.$this->getRootFolder()."/".self::INDEX_FILE, "", TRUE);
	
		// Create Index (Mapping) Files
		$this->createWebsiteIndex();
		$this->createSourceMap();
		
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
		$itemPath = self::MAP_FOLDER;
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
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function publish($version, $branchName = vcs::MASTER_BRANCH)
	{
		// Check branch name
		$branchName = (empty($branchName) ? vcs::MASTER_BRANCH : $branchName);
		
		// Get website id
		$wsID = $this->getID();
		// Get current release folder
		$releaseFolder = $this->vcs->getCurrentRelease($branchName);
		
		// Create Publish Folder (if doesn't already exist)
		$publishFolder = projectLibrary::getPublishedPath($this->getID(), $version);
		folderManager::create(systemRoot.$publishFolder);
		
		// Create website inner folders
		folderManager::create(systemRoot.$publishFolder."/.website/");
		folderManager::create(systemRoot.$publishFolder."/.website/settings/");
		folderManager::create(systemRoot.$publishFolder."/.website/map/");
		
		// Copy settings and map index files
		fileManager::copy(systemRoot.$this->getRootFolder()."/".self::SETTINGS_FOLDER."/settings.xml", systemRoot.$publishFolder."/.website/settings/settings.xml");
		fileManager::copy(systemRoot.$this->getRootFolder()."/".self::SETTINGS_FOLDER."/meta_settings.xml", systemRoot.$publishFolder."/.website/settings/meta_settings.xml");
		fileManager::copy($releaseFolder."/".self::MAP_FOLDER."/source.xml", systemRoot.$publishFolder."/.website/map/source.xml");
		fileManager::copy($releaseFolder."/".self::MAP_FOLDER."/pages.xml", systemRoot.$publishFolder."/.website/map/pages.xml");
		
		// Get robots file
		$robots = fileManager::get(systemRoot.$this->getRootFolder()."/robots.txt");
		
		// Add site map at the end
		$wsSettings = new wsSettings($this->getID());
		$site_url = $wsSettings->get("site_url");
		$sitemap = "Sitemap: ".$site_url."/Sitemap.xml";
		$robots = trim($robots);
		$robots .= "\n\n".$sitemap;
		// Write file
		fileManager::create(systemRoot.$publishFolder."/robots.txt", $robots);
		
		// Get webroot
		$webRoot = $wsSettings->get("web_root");
		
		// Publish Pages
		$pageMap = new pageMap($releaseFolder."/".self::MAP_FOLDER, "pages.xml");
		$folders = $pageMap->getFolders("", TRUE);
		// Push also root folder
		array_push($folders, "/");
		foreach ($folders as $folderPath)
		{
			$pages = $pageMap->getFolderPages($folderPath);
			foreach ($pages as $pageName)
			{
				$pageFolder = $releaseFolder."/".self::PAGES_FOLDER."/".$folderPath."/".$pageName.".page";
				
				// Create destination directory
				// since copy does not create them
				folderManager::create(systemRoot.$publishFolder."/.website/pages/".$folderPath."/".$pageName.".page");
				
				// Copy html
				$pageHTML = systemRoot.$publishFolder."/.website/pages/".$folderPath."/".$pageName.".page/page.html";
				fileManager::copy($pageFolder."/page.html", $pageHTML, TRUE);
				
				// Copy php
				$pagePHP = systemRoot.$publishFolder."/.website/pages/".$folderPath."/".$pageName.".page/page.php";
				fileManager::copy($pageFolder."/page.php", $pagePHP, TRUE);
				
				// Copy settings
				$pageSettings = systemRoot.$publishFolder."/.website/pages/".$folderPath."/".$pageName.".page/settings.xml";
				fileManager::copy($pageFolder."/settings.xml", $pageSettings, TRUE);
				
				// Create Published Page
				// Get contents from template 
				$contents = fileManager::get(systemRoot.paths::getDevRsrcPath()."/websites/pageTemplate.inc");
				
				// Alter the needed values
				$contents = str_replace('{siteInnerPath}', $webRoot, $contents);
				
				// Create the file
				fileManager::create(systemRoot.$publishFolder."/".$folderPath."/".$pageName.".php", $contents, TRUE);
				
				// Get css and js
				$cssContent = fileManager::get($pageFolder."/style.css");
				$jsContent = fileManager::get($pageFolder."/script.js");
				
				// Replace resources vars in css
				$resourceUrl = "/".$webRoot."/lib/rsrc";
				$resourceUrl = directory::normalize($resourceUrl);
				$cssContent = str_replace("%resources%", $resourceUrl, $cssContent);
				$cssContent = str_replace("%{resources}", $resourceUrl, $cssContent);
				$cssContent = str_replace("%media%", $resourceUrl, $cssContent);
				$cssContent = str_replace("%{media}", $resourceUrl, $cssContent);
				
				// Format css (protected full urls (http://, https://))
				$cssContent = str_replace("http://", "httpslsl", $cssContent);
				$cssContent = str_replace("https://", "httpsslsl", $cssContent);
				$cssContent = cssParser::format($cssContent);
				$cssContent = str_replace("httpslsl", "http://", $cssContent);
				$cssContent = str_replace("httpsslsl", "https://", $cssContent);
				
				$pagePath = trim($folderPath."/".$pageName, "/");
				$pagePath = directory::normalize($pagePath);
				$resourceID = resourceLoader::getResourceID("Pages", $pagePath);
				if (!empty($cssContent))
					fileManager::create(systemRoot.$publishFolder."/lib/css/p/".$resourceID.".css", $cssContent, TRUE);
				if (!empty($jsContent))
					fileManager::create(systemRoot.$publishFolder."/lib/js/p/".$resourceID.".js", $jsContent, TRUE);
			}
		}
		
		
		// Publish Source Objects
		$sourceMap = new sourceMap($releaseFolder."/".self::MAP_FOLDER, "source.xml");
		$libraries = $sourceMap->getLibraryList();
		foreach ($libraries as $library)
		{
			$packages = $sourceMap->getPackageList($library);
			foreach ($packages as $package)
			{
				// Set Package cssContent && jsContent
				$cssContent = "";
				$jsContent = "";
		
				// Get object list
				$objects = $sourceMap->getObjectList($library, $package, $namespace = NULL);
				foreach ($objects as $object)
				{
					$namespace = str_replace("::", "/", $object['namespace']);
					$objectPath = $library."/".$package."/".$namespace."/".$object['name'].".object";
					
					// Get css and js
					$fullObjectPath = $releaseFolder.self::SOURCE_FOLDER."/".$objectPath;
					$cssContent .= fileManager::get($fullObjectPath."/model/style.css");
					$jsContent .= fileManager::get($fullObjectPath."/script.js");
					
					// Export source
					$objectSource = fileManager::get($fullObjectPath."/src/class.php");
					$objectPath = $library."/".$package."/".$namespace."/".$object['name'].".php";
					fileManager::create(systemRoot.$publishFolder."/.website/".self::SOURCE_FOLDER."/".$objectPath, $objectSource, TRUE);
				}
			
				// Replace resources vars in css
				$resourceUrl = "/".$webRoot."/lib/rsrc";
				$resourceUrl = directory::normalize($resourceUrl);
				$cssContent = str_replace("%resources%", $resourceUrl, $cssContent);
				$cssContent = str_replace("%{resources}", $resourceUrl, $cssContent);
				$cssContent = str_replace("%media%", $resourceUrl, $cssContent);
				$cssContent = str_replace("%{media}", $resourceUrl, $cssContent);
				
				// Format css (protected full urls (http://, https://))
				$cssContent = str_replace("http://", "httpslsl", $cssContent);
				$cssContent = str_replace("https://", "httpsslsl", $cssContent);
				$cssContent = cssParser::format($cssContent);
				$cssContent = str_replace("httpslsl", "http://", $cssContent);
				$cssContent = str_replace("httpsslsl", "https://", $cssContent);
				
				if (!empty($cssContent))
					fileManager::create(systemRoot.$publishFolder."/lib/css/s/style.css", $cssContent, TRUE);
				if (!empty($jsContent))
					fileManager::create(systemRoot.$publishFolder."/lib/js/s/script.js", $jsContent, TRUE);
			}
		}

		// Export application resources
		$this->publishResources($publishFolder."/lib/rsrc/");
		
		return TRUE;
	}
}
//#section_end#
?>