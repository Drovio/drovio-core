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
importer::import("DEV", "WebTemplates", "prototype/templateThemePrototype");
importer::import("DEV", "Websites", "templates/wsTemplate");
importer::import("DEV", "Websites", "templates/wsTemplateThemeCSS");
importer::import("DEV", "Websites", "templates/wsTemplateThemeJS");
importer::import("DEV", "Websites", "website");

use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Tools\parsers\cssParser;
use \DEV\Version\vcs;
use \DEV\WebTemplates\prototype\templatePrototype;
use \DEV\WebTemplates\prototype\templateThemePrototype;
use \DEV\Websites\templates\wsTemplate;
use \DEV\Websites\templates\wsTemplateThemeCSS;
use \DEV\Websites\templates\wsTemplateThemeJS;
use \DEV\Websites\website;

/**
 * Website template theme manager.
 * 
 * Manages themes for the website's template.
 * 
 * @version	0.1-3
 * @created	September 18, 2015, 12:14 (BST)
 * @updated	December 9, 2015, 14:10 (GMT)
 */
class wsTemplateTheme extends templateThemePrototype
{
	/**
	 * The website template object.
	 * 
	 * @type	wsTemplate
	 */
	private $template;
	
	/**
	 * The current website id.
	 * 
	 * @type	integer
	 */
	private $websiteID;
	
	/**
	 * The theme name.
	 * 
	 * @type	string
	 */
	private $name;
	
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
	 * Create an instance of the theme manager.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @param	string	$templateName
	 * 		The template name.
	 * 
	 * @param	string	$name
	 * 		The theme name.
	 * 		Leave empty for new themes.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($websiteID, $templateName, $name = "")
	{
		// Initialize template project and vcs
		$this->websiteID = $websiteID;
		$this->template = new wsTemplate($websiteID, $templateName);
		$this->vcs = new vcs($websiteID);
		$this->templateName = $templateName;
		$this->name = $name;
		
		// Get page file path
		$themeIndexFilePath = "";
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
		$itemPath = website::TEMPLATES_FOLDER."/".$this->templateName.".".wsTemplate::FILE_TYPE."/".templatePrototype::THEMES_FOLDER."/".parent::getThemeFolder($this->name);
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
	 * Get all theme javascript content.
	 * 
	 * @return	string
	 * 		All the javascript content.
	 */
	public function getJS()
	{
		$themeJS = "";
		
		// Get all js files
		$jsFiles = $this->getAllJS();
		asort($jsFiles);
		foreach ($jsFiles as $jsName)
		{
			$tth_js = new wsTemplateThemeJS($this->websiteID, $this->templateName, $this->name, $jsName);
			$themeJS .= $tth_js->get()."\n";
		}
		
		return trim($themeJS);
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
	 * Get all theme css content.
	 * 
	 * @return	string
	 * 		All theme css content.
	 */
	public function getCSS()
	{
		$themeCSS = "";
		
		// Get all js files
		$cssFiles = $this->getAllCSS();
		asort($cssFiles);
		foreach ($cssFiles as $cssName)
		{
			$tth_css = new wsTemplateThemeCSS($this->websiteID, $this->templateName, $this->name, $cssName);
			$themeCSS.= $tth_css->get($normalCss = TRUE)."\n";
		}
		
		return trim($themeCSS);
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
		
		$styles = $this->getAllCSS();
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