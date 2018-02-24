<?php
//#section#[header]
// Namespace
namespace DEV\Core\ajax;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Core
 * @namespace	\ajax
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::directory");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Projects", "project");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\directory;
use \DEV\Version\vcs;
use \DEV\Projects\project;

importer::import("DEV", "Temp", "vcs2");
use \DEV\Temp\vcs2;


/**
 * AJAX Directory Manager
 * 
 * Manages all the ajax directories (in and out of repository).
 * 
 * @version	{empty}
 * @created	March 31, 2014, 16:13 (EEST)
 * @revised	March 31, 2014, 16:13 (EEST)
 */
class ajaxDirectory
{
	/**
	 * The ajax page's release path.
	 * 
	 * @type	string
	 */
	const RELEASE_PATH = "/ajax/";
	
	/**
	 * Get all pages in a given directory (on the first level).
	 * 
	 * @param	string	$directory
	 * 		The full directory name separated by "/".
	 * 
	 * @return	array
	 * 		An array of all pages in the given directory.
	 */
	public static function getPages($directory)
	{
		$libPath = self::getMapFilepath();
		
		$parser = new DOMParser();
		$parser->load($libPath, FALSE);
		
		// Get Base
		$base = $parser->evaluate("map")->item(0);
		
		// Get Directory base
		if (empty($directory))
			$baseDir = $base;
		else
		{
			$pdir = explode("/", $directory);
			$q_dir = "dir[@name='".implode("']/dir[@name='", $pdir)."']";
			$baseDir = $parser->evaluate($q_dir, $base)->item(0);
		}
		
		$result = array();
		$pages = $parser->evaluate("page", $baseDir);
		foreach ($pages as $page)
			$result[] = $parser->attr($page, "name");
		
		return $result;
	}
	
	/**
	 * Publish all ajax pages to development server.
	 * 
	 * @param	string	$branchName
	 * 		The branch to publish from.
	 * 
	 * @return	void
	 */
	public static function publish($branchName = vcs::MASTER_BRANCH)
	{
		// Get repository
		$vcs = new vcs2(1);
		$releasePath = $vcs->getCurrentRelease($branchName)."/Ajax/";
		
		// Deploy all ajax pages
		$ajaxDirectories = self::getDirectories(TRUE);
		foreach ($ajaxDirectories as $directory)
		{
			$ajaxPages = self::getPages($directory);
			foreach ($ajaxPages as $page)
			{
				$releasePagePath = $releasePath.$directory."/".$page.".php";
				$contents = fileManager::get($releasePagePath);

				$pageFile = systemRoot.self::RELEASE_PATH.$directory."/".$page.".php";
				fileManager::create($pageFile, $contents, TRUE);
			}
		}
	}
	
	/**
	 * Get all ajax page directories.
	 * 
	 * @param	boolean	$full
	 * 		Defines the result output format.
	 * 		If TRUE, the result is an array of all directories separated by "/".
	 * 		If FALSE, the result is a nested array for each directory as a tree.
	 * 
	 * @return	array
	 * 		A nested array of directories or a full array of directories separated by "/".
	 */
	public static function getDirectories($full = FALSE)
	{
		$libPath = self::getMapFilepath();
		
		// Load Index File
		$parser = new DOMParser();
		$parser->load($libPath, FALSE);
		
		$base = $parser->evaluate("map")->item(0);
		
		$result = array();
		// Root directory
		if (!$full)
		{
			$result[""] = "";
			$dirs = $parser->evaluate("dir", $base);
			foreach ($dirs as $dir)
			{
				$name = $parser->attr($dir, "name");
				$result[$name] = self::getSubDirs($parser, $dir);
			}
		}
		else
		{
			$result[] = "";
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
	 * Gets the map index file path.
	 * 
	 * @return	string
	 * 		The map index file path.
	 */
	public static function getMapFilepath()
	{
		$vcs = new vcs2(1);
		
		// Get item ID
		$itemID = self::getMapfileItemID();
		return $vcs->getItemTrunkPath($itemID);
	}
	
	/**
	 * Updates the map file vcs item.
	 * 
	 * @return	string
	 * 		The item's trunk path.
	 */
	public static function updateMapFilepath()
	{
		$vcs = new vcs2(1);
		
		$itemID = self::getMapfileItemID();
		return $vcs->updateItem($itemID, TRUE);
	}
	
	/**
	 * Get the map file item id.
	 * 
	 * @return	string
	 * 		The item id.
	 */
	private static function getMapfileItemID()
	{
		return "m".md5("map_Ajax_map.xml");
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
	 * Gets all sub directories of a given directory.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the xml file.
	 * 
	 * @param	DOMElement	$sub
	 * 		The parent directory element.
	 * 
	 * @return	array
	 * 		An array with all subdirectories separated by "/".
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
	 * 		True on success, false on failure.
	 */
	public function create($dirName, $parentDir = "")
	{
		// Library Path
		$libPath = $this->updateMapFilepath();
		
		// Open Index File and create entry
		$parser = new DOMParser();
		$parser->load($libPath, FALSE);
		
		$base = $parser->evaluate("//map")->item(0);
		
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
			return $parser->update();
		}
		
		return FALSE;
	}
}
//#section_end#
?>