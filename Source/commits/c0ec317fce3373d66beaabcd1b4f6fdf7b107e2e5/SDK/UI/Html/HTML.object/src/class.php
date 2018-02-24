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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("UI", "Html", "DOM");
importer::import("DEV", "Profiler", "logger");

use \UI\Html\DOM;
use \DEV\Profiler\logger;

/**
 * HTML Handler
 * 
 * HTML extends DOM handler for html specific functions.
 * 
 * @version	3.0-1
 * @created	December 19, 2013, 16:14 (EET)
 * @updated	October 16, 2015, 14:21 (EEST)
 */
class HTML extends DOM
{
	/**
	 * Magic method to create all html tags automatically.
	 * 
	 * @param	string	$name
	 * 		The function name caught.
	 * 		In this function it serves as the tag name.
	 * 
	 * @param	array	$arguments
	 * 		All function arguments.
	 * 		They serve as the content, id and class, like DOM::create().
	 * 
	 * @return	mixed
	 * 		The DOMElement created or NULL if the tag is not valid.
	 */
	public static function __callStatic($name, $arguments)
	{
		// Get method name and check for a valid html tag
		$tag = strtolower($name);
		if (!self::htmlTag($tag))
			return NULL;
		
		// Get attributes
		$content = $arguments[0];
		$id = $arguments[1];
		$class = $arguments[2];
		
		// Create element
		return DOM::create($tag, $content, $id, $class);
	}
	
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
		// Normalize class
		$class = trim($class);
		if (empty($class))
			return FALSE;
			
		// Get current class
		$currentClass = trim(parent::attr($elem, "class"));
		
		// Check if class already exists
		$classes = explode(" ", $currentClass);
		if (in_array($class, $classes))
			return TRUE;
		
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
		if ($classKey === FALSE)
			return FALSE;
		
		// Remove class
		unset($classes[$classKey]);
		
		// Set new class
		$newClass = implode(" ", $classes);
		return parent::attr($elem, "class", empty($newClass) ? NULL : $newClass);
	}
	
	/**
	 * Check if the given DOMElement has a given class.
	 * 
	 * @param	DOMElement	$elem
	 * 		The element to check for the class.
	 * 
	 * @param	string	$class
	 * 		The class name.
	 * 
	 * @return	boolean
	 * 		True if the element has the class, false otherwise.
	 */
	public static function hasClass($elem, $class)
	{
		// Get current class
		$itemClass = trim(parent::attr($elem, "class"));
		
		// Check if class already exists
		$classes = explode(" ", $itemClass);
		return in_array($class, $classes);
	}
	
	/**
	 * Set or get a style value for the given element.
	 * This function will append the style rule in the style attribute.
	 * 
	 * @param	DOMElement	$elem
	 * 		The DOMElement to set or get the style value.
	 * 
	 * @param	string	$name
	 * 		The style name.
	 * 
	 * @param	string	$val
	 * 		If the value is NULL or FALSE, the value is considered negative and the style will be removed from the attribute.
	 * 		If the value is empty string (default, null is not included), the function will return the style value.
	 * 		Otherwise, the style will be appended to the style attribute and the new attribute will be returned.
	 * 
	 * @return	mixed
	 * 		Returns FALSE if there is an error.
	 * 		The new style value on success.
	 */
	public static function style($elem, $name, $val = "")
	{
		if (empty($elem))
		{
			logger::log("You are trying to set/get style from an empty element.", logger::DEBUG);
			return FALSE;
		}
		
		// Get all styles from the element
		$elementStyle = parent::attr($elem, "style");
		$elementStyle = trim($elementStyle, "; ");
	 	
		$styleArray = array();
		if (!empty($elementStyle))
			$styleArray = explode(";", $elementStyle);
		$styles = array();
		foreach ($styleArray as $stylePair)
		{
			$pair = explode(":", $stylePair);
			$styles[trim($pair[0])] = trim($pair[1]);
		}
		
		// If value is null or false, remove attribute
		if (is_null($val) || (is_bool($val) && $val === FALSE))
			unset($styles[$name]);
		else if (empty($val))
			return $styles[$name];
		else
			$styles[$name] = $val;
		
		// Pack all styles into one value
		$styleArray = array();
		foreach ($styles as $name => $value)
		{
			$pieces = array($name, $value);
			$styleArray[] = implode(": ", $pieces);
		}
		$elementStyle = implode("; ", $styleArray);
		
		// Set style attribute
		parent::attr($elem, "style", (empty($elementStyle) ? NULL : $elementStyle));
		
		// Return the new element style
		return $elementStyle;
	}
	
	/**
	 * Selects nodes in the html document that match a given css selector.
	 * 
	 * @param	string	$css
	 * 		The css selector to search for in the html document.
	 * 		It does not support pseudo-* for the moment and only supports simple equality attribute-wise.
	 * 		Can hold multiple selectors separated with comma.
	 * 
	 * @param	mixed	$context
	 * 		Can either be a DOMElement as the context of the search, or a css selector.
	 * 		If the selector results in multiple DOMNodes, then the first is selected as the context.
	 * 
	 * @return	mixed
	 * 		Returns the node list that matches the given css selector, or FALSE on malformed input.
	 */
	public static function select($css, $context = NULL)
	{
		// _xpcm_ -> ',' {comma}
		// _xpsp_ -> ' ' {space}
		// _xpor_ -> ' or ' {xpath or clause}
	
		// Prepare css selector
		$css = preg_replace("/[ \t\r\n\s]+/", " ", $css);
		
		// Transform css to xpath
		$xpath = $css;
		
		// Identify Attributes
		$xpath = str_replace("[", "[@", $xpath);
		
		// Identify IDs
		$xpath = preg_replace("/\#(-?[_a-zA-Z]+[_a-zA-Z0-9-]*)/", "[@id='$1']", $xpath);
		
		// Identify Classes
		$xpath = preg_replace("/\.(-?[_a-zA-Z]+[_a-zA-Z0-9-]*)/", "[contains(concat('_xpsp_'_xpcm_@class_xpcm_'_xpsp_')_xpcm_'_xpsp_$1_xpsp_')]", $xpath);
		//$xpath = preg_replace("/\.(-?[_a-zA-Z]+[_a-zA-Z0-9-]*)/", "[contains(@class_xpcm_'$1')]", $xpath);
		
		// Identify root
		if (empty($context))
			$xpath = preg_replace("/[^,]+/", "//$0", $xpath);
			
		// Identify Descendants
		$xpath = preg_replace("/([^>~+])([ ])([^>~+])/", "$1//$2$3", $xpath);
		
		// Identify Children
		$xpath = str_replace(">", "/", $xpath);
		
		// Identify Direct Next siblings
		//$xpath = preg_replace("/\+[ ]+([^ >+~,\/]*(\[.*?\])?[^ >+~,\/]*)/", "/following-sibling::$1[1]", $xpath);
		// Identify Next siblings
		//$xpath = preg_replace("/\~[ ]+([^ >+~,\/]*(\[.*?\])?[^ >+~,\/]*)/", "/following-sibling::$1", $xpath);
	
		// Identify multiple selectors
		$xpath = str_replace(" ", "", $xpath);
		$xpath = str_replace(",", " | ", $xpath);
		
		// Identify "orphans" [no element, just attributes]
		$xpath = str_replace("/[", "/*[", $xpath);
		
		// Restore commas, spaces and or in functions
		$xpath = str_replace("_xpcm_", ",", $xpath);
		$xpath = str_replace("_xpsp_", " ", $xpath);
		//$xpath = str_replace("_xpor_", " or ", $xpath);

		// Get the context node if css context
		if (!empty($context) && is_string($context))
		{
			$ctxList = self::select($context);
			if (empty($ctxList) || empty($ctxList->length))
			{
				logger::log("HTML::select(). Context node is not found.", logger::ERROR);
				return FALSE;
			}
			
			$context = $ctxList->item(0);
		}	
		
		// Evaluate xpath and return the node list
		return parent::evaluate($xpath, $context);
	}
	
	/**
	 * Check if the given xml tag is a valid html tag.
	 * 
	 * @param	string	$tag
	 * 		The html tag to be checked.
	 * 
	 * @return	boolean
	 * 		True if valid, false otherwise.
	 */
	private static function htmlTag($tag)
	{
		// Temporarily return TRUE for all tags
		return TRUE;
	}
}
//#section_end#
?>