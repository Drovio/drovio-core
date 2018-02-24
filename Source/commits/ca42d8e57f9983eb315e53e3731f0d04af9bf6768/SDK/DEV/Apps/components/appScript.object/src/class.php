<?php
//#section#[header]
// Namespace
namespace DEV\Apps\components;

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
 * @package	Apps
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Apps", "application");
importer::import("DEV", "Tools", "parsers::phpParser");
importer::import("DEV", "Version", "vcs");

use \API\Resources\filesystem\fileManager;
use \DEV\Apps\application;
use \DEV\Tools\parsers\phpParser;
use \DEV\Version\vcs;

/**
 * Application Script
 * 
 * The application script manager class.
 * 
 * @version	{empty}
 * @created	April 6, 2014, 9:43 (EEST)
 * @revised	April 6, 2014, 9:47 (EEST)
 */
class appScript
{
	/**
	 * The object's file type.
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "js";
	
	/**
	 * The application object.
	 * 
	 * @type	application
	 */
	private $app;
	
	/**
	 * The script name.
	 * 
	 * @type	string
	 */
	private $name;
	
	/**
	 * The vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Constructor Method. Initializes the script object.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$name
	 * 		The script name
	 * 
	 * @return	void
	 */
	public function __construct($appID, $name = "")
	{
		// Init application
		$this->app = new application($appID);
		
		// Init vcs
		$repository = $this->app->getRepository();
		$this->vcs = new vcs($repository);
		$this->name = $name;
	}
	
	/**
	 * Creates a new object.
	 * 
	 * @param	string	$scriptName
	 * 		The script's name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($scriptName)
	{
		// Set name
		$this->name = $scriptName;
		
		// Create object index
		$status = $this->app->addObjectIndex("scripts", "script", $scriptName);
		if (!$status)
			return FALSE;
		
		// Create vcs object
		$itemID = $this->getItemID();
		$itemPath = application::SCRIPTS_FOLDER;
		$itemName = $scriptName.".".self::FILE_TYPE;
		$scriptFile = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		
		// Create file
		return fileManager::create($scriptFile, "// Type your script code here...", TRUE);
	}
	
	/**
	 * Updates the script source code.
	 * 
	 * @param	string	$code
	 * 		The source code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($code = "")
	{
		// Update and Load view folder
		$itemID = $this->getItemID();
		$scriptFile = $this->vcs->updateItem($itemID);

		// Update File
		$code = phpParser::clear($code);
		return fileManager::create($scriptFile, $code, TRUE);
	}
	
	/**
	 * Gets the script's source code.
	 * 
	 * @return	string
	 * 		The script code.
	 */
	public function get()
	{
		// Load view folder
		$itemID = $this->getItemID();
		$scriptFile = $this->vcs->getItemTrunkPath($itemID);
		
		// Load code
		$code = fileManager::get($scriptFile);
		
		// Return code section
		return trim($code);
	}
	
	/**
	 * Gets the vcs item's id.
	 * 
	 * @return	string
	 * 		The item's hash id.
	 */
	private function getItemID()
	{
		return "js".md5($this->name);
	}
}
//#section_end#
?>