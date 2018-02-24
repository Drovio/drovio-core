<?php
//#section#[header]
// Namespace
namespace API\Developer\components\sql;

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
 * @namespace	\components\sql
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::sql::dvbDomain");
importer::import("API", "Developer", "components::sql::dvbQuery");
importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Developer", "projects::project");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Developer\components\sql\dvbDomain;
use \API\Developer\components\sql\dvbQuery;
use \API\Developer\misc\vcs;
use \API\Developer\projects\project;
use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * Developer's Database Library Manager
 * 
 * Manages global functions for database domains.
 * 
 * @version	{empty}
 * @created	July 3, 2013, 13:27 (EEST)
 * @revised	January 20, 2014, 13:29 (EET)
 */
class dvbLib
{
	/**
	 * The path for the sql map file.
	 * 
	 * @type	string
	 */
	const MAP_PATH = "/Mapping/Library/sql.xml";
	
	/**
	 * Gets the list of all domains
	 * 
	 * @param	boolean	$full
	 * 		Defines whether the returned array will be an array of full names or a nested array.
	 * 
	 * @return	array
	 * 		Returns a list of domains. If full, the array is a list of fullnames. Otherwise, it is a nested array.
	 */
	public static function getDomainList($full = FALSE)
	{
		$path = self::getMapFilepath();
		
		$parser = new DOMParser();
		$parser->load($path, FALSE);
		$base = $parser->evaluate("map")->item(0);
		
		$result = array();
		
		if (!$full)
		{
			// Base domains
			$domains = $parser->evaluate("domain", $base);
			foreach ($domains as $dom)
			{
				$name = $dom->getAttribute("name");
				$result[$name] = self::getSubDomains($parser, $dom);
			}
		}
		else
		{
			// Base domains
			$domains = $parser->evaluate("domain", $base);
			foreach ($domains as $dom)
			{
				$name = $dom->getAttribute("name");
				$result[] = $name;
				$subs = self::getSubDomainsString($parser, $dom);
				
				if (is_array($subs))
					foreach ($subs as $t)
						$result[] = $name.".".$t;
			}
		}
		
		return $result;
	}
	
	/**
	 * Get the sql map file path.
	 * 
	 * @return	string
	 * 		The map index file path.
	 */
	public static function getMapFilepath()
	{
		$repository = project::getRepository(1);
		$vcs = new vcs($repository, FALSE);
		
		// Get item ID
		$itemID = self::getMapfileItemID();
		return $vcs->getItemTrunkPath($itemID);
	}
	
	/**
	 * Updates the vcs item of the map file.
	 * 
	 * @return	string
	 * 		The trunk path to the
	 */
	public static function updateMapFilepath()
	{
		$repository = project::getRepository(1);
		$vcs = new vcs($repository, FALSE);
		
		$itemID = self::getMapfileItemID();
		return $vcs->updateItem($itemID, TRUE);
	}
	
	/**
	 * Get the map index file id.
	 * 
	 * @return	string
	 * 		The item id.
	 */
	private static function getMapfileItemID()
	{
		return "m".md5("map_SQL_map.xml");
	}
	
	/**
	 * Deploy all sql library packages.
	 * 
	 * @param	string	$branchName
	 * 		The branch to deploy.
	 * 
	 * @return	void
	 */
	public static function deploy($branchName = vcs::MASTER_BRANCH)
	{
		// Get repository
		$repository = project::getRepository(1);
		$vcs = new vcs($repository, FALSE);
		$releasePath = $vcs->getCurrentRelease($branchName)."/SQL/";
		
		// Copy map file
		$contents = fileManager::get($releasePath."/map.xml");
		fileManager::create(systemRoot."/System/Library/SQL/map.xml", $contents, TRUE);
		
		// Deploy all queries
		$domainList = self::getDomainList(TRUE);
		foreach ($domainList as $domain)
		{
			$queryList = dvbDomain::getQueries($domain);
			foreach ($queryList as $queryID => $title)
			{
				// Normalize query id
				$queryID = str_replace("q.", "", $queryID);
				$queryID = str_replace("q_", "", $queryID);
				
				$queryFileName = dvbQuery::getName($queryID);
				
				$nsdomain = str_replace(".", "/", $domain);
				
				// Export sql query
				$contents = fileManager::get($releasePath."/".$nsdomain."/".$queryFileName.".sql/query.sql");
				//echo $releasePath."/".$nsdomain."/".$queryFileName.".sql/query.sql\n";
				fileManager::create(systemRoot."/System/Library/SQL/".$nsdomain."/".$queryFileName.".sql", $contents, TRUE);
				
				// Export analysis index
			}
		}
	}
	
	/**
	 * Export entire query library.
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use deploy() instead.
	 */
	public static function export()
	{/*
		$domainList = self::getDomainList(TRUE);
		foreach ($domainList as $domain)
		{
			$queryList = dvbDomain::getQueries($domain);
			foreach ($queryList as $query => $title)
			{
				// Normalize query id
				$query = str_replace("q.", "", $query);
				$query = str_replace("q_", "", $query);
				
				// Export query
				$dvbq = new dvbQuery($domain, $query);
				$dvbq->export();
			}
		}
		
		// Copy map file
		$headPath = self::getMapFilepath();
		$contents = fileManager::get($headPath);
		
		// Create map for SDK
		fileManager::create(systemRoot."/System/Library/SQL/map.xml", $contents, TRUE);
		*/
	}
	
	/**
	 * Get the subdomains of a domain as a nested array. It is a recursive function.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the map file.
	 * 
	 * @param	DOMElement	$base
	 * 		The base domain element.
	 * 
	 * @return	array
	 * 		Nested array.
	 */
	private function getSubDomains($parser, $base)
	{
		$subs = $parser->evaluate("domain", $base);
		
		if ($subs->length == 0)
			return array();
			
		$result = array();
		foreach ($subs as $sub)
		{
			$name = $sub->getAttribute("name");
			$result[$name] = self::getSubDomains($parser, $sub);
		}
		return $result;
	}
	
	/**
	 * Get the subdomains of a domain as a fullname array.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the map file.
	 * 
	 * @param	DOMElement	$base
	 * 		The base domain element.
	 * 
	 * @return	array
	 * 		Array.
	 */
	private function getSubDomainsString($parser, $base)
	{
		$subs = $parser->evaluate("domain", $base);
		
		if ($subs->length == 0)
			return "";
			
		$result = array();
		foreach ($subs as $sub)
		{
			$name = $sub->getAttribute("name");
			$result[] = $name;
			$temp = self::getSubDomainsString($parser, $sub);
			
			if (is_array($temp))
				foreach ($temp as $t)
					$result[] = $name.".".$t;
		}
		return $result;
	}
	
}
//#section_end#
?>