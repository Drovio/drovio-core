<?php
//#section#[header]
// Namespace
namespace DEV\Websites;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Websites
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Websites", "website");

use \API\Resources\filesystem\fileManager;
use \API\Resources\DOMParser;
use \DEV\Websites\website;

/**
 * Website Server
 * 
 * A class to manage website's server.xml file. That file keeps all the required information for the various the user has configure to use with the website project
 * 
 * @version	4.0-3
 * @created	October 3, 2014, 21:09 (EEST)
 * @revised	November 13, 2014, 15:33 (EET)
 */
class wsServer
{
	/**
	 * The FTP connection type identifier
	 * 
	 * @type	string
	 */
	const CON_TYPE_FTP = "ftp";
	
	/**
	 * The SFTP connection type identifier
	 * 
	 * @type	string
	 */
	const CON_TYPE_SFTP = "sftp";
	
	/**
	 * The server id.
	 * 
	 * @type	string
	 */
	private $serverID;

	/**
	 * The website object.
	 * 
	 * @type	website
	 */
	private $website;
	
	/**
	 * The website vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * The parser used to parse the file.
	 * 
	 * @type	DOMParser
	 */
	private $xmlParser;
	
	/**
	 * The servers filepath.
	 * 
	 * @type	string
	 */
	private $serversFile;
	
	/**
	 * Initialize the server manager class.
	 * 
	 * @param	string	$websiteID
	 * 		The website id.
	 * 
	 * @param	string	$serverID
	 * 		The server id to manage.
	 * 		Leave empty for new servers.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($websiteID, $serverID = "")
	{
		// Init vcs and variables
		$this->serverID = $serverID;
		$this->website = new website($websiteID);
		
		// In case of item already exist, get path from trunk
		$rootFolder = $this->website->getRootFolder();
		$this->serversFile = systemRoot.$rootFolder."/servers.xml";
		
		// Initialize parser and load file
		$this->xmlParser = new DOMParser();
		try
		{
			$this->xmlParser->load($this->serversFile, FALSE);
		}
		catch (Exception $ex)
		{
			$this->createServersFile();
		}
	}
	
	/**
	 * Create the servers index file.
	 * 
	 * @return	boolean
	 * 		True on success or if the file already exists, false on failure.
	 */
	private function createServersFile()
	{
		// Check if the file already exists
		$serversFile = $this->serversFile;
		if (file_exists($serversFile))
			return TRUE;
		
		// Create settings file
		fileManager::create($serversFile, "", TRUE);
		
		// Init settings File
		$parser = $this->xmlParser;
		$root = $parser->create("servers");
		$parser->append($root);
		
		// Save file
		return $parser->save($serversFile, "", TRUE);
	}
	
	/**
	 * Create a new server
	 * 
	 * @param	string	$name
	 * 		The server name.
	 * 
	 * @param	string	$address
	 * 		The server address.
	 * 
	 * @param	string	$connectionType
	 * 		The server connection type (ftp or sftp).
	 * 		For more information see the class' constants.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($name, $address, $connectionType = self::CON_TYPE_FTP)
	{
		$parser = $this->xmlParser;
		$root = $parser->evaluate("//servers")->item(0);
		
		// Check if server with the same name already exists
		$server = $parser->evaluate("//server[@name='".$name."']")->item(0);
		if (!empty($server))
			return FALSE;
		
		// Create dynamic server id
		$this->serverID = "srv".md5(time()."_".mt_rand());
		
		// Create new server
		$server = $parser->create("server", "", $this->serverID);
		$parser->attr($server, "name", $name);
		$parser->append($root, $server);
		
		// Add properties
		$this->setProperty($server, "connection", $connectionType);
		$this->setProperty($server, "address", $address);
		
		// Update file
		return $parser->update();
	}
	
	/**
	 * Update the basic server info, such us description, type and connection type.
	 * 
	 * @param	string	$description
	 * 		The server description.
	 * 
	 * @param	string	$serverType
	 * 		The server type.
	 * 		It can be anything, usually the presentation layer defines some specific types.
	 * 
	 * @param	stra	$connectionType
	 * 		The server connection type (ftp or sftp).
	 * 		For more information see the class' constants.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure
	 */
	public function updateInfo($description, $serverType, $connectionType = self::CON_TYPE_FTP)
	{
		$parser = $this->xmlParser;
		
		// Get server to update
		$server = $parser->evaluate("//server[@id='".$this->serverID."']")->item(0);
		if (is_null($server))
			return FALSE;
		
		// Update info properties
		$this->setProperty($server, "connection", $connectionType);
		$this->setProperty($server, "description", $description);
		$this->setProperty($server, "type", $serverType);
		
		// Update file
		return $parser->update();
	}
	
	/**
	 * Update the server connection credentials.
	 * 
	 * @param	string	$username
	 * 		The server username.
	 * 
	 * @param	string	$password
	 * 		The server password.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateCredentials($username, $password)
	{
		$parser = $this->xmlParser;
		
		// Get server to update
		$server = $parser->evaluate("//server[@id='".$this->serverID."']")->item(0);
		if (is_null($server))
			return FALSE;
			
		// Update credentials
		$this->setProperty($server, "username", $username);
		$this->setProperty($server, "password", $password);
		
		return $parser->update();
	}
	
	/**
	 * Remove the current server from the server list.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure
	 */
	public function remove()
	{
		// Get server to remove
		$server = $this->xmlParser->evaluate("//server[@id='".$this->serverID."']")->item(0);
		if (empty($server))
			return FALSE;
		
		// Replace server
		$this->xmlParser->replace($server, null);
		
		// Update file
		return $this->xmlParser->update();
	}
	
	/**
	 * Get current server info.
	 * 
	 * @return	array
	 * 		An array of server information, including:
	 * 		- name,
	 * 		- description,
	 * 		- type,
	 * 		- connection,
	 * 		- address,
	 * 		- username,
	 * 		- password.
	 */
	public function info()
	{
		return $this->getServerInfo($this->serverID);
	}
	
	/**
	 * Get all website servers.
	 * 
	 * @return	array
	 * 		An array of all server information.
	 * 		Each server has the information defined in the info() function.
	 */
	public function getServerList()
	{
		$serverList = array();
		$servers = $this->xmlParser->evaluate("//server");
		foreach($servers as $server)
		{
			// Get server id
			$serverID = $this->xmlParser->attr($server, "id");
			
			// Get server info
			$serverList[$serverID] = $this->getServerInfo($serverID);
		}
		
		// Return list
		return $serverList;
	}
	
	/**
	 * Get server information for a given server id.
	 * 
	 * @param	string	$serverID
	 * 		The server id to get the info for.
	 * 
	 * @return	array
	 * 		An array of server information, including:
	 * 		- name,
	 * 		- description,
	 * 		- type,
	 * 		- connection,
	 * 		- address,
	 * 		- username,
	 * 		- password.
	 */
	private function getServerInfo($serverID)
	{
		$parser = $this->xmlParser;
		
		// Get server to update
		$server = $parser->evaluate("//server[@id='".$serverID."']")->item(0);
		if (is_null($server))
			return FALSE;
		
		// Get server info
		$serverInfo = array();
		$serverInfo['name'] = $parser->attr($server, "name");
		$serverInfo['description'] = $this->getProperty($server, "description");
		$serverInfo['type'] = $this->getProperty($server, "type");
		$serverInfo['connection'] = $this->getProperty($server, "connection");
		$serverInfo['address'] = $this->getProperty($server, "address");
		$serverInfo['username'] = $this->getProperty($server, "username");
		$serverInfo['password'] = $this->getProperty($server, "password");
		
		// Return info
		return $serverInfo;
	}
	
	/**
	 * Get the supported connection types.
	 * 
	 * @return	array
	 * 		An array of all connection types supporter.
	 * 		Currently they are only ftp and sftp.
	 */
	public static function getConnectionTypes()
	{
		$types = array();
		$types[] = self::CON_TYPE_FTP;
		$types[] = self::CON_TYPE_SFTP;
		
		return $types;
	}
	
	/**
	 * Get a server property, except from name.
	 * 
	 * @param	DOMElement	$object
	 * 		The object to get the property from.
	 * 
	 * @param	string	$name
	 * 		The property name.
	 * 
	 * @return	mixed
	 * 		The property value or NULL if the property doesn't exist.
	 */
	private function getProperty($object, $name)
	{
		$parser = $this->xmlParser;
		
		// Get property element
		$property = $parser->evaluate($name, $object)->item(0);
		
		// If not empty get value
		if (!empty($property))
			return $parser->nodeValue($property);
		
		return NULL;
	}
	
	/**
	 * Set a property for the given object.
	 * 
	 * @param	DOMNode	$object
	 * 		The object to set the property for.
	 * 
	 * @param	string	$name
	 * 		The property name.
	 * 
	 * @param	string	$value
	 * 		The property value.
	 * 
	 * @return	void
	 */
	private function setProperty($object, $name, $value)
	{
		$parser = $this->xmlParser;
		
		// Get property element
		$property = $parser->evaluate($name, $object)->item(0);
		
		// If empty, create property
		if (empty($property))
		{
			$property = $parser->create($name);
			$parser->append($object, $property);
		}
		
		// Set property value
		$parser->nodeValue($property, $value);
	}
}
//#section_end#
?>