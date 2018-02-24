<?php
//#section#[header]
// Namespace
namespace API\Developer\components\prime\indexing;

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
 * @namespace	\components\prime\indexing
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");

use \API\Resources\DOMParser;

/**
 * Library index manager.
 * 
 * Manages all library indexing.
 * 
 * @version	{empty}
 * @created	July 23, 2013, 14:05 (EEST)
 * @revised	April 1, 2014, 14:44 (EEST)
 * 
 * @deprecated	Use \DEV\Prototype\sourceMap instead.
 */
class libraryIndex
{
	/**
	 * Gets all packages from the released map index file.
	 * 
	 * @param	string	$path
	 * 		The path to the map file.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	array
	 * 		An array of all packages.
	 */
	public static function getReleasePackageList($path, $libName)
	{
		// Load Index file
		$parser = new DOMParser();
		$parser->load($path."/map.xml");
		
		// Init object array
		$packagesArray = array();
		
		// Get all objects as children of the package
		$packages = $parser->evaluate("//library[@name='".$libName."']/package");
		foreach ($packages as $pkg)
			$packagesArray[] = $parser->attr($pkg, "name");

		return $packagesArray;
	}
}
//#section_end#
?>