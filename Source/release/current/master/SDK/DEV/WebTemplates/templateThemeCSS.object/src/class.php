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
importer::import("DEV", "WebTemplates", "prototype/templateThemeCSSPrototype");
importer::import("DEV", "WebTemplates", "templateProject");
importer::import("DEV", "WebTemplates", "templateTheme");

use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Tools\parsers\cssParser;
use \DEV\Version\vcs;
use \DEV\WebTemplates\prototype\templatePrototype;
use \DEV\WebTemplates\prototype\templateThemeCSSPrototype;
use \DEV\WebTemplates\templateProject;
use \DEV\WebTemplates\templateTheme;

/**
 * Template theme style object.
 * 
 * Manages a theme's style object (scss and css).
 * 
 * @version	0.1-1
 * @created	September 18, 2015, 2:02 (EEST)
 * @updated	September 18, 2015, 2:02 (EEST)
 */
class templateThemeCSS extends templateThemeCSSPrototype
{
	/**
	 * The template project object.
	 * 
	 * @type	templateProject
	 */
	private $template;
	
	/**
	 * The template theme object.
	 * 
	 * @type	templateTheme
	 */
	private $templateTheme;
	
	/**
	 * The style name.
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
	 * The vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Create an instance of the template theme style object.
	 * 
	 * @param	integer	$templateID
	 * 		The template id.
	 * 
	 * @param	string	$themeName
	 * 		The theme name.
	 * 
	 * @param	string	$cssName
	 * 		The css file name.
	 * 
	 * @return	void
	 */
	public function __construct($templateID, $themeName, $cssName = "")
	{
		// Initialize template project and vcs
		$this->template = new templateProject($templateID);
		$this->templateTheme = new templateTheme($templateID, $themeName);
		$this->vcs = new vcs($templateID);
		$this->themeName = $themeName;
		$this->name = $cssName;
		
		// Get css file path
		$cssFilePath = "";
		if (!empty($this->name))
		{
			$itemID = $this->getItemID();
			$cssFilePath = $this->vcs->getItemTrunkPath($itemID);
		}
		
		// Initialize template theme prototype
		$indexFilePath = $this->template->getIndexFilePath($update = FALSE);
		$themeFolderPath = $this->templateTheme->getThemeFolderPath($update = FALSE);
		parent::__construct($indexFilePath, $themeFolderPath, $cssFilePath);
	}
	
	/**
	 * Create a new template style object.
	 * 
	 * @param	string	$name
	 * 		The css file name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if there is a style with the same name in the theme.
	 */
	public function create($name)
	{
		// Check that name is not empty and valid
		if (empty($name))
			return FALSE;
		
		// Set css name
		$name = trim($name);
		$this->name = str_replace(" ", "_", $name);
		
		// Create vcs object
		$itemID = $this->getItemID();
		$itemPath = templatePrototype::THEMES_FOLDER."/".$this->templateTheme->getThemeFolder($this->themeName)."/".templateTheme::CSS_FOLDER;
		$itemName = $this->name.".style";
		$cssFilePath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = TRUE);
		
		// Re-initialize object (and update vcs theme index file)
		$status = parent::create($cssFilePath, $this->name);
		if ($status)
			$this->template->getIndexFilePath($update = TRUE);
		
		return $status;
	}
	
	/**
	 * Update the css file contents.
	 * This will parse scss as well.
	 * 
	 * @param	string	$css
	 * 		The css content.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($css = "")
	{
		// Update vcs item
		$itemID = $this->getItemID();
		$this->vcs->updateItem($itemID);
		
		// Update
		return parent::update($css);
	}
	
	/**
	 * Remove this style object from the theme.
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
		return $this->template->getItemID("thm_css_".$this->name);
	}
}
//#section_end#
?>