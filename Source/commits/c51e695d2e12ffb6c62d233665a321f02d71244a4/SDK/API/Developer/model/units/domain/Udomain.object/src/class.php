<?php
//#section#[header]
// Namespace
namespace API\Developer\model\units\domain;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	{empty}
 * @package	{empty}
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Content", "filesystem::folderManager");
importer::import("API", "Content", "filesystem::fileManager");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Profile", "user");
importer::import("API", "Model", "units::domain::Sdomain");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Comm", "database::connections::interDbConnection");

use \API\Content\filesystem\folderManager;
use \API\Content\filesystem\fileManager;
use \API\Developer\content\document\parsers\phpParser;
use \API\Profile\user;
use \API\Model\units\domain\Sdomain;
use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;

/**
 * Domain Manager
 * 
 * The developer's part for manipulating the system's subdomains
 * 
 * @version	{empty}
 * @created	{empty}
 * @revised	{empty}
 */
class Udomain extends Sdomain
{
	/**
	 * Creates a new subdomain.
	 * 
	 * @param	string	$name
	 * 		The name of the subdomain
	 * 
	 * @param	string	$theme
	 * 		The theme style
	 * 
	 * @param	string	$path
	 * 		The path where this subdomain will be placed. If empty, it gets the default path created by the name.
	 * 
	 * @return	boolean
	 */
	public static function create($name, $theme = "default", $path = "")
	{
		// Check if user is admin
		//if (!user::user_to_group("SYSADMIN"))
			//return FALSE;
		
		// Set domain path
		$domain_path = "_sbd_".($path = "" ? strtolower($name) : strtolower($path));
		
		// Create Database Record
		$dbq = new dbQuery("69221698", "units.domains");
		$dbc = new interDbConnection();
		
		$attr = array();
		$attr['name'] = $name;
		$attr['path'] = "/".$domain_path;
		$attr['theme'] = $theme;
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
		$domain_config_path = systemRoot.paths::getDevRsrcPath()."/Content/Domains/Headers/loader.inc";
		$domain_config_content = fileManager::get($domain_config_path);
		$domain_config_clear = phpParser::unwrap($domain_config_content);
		
		//_____ Prepent Page ID
		$page_prepend = "";
		$page_prepend .= '//__________ Domain Constants __________//'."\n";
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
	 * 		The name of the subdomain to delete
	 * 
	 * @return	boolean
	 */
	public static function delete($name)
	{
		// Check if user is admin
		//if (!user::user_to_group("SYSADMIN"))
			//return FALSE;	
		
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
			
		// Delete config file
		fileManager::remove(systemRoot.$domain_path."/_domainConfig.php");
		
		// Delete domain folder
		return folderManager::remove(systemRoot.$domain_path."/");
	}
}
//#section_end#
?>