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

importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");

use \API\Developer\resources\paths;
use \API\Resources\DOMParser;

/**
 * AJAX Developer Manager
 * 
 * Include developer's information.
 * 
 * @version	{empty}
 * @created	April 22, 2013, 15:39 (EEST)
 * @revised	November 22, 2013, 10:36 (EET)
 */
class ajaxManager
{
	/**
	 * The ajax page's release path.
	 * 
	 * @type	string
	 */
	const RELEASE_PATH = "/ajax/";
	/**
	 * The root repository folder.
	 * 
	 * @type	string
	 */
	const REPOSITORY_PATH = "/Developer/Repositories/Ajax/";
	/**
	 * The map root folder.
	 * 
	 * @type	string
	 */
	const MAP_PATH = "/Mapping/Library/";
	
	/**
	 * Get all pages in a given directory.
	 * 
	 * @param	string	$directory
	 * 		The full directory name.
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function getPages($directory)
	{
		$parser = new DOMParser();
		$libPath = paths::getDevRsrcPath().self::MAP_PATH."/ajax.xml";
		$parser->load($libPath, TRUE);
		
		// Get Base
		$base = $parser->evaluate("AJAX")->item(0);
		
		// Get Directory base
		$pdir = explode("/", $directory);
		$q_dir = "dir[@name='".implode("']/dir[@name='", $pdir)."']";
		$baseDir = $parser->evaluate($q_dir, $base)->item(0);
		
		$result = array();
		$pages = $parser->evaluate("page", $baseDir);
		foreach ($pages as $page)
			$result[] = $parser->attr($page, "name");
		
		return $result;
	}
}
//#section_end#
?>