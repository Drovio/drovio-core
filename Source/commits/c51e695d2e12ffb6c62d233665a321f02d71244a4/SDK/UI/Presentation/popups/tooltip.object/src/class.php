<?php
//#section#[header]
// Namespace
namespace UI\Presentation\popups;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Presentation
 * @namespace	\popups
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Html", "DOM");

use \UI\Html\DOM;

/**
 * Tooltip class
 * 
 * Adds a tooltip functionality to the element.
 * 
 * @version	{empty}
 * @created	September 16, 2013, 13:24 (EEST)
 * @revised	September 16, 2013, 13:24 (EEST)
 */
class tooltip
{
	/**
	 * Sets the tooltip attribute for the current element.
	 * 
	 * @param	DOMElement	$element
	 * 		The element to set the tooltip.
	 * 
	 * @param	string	$tooltip
	 * 		The tooltip content.
	 * 
	 * @return	void
	 */
	public static function set($element, $tooltip)
	{
		// Get context
		$tooltipContent = "";
		if (gettype($tooltip) == "string")
			$tooltipContent = $tooltip;
		else
			$tooltipContent = $tooltip->nodeValue;
		
		// Set attribute
		DOM::attr($element, 'data-tooltip', $tooltipContent);
	}
}
//#section_end#
?>