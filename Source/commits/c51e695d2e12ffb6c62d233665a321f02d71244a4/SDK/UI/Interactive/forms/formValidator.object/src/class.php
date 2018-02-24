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
 * Form Validator
 * 
 * A visual javascript form validator.
 * 
 * @version	{empty}
 * @created	March 11, 2013, 10:56 (EET)
 * @revised	March 11, 2013, 10:56 (EET)
 */
class formValidator
{
	/**
	 * Attaches a formValidator to a given form.
	 * 
	 * @param	DOMElement	$form
	 * 		The form where the validator will be attached
	 * 
	 * @param	string	$mode
	 * 		The mode of the validator.
	 * 		Accepted values:
	 * 		- "verbose" : show all messages (default)
	 * 		- "icon" : show only icons
	 * 
	 * @return	void
	 */
	public static function engage($form, $mode = "verbose")
	{
		DOM::attr($form, "data-validator-mode", $mode);
	}
	
	/**
	 * Adds input information to the given element
	 * 
	 * @param	DOMElement	$element
	 * 		The input element
	 * 
	 * @param	string	$title
	 * 		The annotation title
	 * 
	 * @param	string	$description
	 * 		The annotation description
	 * 
	 * @return	void
	 */
	public static function addAnnotation($element, $title, $description)
	{
		$annotation = array();
		$annotation['title'] = $title;
		$annotation['description'] = $description;
		
		DOM::data($element, "ann-info", $annotation);
	}
	
	/**
	 * Adds a specific validator to the input.
	 * 
	 * @param	DOMElement	$element
	 * 		The input element where the validator will be attached.
	 * 
	 * @param	string	$validator
	 * 		The validator's unique name.
	 * 
	 * @return	void
	 */
	public static function addValidator($element, $validator)
	{
		DOM::attr($element, "data-validator", $validator);
	}
}
//#section_end#
?>