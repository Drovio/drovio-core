<?php
//#section#[header]
// Namespace
namespace UI\Core\components\toolbar;

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
 * @package	Core
 * @namespace	\components\toolbar
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * Toolbar menu item.
 * 
 * Builds a toolbar menu item for all uses.
 * The toolbar item can be of 3 different scopes as mentioned in the build function, one for every case of visibility.
 * 
 * @version	0.1-1
 * @created	June 11, 2014, 9:43 (EEST)
 * @revised	July 16, 2014, 11:27 (EEST)
 */
class toolbarItem extends UIObjectPrototype
{
	/**
	 * The global scope attribute for the item.
	 * 
	 * @type	string
	 */
	const SCOPE_GLOBAL = "global";
	
	/**
	 * The page scope attribute for the item.
	 * 
	 * @type	string
	 */
	const SCOPE_PAGE = "page";
	
	/**
	 * The domain scope attribute for the item.
	 * 
	 * @type	string
	 */
	const SCOPE_DOMAIN = "domain";
	
	/**
	 * Builds the toolbar item.
	 * 
	 * @param	string	$title
	 * 		The item title (if any).
	 * 
	 * @param	string	$id
	 * 		The id of the item (if any).
	 * 
	 * @param	string	$class
	 * 		The class of the item (if any).
	 * 
	 * @param	string	$scope
	 * 		This defines the scope where the item will be visible.
	 * 		The acceptable values:
	 * 		- SCOPE_GLOBAL, for global appearance
	 * 		- SCOPE_DOMAIN, for domain appearance
	 * 		- SCOPE_PAGE, for this page only.
	 * 		The above indicate what items will be removed on the next clearance of the toolbar.
	 * 
	 * @param	mixed	$ico
	 * 		Defines whether this item will have an ico. It can be used as DOMElement to set the ico.
	 * 
	 * @return	toolbarItem
	 * 		The toolbarItem object.
	 */
	public function build($title = "", $id = "", $class = "", $scope = self::SCOPE_PAGE, $ico = TRUE)
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
		
		return $this;
	}
}
//#section_end#
?>