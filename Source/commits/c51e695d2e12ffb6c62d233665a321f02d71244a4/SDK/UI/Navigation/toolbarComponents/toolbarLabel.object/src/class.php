<?php
//#section#[header]
// Namespace
namespace UI\Navigation\toolbarComponents;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Navigation
 * @namespace	\toolbarComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * Toolbar Item
 * 
 * Creates a navigation toolbar item.
 * 
 * @version	{empty}
 * @created	June 8, 2013, 11:46 (EEST)
 * @revised	June 8, 2013, 11:46 (EEST)
 */
class toolbarLabel extends UIObjectPrototype
{
	/**
	 * Builds the toolbarItem with its content.
	 * 
	 * @param	DOMElement	$content
	 * 		The toolbarItem's content.
	 * 
	 * @return	toolbarItem
	 * 		Returns null if no content is given. ToolbarItem otherwise.
	 */
	public function build($content = NULL)
	{
		if (is_null($content))
			return NULL;
		
		// Build Tool
		$label = DOM::create("label", "", "", "toolbarLabel");
		DOM::append($label, $content);
		
		return $this;
	}
}
//#section_end#
?>