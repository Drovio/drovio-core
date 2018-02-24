<?php
//#section#[header]
// Namespace
namespace DEV\Prototype;

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
 * @package	Prototype
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Platform", "accessControl");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Platform\accessControl;

/**
 * Page map index manager.
 * 
 * Creates a map for the pages including folders and pages.
 * 
 * @version	0.2-1
 * @created	December 30, 2014, 17:12 (EET)
 * @updated	July 17, 2015, 12:38 (EEST)
 */
class pageMap
{
	/**
	 * The default map file name.
	 * 
	 * @type	string
	 */
	const MAP_FILE = "map.xml";
	
	/**
	 * The folder path of the file.
	 * 
	 * @type	string
	 */
	private $folderPath;
	
	/**
	 * The map file name.
	 * 
	 * @type	string
	 */
	private $mapFile;
	
	/**
	 * Constructor method for object initialization.
	 * 
	 * @param	string	$folderPath
	 * 		The folder path for the map file.
	 * 
	 * @param	string	$mapFile
	 * 		The map file name.
	 * 		By default the MAP_FILE constant is used.
	 * 
	 * @return	void
	 */
	public function __construct($folderPath, $mapFile = self::MAP_FILE)
	{
		$this->folderPath = $folderPath;
		$this->mapFile = $mapFile;
	}
	
	/**
	 * Create the map file.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createMapFile()
	{
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// If file exists, return TRUE
		if (file_exists($filePath))
			return TRUE;
		
		// Create file
		$parser = new DOMParser();
		$root = $parser->create("pages");
		$parser->append($root);
		fileManager::create($filePath, "", TRUE);
		return $parser->save($filePath, "", TRUE);
	}
	
	/**
	 * Create a new folder in the map.
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
		// Get index path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load file
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		// Initialize parent
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
	 * Remove a folder from the map.
	 * The folder must be empty of pages and other folders.
	 * 
	 * @param	string	$folder
	 * 		The parent folder to create the folder to.
	 * 		Separate each folder with "/".
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeFolder($folder)
	{
		// Check api functionality
		if (!accessControl::internalCall())
			throw new Exception("Only internal calls are allowed to delete a folder!");
			
		// Get index path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load file
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		// Initialize folder name
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
		// Get index path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load file
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		// Initialize parent
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
		
		return $folders;
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
	 * @param	string	$pageType
	 * 		The page type.
	 * 		You can specify a page type in order to handle pages differently.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createPage($parent, $pageName, $pageType = "")
	{
		// Get index path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load file
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		// Initialize parent
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
		$parser->attr($pageElement, "type", $pageType);
		$parser->append($parentFolder, $pageElement);
		return $parser->update();
	}
	
	/**
	 * Remove a page from the map.
	 * 
	 * @param	string	$parent
	 * 		The parent folder to create the folder to.
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
		// Check api functionality
		if (!accessControl::internalCall())
			throw new Exception("Only internal calls are allowed to delete a page!");
			
		// Get index path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load file
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		// Initialize parent
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
	 * @param	boolean	$full
	 * 		Get all pages analysed by type.
	 * 		If FALSE, return all the pages in an array with no types.
	 * 		It is FALSE by default.
	 * 
	 * @return	array
	 * 		An array of all pages.
	 * 		If full, pages are grouped by type.
	 */
	public function getFolderPages($parent = "", $full = FALSE)
	{
		// Get index path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load file
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		// Initialize parent
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		$expression = "/pages";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$expression .= "/page";
		
		// Initialize page array
		$pages = array();
		
		// Get document parent
		$pgs = $parser->evaluate($expression);
		foreach ($pgs as $pgElement)
		{
			$pageType = $parser->attr($pgElement, "type");
			if ($full)
				$pages[$pageType][] = $parser->attr($pgElement, "name");
			else
				$pages[] = $parser->attr($pgElement, "name");
			
		}
		
		return $pages;
	}
}
//#section_end#
?>