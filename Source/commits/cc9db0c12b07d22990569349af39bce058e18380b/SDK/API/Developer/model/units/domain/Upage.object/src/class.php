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

importer::import("API", "Content", "filesystem::fileManager");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Model", "units::domain::Spage");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Comm", "database::connections::interDbConnection");

use \API\Content\filesystem\fileManager;
use \API\Developer\content\document\parsers\phpParser;
use \API\Model\units\sql\dbQuery;
use \API\Model\units\domain\Spage;
use \API\Comm\database\connections\interDbConnection;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;

/**
 * Page Manager
 * 
 * The system's page manager.
 * 
 * @version	{empty}
 * @created	July 5, 2013, 13:52 (EEST)
 * @revised	January 27, 2014, 13:55 (EET)
 */
class Upage extends Spage
{
	/**
	 * Create a page into the given folder
	 * 
	 * @param	integer	$folder_id
	 * 		The folder's id
	 * 
	 * @param	string	$name
	 * 		The page filename.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function create($folder_id, $name)
	{
		// Add Extension
		$page_file_name = $name.".php";
		
		// Create Database Record
		$attr = array();
		$attr['fid'] = $folder_id;
		$attr['name'] = $page_file_name;

		$dbq = new dbQuery("145300706", "units.domains.pages");
		
		$dbc = new interDbConnection();
		$success = $dbc->execute_query($dbq, $attr);
		
		// If an error occured from the transaction (folder doesn't exist), return FALSE
		if (!$success)
			return FALSE;
			
		// Get folder_id
		$page = $dbc->fetch($success);
		$page_id = $page['id'];

		// Get page path
		$page_path = parent::path($page_id);

		// Create page contents
		$pageContent = self::buildPage($page_id);
		
		// Create page file
		return fileManager::create(systemRoot.$page_path, $pageContent);
	}
	
	/**
	 * Update an already existing page.
	 * This includes moving it from one folder to another.
	 * 
	 * @param	integer	$page_id
	 * 		The page's id
	 * 
	 * @param	integer	$module_id
	 * 		The module that this page loads
	 * 
	 * @param	string	$name
	 * 		The page's filename
	 * 
	 * @param	integer	$folder_id
	 * 		The parent's folder id
	 * 
	 * @param	boolean	$static
	 * 		Indicator whether this page is being loaded statically or async
	 * 
	 * @param	{type}	$sitemap
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function update($page_id, $module_id, $name, $folder_id, $static, $sitemap)
	{
		// Get Start Path
		$start_path = parent::path($page_id);
		
		// Add Extension
		$page_file_name = $name.".php";
		
		// Create Database Record
		$attr = array();
		$attr['pid'] = $page_id;
		$attr['mid'] = $module_id;
		$attr['fid'] = $folder_id;
		$attr['static'] = $static;
		$attr['sitemap'] = $sitemap;
		$attr['name'] = $page_file_name;

		$dbq = new dbQuery("1628491456", "units.domains.pages");
		
		$dbc = new interDbConnection();
		$success = $dbc->execute_query($dbq, $attr);
		
		// If an error occured from the transaction (folder doesn't exist), return FALSE
		if (!$success)
			return FALSE;

		// Update page contents
		$pageContent = self::buildPage($page_id);
		fileManager::create(systemRoot.$start_path, $pageContent);
		
		// Get final page path
		$final_path = parent::path($page_id);
		if ($start_path != $final_path)
			return fileManager::move(systemRoot.$start_path, systemRoot.$final_path);
			
		return TRUE;
	}
	
	/**
	 * Build the page contents.
	 * 
	 * @param	integer	$pageID
	 * 		The page id.
	 * 
	 * @return	string
	 * 		Returns the page contents as php code.
	 */
	private static function buildPage($pageID)
	{
		// Create page contents
		//_____ Get Resource
		$pageLoaderPath = systemRoot.paths::getDevRsrcPath()."/Content/Pages/Headers/loader.inc";
		$pageContent = fileManager::get($pageLoaderPath);
		$pageContentCleared = phpParser::unwrap($pageContent);
		
		//_____ Prepent Page ID
		$pagePrepend = "";
		$pagePrepend .= '//__________ Page Id __________//'."\n";
		$pagePrepend .= '$page_id = '.$pageID.";\n";
		
		//_____ Make PHP file
		$finalPageContent = $pagePrepend.$pageContentCleared;
		return phpParser::wrap($finalPageContent);
	}
	
	/**
	 * Delete a page
	 * 
	 * @param	integer	$pageID
	 * 		The page id to delete.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function delete($pageID)
	{
		// Delete page from Database
		$attr = array();
		$attr['id'] = $pageID;
		$dbq = new dbQuery("310659455", "units.domains.pages");
		
		$dbc = new interDbConnection();
		$success = $dbc->execute($dbq, $attr);
		
		// If an error occured from the transaction, return FALSE
		if (!$success)
			return FALSE;
		
		// Delete page file
		$page_path = parent::path($pageID);
		return fileManager::remove(systemRoot.$page_path);
	}
}
//#section_end#
?>