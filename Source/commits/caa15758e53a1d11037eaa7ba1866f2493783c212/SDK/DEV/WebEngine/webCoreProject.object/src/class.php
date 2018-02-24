<?php
//#section#[header]
// Namespace
namespace DEV\WebEngine;

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
 * @package	WebEngine
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "archive/zipManager");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Tools", "parsers/jsParser");
importer::import("DEV", "WebEngine", "sdk/webLibrary");
importer::import("DEV", "WebEngine", "sdk/webPackage");
importer::import("DEV", "WebEngine", "resources/resourceLoader");

use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\archive\zipManager;
use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Version\vcs;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;
use \DEV\WebEngine\sdk\webLibrary;
use \DEV\WebEngine\sdk\webPackage;
use \DEV\WebEngine\resources\resourceLoader;

/**
 * Web Core Project
 * 
 * It is the web core project class.
 * Manages web core publishing and packaging for distribution.
 * 
 * @version	1.0-1
 * @created	December 18, 2014, 11:36 (EET)
 * @updated	January 13, 2015, 18:46 (EET)
 */
class webCoreProject extends project
{
	/**
	 * The web core project id.
	 * 
	 * @type	integer
	 */
	const PROJECT_ID = 3;
	
	/**
	 * The web core project type id.
	 * 
	 * @type	integer
	 */
	const PROJECT_TYPE = 3;

	/**
	 * The vcs manager object for the project.
	 * 
	 * @type	vcs
	 */
	private $vcs;

	/**
	 * Initialize the web core project.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Init project
		parent::__construct(self::PROJECT_ID);
		
		// Init vcs
		$this->vcs = new vcs($this->getID());
	}

	/**
	 * Publish the entire web core project.
	 * 
	 * Publish web sdk.
	 * Create distribution packages.
	 * Publish all resources.
	 * 
	 * @param	{type}	$version
	 * 		{description}
	 * 
	 * @param	string	$branchName
	 * 		The source branch name to publish.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function publish($version, $branchName = vcs::MASTER_BRANCH)
	{
		// Validate account
		if (!$this->validate())
			return FALSE;
		
		// Check branch name
		$branchName = (empty($branchName) ? vcs::MASTER_BRANCH : $branchName);
		
		// Publish SDK inside Framework
		webLibrary::publish($version, $branchName);
		
		// Create packages
		$this->createPackages($version, $branchName);
		
		// Create Publish Folder (if doesn't already exist)
		$publishFolder = projectLibrary::getPublishedPath($this->getID(), $version);
		folderManager::create(systemRoot.$publishFolder);
		
		// Publish Resources
		$this->publishResources($publishFolder."/media/", $innerFolder = "/media/");
		
		return TRUE;
	}
	
	/**
	 * Create all distribution packages for the web core.
	 * 
	 * @param	string	$releaseVersion
	 * 		The release version of the packages.
	 * 
	 * @param	string	$branchName
	 * 		The source branch name to get the files from.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function createPackages($releaseVersion, $branchName = vcs::MASTER_BRANCH)
	{
		// Create temporary folder for archive
		$distrosFolder = "/System/Library/WebDistros/";
		$tempFolder = $distrosFolder."_temp/";
	
		// Get repository
		$releasePath = $this->vcs->getCurrentRelease($branchName)."/SDK/";
		
		// Gather all CSS and JS
		$sdkLib = new webLibrary();
		$libraries = $sdkLib->getList();
		foreach ($libraries as $libName)
		{
			$packages = $sdkLib->getPackageList($libName);
			foreach ($packages as $packageName)
			{
				$sdkp = new webPackage();
				$objects = $sdkp->getPackageObjects($libName, $packageName, NULL);
				
				// Initialize
				$cssContent = "";
				$jsContent = "";
				foreach ($objects as $objectInfo)
				{
					// Get release path
					$ns = str_replace("::", "/", $objectInfo['namespace']);
					$releaseObjectPath = $releasePath."/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".object/";
					
					// Object CSS
					$cssContent .= trim(fileManager::get($releaseObjectPath."/model/style.css"))."\n";
					
					// Object JS
					$jsContent .= trim(fileManager::get($releaseObjectPath."/script.js"))."\n";
				}
				
				// Replace resources vars in css
				$resourcePath = "/lib/rsrc/c/";
				$cssContent = str_replace("%resources%", $resourcePath, $cssContent);
				$cssContent = str_replace("%{resources}", $resourcePath, $cssContent);
				$cssContent = str_replace("%media%", $resourcePath, $cssContent);
				$cssContent = str_replace("%{media}", $resourcePath, $cssContent);
				
				// Format css
				$cssContent = cssParser::format($cssContent);
				
				// Create resource files
				$resourceID = resourceLoader::getResourceID($libName, $packageName);
				if (!empty($cssContent))
					fileManager::create(systemRoot.$tempFolder."/lib/css/c/".$resourceID.".css", $cssContent, TRUE);
				if (!empty($jsContent))
					fileManager::create(systemRoot.$tempFolder."/lib/js/c/".$resourceID.".js", $jsContent, TRUE);
			}
		}
		
		// Add jquery to exported core
		$jqScripts = array();
		$jqScripts['jquery'] = "jquery.js";
		$jqScripts['jquery.easing'] = "jquery.easing.js";
		$jqScripts['jquery.dotimeout'] = "jquery.ba-dotimeout.min.js";
		foreach ($jqScripts as $scriptId => $scriptName)
		{
			$jsContent = fileManager::get(systemRoot.$this->getResourcesFolder()."/jQuery/".$scriptName);
			fileManager::create(systemRoot.$tempFolder."/lib/js/q/jq.".$scriptName.".js", $jsContent);
		}
		
		// Publish Resources for packing
		$this->publishResources($tempFolder."/lib/rsrc/c/");
		
		// Create Web SDK Zip Packages
		$archive = systemRoot.$distrosFolder."/wsdk_".$releaseVersion.".zip";
		
		// Zip files
		zipManager::createInnerDirectory($archive, ".wec");
		$contents = array();
		$contents['dirs'][] = systemRoot.webLibrary::RELEASE_PATH;
		$contents['dirs'][] = systemRoot.$tempFolder."/lib";
		zipManager::append($archive, $contents, ".wec", FALSE);
		
		// Delete temp folder
		folderManager::remove(systemRoot.$tempFolder, "", TRUE);
		
		return TRUE;
	}
}
//#section_end#
?>