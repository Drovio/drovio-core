<?php
//#section#[header]
// Namespace
namespace API\Developer\components\pages;

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
 * @namespace	\components\pages
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "DOMParser");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Resources\DOMParser;

/**
 * Sitemap Manager
 * 
 * Generates the Redback Sitemap file
 * 
 * @version	{empty}
 * @created	July 4, 2013, 18:36 (EEST)
 * @revised	July 4, 2013, 18:36 (EEST)
 */
class sitemap
{
	/**
	 * Generates the sitemap.
	 * 
	 * @return	void
	 */
	public static function generate()
	{
		// Fetch All System Pages
		$fullPages = self::fetchPages();
		
		// Generate XML File
		$parser = new DOMParser();
		$urlset = $parser->create("urlset");
		$parser->append($urlset);
		$parser->attr($urlset, "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
		$parser->attr($urlset, "xsi:schemaLocation", "http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd");
		$parser->attr($urlset, "xmlns", "http://www.sitemaps.org/schemas/sitemap/0.9");
		
		foreach ($fullPages as $page)
		{
			// Create page entry
			$pageEntry = $parser->create("url");
			$parser->append($urlset, $pageEntry);
			
			// Create page attributes
			$loc = $parser->create("loc", $page['loc']);
			$parser->append($pageEntry, $loc);
			
			$lastmod = $parser->create("lastmod", $page['lastmod']);
			$parser->append($pageEntry, $lastmod);
			
			$changefreq = $parser->create("changefreq", "yearly");
			$parser->append($pageEntry, $changefreq);
		}
		
		// Save file
		$parser->save(systemRoot."/", "Sitemap.xml", $format = TRUE);
	}
	
	/**
	 * Fetch all pages from the database.
	 * 
	 * @return	array
	 * 		Returns an array of the full name of each page along with the date updated.
	 */
	private function fetchPages()
	{
		// Fetch all folders
		$fetchedFolders = self::fetchFolders();
		
		// Get all pages
		$dbc = new interDbConnection();
		$q = new dbQuery("1339513504", "units.domains.pages");
		$result = $dbc->execute_query($q);
		$allPages = $dbc->toFullArray($result);
		
		// Fetch Sitemap Pages
		$fullPages = array();
		foreach ($allPages as $page)
			if ($page['sitemap'])
			{
				$fullPages[$page['id']]['loc'] = $fetchedFolders[$page['folder_id']].($page['file'] == "index.php" ? "" : $page['file']);
				$fullPages[$page['id']]['lastmod'] = $page['dateUpdated'];
			}
		
		return $fullPages;
	}
	
	/**
	 * Fetch all folders (with the domains) from the database.
	 * 
	 * @return	array
	 * 		Returns an array of the full name of each folder (with the subdomain) as a url.
	 */
	private function fetchFolders()
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get all domains
		$q = new dbQuery("573078142", "units.domains");
		$result = $dbc->execute_query($q);
		$allDomains = $dbc->to_array($result, "name", "path");
		
		// Get all folders
		$q = new dbQuery("737200095", "units.domains.folders");
		$result = $dbc->execute_query($q);
		$allFolders = $dbc->toFullArray($result);
		
		$fullNames = array();
		foreach ($allFolders as $folder)
		{
			if ($folder['is_root'])
				$fullNames[$folder['id']] = "http://".$folder['domain'].".redback.gr/";
			else
				$fullNames[$folder['id']] = $fullNames[$folder['parent_id']].$folder['name']."/";
			
		}
		
		return $fullNames;
	}
}
//#section_end#
?>