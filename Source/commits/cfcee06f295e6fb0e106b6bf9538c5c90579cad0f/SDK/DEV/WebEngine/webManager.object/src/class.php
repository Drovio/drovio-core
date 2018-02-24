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

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Tools", "parsers::jsParser");
importer::import("DEV", "WebEngine", "sdk::webLibrary");
importer::import("DEV", "WebEngine", "sdk::webPackage");

use \ESS\Protocol\client\BootLoader;
use \API\Resources\filesystem\fileManager;
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
 * @version	0.1-1
 * @created	October 30, 2014, 21:21 (EET)
 * @revised	October 30, 2014, 21:21 (EET)
 */
class webManager
{
	/**
	 * The 'Web Project' code
	 * 
	 * @type	integer
	 */
	const PROJECT_CODE = 3;

	/**
	 * The Web SDK release path
	 * 
	 * @type	string
	 */
	const RELEASE_PATH = "/System/Library/Web/SDK/";
	
	/**
	 * The Web SDK documention path
	 * 
	 * @type	string
	 */
	const DOCS_PATH = "/System/Resources/Documentation/Web/";
 
	/**
	 * The Web SDK model path
	 * 
	 * @type	string
	 */
	const MODEL_PATH = "/System/Resources/Model/Web/";
	
	/**
	 * The Web SDK zip packages loacation path
	 * 
	 * @type	string
	 */
	const PACKAGES_PATH = "/System/Library/WebPackages/";
	
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
		// Get repository
		$project = new project(self::PROJECT_CODE);
		$vcs = new vcs(self::PROJECT_CODE);
		$releasePath = $vcs->getCurrentRelease($branchName);
		
		// Copy map file
		$contents = fileManager::get($releasePath."/map.xml");
		fileManager::create(systemRoot.self::RELEASE_PATH."map.xml", $contents, TRUE);
		fileManager::create(systemRoot.self::DOCS_PATH."map.xml", $contents, TRUE);
		fileManager::create(systemRoot.self::MODEL_PATH."map.xml", $contents, TRUE);
		
		// Deploy all SDK Libraries
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
					$ns = str_replace("::", "/", $objectInfo['namespace']);
					$releaseObjectPath = $releasePath."/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".object/";
					
					// Copy source
					$sourceFile = systemRoot.self::RELEASE_PATH.$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php";
					$contents = fileManager::get($releaseObjectPath."/src/class.php");
					fileManager::create($sourceFile, $contents, TRUE);
					
					// Documentation and Manual file
					$docFile = systemRoot.self::DOCS_PATH.$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php.xml";
					$contents = fileManager::get($releaseObjectPath."/src/doc.xml");
					fileManager::copy($docFile, $contents, TRUE);
					$manFile = systemRoot.self::DOCS_PATH.$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php.man.xml";
					$contents = fileManager::get($releaseObjectPath."/manual.xml");
					fileManager::copy($manFile, $contents, TRUE);
					
					// Model file
					$modelFile = systemRoot.self::MODEL_PATH.$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".xml";
					$contents = fileManager::get($releaseObjectPath."/model/model.xml");
					fileManager::copy($modelFile, $contents, TRUE);
					
					// Object CSS
					$cssContent .= trim(fileManager::get($releaseObjectPath."/model/style.css"))."\n";
					
					// Object JS
					$jsContent .= trim(fileManager::get($releaseObjectPath."/script.js"))."\n";
				}
				
				// Replace resources vars in css
				$resourcePath = "/Library/Media/w";
				$cssContent = str_replace("%resources%", $resourcePath, $cssContent);
				
				// Export CSS Package
				$cssContent = cssParser::format($cssContent);
				BootLoader::exportCSS("Web", $libName, $packageName, $cssContent);
				
				// Export JS Package
				$jsContent = jsParser::format($jsContent);
				BootLoader::exportJS("Web", $libName, $packageName, $jsContent);
			}
		}
		
		// Publish Resources
		//$project->publishResources("/Library/Media/w/");
		
		
		// Create Web SDK Zip Packages
	}
	
}
//#section_end#
?>