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

use \API\Resources\DOMParser;
use \DEV\Websites\website;
use \DEV\Version\vcs;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	0.1-1
 * @created	September 12, 2014, 22:06 (EEST)
 * @revised	September 12, 2014, 22:06 (EEST)
 */
class wsSettings
{
	/**
	 * The application object.
	 * 
	 * @type	application
	 */
	private $website;
	
	/**
	 * The application vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	protected $xmlParser;
	/**
	 * The settings filepath
	 * 
	 * @type	string
	 */
	protected $settingsFile;
	
	/**
	 * Whether the settings file contains the systemRoot.
	 * 
	 * @type	boolean
	 */
	private $rootRelative;
	
	/**
	 * The contructor method
	 * 
	 * @return	void
	 */
	public function __construct($id)
	{
		// Init vcs and variables
		$this->website = new website($id);
		$this->vcs = new vcs($appID);
		
		// Create item (if doesn't exist)
		$itemID = $this->getItemID();
		$itemPath = "/";
		$itemName = "settings";
		$filePath = $this->vcs->createItem($itemID, $itemPath, $itemName.".xml", $isFolder = FALSE);
		
		// In case of item already exist, get path from trunk
		if (empty($filePath))
			$filePath = $this->vcs->getItemTrunkPath($itemID);
		
		// Construct parent
		$settingsFolder = dirname($filePath);
		//parent::__construct($settingsFolder, $itemName, $rootRelative = FALSE);
		
		// Initialize dom parser
		$this->xmlParser = new DOMParser();
		
		// Set class variables
		$this->settingsFile = $settingsFolder."/".$itemName.".xml";
		$this->rootRelative = FALSE;
		
		// If file exists, load settings
		$fileName = ($rootRelative ? systemRoot.$this->settingsFile : $this->settingsFile);
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
		$root = $this->xmlParser->create("Settings");
		$this->xmlParser->append($root);
		
		// Save file
		return $this->xmlParser->save($fileName, "", TRUE);
	}
	
	/**
	 * Loads the settings file.
	 * 
	 * @return	void
	 */
	public function load()
	{
		$this->xmlParser->load($this->settingsFile, $this->rootRelative);
	}
	
	/**
	 * Updates the settings file.
	 * 
	 * @return	boolean
	 * 		Returns TRUE on success, false on failure.
	 */
	public function update()
	{
		return $this->xmlParser->update();
	}
	
	/**
	 * Set the value of server option into the file
	 * 
	 * @param	string	$name
	 * 		The server's name
	 * 
	 * @param	string	$address
	 * 		The server's address
	 * 
	 * @return	void
	 */
	public function addServer($name, $address)
	{
		$srv = $this->parser->evaluate("//server[@name='".$name."']")->item(0);
		if(!is_null($srv))
			return FALSE; // Server entry already exist
		
		$root = $this->parser->evaluate("//configuration")->item(0);
		$srv = $this->parser->create("server");
		$this->parser->append($root, $srv);
			
		$this->parser->attr($srv, "name", $name);
		$this->parser->attr($srv, "address", $address);	
		
		return update();
	}
	
	public function updateServer($name, $newName, $newAddress)
	{
		$srv = $this->parser->evaluate("//server[@name='".$name."']")->item(0);
		if(is_null($srv))
			return FALSE; // Server entry does not exist
			
		$srvName = $this->parser->evaluate("//server[@name='".$newName."']")->item(0);
		if(!is_null($srvName))
			return FALSE; // Name already exist
		
		if(!empty($newName))
			$this->parser->attr($srv, "name", $newName);
		
		if(!empty($newAddress))
			$this->parser->attr($srv, "address", $newAddress);	
		
		return update();
	}
	
	
	/**
	 * Get the Server's properties
	 * 
	 * @param	string	$name
	 * 		The server's name
	 * 
	 * @return	array
	 * 		Array{name, address}
	 */
	public function getServer($name)
	{
		$result = array();
		$srv = $this->parser->evaluate("//server[@name='".$name."']")->item(0);
		
		$result['name'] = $this->parser->attr($srv, "name");
		$result['address'] = $this->parser->attr($srv, "address");
		
		return $result;
	}
	
	/**
	 * A list of all register servers
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function getServerList()
	{
		$result = array();
		$servers = $this->parser->evaluate("//server");
		foreach($servers as $server)
		{
			$result[] = $this->parser->attr($server, 'name');
		}
		return $result;
	}

	/**
	 * Gets the file's item id for the vcs.
	 * 
	 * @return	string
	 * 		The vcs item hash id.
	 */
	private function getItemID()
	{
		return $this->website->getItemID("settings");
	}
}
//#section_end#
?>