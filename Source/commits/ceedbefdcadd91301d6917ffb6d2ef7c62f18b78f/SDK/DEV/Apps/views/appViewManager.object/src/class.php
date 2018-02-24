<?php
//#section#[header]
// Namespace
namespace DEV\Apps\views;

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
 * @namespace	\views
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Geoloc", "locale");
importer::import("DEV", "Apps", "application");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Resources", "paths");

use \API\Resources\filesystem\fileManager;
use \API\Resources\DOMParser;
use \API\Geoloc\locale;
use \DEV\Apps\application;
use \DEV\Version\vcs;
use \DEV\Resources\paths;

/**
 * Application View Manager
 * 
 * This is the class that is responsible for managing the folders and views of an application.
 * 
 * @version	1.0-4
 * @created	September 29, 2014, 11:58 (EEST)
 * @revised	November 17, 2014, 15:14 (EET)
 */
class appViewManager
{
	/**
	 * The application's index file name for views.
	 * 
	 * @type	string
	 */
	const INDEX_FILE = "views.xml";
	
	/**
	 * The application object.
	 * 
	 * @type	application
	 */
	private $application;
	
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
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	void
	 */
	public function __construct($appID)
	{
		// Initialize class variables
		$this->application = new application($appID);
		$this->vcs = new vcs($appID);
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
		
		$expression = "/views";
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
	 * Create a new folder in application views.
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
		
		$expression = "/views";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$parentElement = $parser->evaluate($expression)->item(0);
		if (empty($parentElement))
			return FALSE;

		// Check if folder doesn't already exist
		$folderElement = $parser->evaluate($expression."/folder[@name='".$folder."']")->item(0);
		if (!is_null($folderElement))
			return FALSE;
		
		// Update index file
		$this->updateIndexFile();
		
		$folderElement = $parser->create("folder");
		$parser->attr($folderElement, "name", $folder);
		$parser->append($parentElement, $folderElement);
		return $parser->update();
	}
	
	/**
	 * Remove a view folder.
	 * The folder must be empty of folders and views.
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
		$expression = "/views";
		$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $folder)."']";
		$folderElement = $parser->evaluate($expression)->item(0);
		if (is_null($folderElement))
			return FALSE;
		
		// Check that folder is empty
		if ($folderElement->childNodes->length > 0)
			return FALSE;
		
		// Update index file
		$this->updateIndexFile();
		
		// Replace folder with null
		$parser->replace($folderElement, NULL);
		return $parser->update();
	}
	
	/**
	 * Create a view in the given folder.
	 * It updates the library index and creates a new view object.
	 * 
	 * @param	string	$parent
	 * 		The parent folder to create the folder to.
	 * 		Separate each folder with "/".
	 * 
	 * @param	string	$viewName
	 * 		The view name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createView($parent, $viewName)
	{
		// Get library parser
		$parser = $this->dom_parser;
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		// Create doc library entry
		$expression = "/views";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$parentFolder = $parser->evaluate($expression)->item(0);
		if (empty($parentFolder))
			return FALSE;
		
		// Check if there isn't already a doc with the same name
		$viewElement = $parser->evaluate($expression."/view[@name='".$viewName."']")->item(0);
		if (!is_null($viewElement))
			return TRUE;
		
		// Update index file
		$this->updateIndexFile();
		
		// Create document entry in the library index
		$viewElement = $parser->create("view");
		$parser->attr($viewElement, "name", $viewName);
		$parser->append($parentFolder, $viewElement);
		return $parser->update();
	}
	
	/**
	 * Remove a given view from the application.
	 * 
	 * @param	string	$parent
	 * 		The parent folder of the view.
	 * 		Separate each folder with "/".
	 * 
	 * @param	string	$viewName
	 * 		The view name to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeView($parent, $viewName)
	{
		// Get library parser
		$parser = $this->dom_parser;
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		// Get view folder
		$expression = "/views";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$parentFolder = $parser->evaluate($expression)->item(0);
		if (empty($parentFolder))
			return FALSE;
		
		// Get the view from index
		$viewElement = $parser->evaluate($expression."/view[@name='".$viewName."']")->item(0);
		if (is_null($viewElement))
			return FALSE;
		
		// Update index file
		$this->updateIndexFile();
		
		// Replace view with null
		$parser->replace($viewElement, NULL);
		return $parser->update();
	}
	
	/**
	 * Get all views in a given folder.
	 * 
	 * @param	string	$parent
	 * 		The folder to look down from.
	 * 		The default value is empty, for the root.
	 * 		Separate each folder with "/".
	 * 
	 * @return	array
	 * 		An array of all views.
	 */
	public function getFolderViews($parent = "")
	{
		// Get library parser
		$parser = $this->dom_parser;
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		$expression = "/views";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$expression .= "/view";
		
		$views = array();
		// Get document parent
		$vElements = $parser->evaluate($expression);
		foreach ($vElements as $vElement)
			$views[] = $parser->attr($vElement, "name");
		
		return $views;
	}
	
	/**
	 * Updates the views index file in the vcs.
	 * 
	 * @return	void
	 */
	private function updateIndexFile()
	{
		// Get vcs item id
		$itemID = $this->application->getItemID("viewsIndex");
		
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
		$itemID = $this->application->getItemID("viewsIndex");
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
			$root = $parser->create("views");
			$parser->append($root);
			
			// Create file
			fileManager::create($itemTrunkPath, "", TRUE);
			$parser->save($itemTrunkPath);
			
			// Compatibility code
			// Get old page index and add pages
			$oldViews = $this->application->getViews();
			foreach ($oldViews as $viewName)
				$this->createView("", $viewName);
		}
	}
}
//#section_end#
?>