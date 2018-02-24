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
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Resources", "filesystem/directory");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Tools", "parsers/phpParser");
importer::import("DEV", "Websites", "website");
importer::import("DEV", "Websites", "pages/wsPageManager");
importer::import("DEV", "Version", "vcs");

use \API\Resources\filesystem\directory;
use \API\Resources\filesystem\fileManager;
use \DEV\Tools\parsers\phpParser;
use \DEV\Websites\website;
use \DEV\Websites\pages\wsPageManager;
use \DEV\Version\vcs;

/**
 * Simple Website Page
 * 
 * This handles simple website pages that are not smart page views.
 * 
 * @version	0.1-2
 * @created	July 17, 2015, 12:38 (EEST)
 * @updated	July 17, 2015, 13:37 (EEST)
 */
class sPage
{
	/**
	 * The page type.
	 * 
	 * @type	string
	 */
	const PAGE_TYPE = "spage";
	
	/**
	 * The website object
	 * 
	 * @type	website
	 */
	private $website;
	
	/**
	 * The page name
	 * 
	 * @type	string
	 */
	private $name;
	
	/**
	 * The page folder. Empty for root pages.
	 * 
	 * @type	string
	 */
	private $folder;
	
	/**
	 * The vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;

	/**
	 * Constructor. Initializes the object's variables.
	 * 
	 * @param	integer	$id
	 * 		The website id.
	 * 
	 * @param	string	$folder
	 * 		The page folder.
	 * 
	 * @param	string	$name
	 * 		The page name.
	 * 		For creating new page, leave this empty.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($id, $folder = "", $name = "")
	{
		// Init website
		$this->website = new website($id);
		
		// Init vcs
		$this->vcs = new vcs($id);
		
		// Set folder and name
		$folder = trim($folder);
		$folder = trim($folder, ".");
		$this->folder = directory::normalize($folder);
		
		$name = trim($name);
		$this->name = str_replace(" ", "_", $name);
	}
	
	/**
	 * Creates a new simple website page.
	 * 
	 * @param	string	$name
	 * 		The page name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($name)
	{
		// Check that name is not empty and valid
		if (empty($name))
			return FALSE;
			
		// Set pagename
		$this->name = $name;
		
		// Create object index
		$pMan = new wsPageManager($this->website->getID());
		$status = $pMan->createPage($this->folder, $this->name, "spage");
		if (!$status)
			return FALSE;
		
		// Create vcs object
		$itemID = $this->getItemID();
		$itemPath = website::PAGES_FOLDER."/".$this->folder."/";
		$itemName = $this->name;
		$folder = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		
		// Update page with a default code
		$this->update();
		
		return TRUE;
	}
	
	/**
	 * Get the page's contents.
	 * 
	 * @return	string
	 * 		The page's contents.
	 */
	public function get()
	{
		// Load view folder
		$pagePath = $this->getPagePath();
		
		// Return page contents
		return fileManager::get($pagePath);
	}
	
	/**
	 * Updates the page's contents.
	 * 
	 * @param	string	$contents
	 * 		The page's contents.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($contents = "")
	{
		// Get pafe path
		$pagePath = $this->getPagePath();
		$contents = trim($contents);
		
		// Clear code from unwanted characters
		$contents = phpParser::clear($contents);

		// Update File
		$this->vcs->updateItem($itemID);
		return fileManager::put($pagePath, $contents);
	}
	
	/**
	 * Remove the page from the website.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Remove page from map
		$wpm = new wsPageManager($this->website->getID());
		$status = $wpm->removePage($this->folder, $this->name);
		
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
		return $this->website->getItemID("spage_".$this->folder."_".$this->name);
	}
	
	/**
	 * Get the page path.
	 * 
	 * @return	string
	 * 		The page path.
	 */
	private function getPagePath()
	{
		$itemID = $this->getItemID();
		return $this->vcs->getItemTrunkPath($itemID);
	}
}
//#section_end#
?>