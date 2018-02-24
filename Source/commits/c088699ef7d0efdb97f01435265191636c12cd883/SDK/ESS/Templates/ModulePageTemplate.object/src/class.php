<?php
//#section#[header]
// Namespace
namespace ESS\Templates;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Templates
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Resources", "layoutManager");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "pageComponents::HTMLRibbon");
importer::import("UI", "Html", "pageComponents::toolbarComponents::toolbarItem");

use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\layoutManager;
use \UI\Html\DOM;
use \UI\Html\pageComponents\HTMLRibbon;
use \UI\Html\pageComponents\toolbarComponents\toolbarItem;

/**
 * Module Page Template
 * 
 * Creates a page that a module will populate and show.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 12:08 (EET)
 * @revised	December 11, 2013, 12:38 (EET)
 * 
 * @deprecated	Use \UI\Html\HTMLModulePage directly.
 */
class ModulePageTemplate extends UIObjectPrototype
{
	/**
	 * The module's page title.
	 * 
	 * @type	string
	 */
	protected $title;

	/**
	 * The element where instructions and other stuff will be inserted
	 * 
	 * @type	DOMElement
	 */
	private $invisibles;
	/**
	 * The layout of the page
	 * 
	 * @type	integer
	 */
	private $layoutID;
	
	/**
	 * Constructor Method
	 * 
	 * @param	integer	$layoutID
	 * 		The layout id
	 * 
	 * @return	void
	 */
	public function __construct($layoutID = "")
	{
		$this->layoutID = $layoutID;
	}
	
	/**
	 * Creates the module page.
	 * 
	 * @param	string	$title
	 * 		The page title that this module will set.
	 * 
	 * @param	string	$class
	 * 		The module's style class.
	 * 
	 * @return	ModulePageTemplate
	 * 		{description}
	 */
	public function build($title = "", $class = "")
	{
		// Page Container
		$pageContainer = DOM::create('div', "", "pageContent", "uiPageContent".($class == "" ? "" : " ".$class));
		$this->set($pageContainer);
		DOM::append($pageContainer);
		
		// Page Invisibles
		$this->invisibles = DOM::create('div', "", "pageMisc");
		DOM::append($pageContainer, $this->invisibles);
		
		// Page Title
		$this->setPageTitle($title);
		
		// Create Layout
		$layout = layoutManager::load($this->layoutID);
		DOM::append($pageContainer, $layout);
		
		return $this;
	}
	
	/**
	 * Append an object to a layout section
	 * 
	 * @param	string	$sectionID
	 * 		The section id
	 * 
	 * @param	DOMElement	$content
	 * 		The element to be appended
	 * 
	 * @return	void
	 */
	public function appendToSection($sectionID, $content)
	{
		return layoutManager::append($this->layoutID, $sectionID, $content);
	}
	
	/**
	 * Append a DOMElement to page miscellaneous
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended
	 * 
	 * @return	void
	 */
	public function appendToMisc($element)
	{
		DOM::append($this->invisibles, $element);
	}
	
	/**
	 * Sets the title of the Page
	 * 
	 * @param	string	$title
	 * 		The page title
	 * 
	 * @return	void
	 */
	public function setPageTitle($title)
	{
		// Set module title
		$this->title = $title;
		
		// Get the page Title if there is already
		$pageTitle = DOM::find("page_title");
		
		// Create the new page Title
		$newPageTitle = DOM::create("div", $title, "page_title");
		
		// Remove the page Title if there is already
		if ($pageTitle != NULL)
			$pageTitle->parentNode->removeChild($pageTitle);
		
		// Add the new page Title
		$this->appendToMisc($newPageTitle);
	}
}
//#section_end#
?>