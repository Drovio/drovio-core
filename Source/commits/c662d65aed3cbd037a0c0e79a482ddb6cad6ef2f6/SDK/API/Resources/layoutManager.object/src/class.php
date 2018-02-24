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

importer::import("API", "Resources", "DOMParser");
importer::import("UI", "Html", "DOM");
//importer::import("API", "Developer", "resources::layouts::systemLayout");
importer::import("API", "Developer", "content::document::parsers::cssParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Resources\DOMParser;
use \UI\Html\DOM;
//use \API\Developer\resources\layouts\systemLayout;
use \API\Developer\content\document\parsers\cssParser;
use \API\Resources\filesystem\fileManager;

/**
 * Layout Loader and Manager
 * 
 * Loads any layout for the module's page and appends content to it.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 12:16 (EET)
 * @revised	July 4, 2014, 12:38 (EEST)
 * 
 * @deprecated	This class is deprecated until further notice.
 */
class layoutManager
{
	/**
	 * Holds the path that css files will be exported on release procedure.
	 * 
	 * @type	string
	 */
	const CSS_EXPORT_PATH = '/Library/Resources/css/l/';
	
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
		$layoutManager = new systemLayout($name);
		
		// Get Layout
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
	
	/**
	 * Get the list of all layouts as an associative array (id => name)
	 * 
	 * @param	{type}	$deprecated
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function getList($deprecated = NULL)
	{
		$sysLayoutManager = new systemLayout();
		$sysLayouts = $sysLayoutManager->getAllLayouts();
			
		return $sysLayouts;
	}
	
	/**
	 * Constructs and return filepath for exported system layout css
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function getFilePath()
	{		
		return systemRoot.self::CSS_EXPORT_PATH.self::getFilename().".css";		
	}
	
	/**
	 * Return the hashed filename of layout css file
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function getFilename()
	{
		return "lt.".hash("md5", 'redback.system.layoutstyles');
	}
	
	/**
	 * Exportes all system layout styles in one css file at given location.
	 * Return Null on failure or the css file size on seccess
	 * 
	 * @return	integer
	 * 		{description}
	 */
	public static function export()
	{		
		$layouts = self::getList();		
		// Init complete css string
		$css = "";
		foreach($layouts as $layout)
		{
			$sysLayoutManager = new systemLayout($layout);
			$css .= cssParser::format($sysLayoutManager->getModel(), TRUE);
		}		
		//Save css file in given path
		$status = fileManager::create(self::getFilePath(), $css);	
		
		return $status;
	}	
}
//#section_end#
?>