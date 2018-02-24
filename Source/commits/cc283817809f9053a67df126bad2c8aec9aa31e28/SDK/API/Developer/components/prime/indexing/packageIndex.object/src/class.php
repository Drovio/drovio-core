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
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");

use \API\Resources\DOMParser;

/**
 * Library package index manager.
 * 
 * Manages all package indexing.
 * 
 * @version	{empty}
 * @created	July 23, 2013, 13:55 (EEST)
 * @revised	January 13, 2014, 16:16 (EET)
 */
class packageIndex
{
	/**
	 * Gets all released objects in a given package.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	array
	 * 		Returns an array of object names.
	 */
	public static function getReleasePackageObjects($path, $libName, $packageName)
	{
		// Load Index file
		$parser = new DOMParser();
		$indexFilePath = $path."/map.xml";
		$parser->load($indexFilePath);
		
		// Init object array
		$objectsArray = array();
		
		// Get all objects as children of the package
		$objects = $parser->evaluate("//library[@name='".$libName."']/package[@name='".$packageName."']/object");
		foreach ($objects as $obj)
			$objectsArray[] = $parser->attr($obj, "name");
		
		// Get all
		$objects = $parser->evaluate("//library[@name='".$libName."']/package[@name='".$packageName."']//object");
		foreach ($objects as $obj)
		{
			// Get Namespace
			$nsArray = array();
			$nsArray[] = $parser->attr($obj, "name");
			$parentNode = $obj->parentNode;
			while ($parentNode->tagName == "namespace")
			{
				$nsArray[] = $parser->attr($parentNode, "name");
				$parentNode = $parentNode->parentNode;
			}
			
			// Set namespace
			$nsArray = array_reverse($nsArray);
			$objectsArray[] = implode("::", $nsArray);
		}

		return $objectsArray;
	}
}
//#section_end#
?>