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
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "html::MenuPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\html\MenuPrototype;
use \UI\Html\DOM;

// previous compatibility
importer::import("UI", "Navigation", "menu");
use \UI\Navigation\menu_prototype;

/**
 * Tree View Control
 * 
 * Presents a navigation menu or data source in a tree view output.
 * 
 * @version	{empty}
 * @created	May 6, 2013, 18:49 (EEST)
 * @revised	September 18, 2013, 18:42 (EEST)
 */
class treeView extends MenuPrototype
{	
	/**
	 * Defines whether the view will be sorted.
	 * 
	 * @type	boolean
	 */
	protected $sorting = FALSE;
	
	/**
	 * The array of sublists as an associative array by item id.
	 * 
	 * @type	array
	 */
	protected $subLists;
	
	/**
	 * DEPRECATED. The tree views menu prototype.
	 * 
	 * @type	menu_prototype
	 */
	private $mp;
	
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
		// previous compatibility
		$this->mp = new menu_prototype();
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
	 * @param	boolean	$sorting
	 * 		Defines whether the treeView will be sortable.
	 * 
	 * @return	treeView
	 * 		The treeView itself.
	 */
	public function build($id = "", $class = "", $sorting = FALSE)
	{
		// Create Root Menu
		$id = (empty($id) ? "treeView_".rand() : $id);
		parent::build($id, "treeView".($class == "" ? "" : " $class"));
		$this->sorting = $sorting;

		// Set Root List Class
		DOM::appendAttr($this->menuList, "class", "treeViewList");
		
		return $this;
	}
	
	/**
	 * Inserts an expandable tree Item inside a given parent (by id).
	 * Expandable is the tree item that expands / collapses its list by clicking on it.
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
			DOM::appendAttr($listItem, "class", "open");
			
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
	 * @param	DOMElement	$content
	 * 		The item's content.
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
			DOM::appendAttr($listItem, "class", "open");
		
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
	 * @param	DOMElement	$content
	 * 		The item's content.
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
		$contentWrapper = DOM::create("div", "", "", "treeItemContent");
		if (!is_null($content))
			DOM::append($contentWrapper, $content);
		
		// Build tree item
		$item = parent::getMenuItem($id, $class = "treeItem", $contentWrapper);
		
		if (!empty($expandable))
			DOM::attr($item, "expandable", $expandable);
		
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
	 * Inserts an expandable tree Item inside a given parent.
	 * Expandable is the tree item that expands / collapses its list by clicking on it.
	 * 
	 * @param	DOMElement	$container
	 * 		The parent container
	 * 
	 * @param	string	$id
	 * 		The item's id
	 * 
	 * @param	DOMElement	$content
	 * 		The item's content
	 * 
	 * @return	DOMElement
	 * 		The inserted item.
	 * 
	 * @deprecated	Use $this->insertExpandableTreeItem() instead.
	 */
	public function insert_expandableTreeItem($container, $id, $content)
	{
		$baseList = $this->mp->get_subList($container);
		
		$listItem = $this->_get_treeItem($id, $content, TRUE, "expandable");
		
		DOM::append($baseList, $listItem);
		
		return $listItem;
	}
	
	/**
	 * Inserts a semi-expandable tree Item inside a given parent (by id).
	 * Semi-expandable is the tree item that expands / collapses only when clicking its pointer
	 * 
	 * @param	DOMElement	$container
	 * 		The parent container
	 * 
	 * @param	string	$id
	 * 		The item's id
	 * 
	 * @param	DOMElement	$content
	 * 		The item's content
	 * 
	 * @return	DOMElement
	 * 		The inserted item.
	 * 
	 * @deprecated	Use $this->insertSemiExpandableTreeItem() instead.
	 */
	public function insert_semiExpandableTreeItem($container, $id, $content)
	{
		$baseList = $this->mp->get_subList($container);
		
		$listItem = $this->_get_treeItem($id, $content, TRUE, "semi-expandable");
		
		DOM::append($baseList, $listItem);
		
		return $listItem;
	}
	
	/**
	 * Inserts a tree Item that cannot be expanded inside a container or container's list
	 * 
	 * @param	DOMElement	$container
	 * 		The parent container
	 * 
	 * @param	string	$id
	 * 		The item's id
	 * 
	 * @param	string	$content
	 * 		The item's content
	 * 
	 * @return	DOMElement
	 * 		The inserted item
	 * 
	 * @deprecated	Use $this->insertTreeItem() instead.
	 */
	public function insert_treeItem($container, $id = "", $content)
	{
		$baseList = $this->mp->get_subList($container);
		
		$listItem = $this->_get_treeItem($id, $content);
		
		DOM::append($baseList, $listItem);
		
		return $listItem;
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
	 * 
	 * @deprecated	Use $this->assignSortValue() instead.
	 */
	public function add_sortValue($item, $value)
	{
		$this->assignSortValue($item, $value);
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
		if ($this->sorting)
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
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @param	{type}	$hasSubList
	 * 		{description}
	 * 
	 * @param	{type}	$expandable
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use $this->buildTreeItem() instead.
	 */
	private function _get_treeItem($id, $content = NULL,  $hasSubList = FALSE, $expandable = "")
	{
		$contentWrapper = DOM::create("div", "", "", "treeItemContent");
		DOM::append($contentWrapper, $content);
		
		$item = $this->mp->get_listItem($contentWrapper, "treeItem");
		DOM::attr($item, "id", $id);
		DOM::attr($item, "expandable", $expandable);
		
		if (!$hasSubList)
			return $item;
			
		// Tree Item Pointer
		$pointer = DOM::create('div', "", "", "treePointer");
		DOM::prepend($item, $pointer);
		
		$view = $this->mp->get_subList($item);
		DOM::appendAttr($view, "class", "treeViewList");
		
		return $item;
	}	
	
	/**
	 * Creates the root list
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @param	{type}	$sorting
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use build()->get() instead.
	 */
	public function get_view($id = "", $class = "", $sorting = FALSE)
	{
		$id = ($id == "" ? "treeView_".rand() : $id);
		$class = "treeView".($class == "" ? "" : " $class");
		return $this->build($id, $class, $sorting)->get(); 
	}
}
//#section_end#
?>