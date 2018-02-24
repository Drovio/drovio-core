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

use \API\Platform\DOM\DOM;

/**
 * {title}
 * 
 * Usage
 * 
 * @version	{empty}
 * @created	April 26, 2013, 15:03 (EEST)
 * @revised	April 26, 2013, 15:03 (EEST)
 * 
 * @deprecated	Use \ESS\Prototype\html\MenuPrototype instead.
 */
class menu_prototype
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $menu;
	
	/**
	 * _____ Constructor
	 * 
	 * @return	void
	 */
	public function __construct()
	{
	}
	
	/**
	 * Create a menu holder
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_menu($id = "", $class = "")
	{
		// Create container
		$container = DOM::create("div", "", $id, $class);
		
		// Create menu
		$this->menu = DOM::create("ul");
		DOM::append($container, $this->menu);
		
		return $container;
	}
	
	/**
	 * Inserts a list item to a given subList
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function insert_listItem($content, $item = NULL, $class = "")
	{
		// Create List Item
		$listItem = $this->get_listItem($content, $class);
		
		// Append List Item to subList
		
		$subList = (is_null($item) ? $this->menu : $this->get_subList($item));
		DOM::append($subList, $listItem);
		
		return $listItem;
	}

	/**
	 * Returns the subList of the given item (if any)
	 * 
	 * @param	{type}	$listItem
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_subList($listItem)
	{
		$subList = DOM::evaluate("ul", $listItem);
		
		// If subList exists, return subList
		if ($subList->length != 0)
			return $subList->item(0);
		else
			return $this->insert_subList($listItem);
		
		// Else, return NULL
		return NULL;
	}
	
	/**
	 * Create a list item
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_listItem($content, $class = "")
	{
		// Create List Item
		$listItem = DOM::create("li", "", "", $class);
		DOM::append($listItem, $content);
		
		return $listItem;
	}
	
	/**
	 * Inserts a sublist to a given list item
	 * 
	 * @param	{type}	$listItem
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function insert_subList($listItem)
	{
		// Create subList
		$subList = DOM::create("ul");
		DOM::append($listItem, $subList);
		
		return $subList;
	}
}
//#section_end#
?>