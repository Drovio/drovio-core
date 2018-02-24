<?php
//#section#[header]
// Namespace
namespace API\Developer\components\appcenter;

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
 * @namespace	\components\appcenter
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::prime::indexing::libraryIndex");
importer::import("API", "Developer", "components::appcenter::appPackage");
importer::import("API", "Developer", "resources::paths");

use \API\Developer\components\prime\indexing\libraryIndex;
use \API\Developer\components\appcenter\appPackage;
use \API\Developer\resources\paths;

/**
 * Application Center Library Manager
 * 
 * Manages the indexing of the application center library.
 * 
 * @version	{empty}
 * @created	September 11, 2013, 14:57 (EEST)
 * @revised	April 1, 2014, 11:56 (EEST)
 * 
 * @deprecated	Use core sdk managers instead.
 */
class appLibrary
{
	/**
	 * Creates the index of the application center library.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	boolean
	 * 		TRUE on success, FALSE on failure.
	 */
	public function create($libName)
	{
		return libraryIndex::createMapIndex(paths::getDevRsrcPath()."/Mapping/Library/appCenter/", "appCenter", $libName);
	}
	
	/**
	 * Gets all the libraries in the index.
	 * 
	 * @return	array
	 * 		An array of library names by id.
	 */
	public function getList()
	{
		return libraryIndex::getList(paths::getDevRsrcPath()."/Mapping/Library/appCenter/");
	}
	
	/**
	 * Gets all the packages in the given library.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	array
	 * 		An array of packages.
	 */
	public function getPackageList($libName)
	{
		return libraryIndex::getPackageList(paths::getDevRsrcPath()."/Mapping/Library/appCenter/", $libName, $fullNames = TRUE);
	}
	
	/**
	 * Export the given library to release location.
	 * 
	 * @param	string	$libName
	 * 		The library name to release.
	 * 
	 * @return	void
	 */
	public static function exportLibrary($libName)
	{
		libraryIndex::createReleaseIndex("/System/Resources/Documentation/devKit/appCenter/", $libName);
		
		// Export packages
		$packages = self::getPackageList($libName);
		foreach ($packages as $packageName)
			appPackage::export($libName, $packageName);
	}
}
//#section_end#
?>