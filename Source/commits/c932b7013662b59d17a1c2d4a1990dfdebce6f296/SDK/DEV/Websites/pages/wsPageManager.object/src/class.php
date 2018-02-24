<?php
//#section#[header]
// Namespace
namespace DEV\Websites\pages;

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
 * @namespace	\pages
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Prototype", "pageMap");
importer::import("DEV", "Websites", "website");
importer::import("DEV", "Version", "vcs");

use \DEV\Prototype\pageMap;
use \DEV\Websites\website;
use \DEV\Version\vcs;

/**
 * Website Page Manager
 * 
 * This is the class that is responsible for managing the folders and pages of a website.
 * 
 * @version	4.1-1
 * @created	September 26, 2014, 14:38 (EEST)
 * @revised	December 30, 2014, 17:26 (EET)
 */
class wsPageManager extends pageMap
{
	/**
	 * The website's index file name for pages.
	 * 
	 * @type	string
	 */
	const INDEX_FILE = "pages.xml";
	
	/**
	 * The website object
	 * 
	 * @type	website
	 */
	private $website;
	
	/**
	 * The vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * The index's DOMParser object.
	 * 
	 * @type	DOMParser
	 */
	private $pageMap;
	
	/**
	 * Constructor method.
	 * Creates the library index (if not any) and initializing the library.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @return	void
	 */
	public function __construct($websiteID)
	{
		// Initialize class variables
		$this->website = new website($websiteID);
		$this->vcs = new vcs($websiteID);
		
		// Create item (if not exists)
		$itemID = $this->website->getItemID("pageLibIndex");
		$itemTrunkPath = $this->vcs->getItemTrunkPath($itemID);
		if (!$itemTrunkPath)
		{
			$itemPath = "/";
			$itemName = self::INDEX_FILE;
			$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		}
		
		// Create map file
		parent::__construct(dirname($itemTrunkPath), basename($itemTrunkPath));
		$this->createMapFile();
	}
	
	/**
	 * Create a new folder in the website navigation.
	 * 
	 * @param	string	$parent
	 * 		The parent folder to create the folder to.
	 * 		Separate each folder with "/".
	 * 
	 * @param	string	$folder
	 * 		The name of the folder to create.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createFolder($parent, $folder)
	{
		// Create folder in the map
		$status = parent::createFolder($parent, $folder);
		if (!$status)
			return FALSE;
		
		// Update vcs item
		$this->updateIndexFile();
		return TRUE;
	}
	
	/**
	 * Remove a folder from the website.
	 * The folder must be empty of pages and other folders.
	 * 
	 * @param	string	$folder
	 * 		The folder name to be removed.
	 * 		Separate each subfolder with "/".
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeFolder($folder)
	{
		// Remove folder
		$status = parent::removeFolder($folder);
		if (!$status)
			return FALSE;
		
		// Update vcs item
		$this->updateIndexFile();
		return TRUE;
	}
	
	/**
	 * Create a page in the given folder.
	 * It updates the library index and creates a new page object.
	 * 
	 * @param	string	$parent
	 * 		The parent folder to create the folder to.
	 * 		Separate each folder with "/".
	 * 
	 * @param	string	$pageName
	 * 		The page name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createPage($parent, $pageName)
	{
		// Create page in the map
		$status = parent::createPage($parent, $pageName);
		if (!$status)
			return FALSE;
		
		// Update vcs item
		$this->updateIndexFile();
		return TRUE;
	}
	
	/**
	 * Remove a page from the website.
	 * 
	 * @param	string	$parent
	 * 		The parent folder of the page.
	 * 		Separate each folder with "/".
	 * 
	 * @param	string	$pageName
	 * 		The page name to be removed.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removePage($parent, $pageName)
	{
		// Remove page
		$status = parent::removePage($parent, $pageName);
		if (!$status)
			return FALSE;
		
		// Update vcs item
		$this->updateIndexFile();
		return TRUE;
	}
	
	/**
	 * Updates the pages index file in the vcs.
	 * 
	 * @return	void
	 */
	private function updateIndexFile()
	{
		// Get vcs item id
		$itemID = $this->website->getItemID("pageLibIndex");
		
		// Update in vcs (with force_commit)
		$this->vcs->updateItem($itemID, TRUE);
	}
}
//#section_end#
?>