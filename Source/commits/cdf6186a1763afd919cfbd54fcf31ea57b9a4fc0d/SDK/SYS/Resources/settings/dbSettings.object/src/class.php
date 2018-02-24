<?php
//#section#[header]
// Namespace
namespace SYS\Resources\settings;

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
 * @namespace	\settings
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "settingsManager");

use \API\Resources\DOMParser;
use \API\Resources\settingsManager;

/**
 * Platform database server manager
 * 
 * Manages all database server settings.
 * 
 * @version	0.1-2
 * @created	December 22, 2014, 0:31 (EET)
 * @revised	December 22, 2014, 0:31 (EET)
 */
class dbSettings extends settingsManager
{
	/**
	 * The server name id.
	 * 
	 * @type	string
	 */
	private $serverName;
	
	/**
	 * Initialize settings manager.
	 * 
	 * @param	string	$name
	 * 		The server name.
	 * 		For new servers, set the name here and call create().
	 * 
	 * @return	void
	 */
	public function __construct($name)
	{
		// Get file name
		$this->serverName = $name;
		$fileName = md5("DB_SERVER_".$this->serverName);
		
		// Construct settingsManager
		parent::__construct(systemConfig."/Settings/Databases/", $fileName, TRUE);
	}
	
	/**
	 * Create a new server entry in the list.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create()
	{
		// Create settings file
		$status = parent::create();
		
		// Add server to list
		//if ($status)
			return $this->addServer($this->serverName);
		
		return FALSE;
	}
	
	/**
	 * Update the server details.
	 * 
	 * @param	string	$url
	 * 		The server url.
	 * 
	 * @param	string	$dbName
	 * 		The database name.
	 * 
	 * @param	string	$username
	 * 		The server username.
	 * 
	 * @param	string	$password
	 * 		The server password.
	 * 
	 * @param	string	$dbms
	 * 		The server dbms.
	 * 		For now it supports only MySQL, and this is the default value.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($url, $dbName, $username, $password, $dbms= "MySQL")
	{
		$this->set("SERVER_URL", $url);
		$this->set("DB_NAME", $dbName);
		$this->set("USERNAME", $username);
		$this->set("PASSWORD", $password);
		
		$dbms = (empty($dbms) ? "MySQL" : $dbms);
		$this->set("SERVER_DBMS", $dbms);
	}
	
	/**
	 * Add a new server to the list.
	 * 
	 * @param	string	$name
	 * 		The server name to add.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function addServer($name)
	{
		// Get server list (initialize and create file if not exist)
		$serverList = $this->getServers();
		
		// Add server to list
		$parser = new DOMParser();
		$parser->load(systemConfig."/Settings/Databases/servers.xml");
		$root = $parser->evaluate("/servers")->item(0);
		
		// Check if server already exists
		$server = $parser->evaluate("//server[@name='".$name."']")->item(0);
		if (!empty($server))
			return TRUE;
		
		// Add server
		$server = $parser->create("server");
		$parser->attr($server, "name", $name);
		$parser->append($root, $server);
		
		return $parser->update();
	}
	
	/**
	 * Get all stored servers.
	 * 
	 * @return	array
	 * 		An array of all server names.
	 */
	public static function getServers()
	{
		$parser = new DOMParser();
		try
		{
			$parser->load(systemConfig."/Settings/Databases/servers.xml");
		}
		catch (Exception $ex)
		{
			// Create file
			$root = $parser->create("servers");
			$parser->append($root);
			$parser->save(systemRoot.systemConfig."/Settings/Databases/servers.xml");
		}
		
		// Get list
		$servers = $parser->evaluate("//server");
		$serverList = array();
		foreach ($servers as $server)
			$serverList[] = $parser->attr($server, "name");
		
		// Return list
		return $serverList;
	}
}
//#section_end#
?>