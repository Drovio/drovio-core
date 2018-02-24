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

importer::import("API", "Platform", "DOM::DOM");
importer::import("UI", "Navigation", "menu");
importer::import("UI", "Navigation", "navigator");

use \API\Platform\DOM\DOM;
use \UI\Navigation\menu_prototype;
use \UI\Navigation\navigator;

/**
 * {title}
 * 
 * Usage
 * 
 * @version	{empty}
 * @created	May 8, 2013, 15:12 (EEST)
 * @revised	May 8, 2013, 15:12 (EEST)
 * 
 * @deprecated	Use \UI\Navigation\sideMenu instead.
 */
class simpleMenu extends menu_prototype
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $base_menu;
	
	/**
	 * Creates the navigation menu
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$header
	 * 		{description}
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @param	{type}	$inline
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function build_menu($id = "", $header = NULL, $class = "", $inline = TRUE)
	{
		$base_menu = $this->base_menu;
		
		// Create simpleMenu
		$mClass = "simpleMenu ".$class.($inline ? " inline" : "");
		$navMenu = parent::get_menu($id, $mClass);
		
		// Insert header
		if (!is_null($header) && $header != "")
			$this->insert_header($navMenu, $header);
			
		return $navMenu;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$menu
	 * 		{description}
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function insert_header($menu, $title = NULL)
	{
		// Create Header content
		if (gettype($title) == "string")
			$header_content = DOM::create('span', $title);
		else
			$header_content = $title;
		
		// Create header element
		$header = DOM::create("div", "", "", "simpleMenuHeader");
		DOM::append($header, $header_content);
		
		// Prepend header
		DOM::prepend($menu, $header);
		
		return $header;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$holder
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function insert_subList($holder)
	{
		// Get Sublist
		return parent::get_subList($holder);
	}
	
	/**
	 * Creates and inserts a listItem to $holder
	 * 
	 * @param	{type}	$holder
	 * 		{description}
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @param	{type}	$selected
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function insert_listItem($holder, $title, $class = "", $selected = FALSE)
	{
		$listItem = $this->get_listItem($title, $class, $selected);
		DOM::append($holder, $listItem);
		
		return $listItem;
	}
	
	/**
	 * Creates a list item
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @param	{type}	$selected
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_listItem($title, $class = "", $selected = FALSE)
	{
		// Create list item weblink
		$listItem_a = DOM::create("a");
		DOM::append($listItem, $listItem_a);
		
		// Pointer
		$pointer = DOM::create('div', "", "", "pointer");
		DOM::append($listItem_a, $pointer);
		
		$itemContent = $this->get_listItemContent($title);
		DOM::append($listItem_a, $itemContent);
		
		// Insert List Item
		$extraClass = ($class == "" ? "" : " ".$class).($selected ? " selected" : "");
		$listItem = parent::get_listItem($listItem_a, $class = "simpleMenuListItem".$extraClass);
		
		// Item ico
		$ico = DOM::create('div', "", "", "ico simpleMenuListItemIco");
		DOM::append($listItem, $ico);
		
		return $listItem;
	}
	
	/**
	 * Creates the container and the content of the weblink of the menu item
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_listItemContent($title)
	{
		$content = DOM::create("span", "", "", "simpleMenuListItemText");
		
		if (gettype($title) == "string")
			$listItem_text = DOM::create('span', $title);
		else
			$listItem_text = $title;
		DOM::append($content, $listItem_text);
		
		return $content;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$nav_ref
	 * 		{description}
	 * 
	 * @param	{type}	$nav_targetcontainer
	 * 		{description}
	 * 
	 * @param	{type}	$nav_targetgroup
	 * 		{description}
	 * 
	 * @param	{type}	$nav_navgroup
	 * 		{description}
	 * 
	 * @param	{type}	$nav_display
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function add_static_navigation($item, $nav_ref, $nav_targetcontainer, $nav_targetgroup, $nav_navgroup, $nav_display)
	{
		navigator::add_static_navigation($item, $nav_ref, $nav_targetcontainer, $nav_targetgroup, $nav_navgroup, $nav_display);
		return $item;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$navigation
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function add_web_navigation($item, $navigation)
	{
		navigator::add_web_navigation($item, $navigation['href'], $navigation['target']);
		return $item;
	}
}
//#section_end#
?>