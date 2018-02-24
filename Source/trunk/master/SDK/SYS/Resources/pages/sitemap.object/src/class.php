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

importer::import("ESS", "Environment", "url");
importer::import("SYS", "Resources", "pages/pageFolder");
importer::import("SYS", "Resources", "pages/page");
importer::import("API", "Resources", "DOMParser");

use \ESS\Environment\url;
use \SYS\Resources\pages\pageFolder;
use \SYS\Resources\pages\page;
use \API\Resources\DOMParser;

/**
 * Sitemap Manager
 * 
 * Generates the Redback's Sitemap file.
 * 
 * @version	0.1-3
 * @created	July 9, 2014, 11:00 (EEST)
 * @updated	March 5, 2015, 14:09 (EET)
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
		// Get all page information
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
		// Fetch all folder paths
		$fetchedFolders = self::fetchFolders();
		
		// Get all pages
		$allPages = page::getFolderPages($folderID = NULL);
		
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
		// Get url info
		$currentDomain = url::getDomain();
		$uInfo = url::info();
		$protocol = $uInfo['protocol'];
		
		// Get all folders
		$allFolders = pageFolder::getAllFolders();
		
		// Get folders by full names
		$fullNames = array();
		foreach ($allFolders as $folder)
		{
			if ($folder['is_root'])
				$fullNames[$folder['id']] = $protocol."://".$folder['domain'].".".$currentDomain."/";
			else
				$fullNames[$folder['id']] = $fullNames[$folder['parent_id']].$folder['name']."/";
			
		}
		
		return $fullNames;
	}
}
//#section_end#
?>