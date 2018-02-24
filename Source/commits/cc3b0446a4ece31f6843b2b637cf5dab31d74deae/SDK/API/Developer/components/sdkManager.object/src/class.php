<?php
//#section#[header]
// Namespace
namespace API\Developer\components;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Developer", "content::document::parsers::cssParser");
importer::import("API", "Developer", "content::document::parsers::jsParser");
importer::import("API", "Developer", "components::sdk::sdkLibrary");
importer::import("API", "Developer", "components::sdk::sdkPackage");
importer::import("API", "Developer", "projects::project");
importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Resources", "filesystem::fileManager");

use \ESS\Protocol\client\BootLoader;
use \API\Developer\content\document\parsers\cssParser;
use \API\Developer\content\document\parsers\jsParser;
use \API\Developer\components\sdk\sdkLibrary;
use \API\Developer\components\sdk\sdkPackage;
use \API\Developer\projects\project;
use \API\Developer\misc\vcs;
use \API\Resources\filesystem\fileManager;

/**
 * SDK General Manager
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	March 21, 2013, 11:55 (EET)
 * @revised	April 1, 2014, 13:37 (EEST)
 * 
 * @deprecated	Use \DEV\Core\sdk\sdkLibrary instead.
 */
class sdkManager
{
	/**
	 * The release path
	 * 
	 * @type	string
	 */
	const RELEASE_PATH = "/System/Library/SDK/";
	
	/**
	 * Deploys all the SDK Libraries.
	 * 
	 * @param	string	$branchName
	 * 		The branch name to deploy to latest.
	 * 
	 * @return	void
	 */
	public static function deploy($branchName = vcs::MASTER_BRANCH)
	{
		// Get repository
		$repository = project::getRepository(1);
		$vcs = new vcs($repository, FALSE);
		$releasePath = $vcs->getCurrentRelease($branchName)."/SDK/";
		
		// Copy map file
		$contents = fileManager::get($releasePath."/map.xml");
		fileManager::create(systemRoot.self::RELEASE_PATH."map.xml", $contents, TRUE);
		fileManager::create(systemRoot."/System/Resources/Documentation/SDK/map.xml", $contents, TRUE);
		fileManager::create(systemRoot."/System/Resources/Model/SDK/map.xml", $contents, TRUE);
		
		// Deploy all SDK Libraries
		$sdkLib = new sdkLibrary();
		$libraries = $sdkLib->getList();
		foreach ($libraries as $libName)
		{
			$packages = $sdkLib->getPackageList($libName);
			foreach ($packages as $packageName)
			{
				$sdkp = new sdkPackage();
				$objects = $sdkp->getPackageObjects($libName, $packageName, NULL);
				
				// Initialize
				$cssContent = "";
				$jsContent = "";
				
				foreach ($objects as $objectInfo)
				{
					$ns = str_replace("::", "/", $objectInfo['namespace']);
					$releaseObjectPath = $releasePath."/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".object/";
					
					// Copy source
					$sourceFile = systemRoot.self::RELEASE_PATH.$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php";
					$contents = fileManager::get($releaseObjectPath."/src/class.php");
					fileManager::create($sourceFile, $contents, TRUE);
					
					// Documentation and Manual file
					$docFile = systemRoot."/System/Resources/Documentation/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php.xml";
					$contents = fileManager::get($releaseObjectPath."/src/doc.xml");
					fileManager::copy($docFile, $contents, TRUE);
					$manFile = systemRoot."/System/Resources/Documentation/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php.man.xml";
					$contents = fileManager::get($releaseObjectPath."/manual.xml");
					fileManager::copy($manFile, $contents, TRUE);
					
					// Model file
					$modelFile = systemRoot."/System/Resources/Model/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".xml";
					$contents = fileManager::get($releaseObjectPath."/model/model.xml");
					fileManager::copy($modelFile, $contents, TRUE);
					
					// Object CSS
					$cssContent .= trim(fileManager::get($releaseObjectPath."/model/style.css"))."\n";
					
					// Object JS
					$jsContent .= trim(fileManager::get($releaseObjectPath."/script.js"))."\n";
				}
				
				// Replace resources vars in css
				$resourcePath = "/Library/Media/c";
				$cssContent = str_replace("%resources%", $resourcePath, $cssContent);
				
				// Export CSS Package
				$cssContent = cssParser::format($cssContent);
				BootLoader::exportCSS("Packages", $libName, $packageName, $cssContent);
				
				// Export JS Package
				$jsContent = jsParser::format($jsContent);
				BootLoader::exportJS("Packages", $libName, $packageName, $jsContent);
			}
		}
	}
	
	/**
	 * Exports all the SDK.
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use deploy() instead.
	 */
	public static function export()
	{
		// Export all
		$sdkLib = new sdkLibrary();
		$libraries = $sdkLib->getList();
		foreach ($libraries as $library)
		{
			$packages = $sdkLib->getPackageList($library);
			foreach ($packages as $packageName)
			{
				$sdkp = new sdkPackage();
				$sdkp->export($library, $packageName);
			}
		}
		
		// Copy map file
		$repository = project::getRepository(1);
		$vcs = new vcs($repository, FALSE);
		
		$itemID = "m".md5("map_SDK_map.xml");
		$headPath = $vcs->getItemHeadPath($itemID);
		$contents = fileManager::get($headPath);
		
		// Create map for SDK
		fileManager::create(systemRoot."/System/Library/SDK/map.xml", $contents, TRUE);
		fileManager::create(systemRoot."/System/Resources/Documentation/SDK/map.xml", $contents, TRUE);
		fileManager::create(systemRoot."/System/Resources/Model/SDK/map.xml", $contents, TRUE);
	}
	
	/**
	 * Export an entire library to latest.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use export() function instead.
	 */
	public static function exportLibrary($libName)
	{
		$sdkLib = new sdkLibrary();		
		
		libraryIndex::createReleaseIndex("/System/Resources/Documentation/SDK/", $libName);
		
		$packages = $sdkLib->getPackageList($libName);
		foreach ($packages as $packageName)
			sdkPackage::export($libName, $packageName);
	}	
}
//#section_end#
?>