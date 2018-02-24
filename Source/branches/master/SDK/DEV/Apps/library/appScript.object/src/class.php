<?php
//#section#[header]
// Namespace
namespace DEV\Apps\library;

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
 * @namespace	\library
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
 * @version	0.1-2
 * @created	August 22, 2014, 12:47 (EEST)
 * @revised	September 11, 2014, 20:18 (EEST)
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
	 * 		The script name (if any).
	 * 		For new script, leave this empty.
	 * 
	 * @return	void
	 */
	public function __construct($appID, $name = "")
	{
		// Init application
		$this->app = new application($appID);
		
		// Init vcs
		$this->vcs = new vcs($appID);
		$this->name = $name;
	}
	
	/**
	 * Creates a new application script.
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
	 * 		The new script's code.
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
	 * Remove the script from the application.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Delete from application index
		$status = $this->app->removeObjectIndex("scripts", "script", $this->name);
		
		// If delete is successful, delete from vcs
		if ($status === TRUE)
		{
			// Remove object from vcs
			$itemID = $this->getItemID();
			$this->vcs->deleteItem($itemID);
		}
		
		return $status;
	}
	
	/**
	 * Gets the vcs item's id.
	 * 
	 * @return	string
	 * 		The item's hash id.
	 */
	private function getItemID()
	{
		return $this->app->getItemID("js_".$this->name);
	}
}
//#section_end#
?>