<?php
//#section#[header]
// Namespace
namespace DEV\Websites\settings;

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
 * @namespace	\settings
 * 
 * @copyright	Copyright (C) 2015 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Websites", "website");

use \API\Resources\filesystem\fileManager;
use \DEV\Websites\website;

/**
 * Website Robots file editor
 * 
 * Manages the website's robots txt file.
 * 
 * @version	0.1-1
 * @created	January 2, 2015, 13:26 (EET)
 * @revised	January 2, 2015, 13:26 (EET)
 */
class wsRobots
{
	/**
	 * The website object.
	 * 
	 * @type	website
	 */
	private $website;
	
	/**
	 * Initialize the robots editor.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @return	void
	 */
	public function __construct($websiteID)
	{
		// Init website
		$this->website = new website($websiteID);
	}
	
	/**
	 * Set the robots text file content.
	 * 
	 * @param	string	$robots
	 * 		The robots text file
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function set($robots)
	{
		// Validate website
		if (!$this->website->validate())
			return FALSE;
			
		// Get robots file
		$robotsFile = systemRoot.$this->website->getRootFolder()."/robots.txt";
		
		// Update robots file
		return fileManager::create($robotsFile, $robots);
	}
	
	/**
	 * Get the website robots context.
	 * 
	 * @return	string
	 * 		The robots text file content.
	 */
	public function get()
	{
		// Validate website
		if (!$this->website->validate())
			return FALSE;
			
		// Get robots file
		$robotsFile = systemRoot.$this->website->getRootFolder()."/robots.txt";
		
		// Update robots file
		return fileManager::get($robotsFile);
	}
}
//#section_end#
?>