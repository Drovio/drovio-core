<?php
//#section#[header]
// Namespace
namespace UI\Navigation\toolbarComponents;

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
 * @namespace	\toolbarComponents
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;
use \UI\Html\HTML;

/**
 * Toolbar Item
 * 
 * Creates a navigation toolbar item.
 * 
 * @version	1.0-1
 * @created	June 8, 2013, 11:46 (EEST)
 * @updated	April 21, 2015, 12:45 (EEST)
 */
class toolbarItem extends UIObjectPrototype
{
	/**
	 * Builds the toolbarItem with its content.
	 * 
	 * @param	DOMElement	$content
	 * 		The toolbar item's content.
	 * 
	 * @param	string	$class
	 * 		The extra toolbar item class.
	 * 
	 * @return	toolbarItem
	 * 		The toolbarItem object.
	 */
	public function build($content = NULL, $class = "")
	{
		// Build Tool
		$tool = DOM::create("div", $content, "", "toolbarItem");
		HTML::addClass($tool, $class);
		$this->set($tool);
		
		return $this;
	}
}
//#section_end#
?>