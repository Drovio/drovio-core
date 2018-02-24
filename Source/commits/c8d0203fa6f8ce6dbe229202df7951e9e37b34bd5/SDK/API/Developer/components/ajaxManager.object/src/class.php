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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::ajax::ajaxPage");
importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Developer", "projects::project");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Developer\components\ajax\ajaxPage;
use \API\Developer\misc\vcs;
use \API\Developer\projects\project;
use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * AJAX Developer Manager
 * 
 * Include developer's information.
 * 
 * @version	{empty}
 * @created	April 22, 2013, 15:39 (EEST)
 * @revised	April 1, 2014, 10:07 (EEST)
 * 
 * @deprecated	Use \DEV\Core\ajax\ajaxDirectory instead.
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
		$libPath = paths::getDevRsrcPath().self::MAP_PATH."/ajax.xml";
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
	 * Deploy all ajax pages to latest.
	 * 
	 * @param	string	$branchName
	 * 		The branch to deploy.
	 * 
	 * @return	void
	 */
	public static function deploy($branchName = vcs::MASTER_BRANCH)
	{
		// Get repository
		$repository = project::getRepository(1);
		$vcs = new vcs($repository, FALSE);
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
	 * Export all ajax pages.
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use deploy() instead.
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
		$libPath = paths::getDevRsrcPath().ajaxManager::MAP_PATH."/ajax.xml";
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
		$repository = project::getRepository(1);
		$vcs = new vcs($repository, FALSE);
		
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
		$repository = project::getRepository(1);
		$vcs = new vcs($repository, FALSE);
		
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