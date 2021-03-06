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
importer::import("DEV", "WebTemplates", "prototype/templatePagePrototype");
importer::import("DEV", "Websites", "templates/wsTemplate");
importer::import("DEV", "Websites", "website");

use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Tools\parsers\cssParser;
use \DEV\Version\vcs;
use \DEV\WebTemplates\prototype\templatePrototype;
use \DEV\WebTemplates\prototype\templatePagePrototype;
use \DEV\Websites\templates\wsTemplate;
use \DEV\Websites\website;

/**
 * Website template page manager.
 * 
 * Manages pages for the website's template.
 * 
 * @version	0.1-1
 * @created	September 18, 2015, 13:47 (EEST)
 * @updated	September 18, 2015, 13:47 (EEST)
 */
class wsTemplatePage extends templatePagePrototype
{
	/**
	 * The website template object.
	 * 
	 * @type	wsTemplate
	 */
	private $template;
	
	/**
	 * The page name.
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
	 * Create an instance of the page manager.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @param	string	$templateName
	 * 		The template name.
	 * 
	 * @param	string	$name
	 * 		The page name.
	 * 		Leave empty for new pages.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($websiteID, $templateName, $name = "")
	{
		// Initialize template project and vcs
		$this->template = new wsTemplate($websiteID, $templateName);
		$this->vcs = new vcs($websiteID);
		$this->templateName = $templateName;
		$this->name = $name;
		
		// Get page file path
		$pageFilePath = "";
		if (!empty($this->name))
		{
			$itemID = $this->getItemID();
			$pageFolderPath = $this->vcs->getItemTrunkPath($itemID);
		}
		
		// Initialize template page prototype
		$indexFilePath = $this->template->getIndexFilePath($update = FALSE);
		parent::__construct($indexFilePath, $pageFolderPath);
	}
	
	/**
	 * Create a new template page.
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
		$name = preg_replace("/\.php$/i", "", $name);
		$this->name = str_replace(" ", "_", $name);
		
		// Create vcs object
		$itemID = $this->getItemID();
		$itemPath = website::TEMPLATES_FOLDER."/".$this->templateName.".".wsTemplate::FILE_TYPE."/".templatePrototype::PAGES_FOLDER."/";
		$itemName = parent::getPageFolder($this->name);
		$pageFolderPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = TRUE);
		
		// Re-initialize object
		$indexFilePath = $this->template->getIndexFilePath($update = TRUE);
		return parent::create($pageFolderPath, $this->name);
	}
	
	/**
	 * Update the page's html.
	 * 
	 * @param	string	$html
	 * 		The page's html content.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateHTML($html = "")
	{
		// Update vcs item
		$itemID = $this->getItemID();
		$this->vcs->updateItem($itemID);
		
		// Update file
		return parent::updateHTML($html);
	}
	
	/**
	 * Update the page's css.
	 * 
	 * @param	string	$code
	 * 		The page's css content.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateCSS($code = "")
	{
		// Update vcs item
		$itemID = $this->getItemID();
		$this->vcs->updateItem($itemID);
		
		// Update file
		return parent::updateCSS($code);
	}
	
	/**
	 * Remove the template's page.
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
		return $this->template->getItemID("p_".$this->name);
	}
}
//#section_end#
?>