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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Resources", "pages::pageFolder");
importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "sql::dbQuery");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Resources", "paths");
importer::import("DEV", "Tools", "parsers::phpParser");

use \SYS\Resources\pages\pageFolder;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Resources\filesystem\fileManager;
use \DEV\Resources\paths;
use \DEV\Tools\parsers\phpParser;

/**
 * System Page Manager
 * 
 * Manages all Redback pages.
 * 
 * @version	0.1-3
 * @created	July 8, 2014, 20:04 (EEST)
 * @revised	November 21, 2014, 13:22 (EET)
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
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function create($folderID, $name)
	{
		// Add Extension
		$pageFileName = $name.".php";
		
		$dbc = new dbConnection();
		$dbq = new dbQuery("145300706", "units.domains.pages");
		
		// Create Database Record
		$attr = array();
		$attr['fid'] = $folderID;
		$attr['name'] = $pageFileName;
		$success = $dbc->execute($dbq, $attr);
		
		// If an error occured from the transaction (folder doesn't exist), return FALSE
		if (!$success)
			return FALSE;
			
		// Get page ID
		$page = $dbc->fetch($success);
		$pageID = $page['id'];

		// Get page path, build page and save
		$pagePath = self::path($pageID);
		$pageContent = self::buildPage($pageID);
		return fileManager::create(systemRoot.$pagePath, $pageContent);
	}
	
	/**
	 * Update an already existing page.
	 * This includes moving it from one folder to another.
	 * 
	 * @param	integer	$pageID
	 * 		The page id to update.
	 * 
	 * @param	integer	$moduleID
	 * 		The module that this page will load at runtime.
	 * 
	 * @param	string	$name
	 * 		The page's filename.
	 * 		Must be with extension.
	 * 
	 * @param	integer	$folderID
	 * 		The page's folder id.
	 * 		If different from original, the page will be moved.
	 * 
	 * @param	boolean	$static
	 * 		Indicator whether this page's content will be loaded statically or async.
	 * 
	 * @param	boolean	$sitemap
	 * 		Indicator whether this page will be included in the Redback's sitemap.
	 * 
	 * @param	array	$attributes
	 * 		An array of attributes for the page builder.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function update($pageID, $moduleID, $name, $folderID, $static, $sitemap, $attributes = array())
	{
		// Get Start Path
		$startPath = self::path($pageID);
		
		// Add Extension
		$pageFileName = $name.".php";
		
		// Create Database Record
		$attr = array();
		$attr['pid'] = $pageID;
		$attr['mid'] = $moduleID;
		$attr['fid'] = $folderID;
		$attr['static'] = $static;
		$attr['sitemap'] = $sitemap;
		$attr['name'] = $pageFileName;
		$attr['attributes'] = http_build_query($attributes, '', ';');
		
		$dbc = new dbConnection();
		$dbq = new dbQuery("1628491456", "units.domains.pages");
		$success = $dbc->execute($dbq, $attr);
		
		// If an error occured from the transaction (folder doesn't exist), return FALSE
		if (!$success)
			return FALSE;

		// Update page contents
		$pageContent = self::buildPage($pageID);
		fileManager::create(systemRoot.$startPath, $pageContent);
		
		// Get final page path
		$finalPath = self::path($pageID);
		if ($startPath != $finalPath)
			return fileManager::move(systemRoot.$startPath, systemRoot.$finalPath);
			
		return TRUE;
	}
	
	/**
	 * After creation, this function builds the page content and returns it to be stored in the page file.
	 * 
	 * @param	integer	$pageID
	 * 		The page id to build.
	 * 
	 * @return	string
	 * 		The page php content.
	 */
	private static function buildPage($pageID)
	{
		// Create page contents
		$pageLoaderPath = systemRoot.paths::getDevRsrcPath()."/platform/page.inc";
		$pageContent = fileManager::get($pageLoaderPath);
		$pageContentCleared = phpParser::unwrap($pageContent);
		
		// Prepend Page ID
		$pagePrepend = "";
		$pagePrepend .= '// Page Id'."\n";
		$pagePrepend .= '$page_id = '.$pageID.";\n";
		
		// Make PHP file
		$finalPageContent = $pagePrepend.$pageContentCleared;
		return phpParser::wrap($finalPageContent);
	}
	
	/**
	 * Remove a page from the system.
	 * 
	 * @param	integer	$pageID
	 * 		The page id to delete.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 * 
	 * @deprecated	Use remove() instead.
	 */
	public static function delete($pageID)
	{
		self::remove($pageID);
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
		
		// Delete page from Database
		$attr = array();
		$attr['id'] = $pageID;
		$dbq = new dbQuery("310659455", "units.domains.pages");
		
		$dbc = new dbConnection();
		$success = $dbc->execute($dbq, $attr);
		
		// If an error occurred from the transaction, return FALSE
		if (!$success)
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
		$dbq = new dbQuery("739807288", "units.domains.pages");
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