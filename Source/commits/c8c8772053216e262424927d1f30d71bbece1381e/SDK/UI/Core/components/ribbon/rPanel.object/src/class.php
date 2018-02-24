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

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Core", "components/ribbon/rPanelItem");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Core\components\ribbon\rPanelItem;
use \UI\Html\DOM;

/**
 * Ribbon Panel Object
 * 
 * Builds a ribbon's panel holder with specific style.
 * 
 * @version	0.1-1
 * @created	June 11, 2014, 9:50 (EEST)
 * @updated	June 20, 2015, 14:35 (EEST)
 */
class rPanel extends UIObjectPrototype
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
	 * 		The panel id.
	 * 
	 * @param	boolean	$grouped
	 * 		Indicates whether this panel will container one or two items.
	 * 
	 * @return	rPanel
	 * 		The rPanel object.
	 */
	public function build($id = "", $grouped = FALSE)
	{
		// Create holder
		$this->panelContainer = DOM::create("div", "", $id, "rPanel");
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
	 * 		The panel item type. See rPanelItem for more.
	 * 
	 * @param	string	$title
	 * 		The panel title. See rPanelItem for more.
	 * 
	 * @param	string	$imgURL
	 * 		The panel ico URL. See rPanelItem for more.
	 * 
	 * @param	boolean	$selected
	 * 		The panel selected property. See rPanelItem for more.
	 * 
	 * @return	DOMElement
	 * 		The panel item element.
	 */
	public function insertPanelItem($type = "small", $title = "", $imgURL = "", $selected = FALSE)
	{
		$ribbonItem = new rPanelItem();
		$panelItem = $ribbonItem->build($type, $title, $imgURL, $selected)->get();
		DOM::append($this->panelContainer, $panelItem);
		
		return $panelItem;
	}
}
//#section_end#
?>