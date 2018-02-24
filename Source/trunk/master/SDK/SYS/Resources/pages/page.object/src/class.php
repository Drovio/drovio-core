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

importer::import("SYS", "Resources", "pages/pageFolder");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "modules/resource");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Tools", "codeParser");

use \SYS\Resources\pages\pageFolder;
use \SYS\Comm\db\dbConnection;
use \API\Model\modules\resource;
use \API\Model\sql\dbQuery;
use \API\Resources\filesystem\fileManager;
use \DEV\Tools\codeParser;

/**
 * System Page Manager
 * 
 * Manages all Redback pages.
 * 
 * @version	2.0-3
 * @created	July 8, 2014, 20:04 (EEST)
 * @updated	June 11, 2015, 10:27 (EEST)
 */
class page
{
	/**
	 * Create a page into the given folder
	 * 
	 * @param	integer	$folderID
	 * 		The folder's id as parent.
	 * 
	 * @param	string	$name
	 * 		The page filename.
	 * 		Extension not included, all pages are .php.
	 * 
	 * @param	string	$pageContent
	 * 		The page content.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function create($folderID, $name, $pageContent = "")
	{
		// Initialize database connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("24464920074779", "pages");
		
		// Create Database Record
		$attr = array();
		$attr['fid'] = $folderID;
		$attr['name'] = $name;
		$result = $dbc->execute($dbq, $attr);
		
		// If an error occurred (parent folder doesn't exist), return FALSE
		if (!$result)
			return FALSE;
			
		// Get page ID
		$page = $dbc->fetch($result);
		$pageID = $page['id'];

		// Get page path, build page and save
		$pagePath = self::path($pageID);
		return fileManager::create(systemRoot.$pagePath, $pageContent);
	}
	
	/**
	 * Update an already existing page.
	 * This includes moving it from one folder to another.
	 * 
	 * @param	integer	$pageID
	 * 		The page id to update.
	 * 
	 * @param	string	$name
	 * 		The page's filename.
	 * 		Must be with extension.
	 * 
	 * @param	integer	$folderID
	 * 		The page's folder id.
	 * 		If different from original, the page will be moved.
	 * 
	 * @param	boolean	$sitemap
	 * 		Indicator whether this page will be included in the Redback's sitemap.
	 * 
	 * @param	string	$pageContent
	 * 		The page content.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function update($pageID, $name, $folderID, $sitemap, $pageContent = "")
	{
		// Get Start Path
		$startPath = self::path($pageID);
		
		// Initialize database connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("354395146035", "pages");
		
		// Create Database Record
		$attr = array();
		$attr['pid'] = $pageID;
		$attr['fid'] = $folderID;
		$attr['sitemap'] = $sitemap;
		$attr['name'] = $name;
		$result = $dbc->execute($dbq, $attr);
		
		// If an error occurred (folder doesn't exist), return FALSE
		if (!$result)
			return FALSE;

		// Update page contents
		$pageContent = codeParser::decode($pageContent);
		fileManager::put(systemRoot.$startPath, $pageContent);
		
		// Get final page path
		$finalPath = self::path($pageID);
		if ($startPath != $finalPath)
			return fileManager::move(systemRoot.$startPath, systemRoot.$finalPath);
			
		return TRUE;
	}
	
	/**
	 * Engage a page to load a module.
	 * 
	 * @param	integer	$pageID
	 * 		The page id.
	 * 
	 * @param	integer	$moduleID
	 * 		The module to connect to the given page.
	 * 
	 * @param	boolean	$static
	 * 		Indicator whether this page's module content will be loaded statically or async.
	 * 
	 * @param	array	$attributes
	 * 		An array of attributes for the page builder.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function engageModule($pageID, $moduleID, $static, $attributes = array())
	{
		// Initialize database connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("27543510593512", "pages");
		
		// Update Database
		$attr = array();
		$attr['pid'] = $pageID;
		$attr['mid'] = $moduleID;
		$attr['static'] = $static;
		$attr['attributes'] = http_build_query($attributes, '', ';');
		$success = $dbc->execute($dbq, $attr);
		
		// Update content
		$pagePath = self::path($pageID);
		$pageContent = self::buildModulePage($pageID);
		return fileManager::put(systemRoot.$pagePath, $pageContent);
	}
	
	/**
	 * Build a specific module-connected page content.
	 * 
	 * @param	integer	$pageID
	 * 		The page id to connect the module to.
	 * 
	 * @return	string
	 * 		The page php content.
	 */
	private static function buildModulePage($pageID)
	{
		// Create page contents
		$pageContent = resource::get("/DEV/templates/module_page.inc");
		
		// Set page variables
		$pageContent = str_replace("{pageID}", $pageID, $pageContent);
		
		// Return modified page content
		return $pageContent;
	}
	
	/**
	 * Remove a page from the system.
	 * 
	 * @param	integer	$pageID
	 * 		The page id to delete.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($pageID)
	{
		// Get page filepath
		$pagePath = self::path($pageID);
		
		// Initialize database connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("2348084225363", "pages");
		
		// Delete page from Database
		$attr = array();
		$attr['id'] = $pageID;
		$result = $dbc->execute($dbq, $attr);
		
		// If an error occurred from the transaction, return FALSE
		if (!$result)
			return FALSE;
			
		return fileManager::remove(systemRoot.$pagePath);
	}
	
	/**
	 * Get page information by page id.
	 * 
	 * @param	integer	$pageID
	 * 		The page id to get the information for.
	 * 
	 * @return	array
	 * 		An array of all page information.
	 */
	public static function info($pageID)
	{
		// Get page info
		$dbq = new dbQuery("1587911972443", "pages");
		$dbc = new dbConnection();
		
		$attr = array();
		$attr['id'] = $pageID;
		$result = $dbc->execute($dbq, $attr);
		
		// Get page info
		$pageInfo = $dbc->fetch($result);
		if ($pageInfo)
		{
			// Set attributes as array
			$attrs = str_replace(";", "&", $pageInfo['attributes']);
			parse_str($attrs, $pageInfo['attributes']);
		}
		
		return $pageInfo;
	}
	
	/**
	 * Get all pages inside a given folder.
	 * 
	 * @param	integer	$folderID
	 * 		The folder id to get the pages from.
	 * 
	 * @return	array
	 * 		An array of all pages.
	 */
	public static function getFolderPages($folderID = NULL)
	{
		// Get page info
		$dbq = new dbQuery("25212030657", "pages");
		$dbc = new dbConnection();
		
		$attr = array();
		$attr['fid'] = (empty($folderID) ? "NULL" : $folderID);
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get the page plain content.
	 * 
	 * @param	integer	$pageID
	 * 		The page id to load the content from.
	 * 
	 * @return	string
	 * 		The page plain content.
	 */
	public static function getContent($pageID)
	{
		// Get page path
		$pagePath = self::path($pageID);
		
		// Return page content
		return fileManager::get(systemRoot.$pagePath);
	}
	
	/**
	 * Loads the page file to be executed.
	 * Throws an exception if the page doesn't exist.
	 * 
	 * @param	integer	$pageID
	 * 		The page id to load.
	 * 
	 * @return	void
	 * 
	 * @throws	Exception
	 */
	public static function load($pageID)
	{
		// Get page path
		$pagePath = self::path($pageID);
		
		// Check if file exists and load page file
		if (file_exists(systemRoot.$pagePath))
			return importer::incl($pagePath, TRUE, FALSE);
		else
			throw new Exception("Page not found to be loaded.");
	}
	
	/**
	 * Get the full path of the page
	 * 
	 * @param	integer	$pageID
	 * 		The page id to get the path for.
	 * 
	 * @return	string
	 * 		The full page path.
	 */
	public static function path($pageID)
	{
		// Get page info
		$pageInfo = self::info($pageID);

		// Get folder trail
		$folderPath = pageFolder::trail($pageInfo['folder_id']);
		
		// Create page path
		return $folderPath."/".$pageInfo['file'];
	}
}
//#section_end#
?>