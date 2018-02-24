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

importer::import("DEV", "Websites", "website");
importer::import("DEV", "Version", "vcs");
importer::import("API", "Resources", "DOMParser");

importer::import("API", "Resources", "filesystem::fileManager");
use \API\Resources\DOMParser;
use \DEV\Websites\website;
use \DEV\Version\vcs;

use \API\Resources\filesystem\fileManager;

/**
 * Website Server
 * 
 * A class to manage website's server.xml file. That file keeps all the required information for the various the user has configure to use with the website project
 * 
 * @version	3.0-1
 * @created	October 3, 2014, 21:09 (EEST)
 * @revised	October 10, 2014, 21:35 (EEST)
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
	 * The parser containing the settings file
	 * 
	 * @type	DOMParser
	 */
	private $xmlParser;
	/**
	 * The settings filepath
	 * 
	 * @type	string
	 */
	private $settingsFile;
	
	/**
	 * Whether the settings file contains the systemRoot.
	 * 
	 * @type	boolean
	 */
	private $rootRelative;
	
	/**
	 * The class contructor method
	 * 
	 * @param	integer	$id
	 * 		The project id
	 * 
	 * @return	void
	 */
	public function __construct($id)
	{
		// Init vcs and variables
		$this->website = new website($id);
		$this->vcs = new vcs($id);
		
		// Create item (if doesn't exist)
		$itemID = $this->getItemID();
		$itemPath = "/";
		$itemName = "servers";
		$filePath = $this->vcs->createItem($itemID, $itemPath, $itemName.".xml", $isFolder = FALSE);
		
		// In case of item already exist, get path from trunk
		if (empty($filePath))
			$filePath = $this->vcs->getItemTrunkPath($itemID);
		
		// Construct parent
		$settingsFolder = dirname($filePath);
		
		// Initialize dom parser
		$this->xmlParser = new DOMParser();
		
		// Set class variables
		$this->settingsFile = $settingsFolder."/".$itemName.".xml";
		$this->rootRelative = FALSE;
		
		// If file exists, load settings
		$fileName = ($this->rootRelative ? systemRoot.$this->settingsFile : $this->settingsFile);
		if (file_exists($fileName))
			$this->load();
	}
	
	/**
	 * Creates the settings file.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if settings file already exists.
	 */
	public function create()
	{
		// Set filename
		$fileName = ($this->rootRelative ? systemRoot.$this->settingsFile : $this->settingsFile);
		
		// Check if the file already exists
		if (file_exists($fileName))
			return FALSE;
		
		// Create settings file
		fileManager::create($fileName, "", TRUE);
		
		// Init settings File
		$this->xmlParser = new DOMParser();
		$root = $this->xmlParser->create("servers");
		$this->xmlParser->append($root);
		
		// Save file
		return $this->xmlParser->save($fileName, "", TRUE);
	}
	
	/**
	 * Loads the settings file into the settings object (memory)
	 * 
	 * @return	void
	 */
	private function load()
	{
		$this->xmlParser->load($this->settingsFile, $this->rootRelative);
	}
	
	/**
	 * Writes / Saves the loaded xml settings file to disk
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function update()
	{
		return $this->xmlParser->update();
	}
	
	/**
	 * Set the value of server option into the file
	 * 
	 * @param	string	$id
	 * 		The server's id. Leave it empty to create an new server object
	 * 
	 * @param	string	$name
	 * 		The server's name
	 * 
	 * @param	string	$address
	 * 		The server's address
	 * 
	 * @return	boolean
	 * 		True on success, false on failure
	 */
	public function setServer($id = '', $name, $address = '')
	{
		$srv = null; 
		
		if(empty($name))
			return FALSE; // Server needs a name
		
		$add = FALSE;
		if(empty($id))
		{
			// Get Server item
			$srv = $this->xmlParser->evaluate("//server[@id='".$id."']")->item(0);
			if(!is_null($srv))
				return FALSE; // Server entry already exist
			
			$root = $this->xmlParser->evaluate("//servers")->item(0);
			
			// create $id
			$id = time();
			$add = TRUE;
			
			// Create bean
			$srv = $this->xmlParser->create("server"); 
				$this->xmlParser->attr($srv, 'id', $id);
			$this->xmlParser->append($root, $srv);			
		}
		else
		{
			// Alter existing
			$srv = $this->xmlParser->evaluate("//server[@id='".$id."']")->item(0);
			if(is_null($srv))
				return FALSE; // Server does not exist
		}
		
		// Fill bean
		$this->setProperty($srv, "name", $name);	
		$this->setProperty($srv, "address", $address);
		
		if($this->update())
		{
			if($add)
				return $id;
			else
				return TRUE;
		}		
		return FALSE;
	}
	
	/**
	 * Sets the 'extra properties of a server object'
	 * 
	 * @param	string	$id
	 * 		The server object id
	 * 
	 * @param	string	$description
	 * 		The description property to be set
	 * 
	 * @param	string	$type
	 * 		The type property to be set
	 * 
	 * @param	string	$conType
	 * 		The connection type property to be set
	 * 
	 * @return	boolean
	 * 		True on success, false on failure
	 */
	public function setServerExtra($id, $description = '', $type = '', $conType = self::CON_TYPE_FTP)
	{
		if(empty($id))
			return FALSE;
		
		// Alter existing
		$srv = $this->xmlParser->evaluate("//server[@id='".$id."']")->item(0);
		if(is_null($srv))
			return FALSE; // Server does not exist
			
		// Fill bean
		$this->setProperty($srv, "description", $description);	
		$this->setProperty($srv, "type", $type);
		
		// TODO
		// If connection type changes we need to
		// empty the connection element to avoid inconsistencies
		$prop = $this->xmlParser->create("connection");			 
			$this->xmlParser->attr($prop, 'type', $conType);
		$this->xmlParser->append($srv, $prop);
		
		return $this->update();
	}
	
	
	
	
	/**
	 * Get the Server's object properties
	 * 
	 * @param	string	$id
	 * 		The id of the server object
	 * 
	 * @return	array
	 * 		Array{name, address} of server object properties
	 */
	public function getServer($id)
	{
		if(empty($id))
			return FALSE;
		
		// Alter existing
		$srv = $this->xmlParser->evaluate("//server[@id='".$id."']")->item(0);
		if(is_null($srv))
			return FALSE; // Server does not exist
		
		$result = array();
		$result['name'] = $this->xmlParser->evaluate("name", $srv)->item(0)->nodeValue;
		$result['address'] = $this->xmlParser->evaluate("address", $srv)->item(0)->nodeValue;
		$result['description'] = $this->xmlParser->evaluate("description", $srv)->item(0)->nodeValue;
		$result['type'] = $this->xmlParser->evaluate("type", $srv)->item(0)->nodeValue;
		
		$connection = $this->xmlParser->evaluate("connection", $srv)->item(0);
		$result['conType'] = $this->xmlParser->attr($connection, "type");
		
		return $result;
	}
	
	/**
	 * Deletes a server configuration
	 * 
	 * @param	string	$id
	 * 		The server object id
	 * 
	 * @return	boolean
	 * 		True on success, false on failure
	 */
	public function deleteServer($id)
	{
		if(empty($id))
			return FALSE;
		
		// Alter existing
		$srv = $this->xmlParser->evaluate("//server[@id='".$id."']")->item(0);
		if(is_null($srv))
			return FALSE; // Server does not exist
		
		$this->xmlParser->replace($srv, null);
		
		return $this->update();
	}
	
	/**
	 * A list of all registered servers
	 * 
	 * @return	array
	 * 		array of all servers names
	 */
	public function getServerList()
	{
		$result = array();
		$servers = $this->xmlParser->evaluate("//server");
		foreach($servers as $server)
		{
			$id = $this->xmlParser->attr($server, "id");

			$props = array();
			$props['name'] = $this->xmlParser->evaluate("name", $server)->item(0)->nodeValue;
			$props['address'] = $this->xmlParser->evaluate("address", $server)->item(0)->nodeValue;
			$props['description'] = $this->xmlParser->evaluate("description", $server)->item(0)->nodeValue;
			$props['type'] = $this->xmlParser->evaluate("type", $server)->item(0)->nodeValue;
			
			$connection = $this->xmlParser->evaluate("connection", $server)->item(0);
			$props['conType'] = $this->xmlParser->attr($connection, "type");
			
			$result[$id] = $props;
		}
		return $result;
	}
	
	/**
	 * Sets the FTP connection type properties into the server object
	 * 
	 * @param	string	$id
	 * 		The server object id
	 * 
	 * @param	string	$user
	 * 		The FTP username
	 * 
	 * @param	string	$pass
	 * 		The FTP password
	 * 
	 * @return	boolean
	 * 		True on success, false on failure
	 */
	public function setFTPconfig($id, $user, $pass = '')
	{
		if(empty($id))
			return FALSE;
		
		// Alter existing
		$srv = $this->xmlParser->evaluate("//server[@id='".$id."']")->item(0);
		if(is_null($srv))
			return FALSE; // Server does not exist
			
		$connection = $this->xmlParser->evaluate("connection", $srv)->item(0);
		$conType = $this->xmlParser->attr($connection, "type");
		
		if($conType != self::CON_TYPE_FTP)
			return FALSE;
			
		// Fill bean
		$this->setProperty($connection, "user", $user);	
		$this->setProperty($connection, "pass", $pass);
		
		return $this->update();
	}
	
	/**
	 * Gets the FTP connection type properties
	 * 
	 * @param	string	$id
	 * 		The server object id
	 * 
	 * @return	array
	 * 		Array of FTP connection type properties {username, password}
	 */
	public function getFTPconfig($id)
	{
		if(empty($id))
			return FALSE;
		
		// Alter existing
		$srv = $this->xmlParser->evaluate("//server[@id='".$id."']")->item(0);
		if(is_null($srv))
			return FALSE; // Server does not exist
			
		$connection = $this->xmlParser->evaluate("connection", $srv)->item(0);
		$conType = $this->xmlParser->attr($connection, "type");
		
		if($conType != self::CON_TYPE_FTP)
			return FALSE;
			
		$result = array();
		$result['user'] = $this->xmlParser->evaluate("user", $connection)->item(0)->nodeValue;
		$result['pass'] = $this->xmlParser->evaluate("pass", $connection)->item(0)->nodeValue;
		
		return $result;
	}
	
	/**
	 * Get the supported connection types
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function getConnectionTypes()
	{
		$types = array();
		$types[] = self::CON_TYPE_FTP;
		
		return $types;
	}
	
	/**
	 * Alters or creates a property of the given object
	 * 
	 * @param	DOMNode	$object
	 * 		The object in which the property will be added or altered
	 * 
	 * @param	string	$name
	 * 		The property name
	 * 
	 * @param	string	$value
	 * 		The property value
	 * 
	 * @return	void
	 */
	private function setProperty($object, $name, $value)
	{
		$prop = $this->xmlParser->evaluate("/".$name, $object)->item(0);
		
		if(is_null($prop))
		{
			$prop = $this->xmlParser->create($name);
			$this->xmlParser->append($object, $prop);
		}
		$prop->nodeValue = $value;		
	}

	/**
	 * Gets the file's item id for the vcs.
	 * 
	 * @return	string
	 * 		The vcs hashed id
	 */
	private function getItemID()
	{
		return $this->website->getItemID("servers");
	}
}
//#section_end#
?>