<?php
//#section#[header]
// Namespace
namespace API\Developer\components;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::sdk::sdkLibrary");
importer::import("API", "Developer", "components::sdk::sdkPackage");
importer::import("API", "Developer", "components::prime::indexing::libraryIndex");

use \API\Developer\components\sdk\sdkLibrary;
use \API\Developer\components\sdk\sdkPackage;
use \API\Developer\components\prime\indexing\libraryIndex;

/**
 * SDK General Manager
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	March 21, 2013, 11:55 (EET)
 * @revised	March 28, 2013, 15:27 (EET)
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
	 * Export an entire library to latest.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	void
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