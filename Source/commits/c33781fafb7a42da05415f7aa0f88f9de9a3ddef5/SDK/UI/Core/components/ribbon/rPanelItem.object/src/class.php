<?php
//#section#[header]
// Namespace
namespace UI\Core\components\ribbon;

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
 * @namespace	\components\ribbon
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Protocol", "client/NavigatorProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * Ribbon Panel Item
 * 
 * Creates a simple panel item for the current ribbon control.
 * 
 * @version	1.0-1
 * @created	June 11, 2014, 9:55 (EEST)
 * @updated	June 20, 2015, 14:35 (EEST)
 */
class rPanelItem extends UIObjectPrototype
{
	/**
	 * Builds the panel item.
	 * 
	 * @param	mixed	$title
	 * 		The title of the panel item.
	 * 
	 * @param	string	$imgURL
	 * 		The image URL of the panel ico.
	 * 
	 * @param	boolean	$selected
	 * 		Defines whether the item is selected.
	 * 
	 * @return	rPanelItem
	 * 		The panel item object.
	 */
	public function build($title = "", $imgURL = "", $selected = FALSE)
	{
		// Parameter compatibility
		if ($title == "small" || $title == "big")
		{
			$title = $imgURL;
			$imgURL = $selected;
			
			$args = func_num_args();
			$selected = $args[3];
		}
		
		// Create item holder
		$item = DOM::create("a", "", "", "panelItem".($selected ? " selected" : ""));
		$this->set($item);
		
		// Create item title container
		$itemTitle = DOM::create("div", $title, "", "itemTitle");
		DOM::append($item, $itemTitle);
		
		// Create item image
		if (!empty($imgURL) && $imgURL !== TRUE)
		{
			$imgContainer = DOM::create('img', "", "", "itemImg");
			DOM::attr($imgContainer, "src", $imgURL);
			DOM::append($itemTitle, $imgContainer);
		}
		
		return $this;
	}
	
	/**
	 * Adds web navigation to the item
	 * 
	 * @param	string	$href
	 * 		The href attribute.
	 * 
	 * @param	string	$target
	 * 		The target attribute.
	 * 
	 * @return	void
	 */
	public function addWebNavigation($href, $target = "_self")
	{
		NavigatorProtocol::web($this->get(), $href, $target);
	}
}
//#section_end#
?>