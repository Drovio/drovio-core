<?php
//#section#[header]
// Namespace
namespace API\Developer\model\units\domain;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");

// Import logger
importer::import("API", "Developer", "profiler::logger");
use \API\Developer\profiler\logger as loggr;
use \API\Developer\profiler\logger;
//#section_end#
//#section#[class]
/**
 * @library	{empty}
 * @package	{empty}
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Content", "filesystem::folderManager");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Model", "units::domain::SpageFolder");
importer::import("API", "Comm", "database::connections::interDbConnection");

use \API\Content\filesystem\folderManager;
use \API\Model\units\sql\dbQuery;
use \API\Model\units\domain\SpageFolder;
use \API\Comm\database\connections\interDbConnection;

/**
 * Folder Manager
 * 
 * The system's folder manager for pages.
 * 
 * @version	{empty}
 * @created	{empty}
 * @revised	{empty}
 */
class Ufolder extends SpageFolder
{
	/**
	 * Create a folder
	 * 
	 * @param	string	$name
	 * 		The name of the folder
	 * 
	 * @param	integer	$parent_id
	 * 		The parent's folder id
	 * 
	 * @return	boolean
	 */
	public static function create($name, $parent_id)
	{
		$parentFolder_info = parent::info($parent_id);
		
		// Create folder to database
		$attr = array();
		$attr['name'] = $name;
		$attr['parent_id'] = $parent_id;
		$attr['domain'] = $parentFolder_info['domain'];

		$dbq = new dbQuery("1020682090", "units.domains.folders");
		
		$dbc = new interDbConnection();
		$success = $dbc->execute($dbq, $attr);
			
		// If error occured, return FALSE
		if (!$success)
			return FALSE;

		// Get folder_id
		$folder = $dbc->fetch($success);
		$folder_id = $folder['last_id'];
		$folder_path = self::trail($folder_id);
		
		// Create page folder
		return folderManager::create(systemRoot.$folder_path);
	}
	
	/**
	 * Delete a folder (it must be empty)
	 * 
	 * @param	string	$folder_id
	 * 		The folder's id
	 * 
	 * @return	boolean
	 */
	public static function delete($folder_id)
	{
		// Get folder path
		$folder_path = parent::trail($folder_id);
		
		// Delete domain from Database
		$attr = array();
		$attr['id'] = $folder_id;
		$dbq = new dbQuery("1366357023", "units.domains.folders");
		
		$dbc = new interDbConnection();
		$success = $dbc->execute_query($dbq, $attr);
		
		// If an error occured from the transaction (there are pages in the folder), return FALSE
		if (!$success)
			return FALSE;
		
		// Delete folder
		return folderManager::remove(systemRoot.$folder_path);
	}
}
//#section_end#
?>