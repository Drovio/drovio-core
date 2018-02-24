<?php
//#section#[header]
// Namespace
namespace API\Resources\pages;

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
 * @package	Resources
 * @namespace	\pages
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Comm\database\connections\interDbConnection;
use \API\Developer\resources\paths;
use \API\Developer\content\document\parsers\phpParser;
use \API\Model\units\sql\dbQuery;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\fileManager;

/**
 * Sub-Domain Manager
 * 
 * Manages all the subdomains of the site.
 * 
 * @version	{empty}
 * @created	March 24, 2014, 11:07 (EET)
 * @revised	March 24, 2014, 11:07 (EET)
 */
class domain
{
	/**
	 * Creates a new subdomain.
	 * 
	 * @param	string	$name
	 * 		The name of the subdomain
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
		$dbc = new interDbConnection();
		
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
		$domain_config_path = systemRoot.paths::getDevRsrcPath()."/headers/domain.inc";
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
	 * 		The subdomain name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function delete($name)
	{
		// Get domain path
		$info = parent::info($name);
		$domain_path = $info["path"];
		
		// Delete domain from Database
		$attr = array();
		$attr['name'] = $name;
		$dbq = new dbQuery("1284840680", "units.domains");
		
		$dbc = new interDbConnection();
		$success = $dbc->execute($dbq, $attr);
		
		// If an error occured from the transaction, return FALSE
		if (!$success)
			return FALSE;
			
		// Delete config file and then subdomain folder
		fileManager::remove(systemRoot.$domain_path."/_domainConfig.php");
		return folderManager::remove(systemRoot.$domain_path."/");
	}
	
	/**
	 * Get domain's info.
	 * 
	 * @param	string	$name
	 * 		The domain name.
	 * 
	 * @return	array
	 * 		An array of all domain information.
	 */
	public static function info($name)
	{
		// Get domain info from Database
		$dbq = new dbQuery("758802961", "units.domains");
		$dbc = new interDbConnection();
		
		$attr = array();
		$attr['name'] = $name;
		$result = $dbc->execute($dbq, $attr);
		
		// Fetch Result
		return $dbc->fetch($result);
	}
}
//#section_end#
?>