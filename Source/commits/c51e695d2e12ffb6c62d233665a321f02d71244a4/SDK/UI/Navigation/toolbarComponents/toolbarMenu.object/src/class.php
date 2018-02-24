<?php
//#section#[header]
// Namespace
namespace UI\Navigation\toolbarComponents;

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
 * @namespace	\toolbarComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "html::MenuPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Navigation", "toolbarComponents::toolbarItem");

use \ESS\Prototype\html\MenuPrototype;
use \UI\Html\DOM;
use \UI\Navigation\toolbarComponents\toolbarItem;

/**
 * Navigation Toolbar Menu
 * 
 * Navigation Toolbar Menu
 * 
 * @version	{empty}
 * @created	November 3, 2013, 19:56 (EET)
 * @revised	November 3, 2013, 19:56 (EET)
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
	 * @param	string	$id
	 * 		The menu id.
	 * 
	 * @return	toolbarMenu
	 * 		The toolbarMenu object.
	 */
	public function build($id = "")
	{
		// Build the menu
		$this->menu = new MenuPrototype();
		$menuContainer = $this->menu->build($id, "toolbarMenuContainer")->get();
		
		// Create toolbar item
		return parent::build($menuContainer, $class = "toolbarMenu");
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