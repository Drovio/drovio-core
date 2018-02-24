<?php
//#section#[header]
// Namespace
namespace UI\Navigation\toolbarComponents;

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
 * @namespace	\toolbarComponents
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Prototype", "html/MenuPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Navigation", "toolbarComponents/toolbarItem");

use \ESS\Prototype\html\MenuPrototype;
use \UI\Html\DOM;
use \UI\Navigation\toolbarComponents\toolbarItem;

/**
 * Navigation Toolbar Menu
 * 
 * Navigation Toolbar Menu
 * 
 * @version	1.0-1
 * @created	November 3, 2013, 19:56 (EET)
 * @updated	June 24, 2015, 23:58 (EEST)
 */
class toolbarMenu extends toolbarItem
{
	/**
	 * The MenuPrototype object.
	 * 
	 * @type	MenuPrototype
	 */
	private $menu;
	
	/**
	 * Builds the toolbar menu.
	 * 
	 * @param	mixed	$header
	 * 		The toolbar item header.
	 * 
	 * @param	string	$id
	 * 		The menu id.
	 * 
	 * @param	string	$class
	 * 		The item class.
	 * 
	 * @return	toolbarMenu
	 * 		The toolbarMenu object.
	 */
	public function build($header = "", $id = "", $class = "")
	{
		// Create the toolbar item
		$class = trim($class." tlbMenuHeader");
		$toolbarItem = parent::build($header, $class)->get();
		
		// Build the menu
		$this->menu = new MenuPrototype();
		$menuContainer = $this->menu->build($id, "toolbarItemMenuContainer")->get();
		DOM::append($toolbarItem, $menuContainer);
		
		return $this;
	}
	
	/**
	 * Inserts a menu item.
	 * 
	 * @param	string	$header
	 * 		The item header.
	 * 
	 * @param	string	$id
	 * 		The item id.
	 * 
	 * @return	toolbarMenu
	 * 		The toolbar menu object.
	 */
	public function insertMenuItem($header, $id = "")
	{
		// Create the menu Item
		$headerElement = DOM::create("span", $header, "", "itemHeader");
		return $this->menu->createMenuItem($id, $class = "subMenuItem", $headerElement);
	}
}
//#section_end#
?>