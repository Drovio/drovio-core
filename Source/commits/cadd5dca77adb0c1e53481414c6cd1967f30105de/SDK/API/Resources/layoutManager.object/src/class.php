<?php
//#section#[header]
// Namespace
namespace API\Resources;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Resources
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Html", "DOM");
importer::import("UI", "Layout", "pageLayout");
importer::import("UI", "Layout", "layoutManager");

use \UI\Html\DOM;
use \UI\Layout\pageLayout;
use \UI\Layout\layoutManager as uiLayoutManager;

/**
 * Layout Loader and Manager
 * 
 * Loads any layout for the module's page and appends content to it.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 12:16 (EET)
 * @revised	July 4, 2014, 13:32 (EEST)
 * 
 * @deprecated	Use \UI\Layout\layoutManager instead.
 */
class layoutManager extends uiLayoutManager
{
	/**
	 * Loads the layout from the resources directory.
	 * 
	 * @param	string	$name
	 * 		The layout's name
	 * 
	 * @param	{type}	$deprecated
	 * 		{description}
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public static function load($name = "", $deprecated = NULL)
	{
		$layoutManager = new pageLayout("system", $name);
		$layout_html = $layoutManager->getStructure();
		
		// Create Layout
		$pageLayout = DOM::create("div", "", $name , "uiPageLayout");
		DOM::innerHTML($pageLayout, $layout_html);

		return $pageLayout;
	}
	
	/**
	 * Append a given DOMElement to a layout's section.
	 * 
	 * @param	string	$name
	 * 		The layout's id.
	 * 
	 * @param	string	$sectionId
	 * 		The section's id.
	 * 
	 * @param	DOMElement	$content
	 * 		The element to be appended to the section.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function append($name, $sectionId, $content)
	{
		// Normalize section id
		$section_id = $name."_".$sectionId;

		// Get section
		$section = DOM::find($section_id);
		
		// If there is no such section, return FALSE
		if (empty($section))
			return FALSE;
		
		// Check if content is empty
		if (empty($content))
			return FALSE;
		
		// Append to section
		DOM::append($section, $content);
		
		// Return success
		return TRUE;
	}
}
//#section_end#
?>