<?php
//#section#[header]
// Namespace
namespace API\Developer\model\version;

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
 * @namespace	\model\version
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Content", "filesystem::folderManager");

use \API\Content\filesystem\folderManager;

/**
 * Repository Manager
 * 
 * Handles all the repositories
 * 
 * @version	{empty}
 * @created	July 3, 2013, 12:59 (EEST)
 * @revised	July 3, 2013, 12:59 (EEST)
 * 
 * @deprecated	Functionality no longer used.
 */
class repository
{
	/**
	 * The system's repository folder
	 * 
	 * @type	string
	 */
	const PATH = "/Developer/Repositories";
	
	/**
	 * Create a repository
	 * 
	 * @param	string	$path
	 * 		The repository's path
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function create($path)
	{
		$ns_path = str_replace("::", "/", $path);
		
		return folderManager::create(systemRoot.self::PATH.$ns_path);
	}
	
	/**
	 * Remove a repository (it must be empty of objects)
	 * 
	 * @param	string	$path
	 * 		The repository's path
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function remove($path)
	{
		return folderManager::remove_full(systemRoot.self::PATH.$path);
	}
}
//#section_end#
?>