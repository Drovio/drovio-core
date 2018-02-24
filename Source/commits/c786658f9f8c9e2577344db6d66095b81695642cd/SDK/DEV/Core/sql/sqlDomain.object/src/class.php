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
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Core", "sql/sqlQuery");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Version\vcs;
use \DEV\Core\sql\sqlQuery;

/**
 * Developer's Query Domain Manager
 * 
 * Manages all query domains and the entire sql library.
 * 
 * @version	0.1-2
 * @created	April 1, 2014, 10:46 (EEST)
 * @revised	December 18, 2014, 11:13 (EET)
 */
class sqlDomain
{
	/**
	 * The system library publish folder.
	 * 
	 * @type	string
	 */
	const EXPORT_PATH = "/System/Library/Core/SQL/";
	
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
		return $parser->update();
	}
	
	/**
	 * Remove an sql domain from the map.
	 * The domain must be empty of queries and other domains.
	 * 
	 * @param	string	$domain
	 * 		The domain name (separated by ".").
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($domain)
	{
		$mapPath = self::updateMapFilepath();
		
		// Load index file
		$parser = new DOMParser();
		$parser->load($mapPath, FALSE);
		
		// Replace domain entry with null (if empty of children)
		$xpath = "/map/domain[@name='".str_replace(".", "']/*[@name='", $domain)."']";
		$domainEntry = $parser->evaluate($xpath)->item(0);
		if ($domainEntry->childNodes->length > 0)
			return FALSE;
		
		$parser->replace($domainEntry, NULL);
		return $parser->update();
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
	 * Remove an sql query from the sql map index file.
	 * 
	 * @param	string	$domain
	 * 		The query domain separated by ".".
	 * 
	 * @param	string	$queryID
	 * 		The query id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function removeQuery($domain, $queryID)
	{
		$mapPath = self::updateMapFilepath();
		
		// Load index file
		$parser = new DOMParser();
		$parser->load($mapPath, FALSE);
		
		// Replace query entry with null
		$queryEntry = $parser->find($queryID);
		$parser->replace($queryEntry, NULL);
		
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
		$vcs = new vcs(1);
		
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
		$vcs = new vcs(1);
		
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
		$vcs = new vcs(1);
		$releasePath = $vcs->getCurrentRelease($branchName)."/SQL/";
		
		// Copy map file
		$contents = fileManager::get($releasePath."/map.xml");
		fileManager::create(systemRoot.self::EXPORT_PATH."/map.xml", $contents, TRUE);
		
		// Deploy all queries
		$domainList = self::getList(TRUE);
		foreach ($domainList as $domain)
		{
			$queryList = self::getQueries($domain);
			foreach ($queryList as $queryID => $title)
			{
				// Normalize query id
				$queryID = str_replace("q.", "", $queryID);
				$queryID = str_replace("q_", "", $queryID);
				
				$queryFileName = sqlQuery::getName($queryID);
				
				$nsdomain = str_replace(".", "/", $domain);
				
				// Export sql query
				$contents = fileManager::get($releasePath."/".$nsdomain."/".$queryFileName.".sql/query.sql");
				fileManager::create(systemRoot.self::EXPORT_PATH."/".$nsdomain."/".$queryFileName.".sql", $contents, TRUE);
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