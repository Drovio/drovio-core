<?php
//#section#[header]
// Namespace
namespace DEV\Core\sql;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Core
 * @namespace	\sql
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Projects\project;
use \DEV\Version\vcs;

/**
 * Developer's Query Domain Manager
 * 
 * Manages all query domains and the entire sql library.
 * 
 * @version	{empty}
 * @created	April 1, 2014, 10:46 (EEST)
 * @revised	April 1, 2014, 10:46 (EEST)
 */
class sqlDomain
{
	/**
	 * The system library publish folder.
	 * 
	 * @type	string
	 */
	const EXPORT_PATH = "/System/Library/SQL/";
	
	/**
	 * Create a new domain.
	 * 
	 * @param	string	$name
	 * 		The domain name.
	 * 
	 * @param	string	$parent
	 * 		The parent domain separated by ".".
	 * 		Leave empty for root domain.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function create($name, $parent = "")
	{
		$mapPath = self::updateMapFilepath();
		
		// Check if domain already exists
		$parser = new DOMParser();
		$parser->load($mapPath, FALSE);
		if (!empty($parent))
			$xpath = "/map/domain[@name='".str_replace(".", "']/*[@name='", $parent)."']/*[@name='".$name."']";
		else
			$xpath = "/map/domain[@name='".$name."']";
			
		// If domain already exists, return FALSE
		$domainElement = $parser->evaluate($xpath)->item(0);
		if (!is_null($domainElement))
			return FALSE;
		
		// Get Parent
		if (empty($parent))
			$xpath = "/map";
		else
			$xpath = "/map/domain[@name='".str_replace(".", "']/*[@name='", $parent)."']";
		$parentElement = $parser->evaluate($xpath)->item(0);
		if (is_null($parentElement))
			return FALSE;
		$domainElement = $parser->create('domain');
		$parser->attr($domainElement, "name", $name);
		$parser->append($parentElement, $domainElement);
		$parser->update();
		
		// Create Production Folder and index file
		$nsdomain = str_replace(".", "/", $parent)."/".$name;
		folderManager::create(systemRoot.self::EXPORT_PATH."/", $nsdomain);
		$parser = new DOMParser();
		$base = $parser->create('queries');
		$parser->append($base);
		$parser->save(systemRoot.self::EXPORT_PATH."/".$nsdomain."/", "index.xml", TRUE);
		
		return TRUE;
	}
	
	/**
	 * Add an sql query index entry to the given domain.
	 * 
	 * @param	string	$domain
	 * 		The query domain separated by ".".
	 * 
	 * @param	string	$queryID
	 * 		The query id.
	 * 
	 * @param	string	$title
	 * 		The query title.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function addQuery($domain, $queryID, $title)
	{
		$mapPath = self::updateMapFilepath();
		
		// Load index file
		$parser = new DOMParser();
		$parser->load($mapPath, FALSE);
		
		// Get domain element
		$xpath = "/map/domain[@name='".str_replace(".", "']/*[@name='", $domain)."']";
		$domainElement = $parser->evaluate($xpath)->item(0);
		
		// Append query entry
		$queryEntry = $parser->create("query", "", $queryID);
		$parser->attr($queryEntry, "title", $title);
		$parser->append($domainElement, $queryEntry);
		
		return $parser->update();
	}
	
	/**
	 * Update a query's name in the mapping file.
	 * 
	 * @param	string	$queryID
	 * 		The query id.
	 * 
	 * @param	string	$title
	 * 		The query title.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateQuery($queryID, $title)
	{
		$mapPath = self::updateMapFilepath();
		
		// Load index file
		$parser = new DOMParser();
		$parser->load($mapPath, FALSE);
		
		// Get query element
		$queryElement = $parser->find($queryID);
		$parser->attr($queryElement, "title", $title);
		
		return $parser->update();
	}
	
	/**
	 * Gets the sql queries of the domain.
	 * 
	 * @param	string	$domain
	 * 		The domain separated by ".".
	 * 
	 * @return	array
	 * 		An array of queries by key as the query id and the title as value.
	 */
	public static function getQueries($domain)
	{
		$mapPath = self::getMapFilepath();
		
		// Load index file
		$parser = new DOMParser();
		$parser->load($mapPath, FALSE);
		
		// Get domain element
		$xpath = "/map/domain[@name='".str_replace(".", "']/*[@name='", $domain)."']";
		$domainElement = $parser->evaluate($xpath)->item(0);
		
		$queries = $parser->evaluate("query", $domainElement);
		$domainQueries = array();
		foreach ($queries as $query)
			$domainQueries[$parser->attr($query, "id")] = $parser->attr($query, "title");
			
		return $domainQueries;
	}
	
	/**
	 * Gets the list of all domains
	 * 
	 * @param	boolean	$full
	 * 		If full, the return array is a list of full names.
	 * 		Otherwise, it is a nested array.
	 * 
	 * @return	array
	 * 		Returns a list of domains in the library.
	 */
	public static function getList($full = FALSE)
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
		$project = new project(1);
		$repository = $project->getRepository();
		$vcs = new vcs($repository, FALSE);
		
		// Get item ID
		$itemID = self::getMapfileItemID();
		return $vcs->getItemTrunkPath($itemID);
	}
	
	/**
	 * Updates the vcs item of the map file.
	 * 
	 * @return	string
	 * 		The trunk path to the map index file.
	 */
	public static function updateMapFilepath()
	{
		$project = new project(1);
		$repository = $project->getRepository();
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
	 * Publish all sql library packages to the server.
	 * 
	 * @param	string	$branchName
	 * 		The branch to publish.
	 * 
	 * @return	void
	 */
	public static function publish($branchName = vcs::MASTER_BRANCH)
	{
		// Get repository
		$project = new project(1);
		$repository = $project->getRepository();
		$vcs = new vcs($repository, FALSE);
		$releasePath = $vcs->getCurrentRelease($branchName)."/SQL/";
		
		// Copy map file
		$contents = fileManager::get($releasePath."/map.xml");
		fileManager::create(systemRoot."/System/Library/SQL/map.xml", $contents, TRUE);
		
		// Deploy all queries
		$domainList = self::getDomainList(TRUE);
		foreach ($domainList as $domain)
		{
			$queryList = self::getQueries($domain);
			foreach ($queryList as $queryID => $title)
			{
				// Normalize query id
				$queryID = str_replace("q.", "", $queryID);
				$queryID = str_replace("q_", "", $queryID);
				
				$queryFileName = dvbQuery::getName($queryID);
				
				$nsdomain = str_replace(".", "/", $domain);
				
				// Export sql query
				$contents = fileManager::get($releasePath."/".$nsdomain."/".$queryFileName.".sql/query.sql");
				fileManager::create(systemRoot."/System/Library/SQL/".$nsdomain."/".$queryFileName.".sql", $contents, TRUE);
			}
		}
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
	 * 		A nested array of subdomains.
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
	 * Get the subdomains of a domain as a full name array.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the map file.
	 * 
	 * @param	DOMElement	$base
	 * 		The base domain element.
	 * 
	 * @return	array
	 * 		An array of subdomains as full names.
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