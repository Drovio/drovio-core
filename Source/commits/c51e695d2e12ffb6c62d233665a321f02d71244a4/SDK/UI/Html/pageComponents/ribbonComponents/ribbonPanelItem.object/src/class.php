<?php
//#section#[header]
// Namespace
namespace UI\Html\pageComponents\ribbonComponents;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Html
 * @namespace	\pageComponents\ribbonComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::NavigatorProtocol");
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
 * @version	{empty}
 * @created	April 5, 2013, 10:07 (EEST)
 * @revised	April 5, 2013, 10:07 (EEST)
 */
class ribbonPanelItem extends UIObjectPrototype
{
	/**
	 * Builds the panel item.
	 * 
	 * @param	string	$type
	 * 		Defines the vertical size of the item.
	 * 		Accepted values:
	 * 		- "small"
	 * 		- "big"
	 * 		If none of the above, the "small" will be selected.
	 * 
	 * @param	mixed	$title
	 * 		The title of the panel.
	 * 		This can be string or DOMElement.
	 * 
	 * @param	string	$imgURL
	 * 		The image URL of the panel ico.
	 * 
	 * @param	boolean	$selected
	 * 		Defines whether the item is selected.
	 * 
	 * @return	ribbonPanelItem
	 */
	public function build($type = "small", $title = "", $imgURL = "", $selected = FALSE)
	{
		// Correct type
		$type = ($type != "small" && $type != "big" ? "small" : $type);
		
		// Create item holder
		$item = DOM::create("a", "", "", "panelItem $type".($selected ? " selected" : ""));
		$this->set($item);
		
		// Create item title container
		$itemTitle = DOM::create("div", "", "", "itemTitle");
		DOM::append($item, $itemTitle);
		
		// Create item image
		if ($imgURL != "")
		{
			$imgContainer = DOM::create('img', "", "", "itemImg");
			DOM::attr($imgContainer, "src", $imgURL);
			DOM::append($itemTitle, $imgContainer);
		}
		
		// Insert item title
		if (gettype($title) == "string")
		{
			$titleContent = DOM::create('span', $title, "", "title");
			DOM::attr($itemTitle, 'title', $title);
		}
		else
		{
			$titleContent = $title;
			DOM::attr($itemTitle, 'title', $title->nodeValue);
		}
		DOM::append($itemTitle, $titleContent);
		
		return $this;
	}
	
	/**
	 * Adds web navigation to the item
	 * 
	 * @param	string	$href
	 * 		The href attribute
	 * 
	 * @param	string	$target
	 * 		The target attribute
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