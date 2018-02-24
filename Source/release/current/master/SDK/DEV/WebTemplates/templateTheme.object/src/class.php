<?php
//#section#[header]
// Namespace
namespace DEV\WebTemplates;

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
 * @package	WebTemplates
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("DEV", "Projects", "project");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "WebTemplates", "prototype/templatePrototype");
importer::import("DEV", "WebTemplates", "prototype/templateThemePrototype");
importer::import("DEV", "WebTemplates", "templateProject");

use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Tools\parsers\cssParser;
use \DEV\Version\vcs;
use \DEV\WebTemplates\prototype\templatePrototype;
use \DEV\WebTemplates\prototype\templateThemePrototype;
use \DEV\WebTemplates\templateProject;

/**
 * Template project theme manager.
 * 
 * Manages themes for template project.
 * 
 * @version	0.1-2
 * @created	September 18, 2015, 0:29 (EEST)
 * @updated	September 18, 2015, 1:55 (EEST)
 */
class templateTheme extends templateThemePrototype
{
	/**
	 * The template project object.
	 * 
	 * @type	templateProject
	 */
	private $template;
	
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
	 * Create an instance of the theme manager.
	 * 
	 * @param	integer	$templateID
	 * 		The template id.
	 * 
	 * @param	string	$name
	 * 		The theme name.
	 * 
	 * @return	void
	 */
	public function __construct($templateID, $name = "")
	{
		// Initialize template project and vcs
		$this->template = new templateProject($templateID);
		$this->vcs = new vcs($templateID);
		$this->name = $name;
		
		// Get page file path
		$pageFilePath = "";
		if (!empty($this->name))
		{
			$itemID = $this->getItemID();
			$themeIndexFilePath = $this->vcs->getItemTrunkPath($itemID);
		}
		
		// Initialize template theme prototype
		$indexFilePath = $this->template->getIndexFilePath($update = FALSE);
		parent::__construct($indexFilePath, $themeIndexFilePath);
	}
	
	/**
	 * Create a new template theme.
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
		$name = trim($name);
		$this->name = str_replace(" ", "_", $name);
		
		// Create vcs object
		$itemID = $this->getItemID();
		$itemPath = templatePrototype::THEMES_FOLDER."/".parent::getThemeFolder($this->name);
		$itemName = parent::INDEX_FILE;
		$indexFilePath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		
		// Re-initialize object
		$indexFilePath = $this->template->getIndexFilePath($update = TRUE);
		return parent::create($indexFilePath, $this->name);
	}
	
	/**
	 * Add a theme javascript file to the index.
	 * 
	 * @param	string	$name
	 * 		The javascript file name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if there is another file with the same name.
	 */
	public function addJS($name)
	{
		// Update vcs item
		$itemID = $this->getItemID();
		$this->vcs->updateItem($itemID);
		
		// Add item
		return parent::addJS($name);
	}
	
	/**
	 * Remove a javascript file from the index.
	 * 
	 * @param	string	$name
	 * 		The javascript file name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeJS($name)
	{
		// Update vcs item
		$itemID = $this->getItemID();
		$this->vcs->updateItem($itemID);
		
		// Update
		return parent::removeJS($name);
	}
	
	/**
	 * Add a theme css file to the index.
	 * 
	 * @param	string	$name
	 * 		The css file name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if there is another file with the same name.
	 */
	public function addCSS($name)
	{
		// Update vcs item
		$itemID = $this->getItemID();
		$this->vcs->updateItem($itemID);
		
		// Add item
		return parent::addCSS($name);
	}
	
	/**
	 * Remove a css file from the index.
	 * 
	 * @param	string	$name
	 * 		The css file name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeCSS($name)
	{
		// Update vcs item
		$itemID = $this->getItemID();
		$this->vcs->updateItem($itemID);
		
		// Update
		return parent::removeCSS($name);
	}
	
	/**
	 * Remove the entire theme.
	 * It must be empty from javascript and css files.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Check if there are objects inside the theme
		$scripts = $this->getAllJS();
		if (count($scripts) > 0)
			return FALSE;
		
		$styles = $this->getALLCSS();
		if (count($styles) > 0)
			return FALSE;
			
		// Remove item from vcs
		$itemID = $this->getItemID();
		$this->vcs->deleteItem($itemID);
		
		// Remove page
		return parent::remove($this->name);
	}
	
	/**
	 * Get the them vcs folder path according to vcs.
	 * 
	 * @param	boolean	$update
	 * 		Whether to update the item for commit or not.
	 * 
	 * @return	string
	 * 		The theme folder path.
	 */
	public function getThemeFolderPath($update = FALSE)
	{
		// Get vcs item id
		$itemID = $this->getItemID();
		
		// Get item path (update or not)
		return ($update ? $this->vcs->updateItem($itemID) : $this->vcs->getItemTrunkPath($itemID));
	}
	
	/**
	 * Gets the vcs item's id.
	 * 
	 * @return	string
	 * 		The item's hash id.
	 */
	private function getItemID()
	{
		return $this->template->getItemID("thm_".$this->name);
	}
}
//#section_end#
?>