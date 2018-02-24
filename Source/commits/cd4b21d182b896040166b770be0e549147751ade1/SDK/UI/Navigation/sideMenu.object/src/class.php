<?php
//#section#[header]
// Namespace
namespace UI\Navigation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Navigation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Prototype", "html/MenuPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");

use \ESS\Prototype\html\MenuPrototype;
use \UI\Html\DOM;
use \UI\Html\HTML;

/**
 * Sidebar menu.
 * 
 * Creates a vertical sidebar menu.
 * 
 * @version	0.1-2
 * @created	March 12, 2013, 16:02 (EET)
 * @updated	April 14, 2015, 14:29 (EEST)
 */
class sideMenu extends MenuPrototype
{
	/**
	 * Builds the entire menu.
	 * 
	 * @param	string	$id
	 * 		The id of the menu.
	 * 
	 * @param	mixed	$header
	 * 		The header of the menu.
	 * 		It can be a string or a DOMElement (span).
	 * 
	 * @return	sideMenu
	 * 		The sideMenu object.
	 */
	public function build($id = "", $header = "")
	{
		// Build the Menu
		parent::build($id, "sideMenu");
		
		// Insert header
		$this->setHeader($header);
			
		return $this;
	}
	
	/**
	 * Sets the menu's header.
	 * 
	 * @param	mixed	$header
	 * 		The header of the menu.
	 * 		It can be a string or a DOMElement (span).
	 * 
	 * @return	sideMenu
	 * 		The sideMenu object.
	 */
	public function setHeader($header)
	{
		// Check empty header
		if (empty($header))
			return $this;
			
		// Create header element
		$headerElement = DOM::create("div", $header, "", "sideMenuHeader");
		DOM::prepend($this->get(), $headerElement);
		
		return $this;
	}
	
	/**
	 * Creates a menu list item and inserts it to the sideMenu.
	 * 
	 * @param	string	$id
	 * 		The item's id.
	 * 
	 * @param	DOMElement	$content
	 * 		The item's content.
	 * 
	 * @param	boolean	$selected
	 * 		Sets the current item selected.
	 * 
	 * @return	DOMElement
	 * 		The list item
	 */
	public function insertListItem($id = "", $content = NULL, $selected = FALSE)
	{
		// Set Menu Item Context
		$context = DOM::create("span", $content, "", "sideMenuItemContext");
		
		// Create Menu Item
		$menuItem = parent::createMenuItem($id, $class = "sideMenuItem", $context);
		if ($selected)
			HTML::addClass($menuItem, "selected");
		
		return $menuItem;			
	}
}
//#section_end#
?>