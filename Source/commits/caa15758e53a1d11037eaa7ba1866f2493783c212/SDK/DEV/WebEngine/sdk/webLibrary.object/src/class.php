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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Protocol", "BootLoader2");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Tools", "parsers/jsParser");
importer::import("DEV", "WebEngine", "sdk/webPackage");
importer::import("DEV", "WebEngine", "webCoreProject");

use \ESS\Protocol\BootLoader2;
use \API\Resources\filesystem\fileManager;
use \DEV\Prototype\sourceMap;
use \DEV\Version\vcs;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;
use \DEV\WebEngine\sdk\webPackage;
use \DEV\WebEngine\webCoreProject;

/**
 * Web SDK Library Manager
 * 
 * Handles all operations with web SDK libraries.
 * 
 * @version	0.2-1
 * @created	April 4, 2014, 11:23 (EEST)
 * @updated	January 13, 2015, 18:36 (EET)
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
	 * @param	string	$version
	 * 		The web core project release version.
	 * 
	 * @param	string	$branchName
	 * 		The branch to publish.
	 * 
	 * @return	void
	 */
	public static function publish($version, $branchName = vcs::MASTER_BRANCH)
	{
		// Initialize project and get repository
		$project = new webCoreProject();
		$vcs = new vcs(webCoreProject::PROJECT_ID);
		$releasePath = $vcs->getCurrentRelease($branchName)."/SDK/";
		
		// Get publish folder
		$publishFolder = projectLibrary::getPublishedPath(webCoreProject::PROJECT_ID, $version);
		
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
				$cssContent = "";
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
					$cssContent .= trim(fileManager::get($releaseObjectPath."/model/style.css"))."\n";
					
					// Object JS
					$jsContent .= trim(fileManager::get($releaseObjectPath."/script.js"))."\n";
				}
				
				// Replace resources vars in css
				$resourcePath = $publishFolder."/media";
				$resourcePath = str_replace(paths::getPublishedPath(), "", $resourcePath);
				$resourceUrl = url::resolve("lib", $resourcePath);
				
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
				
				// Export css to publish folder
				BootLoader2::exportCSS(webCoreProject::PROJECT_ID, $libName, $packageName, $cssContent);
				
				// Replace resources vars in js
				$jsContent = str_replace("%resources%", $resourceUrl, $jsContent);
				$jsContent = str_replace("%{resources}", $resourceUrl, $jsContent);
				$jsContent = str_replace("%media%", $resourceUrl, $jsContent);
				$jsContent = str_replace("%{media}", $resourceUrl, $jsContent);
				
				// Format js
				$jsContent = jsParser::format($jsContent);
				
				// Export js to publish folder
				BootLoader2::exportJS(webCoreProject::PROJECT_ID, $libName, $packageName, $jsContent);
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
		return "m".md5("map_WEB_map.xml");
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