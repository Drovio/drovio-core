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
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\model\units\domain
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
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
 * @created	March 24, 2014, 10:59 (EET)
 * @revised	March 24, 2014, 10:59 (EET)
 * 
 * @deprecated	Use \API\Resources\pages\pageFolder instead.
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
	 * 		{description}
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
	 * @param	{type}	$folderID
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function delete($folderID)
	{
		// Get folder path
		$folderPath = parent::trail($folderID);
		
		// Delete domain from Database
		$attr = array();
		$attr['id'] = $folderID;
		$dbq = new dbQuery("1366357023", "units.domains.folders");
		
		$dbc = new interDbConnection();
		$success = $dbc->execute($dbq, $attr);
		
		// If an error occured from the transaction (there are pages in the folder), return FALSE
		if (!$success)
			return FALSE;
		
		// Delete folder
		return folderManager::remove(systemRoot.$folderPath);
	}
}
//#section_end#
?>