<?php
//#section#[header]
// Namespace
namespace API\Developer\components\ajax;

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
 * @namespace	\components\ajax
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
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
 * @revised	April 1, 2014, 10:07 (EEST)
 * 
 * @deprecated	Use \DEV\Core\ajax\ajaxDirectory instead.
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
		$libPath = ajaxManager::updateMapFilepath();
		
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
	 * Get all the directories of the ajax files.
	 * 
	 * @param	boolean	$full
	 * 		Indicates whether the result will be a nested array or not.
	 * 
	 * @return	array
	 * 		A nested array or a full array with directories separated by "/".
	 * 
	 * @deprecated	Use ajaxManager::getDirectories() instead.
	 */
	public static function getDirs($full = FALSE)
	{
		return ajaxManager::getDirectories($full);
	}
}
//#section_end#
?>