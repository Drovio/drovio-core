<?php
//#section#[header]
// Namespace
namespace SYS\Resources\pages;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	SYS
 * @package	Resources
 * @namespace	\pages
 * 
 * @copyright	Copyright (C) 2015 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Resources", "filesystem/folderManager");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Resources\filesystem\folderManager;

/**
 * Folder Manager
 * 
 * The system's folder manager.
 * 
 * @version	0.1-2
 * @created	July 9, 2014, 10:50 (EEST)
 * @revised	January 2, 2015, 10:23 (EET)
 */
class pageFolder
{
	/**
	 * Create a new system folder.
	 * 
	 * @param	string	$name
	 * 		The name of the folder.
	 * 
	 * @param	integer	$parent_id
	 * 		The parent folder id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function create($name, $parent_id)
	{
		$parentFolder_info = self::info($parent_id);
		
		$dbc = new dbConnection();
		
		// Create folder to database
		$attr = array();
		$attr['name'] = $name;
		$attr['parent_id'] = $parent_id;
		$attr['domain'] = $parentFolder_info['domain'];
		$dbq = new dbQuery("1020682090", "units.domains.folders");
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
	 * Remove a folder, it must be empty of other folders and pages.
	 * 
	 * @param	integer	$folderID
	 * 		The folder's id to delete.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 * 
	 * @deprecated	Use remove() instead.
	 */
	public static function delete($folderID)
	{
		self::remove($folderID);
	}
	
	/**
	 * Remove a folder, it must be empty of other folders and pages.
	 * 
	 * @param	integer	$folderID
	 * 		The folder's id to delete.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($folderID)
	{
		// Get folder path
		$folderPath = self::trail($folderID);
		
		$dbc = new dbConnection();
		
		// Delete domain from Database
		$attr = array();
		$attr['id'] = $folderID;
		$dbq = new dbQuery("1366357023", "units.domains.folders");
		$success = $dbc->execute($dbq, $attr);
		
		// If an error occurred from the transaction (there are pages in the folder), return FALSE
		if (!$success)
			return FALSE;
		
		// Delete folder
		return folderManager::remove(systemRoot.$folderPath);
	}
	
	/**
	 * Get a folder's info.
	 * 
	 * @param	integer	$folderID
	 * 		The folder id.
	 * 
	 * @return	array
	 * 		An array of all folder info.
	 */
	public static function info($folderID)
	{
		// Get domain info from Database
		$dbq = new dbQuery("228088174", "units.domains.folders");
		$dbc = new dbConnection();
		
		$attr = array();
		$attr['id'] = $folderID;
		$result = $dbc->execute($dbq, $attr);
		
		// Fetch Result
		return $dbc->fetch($result);
	}
	
	/**
	 * Get a folder's full path trail.
	 * 
	 * @param	integer	$folderID
	 * 		The folder id.
	 * 
	 * @param	string	$delimiter
	 * 		The delimiter to separate the path.
	 * 		The default is the directory separator by php.
	 * 
	 * @return	string
	 * 		The folder's full trail path.
	 */
	public static function trail($folderID, $delimiter = "/")
	{
		// Get page Folder's hierarchy Path
		$dbq = new dbQuery("1482332828", "units.domains.folders");
		$dbc = new dbConnection();
		
		$attr = array();
		$attr['id'] = $folderID;
		$result = $dbc->execute($dbq, $attr);
		
		// Form folder path
		$path = $delimiter;
		while ($row = $dbc->fetch($result))
			$path .= $row['name'].$delimiter;
		
		return $path;
	}
	
	/**
	 * Get all system folders.
	 * 
	 * @return	array
	 * 		An array of all folder information.
	 */
	public static function getAllFolders()
	{
		// Initialize database connection
		$dbc = new dbConnection();
		
		// Get query and execute
		$dbq = new dbQuery("737200095", "units.domains.folders");
		$result = $dbc->execute($dbq);
		
		// Fetch all folders
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>