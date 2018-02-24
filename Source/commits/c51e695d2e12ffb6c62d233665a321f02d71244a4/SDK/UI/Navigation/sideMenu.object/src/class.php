<?php
//#section#[header]
// Namespace
namespace UI\Navigation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Navigation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "html::MenuPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\html\MenuPrototype;
use \UI\Html\DOM;

/**
 * Sidebar menu.
 * 
 * Creates a vertical sidebar menu.
 * 
 * @version	{empty}
 * @created	March 12, 2013, 16:02 (EET)
 * @revised	September 23, 2013, 11:39 (EEST)
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
	 * 		{description}
	 */
	public function build($id = "", $header = "")
	{
		// Build the Menu
		parent::build($id, "sideMenu");
		
		// Insert header
		if (!empty($header))
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
	 * 		{description}
	 */
	public function setHeader($header)
	{
		// Create header element
		$headerElement = DOM::create("div", "", "", "sideMenuHeader");
		DOM::prepend($this->get(), $headerElement);
		
		// Create Header tag
		$headerContent = $header;
		if (gettype($header) == "string")
			$headerContent = DOM::create('span', $header);
		DOM::append($headerElement, $headerContent);
		
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
	 * 		{description}
	 */
	public function insertListItem($id = "", $content = NULL, $selected = FALSE)
	{
		// Set Menu Item Context
		$context = DOM::create("span", "", "", "sideMenuItemContext");
		$menuContext = $content;
		if (gettype($content) == "string")
			$menuContext = DOM::create("span", $content);
		DOM::append($context, $menuContext);
		
		// Create Menu Item
		$menuItem = parent::createMenuItem($id, $class = "sideMenuItem", $context);
		if ($selected)
			DOM::appendAttr($menuItem, "class", "selected");
		
		return $menuItem;			
	}
}
//#section_end#
?>