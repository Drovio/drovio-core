<?php
//#section#[header]
// Namespace
namespace UI\Presentation;

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
 * @package	Presentation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("ESS", "Prototype", "html/MenuPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \ESS\Prototype\html\MenuPrototype;
use \UI\Html\DOM;

/**
 * Tab Control
 * 
 * This is a tab control ui object.
 * It positions tabs on the top of the control.
 * 
 * @version	1.1-6
 * @created	April 26, 2013, 14:47 (EEST)
 * @updated	May 21, 2015, 11:26 (EEST)
 */
class tabControl extends UIObjectPrototype
{
	/**
	 * The control's id.
	 * 
	 * @type	strint
	 */
	private $id;
	/**
	 * The menu prototype used to build the tab menu.
	 * 
	 * @type	MenuPrototype
	 */
	private $menu;
	/**
	 * The tab's pages container.
	 * 
	 * @type	DOMElement
	 */
	private $pages;
	/**
	 * Defines whether the user can close (remove) tabs.
	 * 
	 * @type	boolean
	 */
	private $editable;
	
	/**
	 * Constructor method.
	 * Initializes object variables.
	 * 
	 * @param	boolean	$editable
	 * 		Defines whether the user can close (remove) a tab.
	 * 		It is FALSE by default.
	 * 
	 * @return	void
	 */
	public function __construct($editable = FALSE)
	{
		$this->editable = $editable;
		$this->menu = new MenuPrototype();
	}
	
	/**
	 * Builds the tab control.
	 * 
	 * @param	string	$id
	 * 		The control's id.
	 * 		It is empty by default.
	 * 
	 * @param	boolean	$full
	 * 		Defines whether the tab menu will have 100% width (and the tabs will stretch).
	 * 		It is FALSE by default.
	 * 
	 * @param	boolean	$withBorder
	 * 		Defines whether the tab control will have visual border.
	 * 		It is TRUE by default.
	 * 
	 * @return	tabControl
	 * 		The tabControl object.
	 */
	public function build($id = "", $full = FALSE, $withBorder = TRUE)
	{
		// Tabber Holder
		$this->id = (empty($id) ? "tbc".mt_rand() : $id);
		$tabberClass = "tabControl";
		$tabberClass .= ($full ? " full" : "");
		$tabberClass .= ($this->editable ? " editable" : "");
		$tabberClass .= ($withBorder ? " tbc_wb" : "");
		$tabber = DOM::create("div", "", $this->id, $tabberClass);
		$this->set($tabber);

		// Tabber Menu
		$tabs = $this->menu->build("", "tabs")->get();
		DOM::append($tabber, $tabs);
	
		// Tabber Pages
		$this->pages = DOM::create("div", "", "", "pages");
		DOM::append($tabber, $this->pages);
		
		return $this;
	}
	
	/**
	 * Inserts a new tab.
	 * 
	 * @param	string	$id
	 * 		The id of the tab.
	 * 
	 * @param	mixed	$header
	 * 		The header of the menu tab.
	 * 		It can be a string or a DOMElement.
	 * 
	 * @param	DOMElement	$page
	 * 		The page content.
	 * 
	 * @param	boolean	$selected
	 * 		Defines whether the tab menu item will be trigger focus upon insert.
	 * 		It is FALSE by default.
	 * 
	 * @return	tabControl
	 * 		The tabControl object.
	 */
	public function insertTab($id, $header, $page, $selected = FALSE)
	{
		// Get Container
		$container = $this->getTabberContainer($id, $header, $page, $this->id, $selected);
		
		// Append Elements
		$this->menu->insertMenuItem($container['menuItem']);
		DOM::append($this->pages, $container['tabPage']);
		
		return $this;
	}
	
	/**
	 * Gets the needed items to insert to the tabber through javascript.
	 * 
	 * @param	string	$id
	 * 		The id of the tab.
	 * 
	 * @param	mixed	$header
	 * 		The header of the menu tab.
	 * 		It can be a string or a DOMElement.
	 * 
	 * @param	DOMElement	$page
	 * 		The page content.
	 * 
	 * @param	string	$navID
	 * 		The navigation id for the menu navigation.
	 * 
	 * @param	boolean	$selected
	 * 		Defines whether the tab menu item will be trigger focus upon insert.
	 * 		It is FALSE by default.
	 * 
	 * @return	array
	 * 		Returns an array of the needed items for the tabber. It contains:
	 * 		['menuItem']: the menu item to go into the menu bar
	 * 		['tabPage']: the page to insert into the page pool.
	 */
	public function getTabberContainer($id, $header, $page, $navID = "", $selected = FALSE)
	{
		// Get the menu Item
		$navID = (empty($navID) ? $this->id : $navID);
		$menuItem = $this->getTabberMenuItem($header, $selected);
		$this->menu->addNavigation($menuItem, $id, $navID, "pageCollection_".$navID, "tabberGroup_tabber_".$navID, $display = "none");
		
		// Get the page
		$page = $this->getTabberPage($id, $page, $navID, $selected);
		
		$holder = array();
		$holder['menuItem'] = $menuItem;
		$holder['tabPage'] = $page;
		
		return $holder;
	}
	
	/**
	 * Builds and returns the page body of a tab.
	 * 
	 * @param	string	$id
	 * 		The tab's id.
	 * 
	 * @param	DOMElement	$content
	 * 		The page's content.
	 * 
	 * @param	string	$navID
	 * 		The navigation id for the menu navigation.
	 * 
	 * @param	boolean	$selected
	 * 		Defines whether this page will be visible or not.
	 * 		It is FALSE by default.
	 * 
	 * @return	DOMElement
	 * 		The page element.
	 */
	private function getTabberPage($id, $content = NULL, $navID = "", $selected = FALSE)
	{
		// Create a page Container
		$navID = (empty($navID) ? $this->id : $navID);
		$pageContainer = DOM::create("div", "", $id, "tabPageContainer".($selected ? "" : " noDisplay").($this->editable ? " editable" : ""));
		$this->menu->addNavigationSelector($pageContainer, "pageCollection_".$navID);
		
		// Insert content into page
		if (!empty($content))
			DOM::append($pageContainer, $content);
		
		return $pageContainer;
	}
	
	/**
	 * Creates and returns a tab menu item.
	 * 
	 * @param	mixed	$header
	 * 		The header of the menu tab.
	 * 		String or DOMElement (span).
	 * 
	 * @param	boolean	$selected
	 * 		Defines whether the tab menu item will be trigger focus upon insert.
	 * 		It is FALSE by default.
	 * 
	 * @return	DOMElement
	 * 		The menu item element.
	 */
	private function getTabberMenuItem($header, $selected = FALSE)
	{
		// Create item header
		$itemHeader = DOM::create("span", $header, "", "tabMenuItemText");
		
		// Create item
		$class = "tabMenuItem".($selected ? " selected" : "").($this->editable ? " editable" : "");
		$item = $this->menu->getMenuItem($id = "", $class, $itemHeader);
		
		// Insert closing button if editable
		if ($this->editable)
		{
			$closeBtn = DOM::create("div", "", "", "closeButton");
			DOM::append($item, $closeBtn);
		}
		
		return $item;
	}
}
//#section_end#
?>