<?php
//#section#[header]
// Namespace
namespace API\Developer\model\units\sql;

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
 * @namespace	\model\units\sql
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "DOM::DOMParser");
importer::import("API", "Content", "filesystem::fileManager");
importer::import("API", "Content", "filesystem::folderManager");
importer::import("API", "Profile", "user");
importer::import("API", "Security", "privileges");
importer::import("API", "Developer", "model::version::vcs");
importer::import("API", "Developer", "model::units::sql::dvbQuery");
importer::import("API", "Developer", "content::resources");

use \API\Platform\DOM\DOMParser;
use \API\Content\filesystem\fileManager;
use \API\Content\filesystem\folderManager;
use \API\Profile\user;
use \API\Security\privileges;
use \API\Developer\model\version\vcs;
use \API\Developer\model\units\sql\dvbQuery;
use \API\Developer\content\resources;

/**
 * Developer's Database Library Manager
 * 
 * Handles the database library from the developer's view
 * 
 * @version	{empty}
 * @created	July 3, 2013, 16:09 (EEST)
 * @revised	July 3, 2013, 16:09 (EEST)
 * 
 * @deprecated	Use \API\Developer\components\sql\dvbLib instead.
 */
class dvbLib extends vcs
{
	/**
	 * The production path of the library
	 * 
	 * @type	string
	 */
	const PATH = "/System/Library/SQL";
	/**
	 * The repository path of the library
	 * 
	 * @type	string
	 */
	const REPOSITORY_PATH = "/Developer/Repositories/Library/SQL";
	/**
	 * The mapping filepath
	 * 
	 * @type	string
	 */
	const MAP_PATH = "/Mapping/Library/sql.xml";
	
	/**
	 * Create a domain in the sql library
	 * 
	 * @param	string	$name
	 * 		The domain name
	 * 
	 * @param	string	$parent
	 * 		The domain parent (separated by ".")
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function create_domain($name, $parent = "")
	{
		// Check user privileges
		if (!privileges::get_userToGroup("DBADMIN"))
			return FALSE;
		
		$mapPath = resources::PATH.self::MAP_PATH;
		// Check if domain already exists
		$parser = new DOMParser();
		$parser->load($mapPath, true);
		if ($parent != "")
			$xpath = "//*[@name='".str_replace(".", "']/*[@name='", $parent)."']/*[@name='".$name."']";
		else
			$xpath = "//*[@name='".$name."']";

		$exists = $parser->evaluate($xpath);
		// If domain already exists, return FALSE
		if ($exists->length > 0)
			return FALSE;
		
		$this->name = $name;
		$nsdomain = str_replace(".", "/", $parent)."/".$name;

		// Initialize VCS
		$this->VCS_initialize("/Library/SQL/".$nsdomain, $this->name, dvbQuery::FILE_TYPE);
		
		// Create vcs repository structure
		$this->VCS_create_structure();
		
		// Insert to Mapping
		$parser = new DOMParser();
		$parser->load($mapPath, true);
		
		// Get Parent
		$xpath = "//*[@name='".str_replace(".", "']/*[@name='", $parent)."']";
		$parentElement = $parser->evaluate($xpath)->item(0);
		if (is_null($parentElement))
			$parentElement = $parser->evaluate("//domains")->item(0);
		$domainElement = $parser->create('domain');
		$parser->attr($domainElement, "name", $name);
		$parser->append($parentElement, $domainElement);
		$parser->save(systemRoot.resources::PATH."/Mapping/Library/", "sql.xml", TRUE);
		
		// Create Production Folder and index file
		folderManager::create(systemRoot.self::PATH."/", $nsdomain);
		$parser = new DOMParser();
		$base = $parser->create('queries');
		$parser->append($base);
		$parser->save(systemRoot.self::PATH."/".$nsdomain."/", "index.xml", TRUE);
		
		return TRUE;
	}
	
	/**
	 * Delete a domain from the sql library
	 * 
	 * @param	string	$domain
	 * 		The domain full name (separated by ".")
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function delete_domain($domain)
	{
		// Check user privileges
		if (!privileges::get_userToGroup("DBADMIN"))
			return FALSE;
			
		// Normalize domain path
		$nsdomain = str_replace(".", "/", $domain);
		$mapPath = resources::PATH.self::MAP_PATH;
			
		if (!is_dir(systemRoot.self::REPOSITORY_PATH."/".$nsdomain))
			throw new Exception("Domain '$domain' not found.");
		
		// Remove Development Folder (must be empty of queries)
		$contents = folderManager::get_contentList(systemRoot.self::REPOSITORY_PATH."/".$nsdomain);
		
		// Check if there is only the index file (1 file and 0 directories)
		if (count($contents['dirs']) == 0 && count($contents['files']) == 1)
		{
			fileManager::remove(systemRoot.self::PATH."/".$nsdomain."/index.xml");
			folderManager::remove(systemRoot.self::PATH, $nsdomain);
		}
		else
			return FALSE;
		
		// Remove Repository Folder
		$this->VCS_removeRepository($nsdomain);
			
		// Remove Mapping Node
		$parser = new DOMParser();
		$parser->load($mapPath, true);
		$xpath = "//*[@name='".str_replace(".", "']/*[@name='", $domain)."']";
		$domainElement = $parser->evaluate($xpath)->item(0);
		$parser->replace($domainElement, NULL);
		$parser->save(systemRoot.resources::PATH."/Mapping/Library/", "sql.xml", TRUE);
		
		return TRUE;
	}
	
	/**
	 * Get the sql queries of a domain
	 * 
	 * @param	string	$domain
	 * 		The domain's full name (separated by ".")
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function get_queryList($domain)
	{
		// Get domain path
		$this->name = $domain;
		$nsdomain = str_replace(".", "/", $domain);

		// Initialize VCS
		$this->VCS_initialize("/Library/SQL/".$nsdomain, $this->name, dvbQuery::FILE_TYPE);
		
		// Load directory xml
		$parser = new DOMParser();
		try
		{
			$items = $this->vcsTrunk->get_allItems($parser);
		}
		catch (Exception $ex)
		{
			// Directory xml not found, do nothing.
			// There are no queries in this domain.
			// Exit function
			return array();
		}
		
		// Get queries in an array (seeds)
		$queries = array();
		foreach ($items as $itm)
			$queries["q.".$itm->getAttribute("seed")] = $parser->evaluate("title", $itm)->item(0)->nodeValue;
			
		return $queries;
	}
	
	/**
	 * Gets the list of all domain
	 * 
	 * @param	boolean	$full
	 * 		If FALSE, the result will be nested arrays.
	 * 		If TRUE, the result will be an array of full names.
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function get_domainList($full = FALSE)
	{
		// Check if user is db admin
		if (!privileges::get_userToGroup("DBADMIN"))
			return array();

		$path = resources::PATH.self::MAP_PATH;
		
		$dom_parser = new DOMParser();
		$dom_parser->load($path, true);
		$base = $dom_parser->evaluate("domains")->item(0);
		
		$result = array();
		
		if (!$full)
		{
			// Base domains
			$domains = $dom_parser->evaluate("domain", $base);
			foreach ($domains as $dom)
			{
				$name = $dom->getAttribute("name");
				$result[$name] = self::_get_subDomains($dom_parser, $dom);
			}
		}
		else
		{
			// Base domains
			$domains = $dom_parser->evaluate("domain", $base);
			foreach ($domains as $dom)
			{
				$name = $dom->getAttribute("name");
				$result[] = $name;
				$subs = self::_get_subDomains_string($dom_parser, $dom);
				
				if (is_array($subs))
					foreach ($subs as $t)
						$result[] = $name.".".$t;
			}
		}
		
		return $result;
	}
	
	/**
	 * Get the subdomains of a domain as a nested array
	 * 
	 * @param	DOMParser	$dom_parser
	 * 		The parser to parse the mapping file
	 * 
	 * @param	DOMElement	$base
	 * 		The base domain to act as a root for the subdomains
	 * 
	 * @return	array
	 * 		{description}
	 */
	private function _get_subDomains($dom_parser, $base)
	{
		$subs = $dom_parser->evaluate("domain", $base);
		
		if ($subs->length == 0)
			return array();
			
		$result = array();
		foreach ($subs as $sub)
		{
			$name = $sub->getAttribute("name");
			$result[$name] = self::_get_subDomains($dom_parser, $sub);
		}
		return $result;
	}
	
	/**
	 * Get the subdomains of a domain as a string array
	 * 
	 * @param	DOMParser	$dom_parser
	 * 		The parser to parse the mapping file
	 * 
	 * @param	DOMElement	$base
	 * 		The base domain to act as a root for the subdomains
	 * 
	 * @return	array
	 * 		{description}
	 */
	private function _get_subDomains_string($dom_parser, $base)
	{
		$subs = $dom_parser->evaluate("domain", $base);
		
		if ($subs->length == 0)
			return "";
			
		$result = array();
		foreach ($subs as $sub)
		{
			$name = $sub->getAttribute("name");
			$result[] = $name;
			$temp = self::_get_subDomains_string($dom_parser, $sub);
			
			if (is_array($temp))
				foreach ($temp as $t)
					$result[] = $name.".".$t;
		}
		return $result;
	}
	
}
//#section_end#
?>