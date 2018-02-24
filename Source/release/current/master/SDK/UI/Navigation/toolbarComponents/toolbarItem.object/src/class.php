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
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("UI", "Html", "HTML");
importer::import("UI", "Prototype", "UIObjectPrototype");

use \UI\Html\HTML;
use \UI\Prototype\UIObjectPrototype;

/**
 * Toolbar Item
 * 
 * Creates a navigation toolbar item.
 * 
 * @version	1.0-2
 * @created	June 8, 2013, 9:46 (BST)
 * @updated	November 28, 2015, 17:34 (GMT)
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
		$tool = HTML::div($content, "", "toolbarItem");
		HTML::addClass($tool, $class);
		$this->set($tool);
		
		return $this;
	}
}
//#section_end#
?>