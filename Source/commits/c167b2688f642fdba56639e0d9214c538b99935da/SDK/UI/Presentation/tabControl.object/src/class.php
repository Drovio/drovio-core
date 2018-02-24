<?php
//#section#[header]
// Namespace
namespace UI\Presentation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Presentation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("ESS", "Prototype", "html::MenuPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \ESS\Prototype\html\MenuPrototype;
use \UI\Html\DOM;

/**
 * Tab Control
 * 
 * A tab control item.
 * The tabs are on the top of the control.
 * 
 * @version	{empty}
 * @created	April 26, 2013, 14:47 (EEST)
 * @revised	April 26, 2013, 15:07 (EEST)
 */
class tabControl extends UIObjectPrototype
{
	/**
	 * The control's id
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
	 * 
	 * @return	void
	 */
	public function __construct($editable = FALSE)
	{
		$this->editable = $editable;
		$this->menu = new MenuPrototype();
	}
	
	/**
	 * Builds the tab control
	 * 
	 * @param	string	$id
	 * 		The control's id.
	 * 
	 * @param	boolean	$full
	 * 		Defines whether the tab menu will have 100% width.
	 * 
	 * @return	tabControl
	 */
	public function build($id = "", $full = FALSE)
	{
		// Tabber Holder
		$this->id = ($id == "" ? "tabber_".rand() : $id);
		$tabberClass = "tabControl".($full ? " full" : "").($this->editable ? " editable" : "");
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
	 * 		String or DOMElement (span).
	 * 
	 * @param	DOMElement	$page
	 * 		The page content.
	 * 
	 * @param	boolean	$selected
	 * 		Defines whether the tab menu item will be trigger focus upon insert.
	 * 
	 * @return	void
	 */
	public function insertTab($id, $header, $page, $selected = FALSE)
	{
		// Get Container
		$container = $this->getTabberContainer($id, $header, $page, $this->id, $selected);
		
		// Append Elements
		$this->menu->insertMenuItem($container['menuItem']);
		DOM::append($this->pages, $container['tabPage']);
	}
	
	/**
	 * Returns an array of the needed items to insert to the tabber through javascript.
	 * 
	 * @param	string	$id
	 * 		The id of the tab.
	 * 
	 * @param	mixed	$header
	 * 		The header of the menu tab.
	 * 		String or DOMElement (span).
	 * 
	 * @param	DOMElement	$page
	 * 		The page content.
	 * 
	 * @param	string	$navID
	 * 		The navigation id for the menu navigation.
	 * 
	 * @param	boolean	$selected
	 * 		Defines whether the tab menu item will be trigger focus upon insert.
	 * 
	 * @return	array
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
	 * 
	 * @return	DOMElement
	 */
	private function getTabberPage($id, $content = NULL, $navID = "", $selected = FALSE)
	{
		// Create a page Container
		$navID = (empty($navID) ? $this->id : $navID);
		$pageContainer = DOM::create("div", "", $id, "tabPageContainer".($selected ? "" : " noDisplay").($this->editable ? " editable" : ""));
		$this->menu->addNavigationSelector($pageContainer, "pageCollection_".$navID);
		
		// Insert content into page
		if (!is_null($content))
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
	 * 
	 * @return	DOMElement
	 */
	private function getTabberMenuItem($header, $selected = FALSE)
	{
		// Create the item
		$class = "tabMenuItem".($selected ? " selected" : "").($this->editable ? " editable" : "");
		
		if (gettype($header) == "string")
			$itemHeader = DOM::create("span", $header, "", "tabMenuItemText");
		else
		{
			$itemHeader = DOM::create("span", "", "", "tabMenuItemText");	
			DOM::append($itemHeader, $header);
		}
		
		$item = $this->menu->getMenuItem($id = "", $class, $itemHeader);
		
		// Insert closing button if editable
		if ($this->editable)
		{
			$closeBtn = DOM::create("div", "", "", "closeButton");
			DOM::append($item, $closeBtn);
		}
		
		return $item;
	}
	
	/**
	 * Creates the tabber
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$full
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use build() and get() instead.
	 */
	public function get_control($id = "", $full = FALSE)
	{
		return $this->build($id, $full)->get();
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$header
	 * 		{description}
	 * 
	 * @param	{type}	$page
	 * 		{description}
	 * 
	 * @param	{type}	$selected
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use insertTab() instead.
	 */
	public function insert_tab($id, $header, $page, $selected = FALSE)
	{
		$this->insertTab($id, $header, $page, $selected);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$header
	 * 		{description}
	 * 
	 * @param	{type}	$page
	 * 		{description}
	 * 
	 * @param	{type}	$navContainerID
	 * 		{description}
	 * 
	 * @param	{type}	$selected
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use getTabberContainer() instead.
	 */
	public function get_tabContainer($id, $header, $page, $navContainerID, $selected = FALSE)
	{
		return $this->getTabberContainer($id, $header, $page, $navContainerID, $selected);
	}
	
	/**
	 * Creates a menuItem for the tabs with the given header
	 * 
	 * @param	{type}	$header
	 * 		{description}
	 * 
	 * @param	{type}	$selected
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use getTabberMenuItem() instead.
	 */
	private function get_tabMenuItem($header, $selected = FALSE)
	{
		return $this->getTabberMenuItem($header, $selected);
	}
	
	/**
	 * Creates a page to insert into the page collection with the given content
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @param	{type}	$selected
	 * 		{description}
	 * 
	 * @param	{type}	$nav_id
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use getTabberPage() instead.
	 */
	private function get_tabPage($id, $content = NULL, $selected = FALSE, $nav_id = "")
	{
		return $this->getTabberPage($id, $content, $selected);
	}
}
//#section_end#
?>