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

importer::import("API", "Developer", "components::sql::dvbDomain");
importer::import("API", "Developer", "components::sql::dvbQuery");
importer::import("API", "Resources", "DOMParser");

use \API\Developer\components\sql\dvbDomain;
use \API\Developer\components\sql\dvbQuery;
use \API\Resources\DOMParser;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;

/**
 * Developer's Database Library Manager
 * 
 * Manages global functions for database domains.
 * 
 * @version	{empty}
 * @created	July 3, 2013, 13:27 (EEST)
 * @revised	August 8, 2013, 18:56 (EEST)
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
		$path = paths::getDevRsrcPath().self::MAP_PATH;
		
		$parser = new DOMParser();
		$parser->load($path, true);
		$base = $parser->evaluate("domains")->item(0);
		
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
	 * Export entire query library.
	 * 
	 * @return	void
	 */
	public static function export()
	{
		$domainList = self::getDomainList(TRUE);
		foreach ($domainList as $domain)
		{
			$dvbDom = new dvbDomain($domain);
			$queryList = $dvbDom->getQueries();
			foreach ($queryList as $query => $title)
			{
				// Normalize query id
				$query = str_replace("q.", "", $query);
				
				// Export query
				$dvbq = new dvbQuery($domain, $query);
				$dvbq->export();
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