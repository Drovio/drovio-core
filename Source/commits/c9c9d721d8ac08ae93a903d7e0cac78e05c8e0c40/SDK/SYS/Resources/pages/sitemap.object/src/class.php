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
importer::import("API", "Resources", "DOMParser");

use \API\Model\sql\dbQuery;
use \API\Resources\DOMParser;
use \SYS\Comm\db\dbConnection;

/**
 * Sitemap Manager
 * 
 * Generates the Redback's Sitemap file.
 * 
 * @version	0.1-1
 * @created	July 9, 2014, 11:00 (EEST)
 * @revised	January 2, 2015, 10:40 (EET)
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
	 * 		[page_id]['loc'] as page location
	 * 		[page_id]['lastmod'] as last modification date.
	 */
	private function fetchPages()
	{
		// Fetch all folders
		$fetchedFolders = self::fetchFolders();
		
		// Get all pages
		$dbc = new dbConnection();
		$q = new dbQuery("1339513504", "units.domains.pages");
		$result = $dbc->execute($q);
		$allPages = $dbc->fetch($result, TRUE);
		
		// Fetch Sitemap Pages
		$fullPages = array();
		foreach ($allPages as $page)
			if ($page['sitemap'])
			{
				$fullPages[$page['id']]['loc'] = $fetchedFolders[$page['folder_id']].($page['file'] == "index.php" ? "" : $page['file']);
				
				$parts = explode(" ", $page['dateUpdated']);
				$lastmod = $parts[0];
				$fullPages[$page['id']]['lastmod'] = $lastmod;
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
		$dbc = new dbConnection();
		
		// Get all domains
		$q = new dbQuery("573078142", "units.domains");
		$result = $dbc->execute($q);
		$allDomains = $dbc->to_array($result, "name", "path");
		
		// Get all folders
		$q = new dbQuery("737200095", "units.domains.folders");
		$result = $dbc->execute($q);
		$allFolders = $dbc->fetch($result, TRUE);
		
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