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

// Import logger
importer::import("API", "Developer", "profiler::logger");
use \API\Developer\profiler\logger as loggr;
use \API\Developer\profiler\logger;
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Html
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
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
 * @revised	December 19, 2013, 16:14 (EET)
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
}
//#section_end#
?>