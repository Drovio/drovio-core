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

importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Geoloc", "locale");
importer::import("DEV", "Websites", "website");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Resources", "paths");

use \API\Resources\filesystem\fileManager;
use \API\Resources\DOMParser;
use \API\Geoloc\locale;
use \DEV\Websites\website;
use \DEV\Version\vcs;
use \DEV\Resources\paths;

/**
 * Website Page Manager
 * 
 * This is the class that is responsible for managing the folders and pages of a website.
 * 
 * @version	2.0-1
 * @created	September 26, 2014, 14:38 (EEST)
 * @revised	September 29, 2014, 12:09 (EEST)
 */
class wsPageManager
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
	private $dom_parser;
	
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
		$this->dom_parser = new DOMParser();
		
		// Init Website Pages library
		$this->init();
	}
	
	/**
	 * Get an array of all the folders under the given path.
	 * 
	 * @param	string	$parent
	 * 		The folder to look down from.
	 * 		The default value is empty, for the root.
	 * 		Separate each folder with "/".
	 * 
	 * @param	boolean	$compact
	 * 		Whether to return a single compact array with folders separated by "/" or a nested array.
	 * 
	 * @return	array
	 * 		A nested array of all the folders under the given path.
	 */
	public function getFolders($parent = "", $compact = FALSE)
	{
		// Get library parser
		$parser = $this->dom_parser;
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		$expression = "/pages";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$expression .= "/folder";
		
		$folders = array();
		$fes = $parser->evaluate($expression);
		foreach ($fes as $folderElement)
		{
			$folderName = $parser->attr($folderElement, "name");
			$newParent = (empty($parent) ? "" : $parent."/").$folderName;
			$libFolders = $this->getFolders($newParent, $compact);
			if ($compact)
			{
				$folders[] = $newParent;
				foreach ($libFolders as $lf)
					$folders[] = $lf;
			}
			else
				$folders[$folderName] = $libFolders;
		}
		
		if (empty($folders) && $compact)
			return "";
		return $folders;
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
		// Get library parser
		$parser = $this->dom_parser;
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		$expression = "/pages";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$parentElement = $parser->evaluate($expression)->item(0);
		if (empty($parentElement))
			return FALSE;

		// Check if folder doesn't already exist
		$folderElement = $parser->evaluate($expression."/folder[@name='".$folder."']")->item(0);
		if (!is_null($folderElement))
			return FALSE;
		
		$folderElement = $parser->create("folder");
		$parser->attr($folderElement, "name", $folder);
		$parser->append($parentElement, $folderElement);
		return $parser->update();
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
		// Get library parser
		$parser = $this->dom_parser;
		$folder = trim($folder);
		$folder = trim($folder, "/");
		
		// Get folder from index
		$expression = "/pages";
		$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $folder)."']";
		$folderElement = $parser->evaluate($expression)->item(0);
		if (is_null($folderElement))
			return FALSE;
		
		// Check that folder is empty
		if ($folderElement->childNodes->length > 0)
			return FALSE;
		
		// Replace folder with null
		$parser->replace($folderElement, NULL);
		return $parser->update();
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
		// Get library parser
		$parser = $this->dom_parser;
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		// Create doc library entry
		$expression = "/pages";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$parentFolder = $parser->evaluate($expression)->item(0);
		if (empty($parentFolder))
			return FALSE;
		
		// Check if there isn't already a doc with the same name
		$pageElement = $parser->evaluate($expression."/page[@name='".$pageName."']")->item(0);
		if (!is_null($pageElement))
			return TRUE;
		
		// Create document entry in the library index
		$pageElement = $parser->create("page");
		$parser->attr($pageElement, "name", $pageName);
		$parser->append($parentFolder, $pageElement);
		return $parser->update();
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
		// Get library parser
		$parser = $this->dom_parser;
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		// Get view folder
		$expression = "/pages";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$parentFolder = $parser->evaluate($expression)->item(0);
		if (empty($parentFolder))
			return FALSE;
		
		// Get the view from index
		$pageElement = $parser->evaluate($expression."/page[@name='".$pageName."']")->item(0);
		if (is_null($pageElement))
			return FALSE;
		
		// Replace view with null
		$parser->replace($pageElement, NULL);
		return $parser->update();
	}
	
	/**
	 * Get all pages in a given folder.
	 * 
	 * @param	string	$parent
	 * 		The folder to look down from.
	 * 		The default value is empty, for the root.
	 * 		Separate each folder with "/".
	 * 
	 * @return	array
	 * 		An array of all pages.
	 */
	public function getFolderPages($parent = "")
	{
		// Get library parser
		$parser = $this->dom_parser;
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		$expression = "/pages";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$expression .= "/page";
		
		$pages = array();
		// Get document parent
		$pgs = $parser->evaluate($expression);
		foreach ($pgs as $pgElement)
			$pages[] = $parser->attr($pgElement, "name");
		
		return $pages;
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
	
	/**
	 * Initializes the DOMParser object and loads the library index file.
	 * If the index file doesn't exist, it creates it.
	 * 
	 * @return	void
	 */
	private function init()
	{
		// Get library parser
		$parser = $this->dom_parser;
		
		// Create item (if not exists)
		$itemID = $this->website->getItemID("pageLibIndex");
		$itemTrunkPath = $this->vcs->getItemTrunkPath($itemID);
		if (!$itemTrunkPath)
		{
			$itemPath = "/";
			$itemName = self::INDEX_FILE;
			$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		}
		
		try
		{
			$parser->load($itemTrunkPath, FALSE);
		}
		catch (Exception $ex)
		{
			// Create document's library root
			$root = $parser->create("pages");
			$parser->append($root);
			
			// Create file
			fileManager::create($itemTrunkPath, "", TRUE);
			$parser->save($itemTrunkPath);
		}
	}
}
//#section_end#
?>