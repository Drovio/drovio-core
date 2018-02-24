<?php
//#section#[header]
// Namespace
namespace UI\Html;

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
 * @package	Html
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Html", "DOM");

use \UI\Html\DOM;

/**
 * HTML Handler
 * 
 * HTML extends DOM handler for html specific functions.
 * 
 * @version	{empty}
 * @created	December 19, 2013, 16:14 (EET)
 * @revised	January 23, 2014, 10:05 (EET)
 */
class HTML extends DOM
{
	/**
	 * Adds a class to the given DOMElement.
	 * 
	 * @param	DOMElement	$elem
	 * 		The element to add the class.
	 * 
	 * @param	string	$class
	 * 		The class name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if the class already exists.
	 */
	public static function addClass($elem, $class)
	{
		// Get current class
		$currentClass = trim(parent::attr($elem, "class"));
		
		// Check if class already exists
		$classes = explode(" ", $currentClass);
		if (in_array($class, $classes))
			return FALSE;
		
		// Append new class
		return parent::appendAttr($elem, "class", $class);
	}
	
	/**
	 * Removes a class from a given DOMElement.
	 * 
	 * @param	DOMElement	$elem
	 * 		The element to add the class.
	 * 
	 * @param	string	$class
	 * 		The class name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if the class already exists.
	 */
	public static function removeClass($elem, $class)
	{
		// Get current class
		$currentClass = trim(parent::attr($elem, "class"));
		
		// Check if class doesn't exists
		$classes = explode(" ", $currentClass);
		$classKey = array_search($class, $classes);
		if (!$classKey)
			return FALSE;
		
		// Remove class
		unset($classes[$classKey]);
		
		// Set new class
		$newClass = implode(" ", $classes);
		return parent::attr($elem, "class", $newClass);
	}
	
	/**
	 * Selects nodes in the html document that match a given css selector.
	 * 
	 * @param	string	$css
	 * 		The css selector to search for in the html document.
	 * 		It does not support pseudo-* for the moment and only supports simple equality attribute-wise.
	 * 		Can hold multiple selectors separated with comma.
	 * 
	 * @return	string
	 * 		Returns the node list that matches the given css selector.
	 */
	public static function select($css)
	{
		// Prepare css selector
		$css = preg_replace("/[\r\n\s]+/", " ", $css);
		
		// Transform css to xpath
		$xpath = $css;
		// Identify IDs
		$xpath = preg_replace("/\#(-?[_a-zA-Z]+[_a-zA-Z0-9-]*)/", "[id='$1']", $xpath);
		// Identify Classes
		$xpath = preg_replace("/\.(-?[_a-zA-Z]+[_a-zA-Z0-9-]*)/", "[class='$1']", $xpath);
		// Identify Attributes
		$xpath = str_replace("[", "[@", $xpath);
		// Identify root [atm everywhere in document]
		$xpath = preg_replace("/[^,]+/", "//$0", $xpath);
		// Identify Descendants
		$xpath = preg_replace("/\b[ ]\b/", "//", $xpath);
		// Identify Children
		$xpath = str_replace(">", "/", $xpath);
		// Identify Direct Next siblings
		$xpath = preg_replace("/\+[ ]+([^ >+~,\/]*(\[.*?\])?[^ >+~,\/]*)/", "/following-sibling::$1[1]", $xpath);
		// Identify Next siblings
		$xpath = preg_replace("/\~[ ]+([^ >+~,\/]*(\[.*?\])?[^ >+~,\/]*)/", "/following-sibling::$1", $xpath);
		
		/*$xpath = preg_replace_callback(
		        '/[^ >+~,]*\[.*?\][^ >+~,]*|[^ >+~,]+/',
		        function ($matches) {
				// Identify Attributes
				$mid = str_replace("[", "[@", $matches[0]);
				
				return $mid;
		        },
		        $xpath
		    );*/
		
		// Identify multiple selectors
		$xpath = str_replace(" ", "", $xpath);
		$xpath = str_replace(",", " | ", $xpath);
		// Identify "orphans" [no element, just attributes]
		$xpath = str_replace("//[", "//*[", $xpath);
		
		
		// Evaluate xpath and return the node list
		return parent::evaluate($xpath);
	}
	
	/*public static function select($css)
	{
		// Prepare css selector
		$css = preg_replace("/[\r\n\s]+/", " ", $css);
		
		// Transform css to xpath
		$xpath = $css;
		// Identify Attributes
		$xpath = str_replace("[", "[@", $xpath);
		// Identify IDs
		$xpath = preg_replace("/\#(-?[_a-zA-Z]+[_a-zA-Z0-9-]*)/", "[@id='$1']", $xpath);
		// Identify Classes
		$xpath = preg_replace("/\.(-?[_a-zA-Z]+[_a-zA-Z0-9-]*)/", "[matches(@class_xpcm_'\b$1\b')]", $xpath);
		// Identify root [atm everywhere in document]
		$xpath = preg_replace("/[^,]+/", "//$0", $xpath);
		// Identify Descendants
		$xpath = preg_replace("/\b[ ]\b/", "//", $xpath);
		// Identify Children
		$xpath = str_replace(">", "/", $xpath);
		// Identify Direct Next siblings
		$xpath = preg_replace("/\+[ ]+([^ >+~,\/]*(\[.*?\])?[^ >+~,\/]*)/", "/following-sibling::$1[1]", $xpath);
		// Identify Next siblings
		$xpath = preg_replace("/\~[ ]+([^ >+~,\/]*(\[.*?\])?[^ >+~,\/]*)/", "/following-sibling::$1", $xpath);
		
		/*$xpath = preg_replace_callback(
		        '/[^ >+~,]*\[.*?\][^ >+~,]*|[^ >+~,]+/',
		        function ($matches) {
				// Identify Attributes
				$mid = str_replace("[", "[@", $matches[0]);
				
				return $mid;
		        },
		        $xpath
		    );*
	
		// Identify multiple selectors
		$xpath = str_replace(" ", "", $xpath);
		$xpath = str_replace(",", " | ", $xpath);
		// Identify "orphans" [no element, just attributes]
		$xpath = str_replace("//[", "//*[", $xpath);
		
		// Restore commas in functions
		$xpath = str_replace("_xpcm_", ",", $xpath);
//return $xpath;
		// Evaluate xpath and return the node list
		return parent::evaluate($xpath);
	}*/
}
//#section_end#
?>