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

importer::import("API", "Resources", "filesystem::directory");
importer::import("UI", "Html", "DOM");

use \API\Resources\filesystem\directory;
use \UI\Html\DOM;

/**
 * Form Auto Complete
 * 
 * Auto completes other inputs based on specific element input values.
 * 
 * @version	{empty}
 * @created	March 11, 2013, 11:19 (EET)
 * @revised	March 11, 2013, 11:19 (EET)
 */
class formAutoComplete
{
	/**
	 * Makes an element master for auto completing other elements according to input value.
	 * 
	 * @param	DOMElement	$element
	 * 		The master element.
	 * 
	 * @param	array	$fill
	 * 		The set of input elements which will be filled with the new values.
	 * 
	 * @param	array	$hide
	 * 		The set of input elements which will be hidden
	 * 
	 * @param	array	$populate
	 * 		The set of input elements which will be populated with new values (select etc)
	 * 
	 * @param	string	$mode
	 * 		Sets the behavior of the autocomplete elements in case any element changes its value.
	 * 		Accepted values:
	 * 		- "strict" : Autocomplete breaks on value change
	 * 		- "lenient" : Autocomplete preserves on value change
	 * 
	 * @return	void
	 */
	public static function engage($element, $path, $fill = array(), $hide = array(), $populate = array(), $mode = "strict")
	{
		$autoComplete = array();
		$autoComplete['fill'] = implode("|", $fill);
		$autoComplete['hide'] = implode("|", $hide);
		$autoComplete['populate'] = implode("|", $populate);
		$autoComplete['mode'] = $mode;
		$autoComplete['path'] = directory::normalize($path);
		
		DOM::data($element, "autocomplete", $autoComplete);
	}
}
//#section_end#
?>