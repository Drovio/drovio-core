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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Model", "core/resource");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "filesystem/directory");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Prototype", "pageMap");
importer::import("DEV", "Profiler", "logger");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Tools", "parsers/jsParser");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "WebEngine", "resources/resourceLoader");
importer::import("DEV", "WebTemplates", "prototype/templatePrototype");
importer::import("DEV", "WebTemplates", "prototype/templateThemePrototype");
importer::import("DEV", "WebTemplates", "prototype/templateThemeCSSPrototype");
importer::import("DEV", "WebTemplates", "prototype/templateThemeJSPrototype");
importer::import("DEV", "Websites", "pages/sPage");
importer::import("DEV", "Websites", "pages/wsPage");
importer::import("DEV", "Websites", "source/srcPackage");
importer::import("DEV", "Websites", "settings/wsSettings");
importer::import("DEV", "Websites", "templates/wsTemplate");
importer::import("DEV", "Websites", "wsServer");

use \API\Model\core\resource;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\directory;
use \DEV\Prototype\sourceMap;
use \DEV\Prototype\pageMap;
use \DEV\Profiler\logger;
use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;
use \DEV\Version\vcs;
use \DEV\WebEngine\resources\resourceLoader;
use \DEV\WebTemplates\prototype\templatePrototype;
use \DEV\WebTemplates\prototype\templateThemePrototype;
use \DEV\WebTemplates\prototype\templateThemeCSSPrototype;
use \DEV\WebTemplates\prototype\templateThemeJSPrototype;
use \DEV\Websites\pages\sPage;
use \DEV\Websites\pages\wsPage;
use \DEV\Websites\source\srcPackage;
use \DEV\Websites\settings\wsSettings;
use \DEV\Websites\templates\wsTemplate;
use \DEV\Websites\wsServer;

/**
 * Website Manager Class
 * 
 * This is the main class for managing a website project.
 * 
 * @version	7.0-3
 * @created	June 30, 2014, 10:58 (BST)
 * @updated	December 12, 2015, 19:16 (GMT)
 */
class website extends project
{
	/**
	 * The website project type id.
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
	 * The name of the templates folder.
	 * 
	 * @type	string
	 */
	const TEMPLATES_FOLDER = 'templates';
	
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
		$this->createPages();
		
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
		$entry = $parser->create(self::TEMPLATES_FOLDER);
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
	 * Create initial website pages including index.php, robots.txt, Sitemap.xml
	 * 
	 * @return	void
	 */
	private function createPages()
	{
		// Get website id
		$websiteID = $this->getID();
		
		// Create index page
		$wsPage = new wsPage($websiteID, $folder = "");
		$wsPage->create("index.php");
		
		// Create sitemap
		$sPage = new sPage($websiteID, $folder = "");
		$sPage->create("Sitemap.xml");
		
		// Create robots
		$sPage = new sPage($websiteID, $folder = "");
		$sPage->create("robots.txt");
		
		$robotsContents = "User-agent: *\n";
		$robotsContents .= "Disallow: ./";
		$sPage->update($robotsContents);
	}
	
	/**
	 * Get all website templates.
	 * 
	 * @return	array
	 * 		An array of all template names.
	 */
	public function getTemplates()
	{
		return $this->getIndexObjects(self::TEMPLATES_FOLDER, "template");
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
	 * @return	boolean
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
		$itemTrunkPath = $this->vcs->updateItem($itemID, TRUE);
		
		// Load index
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		
		// Check if the group exists (compatibility)
		$groupItem = $parser->evaluate("//".$group)->item(0);
		if (empty($groupItem))
		{
			$root = $parser->evaluate("/website")->item(0);
			$groupItem = $parser->create($group);
			$parser->append($root, $groupItem);
		}
		
		// Check if item already exists
		$item = $parser->evaluate("//".$group."/".$type."[@name='".$name."']")->item(0);
		if (!is_null($item))
			return FALSE;
		
		// Get The root
		$root = $parser->evaluate("//".$group)->item(0);
		if (empty($root))
			return FALSE;
			
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
		$itemTrunkPath = $this->vcs->updateItem($itemID, TRUE);
		
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
		
		// Get current release folder
		$releaseFolder = $this->vcs->getCurrentRelease($branchName);
		
		// Create Publish Folder (if doesn't already exist)
		$publishFolder = projectLibrary::getPublishedPath($this->getID(), $version);
		folderManager::create(systemRoot.$publishFolder);
		$websiteFolder = $publishFolder."/.website";
		
		// Create website inner folders
		folderManager::create(systemRoot.$websiteFolder."/");
		folderManager::create(systemRoot.$websiteFolder."/settings/");
		folderManager::create(systemRoot.$websiteFolder."/".self::MAP_FOLDER);
		
		// Copy settings and map index files
		fileManager::copy(systemRoot.$this->getRootFolder()."/".self::SETTINGS_FOLDER."/settings.xml", systemRoot.$websiteFolder."/settings/settings.xml");
		fileManager::copy(systemRoot.$this->getRootFolder()."/".self::SETTINGS_FOLDER."/meta_settings.xml", systemRoot.$websiteFolder."/settings/meta_settings.xml");
		fileManager::copy($releaseFolder."/".self::INDEX_FILE, systemRoot.$websiteFolder."/".self::INDEX_FILE);
		fileManager::copy($releaseFolder."/".self::MAP_FOLDER."/".self::SOURCE_INDEX, systemRoot.$websiteFolder."/".self::MAP_FOLDER."/".self::SOURCE_INDEX);
		fileManager::copy($releaseFolder."/".self::MAP_FOLDER."/pages.xml", systemRoot.$websiteFolder."/".self::MAP_FOLDER."/pages.xml");
		
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
		
		// Export website resources to the right folder
		$this->publishResources($publishFolder."/lib/ws/rsrc/");
		
		// Resource url
		$resourceUrl = "/".$webRoot."/lib/ws/rsrc/";
		$resourceUrl = directory::normalize($resourceUrl);
		
		// Get page template contents
		$pageTemplate = resource::get("/resources/DEV/websites/pageTemplate.inc");
		$pageTemplate = str_replace('{siteInnerPath}', $webRoot, $pageTemplate);
		
		// Publish Templates (themes)
		$templates = array();
		$parser = new DOMParser();
		try
		{
			// Load file
			$parser->load($releaseFolder."/".self::INDEX_FILE, FALSE);
			
			// Load templates
			$tplElements = $parser->evaluate("//template");
			foreach ($tplElements as $tpl)
				$templates[] = $parser->attr($tpl, "name");
		}
		catch (Exception $ex) {}
		foreach ($templates as $templateName)
		{
			$templateFolder = $releaseFolder."/".self::TEMPLATES_FOLDER."/".$templateName.".".wsTemplate::FILE_TYPE;
			$templateIndexFilePath = $templateFolder."/".templatePrototype::INDEX_FILE;
			$template = new templatePrototype($templateIndexFilePath);
			$templateThemes = $template->getThemes();
			foreach ($templateThemes as $themeName)
			{
				// Initialize theme css and js
				$themeCSS = "";
				$themeJS = "";
				
				// Get theme prototype
				$themeFolderPath = $templateFolder."/".templatePrototype::THEMES_FOLDER."/".templateThemePrototype::getThemeFolder($themeName);
				$themeIndexFilePath = $themeFolderPath."/".templateThemePrototype::INDEX_FILE;
				$tplTheme = new templateThemePrototype($templateIndexFilePath, $themeIndexFilePath);
				
				// Get all theme css files
				$themeCSSObjects = $tplTheme->getAllCSS();
				foreach ($themeCSSObjects as $themeCSSName)
				{
					$cssFolderPath = $themeFolderPath."/".templateThemePrototype::CSS_FOLDER."/".$themeCSSName.".".templateThemeCSSPrototype::FILE_TYPE;
					$tplThemeCss = new templateThemeCSSPrototype($templateIndexFilePath, $themeIndexFilePath, $cssFolderPath);
					$themeCSS .= $tplThemeCss->get($normalCSS = TRUE)."\n";
				}
				// Create theme css resource file
				$themeCSS = str_replace("%resources%", $resourceUrl, $themeCSS);
				$themeCSS = str_replace("%{resources}", $resourceUrl, $themeCSS);
				$themeCSS = str_replace("%media%", $resourceUrl, $themeCSS);
				$themeCSS = str_replace("%{media}", $resourceUrl, $themeCSS);
				$themeCSS = cssParser::format($themeCSS);
				if (!empty($themeCSS))
				{
					$cssResource = "/lib/ws/css/t/".resourceLoader::getFileName("css", $templateName, $themeName, resourceLoader::RSRC_CSS);
					fileManager::create(systemRoot.$publishFolder.$cssResource.".css", $themeCSS, TRUE);
				}
				
				// Get all theme js files
				$themeJSObjects = $tplTheme->getAllJS();
				foreach ($themeJSObjects as $themeJSName)
				{
					$jsFilePath = $themeFolderPath."/".templateThemePrototype::JS_FOLDER."/".$themeJSName.".js";
					$tplThemeJs = new templateThemeJSPrototype($templateIndexFilePath, $themeIndexFilePath, $jsFilePath);
					$themeJS .= $tplThemeJs->get()."\n";
				}
				// Create theme js resource file
				$themeJS = jsParser::format($themeJS);
				if (!empty($themeJS))
				{
					$jsResource = "/lib/ws/js/t/".resourceLoader::getFileName("js", $templateName, $themeName, resourceLoader::RSRC_JS);
					fileManager::create(systemRoot.$publishFolder.$jsResource.".js", $themeJS, TRUE);
				}
			}
		}
		
		// Publish Pages
		$pageMap = new pageMap($releaseFolder."/".self::MAP_FOLDER, "pages.xml");
		$folders = $pageMap->getFolders("", TRUE);
		array_push($folders, "/");
		foreach ($folders as $folderPath)
		{
			$wPages = $pageMap->getFolderPages($folderPath, $full = TRUE);
			foreach ($wPages as $pageType => $pages)
				foreach ($pages as $pageName)
					if ($pageType == wsPage::PAGE_TYPE)
					{
						$pageName = rtrim($pageName, ".php");
						$pageFolder = $releaseFolder."/".self::PAGES_FOLDER."/".$folderPath."/".$pageName.".page";
						
						// Create destination directory
						folderManager::create(systemRoot.$websiteFolder."/".self::PAGES_FOLDER."/".$folderPath."/".$pageName.".page");
						
						// Copy html
						$pageHTML = systemRoot.$websiteFolder."/".self::PAGES_FOLDER."/".$folderPath."/".$pageName.".page/page.html";
						// Replace resources vars in page html
						$pageHTML = str_replace("%resources%", $resourceUrl, $pageHTML);
						$pageHTML = str_replace("%{resources}", $resourceUrl, $pageHTML);
						$pageHTML = str_replace("%media%", $resourceUrl, $pageHTML);
						$pageHTML = str_replace("%{media}", $resourceUrl, $pageHTML);
						// Create file
						fileManager::copy($pageFolder."/page.html", $pageHTML, TRUE);
						
						// Copy php
						$pagePHP = systemRoot.$websiteFolder."/".self::PAGES_FOLDER."/".$folderPath."/".$pageName.".page/page.php";
						fileManager::copy($pageFolder."/page.php", $pagePHP, TRUE);
						
						// Copy settings
						$pageSettings = systemRoot.$websiteFolder."/".self::PAGES_FOLDER."/".$folderPath."/".$pageName.".page/settings.xml";
						fileManager::copy($pageFolder."/settings.xml", $pageSettings, TRUE);
						
						// Create landing pages
						$relPagePath =	$publishFolder."/".$folderPath."/".$pageName;
						$relPagePath = rtrim($relPagePath, ".php").".php";
						fileManager::create(systemRoot.$relPagePath, $pageTemplate, TRUE);
						
						// Get css and js
						$cssContent = fileManager::get($pageFolder."/style.css");
						$jsContent = fileManager::get($pageFolder."/script.js");
						
						// Replace resources vars in css
						$cssContent = str_replace("%resources%", $resourceUrl, $cssContent);
						$cssContent = str_replace("%{resources}", $resourceUrl, $cssContent);
						$cssContent = str_replace("%media%", $resourceUrl, $cssContent);
						$cssContent = str_replace("%{media}", $resourceUrl, $cssContent);
						
						// Format css
						$cssContent = cssParser::format($cssContent);
						
						$pagePath = trim($folderPath."/".$pageName, "/");
						$pagePath = directory::normalize($pagePath);
						if (!empty($cssContent))
						{
							$cssResource = "/lib/ws/css/p/".resourceLoader::getFileName("css", "Pages", $pagePath, resourceLoader::RSRC_CSS);
							fileManager::create(systemRoot.$publishFolder.$cssResource.".css", $cssContent, TRUE);
						}
						if (!empty($jsContent))
						{
							$jsResource = "/lib/ws/js/p/".resourceLoader::getFileName("js", "Pages", $pagePath, resourceLoader::RSRC_JS);
							fileManager::create(systemRoot.$publishFolder.$jsResource.".js", $jsContent, TRUE);
						}
					}
					else
					{
						$pagePath = $releaseFolder."/".self::PAGES_FOLDER."/".$folderPath."/".$pageName;
						$pageContents = fileManager::get($pagePath);
						
						// Create landing page
						fileManager::create(systemRoot.$publishFolder."/".$folderPath."/".$pageName, $pageContents, TRUE);
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
					fileManager::create(systemRoot.$websiteFolder."/".self::SOURCE_FOLDER."/".$objectPath, $objectSource, TRUE);
				}
			
				// Replace resources vars in css
				$cssContent = str_replace("%resources%", $resourceUrl, $cssContent);
				$cssContent = str_replace("%{resources}", $resourceUrl, $cssContent);
				$cssContent = str_replace("%media%", $resourceUrl, $cssContent);
				$cssContent = str_replace("%{media}", $resourceUrl, $cssContent);
				
				// Format css
				$cssContent = cssParser::format($cssContent);
				
				// Create resource files
				if (!empty($cssContent))
				{
					$cssResource = "/lib/ws/css/s/".resourceLoader::getFileName("css", $library, $package, resourceLoader::RSRC_CSS);
					fileManager::create(systemRoot.$publishFolder.$cssResource.".css", $cssContent, TRUE);
				}
				if (!empty($jsContent))
				{
					$jsResource = "/lib/ws/js/s/".resourceLoader::getFileName("js", $library, $package, resourceLoader::RSRC_JS);
					fileManager::create(systemRoot.$publishFolder.$jsResource.".js", $jsContent, TRUE);
				}
			}
		}
		
		// Publish website literals only
		$this->publishLiterals($websiteFolder);
		
		return TRUE;
	}
}
//#section_end#
?>