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
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::ajax::ajaxPage");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");

use \API\Developer\components\ajax\ajaxPage;
use \API\Developer\resources\paths;
use \API\Resources\DOMParser;

/**
 * AJAX Developer Manager
 * 
 * Include developer's information.
 * 
 * @version	{empty}
 * @created	April 22, 2013, 15:39 (EEST)
 * @revised	December 24, 2013, 11:38 (EET)
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
	
	/**
	 * Export all ajax pages.
	 * 
	 * @return	void
	 */
	public static function exportPages()
	{
		$ajaxDirectories = self::getDirectories(TRUE);
		foreach ($ajaxDirectories as $directory)
		{
			$ajaxPages = self::getPages($directory);
			foreach ($ajaxPages as $page)
			{
				$ajxPage = new ajaxPage($page, $directory);
				$ajxPage->export();
			}
		}
	}
	
	/**
	 * Get all ajax page directories.
	 * 
	 * @param	boolean	$full
	 * 		Defines the result output format.
	 * 
	 * @return	array
	 * 		A nested array of directories or a full array of directories separated by "/".
	 */
	public static function getDirectories($full = FALSE)
	{
		// Load Index File
		$parser = new DOMParser();
		$libPath = paths::getDevRsrcPath().ajaxManager::MAP_PATH."/ajax.xml";
		$parser->load($libPath, TRUE);
		
		$base = $parser->evaluate("AJAX")->item(0);
		
		$result = array();
		if (!$full)
		{
			$dirs = $parser->evaluate("dir", $base);
			foreach ($dirs as $dir)
			{
				$name = $parser->attr($dir, "name");
				$result[$name] = self::getSubDirs($parser, $dir);
			}
		}
		else
		{
			$dirs = $parser->evaluate("dir", $base);
			foreach ($dirs as $dir)
			{
				$name = $parser->attr($dir, "name");
				$result[] = $name;
				$subs = self::getSubDirsString($parser, $dir);
				
				if (is_array($subs))
					foreach ($subs as $t)
						$result[] = $name."/".$t;
			}
		}
		
		return $result;
	}
	
	/**
	 * Gets all sub directories of a given directory.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the xml file.
	 * 
	 * @param	DOMElement	$sub
	 * 		The parent directory element.
	 * 
	 * @return	array
	 * 		A nested array for each subdirectory.
	 */
	private static function getSubDirs($parser, $sub)
	{
		$dirs = $parser->evaluate("dir", $sub);
		
		if ($dirs->length == 0)
			return array();
		
		$result = array();
		foreach ($dirs as $dir)
		{
			$name = $parser->attr($dir, "name");
			$result[$name] = self::getSubDirs($parser, $dir);
		}
		return $result;
	}
	
	/**
	 * {description}
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the xml file.
	 * 
	 * @param	DOMElement	$sub
	 * 		The parent directory element.
	 * 
	 * @return	void
	 */
	private static function getSubDirsString($parser, $sub)
	{
		$dirs = $parser->evaluate("dir", $sub);
		
		if ($dirs->length == 0)
			return array();
		
		$result = array();
		foreach ($dirs as $dir)
		{
			$name = $parser->attr($dir, "name");
			$result[] = $name;
			$temp = self::getSubDirsString($parser, $dir);
			
			if (is_array($temp))
				foreach ($temp as $t)
					$result[] = $name."/".$t;
		}
		return $result;
	}
}
//#section_end#
?>