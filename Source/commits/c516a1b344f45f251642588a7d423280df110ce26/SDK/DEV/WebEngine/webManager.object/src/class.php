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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "BootLoader");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "archive::zipManager");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Tools", "parsers::jsParser");
importer::import("DEV", "WebEngine", "sdk::webLibrary");
importer::import("DEV", "WebEngine", "sdk::webPackage");

use \ESS\Protocol\BootLoader;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\archive\zipManager;
use \DEV\Projects\project;
use \DEV\Version\vcs;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;
use \DEV\WebEngine\sdk\webLibrary;
use \DEV\WebEngine\sdk\webPackage;


/**
 * Web SDK Manager Class
 * 
 * Class is responsible for publishing / releasing the Web SDK and building the appropriate Packages Bundles for uploading the Web Core to a remote server
 * 
 * @version	0.2-4
 * @created	October 30, 2014, 21:21 (EET)
 * @revised	November 7, 2014, 12:02 (EET)
 */
class webManager
{
	/**
	 * Publishes all the SDK Libraries.
	 * 
	 * @param	string	$branchName
	 * 		The branch to publish.
	 * 
	 * @return	void
	 */
	public static function publish($branchName = vcs::MASTER_BRANCH)
	{
		// Publish SDK inside Framework
		sdkLibrary::publish($branchName);
		
		
		// Create packages
		// Create temporary folder for archive
		$distrosFolder = "/System/Library/WebDistros/";
		$tempFolder = $distrosFolder."_temp/";
	
		// Get repository
		$projectID = 3;
		$project = new project($projectID);
		$vcs = new vcs($projectID);
		$releasePath = $vcs->getCurrentRelease($branchName)."/SDK/";
		
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
				$cssStyles = "";
				$jsContent = "";
				foreach ($objects as $objectInfo)
				{
					// Object CSS
					$cssStyles .= trim(fileManager::get($releaseObjectPath."/model/style.css"))."\n";
					
					// Object JS
					$jsContent .= trim(fileManager::get($releaseObjectPath."/script.js"))."\n";
				}
				
				// Create temporary core css file for packing
				// Replace resources vars in css
				$resourcePath = "/wrsc/Media/";
				$cssContent = str_replace("%resources%", $resourcePath, $cssStyles);
				
				// TODO
				// For now do not export, just create all the files to a folder				
				fileManager::create(systemRoot.$tempFolder."/wrsc/css/".$libName."_".$packageName.".css", $cssContent, TRUE);
				fileManager::create(systemRoot.$tempFolder."/wrsc/js/".$libName."_".$packageName.".js", $jsContent, TRUE);
			}
		}
		
		// Publish Resources for packing
		$project->publishResources($tempFolder."/wrsc/media/");
		
		
		// Create Web SDK Zip Packages
		// Find repository release version where this project publish originates from
		// TODO
		
		$version = '0.1';
		$archive = systemRoot.$distrosFolder."/wsdk_".$version.".zip";
		zipManager::createInnerDirectory($archive, ".wec");
		$contents = array();
		$dirs = array();
		$dirs[] = systemRoot.webLibrary::RELEASE_PATH;
		$dirs[] = systemRoot.$tempFolder."/wrsc";
		$contents['dirs'] = $dirs;
		zipManager::append($archive, $contents, ".wec", FALSE);
		
		// Delete temp folder
		folderManager::remove(systemRoot.$tempFolder, "", TRUE);
	}
}
//#section_end#
?>