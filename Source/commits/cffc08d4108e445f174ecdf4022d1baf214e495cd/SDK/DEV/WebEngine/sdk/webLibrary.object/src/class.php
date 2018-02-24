<?php
//#section#[header]
// Namespace
namespace DEV\WebEngine\sdk;

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
 * @namespace	\sdk
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "BootLoader");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Tools", "parsers::jsParser");
importer::import("DEV", "WebEngine", "sdk::webPackage");

use \ESS\Protocol\BootLoader;
use \API\Resources\filesystem\fileManager;
use \DEV\Prototype\sourceMap;
use \DEV\Projects\project;
use \DEV\Version\vcs;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;
use \DEV\WebEngine\sdk\webPackage;

/**
 * Web SDK Library Manager
 * 
 * Handles all operations with web SDK libraries.
 * 
 * @version	0.1-1
 * @created	April 4, 2014, 11:23 (EEST)
 * @revised	November 7, 2014, 10:54 (EET)
 */
class webLibrary
{
	/**
	 * The SDK release path.
	 * 
	 * @type	string
	 */
	const RELEASE_PATH = "/System/Library/Web/SDK/";
	
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
		
		// Library name must be capitals
		$library = strtoupper($library);
		
		// Create index
		$this->loadSourceMap();
		return $this->sourceMap->createLibrary($library);
	}
	
	/**
	 * Remove a library from the web SDK.
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
		// Library name must be capitals
		$library = strtoupper($library);
		
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
	 * Publishes all the Web SDK Libraries.
	 * 
	 * @param	string	$branchName
	 * 		The branch to publish.
	 * 
	 * @return	void
	 */
	public static function publish($branchName = vcs::MASTER_BRANCH)
	{
		// Get repository
		$project = new project(3);
		$vcs = new vcs(3);	
		$releasePath = $vcs->getCurrentRelease($branchName)."/SDK/";
		
		// Copy map file
		$contents = fileManager::get($releasePath."/map.xml");
		fileManager::create(systemRoot.SELF::RELEASE_PATH."/map.xml", $contents, TRUE);
		fileManager::create(systemRoot."/System/Resources/Documentation/Web/map.xml", $contents, TRUE);
		fileManager::create(systemRoot."/System/Resources/Model/Web/map.xml", $contents, TRUE);
		
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
				$cssStyles = "";
				$jsContent = "";
				
				foreach ($objects as $objectInfo)
				{
					$ns = str_replace("::", "/", $objectInfo['namespace']);
					$releaseObjectPath = $releasePath."/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".object/";
					
					// Copy source
					$sourceFile = systemRoot.self::RELEASE_PATH.$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php";
					$contents = fileManager::get($releaseObjectPath."/src/class.php");
					
					// If $contents is NULL there was an error. Avoid to create an empty file
					if (!is_null($contents))
						fileManager::create($sourceFile, $contents, TRUE);
					else
					{
						// Break the current iteration 
						// TODO
						// Consider NOT breaking if the creation of .php is not essential
						continue;
					}

					// Documentation and Manual file
					$docFile = systemRoot."/System/Resources/Documentation/Web/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php.xml";
					$path = $releaseObjectPath."/src/doc.xml";
					fileManager::copy($path, $docFile, TRUE);
					
					$manFile = systemRoot."/System/Resources/Documentation/Web/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php.man.xml";
					$path = $releaseObjectPath."/manual.xml";
					fileManager::copy($path, $manFile, TRUE);
					
					// Model file
					$modelFile = systemRoot."/System/Resources/Model/Web/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".xml";
					$path = $releaseObjectPath."/model/model.xml";
					fileManager::copy($path, $modelFile, TRUE);
					
					// Object CSS
					$cssStyles .= trim(fileManager::get($releaseObjectPath."/model/style.css"))."\n";
					
					// Object JS
					$jsContent .= trim(fileManager::get($releaseObjectPath."/script.js"))."\n";
				}
				
				// Replace resources vars in css
				$resourcePath = "/Library/Media/w";
				$cssContent = str_replace("%resources%", $resourcePath, $cssStyles);
				
				// Export CSS Package
				$cssContent = cssParser::format($cssContent);
				BootLoader::exportCSS("Web", $libName, $packageName, $cssContent);
				
				// Export JS Package
				$jsContent = jsParser::format($jsContent);
				BootLoader::exportJS("Web", $libName, $packageName, $jsContent);
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
		return "m".md5("map__map.xml");
	}
	
	/**
	 * Loads the SDK's source map file.
	 * 
	 * @return	sourceMap
	 * 		The sourceMap object with the map file loaded.
	 */
	private function loadSourceMap()
	{
		if (empty($this->sourceMap))
		{
			// Get map file trunk path
			$vcs = new vcs(3);
			$itemID = $this->getMapfileID();
			$mapFilePath = $vcs->getItemTrunkPath($itemID);
			$this->sourceMap = new sourceMap(dirname($mapFilePath), basename($mapFilePath));
		}
		
		return $this->sourceMap;
	}
	
	/**
	 * Updates the source map index file.
	 * 
	 * @return	void
	 */
	private function updateMapFile()
	{
		// Update map file
		$vcs = new vcs(3);
		$itemID = $this->getMapfileID();
		$vcs->updateItem($itemID, TRUE);
	}
}
//#section_end#
?>