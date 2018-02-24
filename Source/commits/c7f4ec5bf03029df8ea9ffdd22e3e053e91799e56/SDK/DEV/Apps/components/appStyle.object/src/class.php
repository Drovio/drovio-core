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
 * Application Style
 * 
 * The application style manager class.
 * 
 * @version	{empty}
 * @created	April 6, 2014, 9:39 (EEST)
 * @revised	April 6, 2014, 9:47 (EEST)
 */
class appStyle
{
	/**
	 * The object's file type.
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "css";
	
	/**
	 * The application object.
	 * 
	 * @type	application
	 */
	private $app;
	
	/**
	 * The style name
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
	 * Constructor Method. Initializes the style object.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$name
	 * 		The object's name.
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
	 * Creates a new style.
	 * 
	 * @param	string	$styleName
	 * 		The style's name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($styleName)
	{
		// Set name
		$this->name = $styleName;
		
		// Create object index
		$this->app->addObjectIndex("styles", "style", $styleName);
		
		// Create vcs object
		$itemID = $this->getItemID();
		$itemPath = application::STYLES_FOLDER;
		$itemName = $styleName.".".self::FILE_TYPE;
		$styleFile = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		
		// Create file
		return fileManager::create($styleFile, "/* Type your css rules here... */", TRUE);
	}
	
	/**
	 * Updates the style source code.
	 * 
	 * @param	string	$code
	 * 		The css code.
	 * 
	 * @return	void
	 */
	public function update($code = "")
	{
		// Update and Load view folder
		$itemID = $this->getItemID();
		$styleFile = $this->vcs->updateItem($itemID);

		// Update File
		$code = phpParser::clear($code);
		return fileManager::create($styleFile, $code, TRUE);
	}
	
	/**
	 * Gets the style's source code.
	 * 
	 * @return	string
	 * 		The script code.
	 */
	public function get()
	{
		// Load view folder
		$itemID = $this->getItemID();
		$styleFile = $this->vcs->getItemTrunkPath($itemID);
		
		// Load code
		$code = fileManager::get($styleFile);
		
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
		return "css".md5($this->name);
	}
}
//#section_end#
?>