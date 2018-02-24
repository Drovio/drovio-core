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
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("UI", "Navigation", "toolbarComponents/toolbarItem");
importer::import("UI", "Html", "HTML");

use \UI\Navigation\toolbarComponents\toolbarItem;
use \UI\Html\HTML;

/**
 * Tollbar Button Component
 * 
 * The toolbar component for a button.
 * 
 * @version	1.0-1
 * @created	September 25, 2014, 19:39 (EEST)
 * @updated	June 24, 2015, 14:12 (EEST)
 */
class toolbarButton extends toolbarItem
{
	/**
	 * Create a new toolbar button.
	 * 
	 * @param	mixed	$content
	 * 		The button content.
	 * 
	 * @param	string	$class
	 * 		The button extra class.
	 * 
	 * @return	void
	 */
	public function __construct($content, $class ='')
	{
		// Create button
		$btn = HTML::create("button", $content, "", "tlbButton");
		HTML::addClass($btn, $class);
		
		// Create toolbar item with button as content
		parent::__construct($btn);
	}
}
//#section_end#
?>