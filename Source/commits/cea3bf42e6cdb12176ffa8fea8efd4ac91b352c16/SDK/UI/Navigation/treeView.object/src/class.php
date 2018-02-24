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
 * Tree View Control
 * 
 * Presents a navigation menu or data source in a tree view output.
 * 
 * @version	2.0-3
 * @created	May 6, 2013, 18:49 (EEST)
 * @updated	May 21, 2015, 16:47 (EEST)
 */
class treeView extends MenuPrototype
{
	/**
	 * The array of sublists as an associative array by item id.
	 * 
	 * @type	array
	 */
	protected $subLists;
	
	/**
	 * Constructor Method.
	 * Initializes object's variables and properties.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Initialize sublists
		$this->subLists = array();
	}
	
	/**
	 * Builds the object's model.
	 * 
	 * @param	string	$id
	 * 		The id of the view.
	 * 
	 * @param	string	$class
	 * 		The extra class of the view.
	 * 
	 * @return	treeView
	 * 		The treeView itself.
	 */
	public function build($id = "", $class = "")
	{
		// Create Root Menu
		$id = (empty($id) ? "trv".mt_rand() : $id);
		parent::build($id, "treeView".(empty($class) ? "" : " ".$class));

		// Set Root List Class
		HTML::addClass($this->menuList, "treeViewList");
		
		return $this;
	}
	
	/**
	 * Inserts an expandable tree Item inside a given parent (by id).
	 * Expandable is the tree item that expands / collapses its list by clicking on it.
	 * 
	 * @param	string	$id
	 * 		The item's id.
	 * 
	 * @param	mixed	$content
	 * 		The item's content, it can be string or DOMElement.
	 * 
	 * @param	string	$parentId
	 * 		The parent item's id. If empty, the item will be inserted to the root.
	 * 
	 * @param	boolean	$open
	 * 		If set to TRUE the tree item will be initially open.
	 * 
	 * @return	DOMElement
	 * 		The inserted item.
	 */
	public function insertExpandableTreeItem($id, $content, $parentId = "", $open = FALSE)
	{
		// Build Tree Item
		$listItem = $this->buildTreeItem($id, $content, TRUE, "expandable");
		if ($open)
			HTML::addClass($listItem, "open");
			
		// Insert to parent or to root
		$this->append($listItem, $parentId);
		
		// Return item
		return $listItem;
	}
	
	/**
	 * Inserts a semi-expandable tree Item inside a given parent (by id).
	 * Semi-expandable is the tree item that expands / collapses only when clicking its pointer
	 * 
	 * @param	string	$id
	 * 		The item's id.
	 * 
	 * @param	mixed	$content
	 * 		The item's content, it can be string or DOMElement.
	 * 
	 * @param	string	$parentId
	 * 		The parent item's id. If empty, the item will be inserted to the root.
	 * 
	 * @param	boolean	$open
	 * 		If set to TRUE the tree item will be initially open.
	 * 
	 * @return	DOMElement
	 * 		The inserted item.
	 */
	public function insertSemiExpandableTreeItem($id, $content, $parentId = "", $open = FALSE)
	{
		// Build Tree Item
		$listItem = $this->buildTreeItem($id, $content, TRUE, "semi-expandable");
		if ($open)
			HTML::addClass($listItem, "open");
		
		// Insert to parent or to root
		$this->append($listItem, $parentId);
		
		// Return item
		return $listItem;
	}
	
	/**
	 * Inserts a normal tree Item inside a given parent (by id).
	 * 
	 * @param	string	$id
	 * 		The item's id.
	 * 
	 * @param	mixed	$content
	 * 		The item's content, it can be string or DOMElement.
	 * 
	 * @param	string	$parentId
	 * 		The parent item's id. If empty, the item will be inserted to the root.
	 * 
	 * @return	DOMElement
	 * 		The inserted item.
	 */
	public function insertTreeItem($id, $content, $parentId = "")
	{
		// Build Tree Item
		$listItem = $this->buildTreeItem($id, $content);
		
		// Insert to parent or to root
		$this->append($listItem, $parentId);
		
		// Return item
		return $listItem;
	}
	
	/**
	 * Inserts a normal tree Item inside a given parent (by id).
	 * 
	 * @param	string	$id
	 * 		The item's id.
	 * 
	 * @param	DOMElement	$content
	 * 		The item's content.
	 * 
	 * @param	string	$parentId
	 * 		The parent item's id. If empty, the item will be inserted to the root.
	 * 
	 * @return	DOMElement
	 * 		The inserted item.
	 * 
	 * @deprecated	Use $this->insertTreeItem() instead.
	 */
	public function insertItem($id, $content, $parentId = "")
	{
		return $this->insertTreeItem($id, $content, $parentId);
	}
	
	/**
	 * Appends a given listItem to the root or to a given parent.
	 * 
	 * @param	DOMElement	$listItem
	 * 		The listItem to be appended.
	 * 
	 * @param	string	$parentId
	 * 		The parent item's id. If empty, the item will be inserted to the root.
	 * 
	 * @return	void
	 */
	public function append($listItem, $parentId = "")
	{
		$parent = $this->menuList;
		if (!empty($parentId) && isset($this->subLists["sub_".$parentId]) && !is_null($this->subLists["sub_".$parentId]))
			$parent = $this->subLists["sub_".$parentId];
		
		
		DOM::append($parent, $listItem);
	}
	
	/**
	 * Builds the treeItem according to attributes given.
	 * 
	 * @param	string	$id
	 * 		The item's id.
	 * 
	 * @param	mixed	$content
	 * 		The item's content, it can be string or DOMElement.
	 * 
	 * @param	boolean	$hasSubList
	 * 		Defines whether this item will have a sublist.
	 * 
	 * @param	string	$expandable
	 * 		Defines whether this item will be expandable, semi-expandable or none.
	 * 
	 * @return	DOMelement
	 * 		The created tree item
	 */
	protected function buildTreeItem($id, $content = NULL, $hasSubList = FALSE, $expandable = "")
	{
		// Create content container
		$contentWrapper = DOM::create("div", $content, "", "treeItemContent");
		
		// Build tree item
		$item = parent::getMenuItem($id, $class = "treeItem", $contentWrapper);
		
		// Set expandable
		if (!empty($expandable))
			DOM::attr($item, "expandable", $expandable);
		
		// Assign sort value
		$sortValue = (is_string($content) ? $content : $content->nodeValue);
		$this->assignSortValue($item, $sortValue);
		
		// Return if no sublist
		if (!$hasSubList)
			return $item;
			
		// Tree Item Pointer
		$pointer = DOM::create("div", "", "", "treePointer");
		DOM::prepend($item, $pointer);
		
		// Sub List 
		$subMenu = new MenuPrototype();
		$subList = $subMenu->build("", "subTreeView")->get();
		DOM::append($item, $subList);
		
		// Keep sublist
		$this->subLists["sub_".$id] = DOM::evaluate("ul", $subList)->item(0);
		
		return $item;
	}
	
	/**
	 * Assigns a sorting value to a tree item
	 * 
	 * @param	DOMElement	$item
	 * 		The tree item
	 * 
	 * @param	string	$value
	 * 		The value to be assign for sorting
	 * 
	 * @return	void
	 */
	public function assignSortValue($item, $value)
	{
		DOM::attr($item, "sort-value", $value);
	}
	
	/**
	 * Returns the contents of the tree item
	 * 
	 * @param	DOMElement	$item
	 * 		A tree item.
	 * 
	 * @return	DOMElement
	 * 		The contents node of the item
	 */
	public function getTreeItemContent($item)
	{
		return DOM::evaluate("*[@class='treeItemContent']", $item)->item(0);
	}
}
//#section_end#
?>