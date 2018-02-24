<?php
//#section#[header]
// Namespace
namespace DEV\WebExtensions\components;

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
 * @package	WebExtensions
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "WebExtensions", "extension");
importer::import("DEV", "Tools", "parsers::phpParser");
importer::import("DEV", "Version", "vcs");

use \API\Resources\filesystem\fileManager;
use \DEV\WebExtensions\extension;
use \DEV\Tools\parsers\phpParser;
use \DEV\Version\vcs;

/**
 * Extension Theme
 * 
 * The extension theme manager class.
 * 
 * @version	{empty}
 * @created	May 23, 2014, 10:16 (EEST)
 * @revised	May 23, 2014, 10:16 (EEST)
 */
class extTheme
{
	/**
	 * The object's file type.
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "css";
	
	/**
	 * The extension object.
	 * 
	 * @type	extension
	 */
	private $ext;
	
	/**
	 * The theme name.
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
	 * Initializes the style object.
	 * 
	 * @param	integer	$extID
	 * 		The extension id.
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @return	void
	 */
	public function __construct($extID, $name = "")
	{
		// Init application
		$this->ext = new extension($extID);
		
		// Init vcs
		$this->vcs = new vcs($extID);
		$this->name = $name;
	}
	
	/**
	 * Creates a new theme.
	 * 
	 * @param	string	$themeName
	 * 		The theme name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($themeName)
	{
		// Set name
		$this->name = $themeName;
		
		// Create object index
		$status = $this->ext->addObjectIndex("themes", "theme", $styleName);
		if (!$status)
			return FALSE;
		
		// Create vcs object
		$itemID = $this->getItemID();
		$itemPath = extension::THEMES_FOLDER;
		$itemName = $themeName.".".self::FILE_TYPE;
		$styleFile = $this->vcs->createItem($itemID, $itemPath, $itemName, $smart = FALSE);
		
		// Create file
		return fileManager::create($styleFile, "/* Type your css rules here... */", TRUE);
	}
	
	/**
	 * Updates the theme css code.
	 * 
	 * @param	string	$code
	 * 		The css code.
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
		$code = phpParser::clear($code);
		return fileManager::create($styleFile, $code, TRUE);
	}
	
	/**
	 * Gets the theme's css code.
	 * 
	 * @return	string
	 * 		The css code.
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
		return "thm".md5($this->name);
	}
}
//#section_end#
?>