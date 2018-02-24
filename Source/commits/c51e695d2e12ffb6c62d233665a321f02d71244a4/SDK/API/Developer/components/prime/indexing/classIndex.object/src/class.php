<?php
//#section#[header]
// Namespace
namespace API\Developer\components\prime\indexing;

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
 * @namespace	\components\prime\indexing
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "profiler::logger");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Developer\profiler\logger;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * PHP Object Class Index
 * 
 * Manages object indexing.
 * 
 * @version	{empty}
 * @created	July 23, 2013, 13:38 (EEST)
 * @revised	September 19, 2013, 11:16 (EEST)
 */
class classIndex
{
	/**
	 * Creates object index entry to given map file.
	 * 
	 * @param	string	$mapFilepath
	 * 		The map file path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$nsName
	 * 		The namespace name.
	 * 
	 * @param	string	$objectName
	 * 		The object name.
	 * 
	 * @param	string	$title
	 * 		The object title.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function createIndex($mapFilepath, $libName, $packageName, $nsName, $objectName, $title = "")
	{
		// Open Index File and create entry
		$parser = new DOMParser();
		$parser->load($mapFilepath, TRUE);
		
		// Search for package
		$package = $parser->evaluate("//package[@id='$packageName']")->item(0);
		if (is_null($package))
		{
			logger::log("Package '$packageName' doesn't exist inside Library '$libName'.", logger::ERROR);
			return FALSE;
		}
		
		// If not namespace given, get package as parent
		if ($nsName == "")
			$parent = $package;
		else
		{
			// If namespace given, search for namespace
			$nss = explode("::", $nsName);
			$q_nss = "namespace[@name='".implode("']/namespace[@name='", $nss)."']";
			$parent = $parser->evaluate($q_nss, $package)->item(0);
			if (is_null($parent))
			{
				logger::log("Namespace '$nsName' doesn't exist inside Package '$libName' -> '$packageName'.", logger::ERROR);
				return FALSE;
			}
		}
			
		// Search for same object
		$object = $parser->evaluate("object[@name='$objectName']", $parent)->item(0);
		
		if (!is_null($object))
		{
			logger::log("Object '$nsName' -> '$objectName' already exists in '$libName' -> '$packageName'.", logger::WARNING);
			return FALSE;
		}

		$object = $parser->create("object");
		$parser->attr($object, "name", $objectName);
		$parser->attr($object, "title", $title);
		$parser->append($parent, $object);
		return $parser->save(systemRoot.$mapFilepath, "", TRUE);
	}
}
//#section_end#
?>