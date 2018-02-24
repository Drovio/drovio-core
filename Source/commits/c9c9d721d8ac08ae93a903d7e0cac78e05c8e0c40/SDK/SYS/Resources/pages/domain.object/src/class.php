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
 * @copyright	Copyright (C) 2015 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Resources", "paths");
importer::import("DEV", "Tools", "parsers/phpParser");

use \SYS\Comm\db\dbConnection;
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
 * @version	0.1-4
 * @created	July 8, 2014, 19:52 (EEST)
 * @revised	January 2, 2015, 10:22 (EET)
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
		$dbq = new dbQuery("69221698", "units.domains");
		$dbc = new dbConnection();
		
		$attr = array();
		$attr['name'] = $name;
		$attr['path'] = "/".$domain_path;
		$success = $dbc->execute($dbq, $attr);
		
		// If an error occured from the transaction (domain already exists), return FALSE
		if (!$success)
			return FALSE;
			
		// Create Folder Record
		$dbq = new dbQuery("1020682090", "units.domains.folders");
		
		$attr = array();
		$attr['name'] = $domain_path;
		$attr['domain'] = $name;
		$attr['parent_id'] = "NULL";
		$dbc->execute($dbq, $attr);
		
		// Create domain folder
		folderManager::create(systemRoot."/".$domain_path."/");
		
		// Create domain config file
		//_____ Get Resource
		$domain_config_path = systemRoot.paths::getDevRsrcPath()."/platform/domain.inc";
		$domain_config_content = fileManager::get($domain_config_path);
		$domain_config_clear = phpParser::unwrap($domain_config_content);
		
		//_____ Prepent Page ID
		$page_prepend = "";
		$page_prepend .= '// Domain Variables'."\n";
		$page_prepend .= '$__pageDomain = \''.$name.'\';'."\n";
		$page_prepend .= '$__domainPath = \''."/".$domain_path.'\';'."\n\n";
		
		//_____ Make PHP file
		$final_config = $page_prepend.$domain_config_clear;
		$final_config = phpParser::wrap($final_config);
		
		// Create domain config file
		return fileManager::create(systemRoot."/".$domain_path."/_domainConfig.php", $final_config, TRUE);
	}
	
	/**
	 * Delete a subdomain (it must be empty of folders and pages)
	 * 
	 * @param	string	$name
	 * 		The subdomain name to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 * 
	 * @deprecated	Use remove() instead.
	 */
	public static function delete($name)
	{
		self::remove($name);
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
		$dbq = new dbQuery("1284840680", "units.domains");
		
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
		$dbq = new dbQuery("758802961", "units.domains");
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
}
//#section_end#
?>