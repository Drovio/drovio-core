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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "filesystem/directory");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\directory;

/**
 * Folder Manager
 * 
 * The system's folder manager.
 * 
 * @version	1.0-2
 * @created	July 9, 2014, 10:50 (EEST)
 * @updated	May 19, 2015, 23:13 (EEST)
 */
class pageFolder
{
	/**
	 * Create a new system folder.
	 * 
	 * @param	string	$name
	 * 		The name of the folder.
	 * 
	 * @param	integer	$parentID
	 * 		The parent folder id.
	 * 		Leave empty to create in the domain root.
	 * 		It is NULL by default.
	 * 
	 * @param	string	$domain
	 * 		The folder domain.
	 * 		Leave empty to add to a certain folder as child.
	 * 		It is NULL by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function create($name, $parentID = NULL, $domain = NULL)
	{
		// Get parent folder info
		if (!empty($parentID))
		{
			$parentFolder_info = self::info($parentID);
			$domain = $parentFolder_info['domain'];
		}
		else if (!empty($domain))
			$parentID = "NULL";
		else
			return FALSE;
		
		// Create folder to database
		$dbc = new dbConnection();
		$dbq = new dbQuery("1906906072983", "pages.folders");
		
		$attr = array();
		$attr['name'] = $name;
		$attr['parent_id'] = $parentID;
		$attr['domain'] = $domain;
		$result = $dbc->execute($dbq, $attr);
			
		// If error occurred (domain or parent folder doesn't exist), return FALSE
		if (!$result)
			return FALSE;

		// Get folder_id
		$folder = $dbc->fetch($result);
		$folder_id = $folder['last_id'];
		$folder_path = self::trail($folder_id);
		
		// Create page folder
		if (!file_exists(systemRoot.$folder_path))
			return folderManager::create(systemRoot.$folder_path);
			
		return TRUE;
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
		$dbq = new dbQuery("27297279847345", "pages.folders");
		
		// Delete domain from Database
		$attr = array();
		$attr['id'] = $folderID;
		$result = $dbc->execute($dbq, $attr);
		
		// If an error occurred (there are pages and/or other folders in the folder), return FALSE
		if (!$result)
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
		$dbc = new dbConnection();
		$dbq = new dbQuery("19927384987829", "pages.folders");
		
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
		$dbc = new dbConnection();
		$dbq = new dbQuery("15564072624107", "pages.folders");
		
		$attr = array();
		$attr['id'] = $folderID;
		$result = $dbc->execute($dbq, $attr);
		
		// Form folder path
		$path = $delimiter;
		while ($row = $dbc->fetch($result))
			$path .= $row['name'].$delimiter;
		
		return directory::normalize($path);
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
		$dbq = new dbQuery("1776501196441", "pages.folders");
		$result = $dbc->execute($dbq);
		
		// Fetch all folders
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all domain subfolders.
	 * 
	 * @param	string	$domain
	 * 		The domain name.
	 * 
	 * @return	array
	 * 		An array of all domain folders in the first depth.
	 */
	public static function getDomainFolders($domain)
	{
		// Initialize database connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("17055148612925", "pages.folders");
		
		// Get query and execute
		$attr = array();
		$attr['domain'] = $domain;
		$result = $dbc->execute($dbq, $attr);
		
		// Fetch all folders
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all subfolders of a given folder.
	 * 
	 * @param	integer	$folderID
	 * 		The parent folder id.
	 * 
	 * @return	array
	 * 		An array of all subfolders in the first depth.
	 */
	public static function getSubFolders($folderID)
	{
		// Initialize database connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("3446131105843", "pages.folders");
		
		// Get query and execute
		$attr = array();
		$attr['pid'] = $folderID;
		$result = $dbc->execute($dbq, $attr);
		
		// Fetch all folders
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>