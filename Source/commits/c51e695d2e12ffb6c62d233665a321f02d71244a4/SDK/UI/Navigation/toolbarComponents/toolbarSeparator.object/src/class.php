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
 * Toolbar Separator
 * 
 * Creates toolar separator.
 * 
 * @version	{empty}
 * @created	July 3, 2013, 17:02 (EEST)
 * @revised	July 3, 2013, 17:02 (EEST)
 */
class toolbarSeparator  extends UIObjectPrototype
{
	/**
	 * Builds the separator.
	 * 
	 * @return	toolbarSeparator
	 * 		The toolbarSeparator object.
	 */
	public function build()
	{
		// Create separator
		$separator = DOM::create("div", "", "", "toolSeparator");
		$this->set($separator);
		
		return $this;
	}
}
//#section_end#
?>