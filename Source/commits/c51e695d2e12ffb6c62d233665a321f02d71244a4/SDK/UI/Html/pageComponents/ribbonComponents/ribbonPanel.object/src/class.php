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

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "pageComponents::ribbonComponents::ribbonPanelItem");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;
use \UI\Html\pageComponents\ribbonComponents\ribbonPanelItem;

/**
 * Ribbon Panel
 * 
 * Builds a ribbon panel holder
 * 
 * @version	{empty}
 * @created	April 5, 2013, 10:07 (EEST)
 * @revised	April 5, 2013, 10:25 (EEST)
 */
class ribbonPanel extends UIObjectPrototype
{
	/**
	 * The panel container. Different if grouped.
	 * 
	 * @type	DOMElement
	 */
	private $panelContainer;
	
	/**
	 * Builds the panel
	 * 
	 * @param	string	$id
	 * 		The panel id
	 * 
	 * @param	boolean	$grouped
	 * 		Indicates whether this panel will container one or two items.
	 * 
	 * @return	ribbonPanel
	 */
	public function build($id = "", $grouped = FALSE)
	{
		// Create holder
		$this->panelContainer = DOM::create("div", "", $id, "ribbonPanel");
		$this->set($this->panelContainer);
		
		// Insert Group
		if ($grouped)
		{
			$this->panelContainer = DOM::create("div", "", "", "panelGroup");
			DOM::append($this->get(), $this->panelContainer);
		}
		
		return $this;
	}
	
	/**
	 * Create and insert a panel item.
	 * 
	 * @param	string	$type
	 * 		The panel item type.
	 * 		See ribbonPanelItem.
	 * 
	 * @param	string	$title
	 * 		The panel title.
	 * 		See ribbonPanelItem.
	 * 
	 * @param	string	$imgURL
	 * 		The panel ico URL.
	 * 		See ribbonPanelItem.
	 * 
	 * @param	boolean	$selected
	 * 		The panel selected property.
	 * 		See ribbonPanelItem.
	 * 
	 * @return	DOMElement
	 */
	public function insertPanelItem($type = "small", $title = "", $imgURL = "", $selected = FALSE)
	{
		$ribbonItem = new ribbonPanelItem();
		$panelItem = $ribbonItem->build($type, $title, $imgURL, $selected)->get();
		DOM::append($this->panelContainer, $panelItem);
		
		return $panelItem;
	}
}
//#section_end#
?>