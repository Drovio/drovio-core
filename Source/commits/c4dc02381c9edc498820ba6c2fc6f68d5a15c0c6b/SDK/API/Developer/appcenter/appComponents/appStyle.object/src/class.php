<?php
//#section#[header]
// Namespace
namespace API\Developer\appcenter\appComponents;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\appcenter\appComponents
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "appcenter::application");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Developer\appcenter\application;
use \API\Developer\content\document\parsers\phpParser;
use \API\Resources\filesystem\fileManager;

/**
 * Application style
 * 
 * The application style manager class.
 * 
 * @version	{empty}
 * @created	June 4, 2013, 15:40 (EEST)
 * @revised	April 6, 2014, 9:32 (EEST)
 * 
 * @deprecated	USe \DEV\Apps\components\appStyle instead.
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
	 * The applications vcs manager object.
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
	 * Constructor Method. Initializes the style object.
	 * 
	 * @param	vcs	$vcs
	 * 		The application's vcs manager object.
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
		application::addObjectIndex($this->devPath, "styles", "style", $this->name);
		
		// Create vcs object
		$itemID = $this->getItemID();
		$styleFile = $this->vcs->createItem($itemID, application::STYLES_FOLDER, $this->name.".".self::FILE_TYPE, $isFolder = FALSE);
		
		// Create file
		fileManager::create($styleFile, "/* Type your css rules here... */", TRUE);
		
		return TRUE;
	}
	
	/**
	 * Updates the style source code.
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
		$styleFile = $this->vcs->updateItem($itemID);

		// Update File
		$code = phpParser::clearCode($code);
		return fileManager::create($styleFile, $code, TRUE);
	}
	
	/**
	 * Gets the style source code.
	 * 
	 * @return	string
	 * 		The style source code.
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
	 * Gets the style item id.
	 * 
	 * @return	string
	 * 		The item hash id.
	 */
	private function getItemID()
	{
		return "stl".md5($this->name);
	}
}
//#section_end#
?>