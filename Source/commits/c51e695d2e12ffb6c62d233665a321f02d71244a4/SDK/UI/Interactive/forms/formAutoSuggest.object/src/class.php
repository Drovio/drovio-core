<?php
//#section#[header]
// Namespace
namespace UI\Interactive\forms;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Interactive
 * @namespace	\forms
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Html", "DOM");

use \UI\Html\DOM;

/**
 * Form Auto Suggest
 * 
 * Displays automatic suggestions for a given input.
 * 
 * @version	{empty}
 * @created	March 11, 2013, 11:26 (EET)
 * @revised	March 11, 2013, 11:26 (EET)
 */
class formAutoSuggest
{
	/**
	 * Adds a autosuggest controller to the given element.
	 * 
	 * @param	DOMElement	$element
	 * 		The element where the controller will be attached.
	 * 
	 * @param	string	$path
	 * 		The suggestion's path.
	 * 
	 * @return	void
	 */
	public static function engage($element, $path)
	{
		DOM::attr($element, "data-as-path", $path);
	}
}
//#section_end#
?>