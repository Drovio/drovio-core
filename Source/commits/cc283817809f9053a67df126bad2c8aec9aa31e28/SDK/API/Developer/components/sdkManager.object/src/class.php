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

importer::import("API", "Developer", "components::sdk::sdkLibrary");
importer::import("API", "Developer", "components::sdk::sdkPackage");
importer::import("API", "Developer", "components::prime::indexing::libraryIndex");
importer::import("API", "Developer", "projects::project");
importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Developer\components\sdk\sdkLibrary;
use \API\Developer\components\sdk\sdkPackage;
use \API\Developer\components\prime\indexing\libraryIndex;
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
 * @revised	January 14, 2014, 10:53 (EET)
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
	 * The developer's mapping path
	 * 
	 * @type	string
	 */
	const MAP_PATH = "/Mapping/Library/SDK/";
	
	/**
	 * Exports all the SDK.
	 * 
	 * @return	void
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
		$headPath = $vcs->getItemTrunkPath($itemID);
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