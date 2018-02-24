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
importer::import("API", "Developer", "versionControl::vcsManager");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");

use \API\Developer\components\sql\dvbLib;
use \API\Developer\versionControl\vcsManager;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;

/**
 * Developer's Query Domain Manager
 * 
 * Manages all query domains.
 * 
 * @version	{empty}
 * @created	July 3, 2013, 14:51 (EEST)
 * @revised	July 3, 2013, 14:51 (EEST)
 */
class dvbDomain extends vcsManager
{
	/**
	 * The domain name.
	 * 
	 * @type	string
	 */
	private $domainName;
	
	/**
	 * The inner export folder.
	 * 
	 * @type	string
	 */
	const EXPORT_PATH = "/System/Library/SQL/";
	
	/**
	 * The root repository folder.
	 * 
	 * @type	string
	 */
	const REPOSITORY_PATH = "/Repositories/";
	
	/**
	 * The inner repository folder.
	 * 
	 * @type	string
	 */
	const INNER_PATH = "/Library/SQL/";
	
	/**
	 * Initializes the domain.
	 * 
	 * @param	string	$domain
	 * 		The domain name (optional).
	 * 
	 * @return	void
	 */
	public function __construct($domain = "")
	{
		$this->domainName = $domain;
	}
	
	/**
	 * Creates a domain in the sql library.
	 * 
	 * @param	string	$name
	 * 		The domain name.
	 * 
	 * @param	string	$parent
	 * 		The domain parent (separated by ".").
	 * 
	 * @return	boolean
	 * 		Creation status.
	 */
	public function create($name, $parent = "")
	{
		$mapPath = paths::getDevRsrcPath().dvbLib::MAP_PATH;
		
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
		$this->VCS_initialize(paths::getDevPath().self::REPOSITORY_PATH, self::INNER_PATH.$nsdomain, $this->name, "qDomain");
		
		// Create vcs repository structure
		$this->VCS_createStructure();
		
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
		$parser->save(systemRoot.paths::getDevRsrcPath()."/Mapping/Library/", "sql.xml", TRUE);
		
		// Create Production Folder and index file
		folderManager::create(systemRoot.self::EXPORT_PATH."/", $nsdomain);
		$parser = new DOMParser();
		$base = $parser->create('queries');
		$parser->append($base);
		$parser->save(systemRoot.self::EXPORT_PATH."/".$nsdomain."/", "index.xml", TRUE);
		
		return TRUE;
	}
	
	/**
	 * Deletes a domain from the sql library.
	 * 
	 * @return	boolean
	 * 		The deletion status.
	 */
	public function delete()
	{
		// Normalize domain path
		$nsdomain = str_replace(".", "/", $this->domainName);
		$mapPath = paths::getDevRsrcPath().dvbLib::MAP_PATH;
			
		if (!is_dir(systemRoot.paths::getDevPath().self::REPOSITORY_PATH.self::INNER_PATH."/".$nsdomain))
			throw new Exception("Domain '$this->domainName' not found.");
		
		// Remove Development Folder (must be empty of queries)
		$contents = folderManager::get_contentList(systemRoot.paths::getDevPath().self::REPOSITORY_PATH.self::INNER_PATH."/".$nsdomain);
		
		// Check if there is only the index file (1 file and 0 directories)
		if (count($contents['dirs']) == 0 && count($contents['files']) == 1)
		{
			fileManager::remove(systemRoot.self::EXPORT_PATH."/".$nsdomain."/index.xml");
			folderManager::remove(systemRoot.self::EXPORT_PATH, $nsdomain);
		}
		else
			return FALSE;
		
		// Remove Repository Folder
		$this->VCS_removeRepository($nsdomain);
			
		// Remove Mapping Node
		$parser = new DOMParser();
		$parser->load($mapPath, true);
		$xpath = "//*[@name='".str_replace(".", "']/*[@name='", $this->domainName)."']";
		$domainElement = $parser->evaluate($xpath)->item(0);
		$parser->replace($domainElement, NULL);
		$parser->save(systemRoot.paths::getDevRsrcPath()."/Mapping/Library/", "sql.xml", TRUE);
		
		return TRUE;
	}
	
	/**
	 * Gets the sql queries of the domain.
	 * 
	 * @return	array
	 * 		Array of queries by key.
	 */
	public function getQueries()
	{
		// Get domain path
		$this->name = $this->domainName;
		$nsdomain = str_replace(".", "/", $this->domainName);

		// Initialize VCS
		$this->VCS_initialize(paths::getDevPath().self::REPOSITORY_PATH, self::INNER_PATH.$nsdomain, $this->name, "qDomain");
		
		// Get trunk's query items
		try
		{	
			$parser = new DOMParser();
			$items = $this->vcsTrunk->getAllItems($parser, $this->getWorkingBranch());
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
}
//#section_end#
?>