<?php
//#section#[header]
// Namespace
namespace API\Developer\appcenter\appComponents;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\appcenter\appComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "appcenter::application");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Developer\appcenter\application;
use \API\Developer\content\document\parsers\phpParser;
use \API\Resources\filesystem\fileManager;

/**
 * Application Script
 * 
 * The application script manager class.
 * 
 * @version	{empty}
 * @created	June 4, 2013, 15:40 (EEST)
 * @revised	October 29, 2013, 19:59 (EET)
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
	 * The vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * The object's developer path.
	 * 
	 * @type	string
	 */
	private $devPath;
	
	/**
	 * Constructor Method. Initializes the script object.
	 * 
	 * @param	vcs	$vcs
	 * 		The application's vcs object.
	 * 
	 * @param	string	$devPath
	 * 		The application inner path for the object.
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @return	void
	 */
	public function __construct($vcs, $devPath, $name = "")
	{
		// Put your constructor method code here.
		$this->vcs = $vcs;
		$this->devPath = $devPath;
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
		application::addObjectIndex($this->devPath, "scripts", "script", $this->name);
		
		// Create vcs object
		$itemID = $this->getItemID();
		$scriptFile = $this->vcs->createItem($itemID, application::SCRIPTS_FOLDER, $this->name.".".self::FILE_TYPE, $isFolder = FALSE);
		
		// Create file
		fileManager::create($scriptFile, "// Type your script code here...", TRUE);
		
		return TRUE;
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
		$code = phpParser::clearCode($code);
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
	 * Gets the script item's id.
	 * 
	 * @return	string
	 * 		The item hash id.
	 */
	private function getItemID()
	{
		return "scr".md5($this->name);
	}
}
//#section_end#
?>