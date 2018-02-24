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
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::sql::dvbLib");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");

use \API\Developer\components\sql\dvbLib;
use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;

/**
 * Developer's Query Domain Manager
 * 
 * Manages all query domains.
 * 
 * @version	{empty}
 * @created	July 3, 2013, 14:51 (EEST)
 * @revised	January 2, 2014, 10:56 (EET)
 */
class dvbDomain
{
	/**
	 * The inner export folder.
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
		$mapPath = dvbLib::updateMapFilepath();
		
		// Check if domain already exists
		$parser = new DOMParser();
		$parser->load($mapPath, FALSE);
		if ($parent != "")
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
		$mapPath = dvbLib::updateMapFilepath();
		
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
		$mapPath = dvbLib::updateMapFilepath();
		
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
	 * 		Array of queries by key.
	 */
	public static function getQueries($domain)
	{
		$mapPath = dvbLib::getMapFilepath();
		
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
}
//#section_end#
?>