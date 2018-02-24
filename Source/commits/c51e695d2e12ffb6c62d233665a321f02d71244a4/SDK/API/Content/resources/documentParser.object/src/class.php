<?php
//#section#[header]
// Namespace
namespace API\Content\resources;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Content
 * @namespace	\resources
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Content", "filesystem::fileManager");
importer::import("API", "Content", "resources");
importer::import("API", "Platform", "DOM::DOM");
importer::import("API", "Geoloc", "locale");

use \API\Content\filesystem\fileManager;
use \API\Content\resources;
use \API\Platform\DOM\DOM;
use \API\Geoloc\locale;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	October 4, 2013, 12:02 (EEST)
 * @revised	October 25, 2013, 17:49 (EEST)
 * 
 * @deprecated	This class is deprecated until further notice. Use API\Resources\documentParser instead
 */
class documentParser
{
	/**
	 * {description}
	 * 
	 * @param	{type}	$file
	 * 		{description}
	 * 
	 * @param	{type}	$text
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function load($file, $text = TRUE)
	{
		// Normalize File Path
		$file = str_replace("::", "/", $file);
		
		// Create full path
		$documentPath = systemRoot.resources::PATH."/Documents/".locale::get_locale()."/".$file;
		
		// Get Content
		$content = fileManager::get_contents($documentPath);
		
		// If only text requested, send text
		if ($text)
			return $content;
			
		// Wrap in a div element
		$element = DOM::create("div");
		DOM::innerHTML($element, $content);
		
		return $element;
	}
}
//#section_end#
?>