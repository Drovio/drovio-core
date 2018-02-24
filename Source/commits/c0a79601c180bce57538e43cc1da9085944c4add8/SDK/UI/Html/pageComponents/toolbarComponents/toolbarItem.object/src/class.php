<?php
//#section#[header]
// Namespace
namespace UI\Html\pageComponents\toolbarComponents;

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
 * @package	Html
 * @namespace	\pageComponents\toolbarComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * Toolbar Menu Item
 * 
 * Builds a toolbar menu item
 * 
 * @version	{empty}
 * @created	April 11, 2013, 10:39 (EEST)
 * @revised	October 15, 2013, 14:37 (EEST)
 */
class toolbarItem extends UIObjectPrototype
{
	/**
	 * Constructor Method
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Put your constructor method code here.
	}
	
	/**
	 * Builds the UI Object holder.
	 * 
	 * @param	string	$title
	 * 		The item title (if any)
	 * 
	 * @param	string	$id
	 * 		The id of the item
	 * 
	 * @param	string	$class
	 * 		The class of the item
	 * 
	 * @param	string	$scope
	 * 		This defines the scope where the item will be visible.
	 * 		The acceptable values:
	 * 		- "global", for global appearance
	 * 		- "domain", for domain appearance
	 * 		- "page", for this page only.
	 * 		The above indicate what items will be removed on the next clearance of the toolbar.
	 * 
	 * @param	mixed	$ico
	 * 		Defines whether this item will have an ico. It can be used as DOMElement to set the ico.
	 * 
	 * @param	integer	$index
	 * 		The item's index position among other items in the toolbar.
	 * 
	 * @return	toolbarItem
	 * 		{description}
	 */
	public function build($title = "", $id = "", $class = "", $scope = "page", $ico = TRUE, $index = 0)
	{
		$item = DOM::create("a", "", $id, "tlbNavItem".($class == "" ? "" : " ".$class));
		$this->set($item);

		// Navigation Item Header
		$hd = DOM::create('div', "", "", "navMenuHeader");
		$hd_title = DOM::create('span', $title, "", "title");
		DOM::append($hd, $hd_title);
		
		// Create item ico
		if ($ico)
		{
			$hd_ico = DOM::create('span', "", "", "navIco");
			DOM::append($hd, $hd_ico);
			
			// If ico is object, insert
			if ($ico !== TRUE)
				DOM::append($hd_ico, $ico);
		}

		DOM::append($item, $hd);
		
		// Set Item Scope
		$type = array();
		$type['scope'] = $scope;
		DOM::data($item, 'tlb-nav', $type);
		
		// Set Item Position Index
		$pos = array();
		$pos['index'] = $index;
		DOM::data($item, "instr", $pos);
		
		return $this;
	}
}
//#section_end#
?>