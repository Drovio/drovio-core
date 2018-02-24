<?php
//#section#[header]
// Namespace
namespace DEV\Websites\templates;

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
 * @namespace	\templates
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("DEV", "Projects", "project");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "WebTemplates", "prototype/templatePrototype");
importer::import("DEV", "Websites", "website");

use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Tools\parsers\cssParser;
use \DEV\Version\vcs;
use \DEV\WebTemplates\prototype\templatePrototype;
use \DEV\Websites\website;

/**
 * Website template manager.
 * 
 * Manages website templates.
 * 
 * @version	0.1-1
 * @created	September 18, 2015, 15:05 (EEST)
 * @updated	September 18, 2015, 15:05 (EEST)
 */
class wsTemplate
{
	/**
	 * The object type / extension
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "template";
	
	/**
	 * The template name.
	 * 
	 * @type	string
	 */
	private $name;
	
	/**
	 * The vcs manager object
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * The website object.
	 * 
	 * @type	website
	 */
	private $website;
	
	/**
	 * The template prototype object.
	 * 
	 * @type	templatePrototype
	 */
	private $template;
	
	/**
	 * Initialize a website template.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @param	string	$templateName
	 * 		The template name.
	 * 		Leave empty for new template.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($websiteID, $templateName = "")
	{
		// Initialize website
		$this->website = new website($websiteID);
		$this->name = $templateName;
		
		// Init vcs
		$this->vcs = new vcs($this->website->getID());
		
		// Initialize template prototype
		$indexFilePath = $this->getIndexFilePath($update = FALSE);
		if (!empty($indexFilePath))
			$this->template = new templatePrototype($indexFilePath);
	}
	
	/**
	 * Create a new website template.
	 * 
	 * @param	string	$templateName
	 * 		The template name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if there is a template with the same name.
	 */
	public function create($templateName)
	{
		if (empty($templateName))
			return FALSE;
			
		// Add template to website index
		$this->name = $templateName;
		$status = $this->website->addObjectIndex(website::TEMPLATES_FOLDER, "template", $this->name);
		if (!$status)
			return FALSE;
		
		// Create vcs item
		$itemID = $this->getItemID("templateIndex");
		$itemPath = website::TEMPLATES_FOLDER."/".$this->name.".".self::FILE_TYPE."/";
		$itemName = templatePrototype::INDEX_FILE;
		$indexFilePath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		
		// Initialize template prototype
		$this->template = new templatePrototype($indexFilePath);
		
		return $indexFilePath;
	}
	
	/**
	 * Remove the template from the website.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Remove from object index
		$status = $this->website->removeObjectIndex(website::TEMPLATES_FOLDER, "template", $templateName);
		if (!$status)
			return FALSE;
		
		// Remove from vcs
		$itemID = $this->getItemID("templateIndex");
		$this->vcs->deleteItem($itemID);
	}
	
	/**
	 * Get the index file path according to vcs.
	 * 
	 * @param	boolean	$update
	 * 		Whether to update the item for commit or not.
	 * 
	 * @return	string
	 * 		The index file path.
	 */
	public function getIndexFilePath($update = FALSE)
	{
		// Get vcs item id
		$itemID = $this->getItemID("templateIndex");
		
		// Get item path (update or not)
		return ($update ? $this->vcs->updateItem($itemID) : $this->vcs->getItemTrunkPath($itemID));
	}
	
	/**
	 * Get all template pages.
	 * 
	 * @return	array
	 * 		An array of all page names.
	 */
	public function getPages()
	{
		return $this->template->getPages();
	}
	
	/**
	 * Get all template themes.
	 * 
	 * @return	array
	 * 		An array of all theme names.
	 */
	public function getThemes()
	{
		return $this->template->getThemes();
	}
	
	/**
	 * Add an object entry to the main template index.
	 * 
	 * @param	string	$group
	 * 		The name of the group.
	 * 
	 * @param	string	$type
	 * 		The object type (the tag name).
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if there is another object of the same type with the same name.
	 */
	public function addObjectIndex($group, $type, $name)
	{
		// Get vcs item and update
		$itemID = $this->getItemID("templateIndex");
		$this->vcs->updateItem($itemID);
		
		// Add object index to template prototype
		return $this->template->addObjectIndex($group, $type, $name);
	}
	
	/**
	 * Remove an object entry from the template index.
	 * 
	 * @param	string	$group
	 * 		The name of the group.
	 * 
	 * @param	string	$name
	 * 		The object type (the tag name).
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeObjectIndex($group, $name)
	{
		// Get vcs item and update
		$itemID = $this->getItemID("templateIndex");
		$this->vcs->updateItem($itemID);
		
		// Add object index to template prototype
		return $this->template->removeObjectIndex($group, $name);
	}
	
	/**
	 * Get the vcs item id.
	 * Use this class as a template for template item ids.
	 * 
	 * @param	string	$suffix
	 * 		The id suffix.
	 * 
	 * @return	string
	 * 		The item hash id.
	 */
	public function getItemID($suffix)
	{
		return $this->website->getItemID("ws_tpl_".$this->name."_".$suffix);
	}
}
//#section_end#
?>