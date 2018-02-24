<?php
//#section#[header]
// Namespace
namespace API\Developer\components\sql;

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
 * @revised	December 11, 2013, 11:07 (EET)
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
	 * The inner repository folder.
	 * 
	 * @type	string
	 */
	const INNER_PATH = "/Library/SQL/";
	
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
		$mapPath = paths::getDevRsrcPath().dvbLib::MAP_PATH;
		
		// Check if domain already exists
		$parser = new DOMParser();
		$parser->load($mapPath, TRUE);
		if ($parent != "")
			$xpath = "/domains/domain[@name='".str_replace(".", "']/*[@name='", $parent)."']/*[@name='".$name."']";
		else
			$xpath = "/domains/domain[@name='".$name."']";
			
		// If domain already exists, return FALSE
		$domainElement = $parser->evaluate($xpath)->item(0);
		if (!is_null($domainElement))
			return FALSE;
		
		// Get Parent
		if (empty($parent))
			$xpath = "/domains";
		else
			$xpath = "/domains/domain[@name='".str_replace(".", "']/*[@name='", $parent)."']";
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
		// Load index file
		$parser = new DOMParser();
		$parser->load(paths::getDevRsrcPath()."/Mapping/Library/sql.xml");
		
		// Get domain element
		$xpath = "/domains/domain[@name='".str_replace(".", "']/*[@name='", $domain)."']";
		echo $xpath."\n";
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