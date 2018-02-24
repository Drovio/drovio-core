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

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("SYS", "Resources", "pages/pageFolder");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Resources", "paths");
importer::import("DEV", "Tools", "parsers/phpParser");

use \SYS\Comm\db\dbConnection;
use \SYS\Resources\pages\pageFolder;
use \API\Model\sql\dbQuery;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\fileManager;
use \DEV\Resources\paths;
use \DEV\Tools\parsers\phpParser;

/**
 * Sub-Domain Manager
 * 
 * Manages all Redback's subdomains.
 * 
 * @version	2.0-2
 * @created	July 8, 2014, 19:52 (EEST)
 * @updated	February 13, 2015, 12:31 (EET)
 */
class domain
{
	/**
	 * Creates a new subdomain.
	 * 
	 * @param	string	$name
	 * 		The name of the subdomain.
	 * 
	 * @param	string	$path
	 * 		The subdomain inner system path.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function create($name, $path = "")
	{
		// Set domain path
		$domain_path = "_sbd_".($path = "" ? strtolower($name) : strtolower($path));
		
		// Create Database Record
		$dbq = new dbQuery("28309967134877", "pages.domains");
		$dbc = new dbConnection();
		
		$attr = array();
		$attr['name'] = $name;
		$attr['path'] = "/".$domain_path;
		$success = $dbc->execute($dbq, $attr);
		
		// If domain already exists, return FALSE
		if (!$success)
			return FALSE;
			
		// Create Domain Folder
		pageFolder::create($domain_path, NULL, $name);
		
		// Get domain info
		$domainInfo = self::info($name);
		$domainFolderID = $domainInfo['id'];
		
		// Create domain config file
		$domainConfigFileContent = fileManager::get(systemRoot.paths::getModulesRsrcPath()."/templates/domain.inc");
		
		// Set domain variables
		$domainConfigFileContent = str_replace("{domain_name}", $name, $domainConfigFileContent);
		$domainConfigFileContent = str_replace("{domain_path}", "/".$domain_path, $domainConfigFileContent);
		
		// Create domain config page file
		return page::create($domainFolderID, "_domainConfig.php", $pageContent = $domainConfigFileContent);
	}
	
	/**
	 * Remove a subdomain (it must be empty of folders and pages)
	 * 
	 * @param	string	$name
	 * 		The subdomain name to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($name)
	{
		// Get domain path
		$info = parent::info($name);
		$domain_path = $info["path"];
		
		// Delete domain from Database
		$attr = array();
		$attr['name'] = $name;
		$dbq = new dbQuery("2015044238119", "pages.domains");
		
		$dbc = new dbConnection();
		$success = $dbc->execute($dbq, $attr);
		
		// If an error occured from the transaction, return FALSE
		if (!$success)
			return FALSE;
			
		// Delete config file and then subdomain folder
		fileManager::remove(systemRoot.$domain_path."/_domainConfig.php");
		return folderManager::remove(systemRoot.$domain_path."/");
	}
	
	/**
	 * Get a subdomain's info.
	 * 
	 * @param	string	$name
	 * 		The domain name.
	 * 
	 * @return	array
	 * 		An array of all domain information or NULL if the subdomain doesn't exist.
	 */
	public static function info($name)
	{
		// Get domain info from Database
		$dbq = new dbQuery("21215746512401", "pages.domains");
		$dbc = new dbConnection();
		
		$attr = array();
		$attr['name'] = $name;
		$result = $dbc->execute($dbq, $attr);
		
		// Fetch Result
		if ($result)
			return $dbc->fetch($result);
		
		// Domain does not exist, return NULL
		return NULL;
	}
	
	/**
	 * Get all platform subdomains.
	 * 
	 * @return	array
	 * 		An array of all subdomains.
	 */
	public static function getAllDomains()
	{
		// Get all domains from database
		$dbc = new dbConnection();
		$dbq = new dbQuery("23416866984944", "pages.domains");
		$result = $dbc->execute($dbq);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>