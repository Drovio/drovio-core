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
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("DEV", "Projects", "project");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "WebTemplates", "prototype/templatePrototype");
importer::import("DEV", "WebTemplates", "prototype/templateThemeJSPrototype");
importer::import("DEV", "Websites", "templates/wsTemplate");
importer::import("DEV", "Websites", "templates/wsTemplateTheme");
importer::import("DEV", "Websites", "website");

use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Tools\parsers\cssParser;
use \DEV\Version\vcs;
use \DEV\WebTemplates\prototype\templatePrototype;
use \DEV\WebTemplates\prototype\templateThemeJSPrototype;
use \DEV\Websites\templates\wsTemplate;
use \DEV\Websites\templates\wsTemplateTheme;
use \DEV\Websites\website;

/**
 * Template theme javascript object.
 * 
 * Manages a theme's javascript file.
 * 
 * @version	0.1-2
 * @created	September 18, 2015, 11:58 (BST)
 * @updated	December 11, 2015, 16:12 (GMT)
 */
class wsTemplateThemeJS extends templateThemeJSPrototype
{
	/**
	 * The website template object.
	 * 
	 * @type	wsTemplate
	 */
	private $template;
	
	/**
	 * The website template theme object.
	 * 
	 * @type	wsTemplateTheme
	 */
	private $templateTheme;
	
	/**
	 * The javascript file name.
	 * 
	 * @type	string
	 */
	private $name;
	
	/**
	 * The theme name.
	 * 
	 * @type	string
	 */
	private $themeName;
	
	/**
	 * The template name.
	 * 
	 * @type	string
	 */
	private $templateName;
	
	/**
	 * The vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Create an instance of the template theme javascript file.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @param	string	$templateName
	 * 		The template name.
	 * 
	 * @param	string	$themeName
	 * 		The theme name.
	 * 
	 * @param	string	$jsName
	 * 		The javasript file name.
	 * 		Leave empty for new file.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($websiteID, $templateName, $themeName, $jsName = "")
	{
		// Initialize template project and vcs
		$this->template = new wsTemplate($websiteID, $templateName);
		$this->templateTheme = new wsTemplateTheme($websiteID, $templateName, $themeName);
		$this->vcs = new vcs($websiteID);
		$this->templateName = $templateName;
		$this->themeName = $themeName;
		$this->name = $jsName;
		
		// Get js file path
		$jsFilePath = "";
		if (!empty($this->name))
		{
			$itemID = $this->getItemID();
			$jsFilePath = $this->vcs->getItemTrunkPath($itemID);
		}
		
		// Initialize template theme prototype
		$indexFilePath = $this->template->getIndexFilePath($update = FALSE);
		$themeFolderPath = $this->templateTheme->getThemeFolderPath($update = FALSE);
		parent::__construct($indexFilePath, $themeFolderPath, $jsFilePath);
	}
	
	/**
	 * Create a new javascript file.
	 * 
	 * @param	string	$name
	 * 		The javascript file name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if there is a javascript with the same name in the theme.
	 */
	public function create($name)
	{
		// Check that name is not empty and valid
		if (empty($name))
			return FALSE;
		
		// Set js name
		$name = trim($name);
		$this->name = str_replace(" ", "_", $name);
		
		// Create vcs object
		$itemID = $this->getItemID();
		$itemPath = website::TEMPLATES_FOLDER."/".$this->templateName.".".wsTemplate::FILE_TYPE."/".templatePrototype::THEMES_FOLDER."/".$this->templateTheme->getThemeFolder($this->themeName)."/".wsTemplateTheme::JS_FOLDER;
		$itemName = $this->name.".js";
		$jsFilePath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		
		// Re-initialize object (and update vcs theme index file)
		$status = parent::create($jsFilePath, $this->name);
		if ($status)
			$this->template->getIndexFilePath($update = TRUE);
		
		return $status;
	}
	
	/**
	 * Update the file contents.
	 * 
	 * @param	string	$js
	 * 		The javascript contents.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($js = "")
	{
		// Update vcs item
		$itemID = $this->getItemID();
		$this->vcs->updateItem($itemID);
		
		// Update
		return parent::update($js);
	}
	
	/**
	 * Remove the javascript file from the theme.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Remove item from vcs
		$itemID = $this->getItemID();
		$this->vcs->deleteItem($itemID);
		
		// Remove page
		$this->template->getIndexFilePath($update = TRUE);
		return parent::remove($this->name);
	}
	
	/**
	 * Gets the vcs item's id.
	 * 
	 * @return	string
	 * 		The item's hash id.
	 */
	private function getItemID()
	{
		return $this->template->getItemID("thm_js_".$this->themeName."_".$this->name);
	}
}
//#section_end#
?>