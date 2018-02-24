<?php
//#section#[header]
// Namespace
namespace DEV\WebTemplates;

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
 * @package	WebTemplates
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("DEV", "Projects", "project");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "WebTemplates", "prototype/template");

use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Tools\parsers\cssParser;
use \DEV\Version\vcs;
use \DEV\WebTemplates\prototype\template;

/**
 * Template project class
 * 
 * This class represents a template object and uses the template prototype for structure.
 * 
 * @version	0.1-1
 * @created	September 17, 2015, 1:56 (EEST)
 * @updated	September 17, 2015, 1:56 (EEST)
 */
class templateProject extends project
{
	/**
	 * The template project type id.
	 * 
	 * @type	integer
	 */
	const PROJECT_TYPE = 6;

	/**
	 * The vcs manager object
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * The template prototype object.
	 * 
	 * @type	template
	 */
	private $template;
	
	/**
	 * Initialie a website template project.
	 * 
	 * @param	integer	$id
	 * 		The template's id.
	 * 		Leave empty for new template or name initialization.
	 * 		It is empty by default.
	 * 
	 * @param	string	$name
	 * 		The template's unique name.
	 * 		Leave empty for new template or id initialization.
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
		
		// Get index file path
		$indexFilePath = $this->createIndexFile();
		
		// Initialize template prototype
		$this->template = new template($indexFilePath, $rootRelative = FALSE);
	}
	
	/**
	 * Create the template structure.
	 * This function initializes the vcs object and calls the parent to create the template structure.
	 * 
	 * @return	void
	 */
	protected function createIndexFile()
	{
		// Create vcs item
		$itemID = $this->getItemID("templateIndex");
		$itemPath = "/";
		$itemName = template::INDEX_FILE;
		$indexFilePath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		if ($indexFilePath === FALSE)
			$indexFilePath = $this->vcs->getIndexFilePath($update = FALSE);
		
		return $indexFilePath;
	}
	
	/**
	 * Implementation of the abstract parent method.
	 * It gets the index file path according to vcs.
	 * 
	 * @param	boolean	$update
	 * 		Whether to update the item for commit or not.
	 * 
	 * @return	string
	 * 		The index file path.
	 */
	public function getIndexFilePath($update = FALSE)
	{
		// Get vcs item id
		$itemID = $this->getItemID("templateIndex");
		
		// Get item path (update or not)
		return ($update ? $this->vcs->updateItem($itemID) : $this->vcs->getItemTrunkPath($itemID));
	}
	
	/**
	 * Get all template pages.
	 * 
	 * @return	array
	 * 		An array of all page names.
	 */
	public function getPages()
	{
		return $this->template->getPages();
	}
	
	/**
	 * Get all template themes.
	 * 
	 * @return	array
	 * 		An array of all theme names.
	 */
	public function getThemes()
	{
		return $this->template->getThemes();
	}
	
	/**
	 * Add an object entry to the main template index.
	 * 
	 * @param	string	$group
	 * 		The name of the group.
	 * 
	 * @param	string	$type
	 * 		The object type (the tag name).
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if there is another object of the same type with the same name.
	 */
	public function addObjectIndex($group, $type, $name)
	{
		// Get vcs item and update
		$itemID = $this->getItemID("templateIndex");
		$this->vcs->updateItem($itemID);
		
		// Add object index to template prototype
		return $this->template->addObjectIndex($group, $type, $name);
	}
	
	/**
	 * Remove an object entry from the template index.
	 * 
	 * @param	string	$group
	 * 		The name of the group.
	 * 
	 * @param	string	$name
	 * 		The object type (the tag name).
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeObjectIndex($group, $name)
	{
		// Get vcs item and update
		$itemID = $this->getItemID("templateIndex");
		$this->vcs->updateItem($itemID);
		
		// Add object index to template prototype
		return $this->template->removeObjectIndex($group, $name);
	}
	
	/**
	 * Get the vcs item id.
	 * Use this class as a template for template item ids.
	 * 
	 * @param	string	$suffix
	 * 		The id suffix.
	 * 
	 * @return	string
	 * 		The item hash id.
	 */
	public function getItemID($suffix)
	{
		return "wstpl".md5("wstpl_".$this->getID()."_".$suffix);
	}
	
	/**
	 * Publish the current template to the given version.
	 * 
	 * @param	string	$version
	 * 		The version to publish the template.
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
		/*
		// Get current release folder
		$releaseFolder = $this->vcs->getCurrentRelease($branchName);
		
		// Create Publish Folder (if doesn't already exist)
		$publishFolder = projectLibrary::getPublishedPath($this->getID(), $version);
		folderManager::create(systemRoot.$publishFolder);
		$websiteFolder = $publishFolder."/.website";
		
		// Create website inner folders
		folderManager::create(systemRoot.$websiteFolder."/");
		folderManager::create(systemRoot.$websiteFolder."/settings/");
		folderManager::create(systemRoot.$websiteFolder."/map/");
		
		// Copy settings and map index files
		fileManager::copy(systemRoot.$this->getRootFolder()."/".self::SETTINGS_FOLDER."/settings.xml", systemRoot.$websiteFolder."/settings/settings.xml");
		fileManager::copy(systemRoot.$this->getRootFolder()."/".self::SETTINGS_FOLDER."/meta_settings.xml", systemRoot.$websiteFolder."/settings/meta_settings.xml");
		fileManager::copy($releaseFolder."/".self::MAP_FOLDER."/source.xml", systemRoot.$websiteFolder."/map/source.xml");
		fileManager::copy($releaseFolder."/".self::MAP_FOLDER."/pages.xml", systemRoot.$websiteFolder."/map/pages.xml");
		
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
		
		// Resource url
		$resourceUrl = "/".$webRoot."/lib/rsrc/ws";
		$resourceUrl = directory::normalize($resourceUrl);
		
		// Get page template contents
		$pageTemplate = resource::get("/resources/DEV/websites/pageTemplate.inc");
		$pageTemplate = str_replace('{siteInnerPath}', $webRoot, $pageTemplate);
		
		// Publish Pages
		$pageMap = new pageMap($releaseFolder."/".self::MAP_FOLDER, "pages.xml");
		$folders = $pageMap->getFolders("", TRUE);
		// Push also root folder
		array_push($folders, "/");
		foreach ($folders as $folderPath)
		{
			$wPages = $pageMap->getFolderPages($parent, $full = TRUE);
			foreach ($wPages as $pageType => $pages)
				foreach ($pages as $pageName)
					if ($pageType == wsPage::PAGE_TYPE)
					{
						$pageFolder = $releaseFolder."/".self::PAGES_FOLDER."/".$folderPath."/".$pageName.".page";
						
						// Create destination directory
						folderManager::create(systemRoot.$websiteFolder."/pages/".$folderPath."/".$pageName.".page");
						
						// Copy html
						$pageHTML = systemRoot.$websiteFolder."/pages/".$folderPath."/".$pageName.".page/page.html";
						// Replace resources vars in page html
						$pageHTML = str_replace("%resources%", $resourceUrl, $pageHTML);
						$pageHTML = str_replace("%{resources}", $resourceUrl, $pageHTML);
						$pageHTML = str_replace("%media%", $resourceUrl, $pageHTML);
						$pageHTML = str_replace("%{media}", $resourceUrl, $pageHTML);
						// Create file
						fileManager::copy($pageFolder."/page.html", $pageHTML, TRUE);
						
						// Copy php
						$pagePHP = systemRoot.$websiteFolder."/pages/".$folderPath."/".$pageName.".page/page.php";
						fileManager::copy($pageFolder."/page.php", $pagePHP, TRUE);
						
						// Copy settings
						$pageSettings = systemRoot.$websiteFolder."/pages/".$folderPath."/".$pageName.".page/settings.xml";
						fileManager::copy($pageFolder."/settings.xml", $pageSettings, TRUE);
						
						// Create landing pages
						$relPagePath =	$publishFolder."/".$folderPath."/".$pageName;
						$relPagePath = rtrim($relPagePath, ".php");
						$relPagePath .= ".php";
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
		*/
		
		// Export website resources to the right folder
		//$this->publishResources($publishFolder."/lib/rsrc/ws/");

		return TRUE;
	}
}
//#section_end#
?>