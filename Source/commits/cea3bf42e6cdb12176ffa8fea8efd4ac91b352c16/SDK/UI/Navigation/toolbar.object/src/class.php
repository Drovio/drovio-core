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

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Navigation", "toolbarComponents/toolbarItem");
importer::import("UI", "Navigation", "toolbarComponents/toolbarSeparator");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Navigation\toolbarComponents\toolbarItem;
use \UI\Navigation\toolbarComponents\toolbarSeparator;

/**
 * Navigation Toolbar
 * 
 * Builds a navigation toolbar with a given orientation and position.
 * 
 * @version	0.2-2
 * @created	May 8, 2013, 14:51 (EEST)
 * @updated	May 21, 2015, 16:59 (EEST)
 */
abstract class toolbar extends UIObjectPrototype
{
	/**
	 * Builds the toolbar.
	 * It returns the holder. In the future this will return the toolbar. It should not be used as a return value for building content.
	 * Use build() and get() instead.
	 * 
	 * @param	string	$dock
	 * 		The dock position of the toolbar.
	 * 		Possible values:
	 * 		T - Top, Horizontal
	 * 		B - Bottom, Horizontal
	 * 		L - Left, Vertical
	 * 		R - Right, Vertical
	 * 
	 * @param	string	$id
	 * 		The toolbar unique id.
	 * 		If empty, it will get a random unique id.
	 * 		It is empty by default.
	 * 
	 * @param	string	$class
	 * 		The toolbar extra class for custom styling.
	 * 		It is empty by default.
	 * 
	 * @return	toolbar
	 * 		The toolbar object.
	 */
	public function build($dock = "T", $id = "", $class = "")
	{
		// Build toolbar holder
		$id = (empty($id) ? "tlb_".mt_rand() : $id);
		$holder = DOM::create("div", "", $id, "uiToolbar");
		$this->set($holder);
		
		// Add extra classes
		HTML::addClass($holder, "orient".($dock == "B" | $dock == "T" ? "H" : "V"));
		HTML::addClass($holder, "dock".strtoupper($dock));
		HTML::addClass($holder, $class);
		
		return $this;
	}
	
	/**
	 * Inserts an item into the toolbar.
	 * 
	 * @param	DOMElement	$content
	 * 		The tool to be inserted.
	 * 
	 * @return	void
	 */
	public function insertTool($content)
	{
		if (empty($content))
			return NULL;
			
		// Append Tool Content
		DOM::append($this->get(), $content);
	}
	
	/**
	 * Inserts a toolbarItem into the toolbar.
	 * 
	 * @param	DOMElement	$content
	 * 		The toolbarItem's content.
	 * 
	 * @return	void
	 */
	public function insertToolbarItem($content)
	{
		if (empty($content))
			return NULL;
		
		// Build toolbar Item
		$tItem = new toolbarItem();
		$tContent = $tItem->build($content)->get();
		
		// Append item
		$this->insertTool($tContent);
	}
	
	/**
	 * Insert a toolbar separator.
	 * 
	 * @return	void
	 */
	public function insertSeparator()
	{
		// Create separator
		$tSeparator = new toolbarSeparator();
		$tContent = $tSeparator->build()->get();
		
		// Append the item
		$this->insertTool($tContent);
	}
	
	/**
	 * Prepares the parent's style for getting the toolbar.
	 * 
	 * @param	DOMElement	$parent
	 * 		The parent element.
	 * 
	 * @param	string	$dock
	 * 		The toolbar's dock position.
	 * 		See build() for more information.
	 * 
	 * @return	void
	 */
	public static function setParent($parent, $dock = "T")
	{
		HTML::addClass($parent, "toolbarContainer dock".$dock);
	}
	
	/**
	 * Insert a tool
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use insertTool() instead.
	 */
	public function insert_tool($content)
	{
		return $this->insertTool($content);
	}
	
	/**
	 * Insert a toolbar separator
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use insertSeparator() instead.
	 */
	public function insert_separator()
	{
		return $this->insertSeparator();
	}
}
//#section_end#
?>