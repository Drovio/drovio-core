<?php
//#section#[header]
// Namespace
namespace API\Developer\components\ajax;

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
 * @namespace	\components\ajax
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::ajaxManager");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::directory");

use \API\Developer\components\ajaxManager;
use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\directory;

/**
 * AJAX Directory Manager
 * 
 * Manages all the ajax directories (in and out of repository).
 * 
 * @version	{empty}
 * @created	April 22, 2013, 16:00 (EEST)
 * @revised	November 22, 2013, 10:40 (EET)
 */
class ajaxDirectory
{
	/**
	 * Create an ajax directory.
	 * 
	 * @param	string	$dirName
	 * 		The directory name.
	 * 
	 * @param	string	$parentDir
	 * 		The parent directory.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function create($dirName, $parentDir = "")
	{
		// Create Map Index 
		return $this->createMapIndex($dirName, $parentDir);
	}
	
	/**
	 * Create map index entry.
	 * 
	 * @param	string	$dirName
	 * 		The directory name.
	 * 
	 * @param	string	$parentDir
	 * 		The parent directory.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	private function createMapIndex($dirName, $parentDir = "")
	{
		// Library Path
		$libPath = paths::getDevRsrcPath().ajaxManager::MAP_PATH."/ajax.xml";
		
		// Open Index File and create entry
		$parser = new DOMParser();
		try
		{
			$parser->load($libPath, TRUE);
		}
		catch (Exception $ex)
		{
		}
		
		$base = $parser->evaluate("//AJAX")->item(0);
		if (is_null($base))
		{
			$base = $parser->create("AJAX");
			$parser->append($base);
		}
		
		if (empty($parentDir))
			$baseDir = $base;
		else
		{
			// If parent directory given, search for it
			$pdir = explode("/", $parentDir);
			$q_dir = "dir[@name='".implode("']/dir[@name='", $pdir)."']";
			$baseDir = $parser->evaluate($q_dir, $base)->item(0);
			if (is_null($baseDir))
				throw new Exception("Parent directory '$parentDir' doesn't exist.");
		}
		
		// Create root directory (if not already exists)
		$dir = $parser->evaluate("dir[@name='$dirName']", $baseDir)->item(0);
		if (is_null($dir))
		{
			$dir = $parser->create("dir");
			$parser->attr($dir, "name", $dirName);
			$parser->append($baseDir, $dir);
			return $parser->save(systemRoot.$libPath, "", TRUE);
		}
		
		return FALSE;
	}
	
	/**
	 * Delete an existing ajax directory.
	 * It must be empty.
	 * 
	 * @param	string	$dirName
	 * 		The directory name.
	 * 
	 * @param	string	$parentDir
	 * 		The parent directory.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function delete($dirName, $parentDir = "")
	{
	}
	
	/**
	 * Get all the directories of the ajax files in a nested array.
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function getDirs($full = FALSE)
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
	 * Get the sub directories of a given directory.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the xml file.
	 * 
	 * @param	DOMElement	$sub
	 * 		The parent directory.
	 * 
	 * @return	array
	 * 		{description}
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