<?php
//#section#[header]
// Namespace
namespace DEV\Core\sdk;

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
 * @package	Core
 * @namespace	\sdk
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Protocol", "BootLoader");
importer::import("ESS", "Environment", "url");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Core", "coreProject");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Resources", "paths");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Tools", "parsers/jsParser");

use \ESS\Protocol\BootLoader;
use \ESS\Environment\url;
use \API\Resources\filesystem\fileManager;
use \DEV\Core\coreProject;
use \DEV\Prototype\sourceMap;
use \DEV\Projects\projectLibrary;
use \DEV\Resources\paths;
use \DEV\Version\vcs;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;

/**
 * Core SDK Library Manager
 * 
 * Handles all operations with core SDK libraries.
 * 
 * @version	0.2-7
 * @created	April 1, 2014, 12:53 (EEST)
 * @updated	February 18, 2015, 16:17 (EET)
 */
class sdkLibrary
{
	/**
	 * The SDK release path.
	 * 
	 * @type	string
	 */
	const RELEASE_PATH = "/System/Library/Core/SDK/";
	
	/**
	 * The sourceMap object.
	 * 
	 * @type	sourceMap
	 */
	private $sourceMap;
	
	/**
	 * Creates a new SDK library.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($library)
	{
		// Update map file
		$this->updateMapFile();
		
		// Create index
		$this->loadSourceMap();
		return $this->sourceMap->createLibrary($library);
	}
	
	/**
	 * Remove a library from the core SDK.
	 * The library must be empty.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($library)
	{
		// Create index
		$this->loadSourceMap();
		$status = FALSE;
		try
		{
			$status = $this->sourceMap->deleteLibrary($library);
		}
		catch (Exception $ex)
		{
			$status = FALSE;
		}
		
		// Update vcs map file
		if ($status)
			$this->updateMapFile();
		
		return $status;
	}
	
	/**
	 * Gets a list of all libraries.
	 * 
	 * @return	array
	 * 		An array of all libraries in the SDK.
	 */
	public function getList()
	{
		$this->loadSourceMap();
		return $this->sourceMap->getLibraryList();
	}
	
	/**
	 * Get all packages in the given library
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @return	array
	 * 		An array of all packages in the library
	 */
	public function getPackageList($library)
	{
		$this->loadSourceMap();
		return $this->sourceMap->getPackageList($library);
	}
	
	/**
	 * Publishes all the SDK Libraries.
	 * 
	 * @param	string	$version
	 * 		The core project release version.
	 * 
	 * @param	string	$branchName
	 * 		The branch to publish.
	 * 
	 * @return	void
	 */
	public static function publish($version, $branchName = vcs::MASTER_BRANCH)
	{
		// Check version
		if (empty($version))
			return FALSE;
			
		// Get repository
		$vcs = new vcs(coreProject::PROJECT_ID);
		$releasePath = $vcs->getCurrentRelease($branchName)."/SDK/";
		
		// Get publish folder
		$publishFolder = projectLibrary::getPublishedPath(coreProject::PROJECT_ID, $version);
		$innerFolder = "/media";
		
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
					$contents = fileManager::get($releaseObjectPath."/src/class.php");
					$sourceFile = systemRoot.self::RELEASE_PATH.$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php";
					fileManager::create($sourceFile, $contents, TRUE);
					
					// Documentation file
					$contents = fileManager::get($releaseObjectPath."/src/doc.xml");
					$docFile = systemRoot."/System/Resources/Documentation/SDK/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php.xml";
					fileManager::create($docFile, $contents, TRUE);
					// Manual file
					$contents = fileManager::get($releaseObjectPath."/manual.html");
					$manFile = systemRoot."/System/Resources/Documentation/SDK/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".manual.html";
					fileManager::create($manFile, $contents, TRUE);
					
					// Model file
					$contents = fileManager::get($releaseObjectPath."/model/model.xml");
					$modelFile = systemRoot."/System/Resources/Model/SDK/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".xml";
					fileManager::create($modelFile, $contents, TRUE);
					
					// Object CSS
					$cssContent .= trim(fileManager::get($releaseObjectPath."/model/style.css"))."\n";
					
					// Object JS
					$jsContent .= trim(fileManager::get($releaseObjectPath."/script.js"))."\n";
				}
				// Resolve urls in css, format and export
				$cssContent = BootLoader::resolveURLs(coreProject::PROJECT_ID, $cssContent, $version, $protocol = NULL, $innerFolder);
				$cssContent = cssParser::format($cssContent);
				BootLoader::exportCSS(coreProject::PROJECT_ID, $libName, $packageName, $cssContent, $version);
				
				// Resolve urls in js, format and export
				$jsContent = BootLoader::resolveURLs(coreProject::PROJECT_ID, $jsContent, $version, $protocol = NULL, $innerFolder);
				$jsContent = jsParser::format($jsContent);
				BootLoader::exportJS(coreProject::PROJECT_ID, $libName, $packageName, $jsContent, $version);
			}
		}
	}
	
	/**
	 * Get the map file item id for the vcs.
	 * 
	 * @return	string
	 * 		The hash item id.
	 */
	public static function getMapfileID()
	{
		return "m".md5("map_SDK_map.xml");
	}
	
	/**
	 * Initializes the source map object for getting the source information from the source index.
	 * 
	 * @return	object
	 * 		The sourceMap object.
	 */
	private function loadSourceMap()
	{
		if (empty($this->sourceMap))
		{
			// Get map file trunk path
			$vcs = new vcs(coreProject::PROJECT_ID);
			$itemID = $this->getMapfileID();
			$mapFilePath = $vcs->getItemTrunkPath($itemID);
			$this->sourceMap = new sourceMap(dirname($mapFilePath), basename($mapFilePath));
		}
		
		return $this->sourceMap;
	}
	
	/**
	 * Updates the source map index file in the vcs.
	 * 
	 * @return	void
	 */
	private function updateMapFile()
	{
		// Update map file
		$vcs = new vcs(coreProject::PROJECT_ID);
		$itemID = $this->getMapfileID();
		$vcs->updateItem($itemID, TRUE);
	}
}
//#section_end#
?>