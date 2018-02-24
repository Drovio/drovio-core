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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
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
 * @version	0.1-1
 * @created	June 8, 2013, 11:46 (EEST)
 * @revised	July 17, 2014, 16:44 (EEST)
 */
class toolbarItem extends UIObjectPrototype
{
	/**
	 * Builds the toolbarItem with its content.
	 * 
	 * @param	DOMElement	$content
	 * 		The toolbarItem's content.
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @return	toolbarItem
	 * 		Returns null if no content is given. ToolbarItem otherwise.
	 */
	public function build($content = NULL, $class = "toolbarItem")
	{
		if (empty($content))
			return NULL;
			
		// Build Tool
		$tool = DOM::create("div", "", "", $class);
		$this->set($tool);
		
		// Append Content
		DOM::append($tool, $content);
		
		return $this;
	}
}
//#section_end#
?>