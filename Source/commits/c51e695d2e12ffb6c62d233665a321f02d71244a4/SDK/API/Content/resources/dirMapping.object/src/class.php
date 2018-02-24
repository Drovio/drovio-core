<?php
//#section#[header]
// Namespace
namespace API\Content\resources;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");

// Use Important Headers
use \API\Platform\importer;
use \Exception;
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Content
 * @namespace	\resources
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "DOM::DOMParser");
importer::import("API", "Content", "resources");
importer::import("API", "Security", "accessControl");

use \API\Platform\DOM\DOMParser;
use \API\Content\resources;
use \API\Security\accessControl;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	October 4, 2013, 12:03 (EEST)
 * @revised	October 4, 2013, 12:03 (EEST)
 * 
 * @deprecated	This class is generally deprecated.
 */
class dirMapping
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const PATH = "/Mapping/DIRS/";
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$mapName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function get_map($mapName)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		$parser = self::load_mapFile($mapName);
		return $parser->getXML();
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$mapName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function get_mapList($mapName)
	{
	}

	/**
	 * {description}
	 * 
	 * @param	{type}	$mapName
	 * 		{description}
	 * 
	 * @param	{type}	$parent
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function get_folders($mapName, $parent = "")
	{
		// Get mapFile
		$parser = self::load_mapFile($mapName);
		
		// Get parent
		if ($parent == "")
			$folderExpr = "/folder";
		else
		{
			$parents = explode("/", $parent);
			$folderExpr = "folder[@name='".implode("']/folder[@name='", $parents)."']";
		}
		
		// Get Parent
		$parentElement = $parser->evaluate($folderExpr)->item(0);
		
		// Get children folders
		
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$mapName
	 * 		{description}
	 * 
	 * @param	{type}	$parent
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function get_files($mapName, $parent = "")
	{
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$mapName
	 * 		{description}
	 * 
	 * @return	void
	 */
	private static function load_mapFile($mapName)
	{
		$parser = new DOMParser();
		
		$path = resources::DEV_PATH.self::PATH."/".$mapName.".xml";
		$parser->load($path);
		
		return $parser;
	}
}
//#section_end#
?>