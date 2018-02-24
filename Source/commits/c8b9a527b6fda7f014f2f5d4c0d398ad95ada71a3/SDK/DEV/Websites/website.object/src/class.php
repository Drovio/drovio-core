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

importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "Websites", "wsSettings");

use \DEV\Projects\project;
use \DEV\Version\vcs;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Websites\wsSettings;
/**
 * Website Manager Class
 * 
 * This is the main class for managing a website project.
 * 
 * @version	0.2-1
 * @created	June 30, 2014, 12:58 (EEST)
 * @revised	September 15, 2014, 22:00 (EEST)
 */
class website extends project {
	/**
	 * The project type code as it stored in database
	 * 
	 * @type	integer
	 */
	const PROJECT_TYPE = 5;
	

	/**
	 * The index file name.
	 * 
	 * @type	string
	 */
	const INDEX_FILE = "index.xml";	

	/**
	 * the name of the pages folder
	 * 
	 * @type	string
	 */
	const PAGES_FOLDER = 'pages';

	/**
	 * The vcs manager object
	 * 
	 * @type	vcs
	 */
	private $vcs;

	/**
	 * The contructor method
	 * 
	 * @param	sting	$id
	 * 		The projects' id
	 * 
	 * @param	sting	$name
	 * 		The projects' name
	 * 
	 * @return	void
	 */
	public function __construct($id = "", $name = "")
	{
		// Init project
		parent::__construct($id, $name);
		
		// Init vcs
		$this->vcs = new vcs($id);
		
		// Create structure (if first time)
		$this->createStructure();
	}
	
	/**
	 * Creates the (folder / file) structure of the project at creation.
	 * 
	 * @return	boolean
	 * 		True on success, false elsewhere
	 */
	private function createStructure()
	{
		// Check if structure is already there
		if (file_exists(systemRoot.$this->getRootFolder()."/".self::INDEX_FILE))
			return FALSE;
		
		// Create index file
		fileManager::create(systemRoot.$this->getRootFolder()."/".self::INDEX_FILE, "", TRUE);
	
		// Create Index (Mapping) Files
		$this->createMappngFile();
				
		
		// Create Settings File
		//$settings = new wsSettings($this->getID());
		//$settings->create();
		
		
		
		
		return TRUE;	
	}
	
	/**
	 * Creates the mapping / index file of the project at creation.
	 * 
	 * @return	boolean
	 * 		True on success, false elsewhere
	 */
	private function createMappngFile()
	{
		// Create vcs item
		$itemID = $this->getItemID("wsIndex");
		$itemPath = "/";
		$itemName = self::INDEX_FILE;
		$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		
		// Create index root
		$parser = new DOMParser();
		$root = $parser->create("website");
		$parser->append($root);
		$parser->save($itemTrunkPath, FALSE);
		
		// Populate index
		$entry = $parser->create(self::PAGES_FOLDER);
		$parser->append($root, $entry);
				//
		
		$parser->update();
	}
	
	/**
	 * Gets all website pagess.
	 * 
	 * @return	array
	 * 		An array of all object names by group.
	 */
	public function getPages()
	{
		return $this->getIndexObjects("pages", "page");
	}
	
	/**
	 * Get objects from the website index.
	 * 
	 * @param	string	$group
	 * 		The object's group.
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @return	array
	 * 		An array of all object names by group.
	 */
	private function getIndexObjects($group, $name)
	{
		// Get vcs item and load index
		$itemID = $this->getItemID("wsIndex");
		$itemTrunkPath = $this->vcs->getItemTrunkPath($itemID);
		
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		
		// Get The root
		$root = $parser->evaluate("//".$group)->item(0);

		// Get all packages
		$objects = array();
		$elements = $parser->evaluate($name, $root);
		foreach ($elements as $elem)
			$objects[] = $parser->attr($elem, "name");
		
		return $objects;
	}
	
	/**
	 * Adds an object's index to the website index file.
	 * 
	 * @param	string	$group
	 * 		The object group.
	 * 
	 * @param	string	$type
	 * 		The object type.
	 * 
	 * @param	string	$name
	 * 		The object name.
	 * 
	 * @return	boolean
	 * 		True on success, false if object already exist.
	 */
	public function addObjectIndex($group, $type, $name)
	{
		// Get vcs item and update
		$itemID = $this->getItemID("wsIndex");
		$itemTrunkPath = $this->vcs->updateItem($itemID);
		
		// Load index
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		
		// Check if item already exists
		$item = $parser->evaluate("//".$group."/".$type."[@name='".$name."']")->item(0);
		if (!is_null($item))
			return FALSE;
		
		// Get The root
		$root = $parser->evaluate("//".$group)->item(0);
		
		// Create object
		$obj = $parser->create($type);
		$parser->attr($obj, "name", $name);
		$parser->append($root, $obj);
		
		// Update file
		return $parser->update();
	}
	
	/**
	 * Remove an object from the website's index file.
	 * 
	 * @param	string	$group
	 * 		The object group.
	 * 
	 * @param	string	$name
	 * 		The object name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeObjectIndex($group, $name)
	{
		// Get vcs item and update
		$itemID = $this->getItemID("wsIndex");
		$itemTrunkPath = $this->vcs->updateItem($itemID);
		
		// Load index
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		
		// Check if item already exists
		$item = $parser->evaluate("//".$group."[@name='".$name."']")->item(0);
		if (is_null($item))
			return FALSE;
		
		// Remove item and update file
		$parser->replace($item, NULL);
		return $parser->update();
	}
	
	/**
	 * Get the vcs item id.
	 * 
	 * @param	string	$suffix
	 * 		The id suffix.
	 * 
	 * @return	string
	 * 		The item hash id.
	 */
	public function getItemID($suffix)
	{
		return "ws".md5("ws_".$this->getID()."_".$suffix);
	}

}
//#section_end#
?>