<?php
//#section#[header]
// Namespace
namespace UI\Layout;

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
 * @package	Layout
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Layout", "pageLayout");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Tools", "parsers::cssParser");

use \API\Resources\DOMParser;
use \UI\Html\DOM;
use \UI\Layout\pageLayout;
use \API\Resources\filesystem\fileManager;
use \DEV\Tools\parsers\cssParser;

/**
 * System Layout Loader and Manager
 * 
 * Manages all page layouts in the system, including export and loading.
 * 
 * @version	{empty}
 * @created	July 4, 2014, 13:27 (EEST)
 * @revised	July 4, 2014, 13:27 (EEST)
 */
class layoutManager
{
	/**
	 * Holds the path where the layout css will be exported on release procedure.
	 * 
	 * @type	string
	 */
	const CSS_EXPORT_PATH = '/Library/Resources/css/l/';
	
	/**
	 * Get the filepath for the system's layout css path.
	 * 
	 * @return	string
	 * 		The full system's layout css exported path.
	 */
	public static function getFilePath()
	{		
		return systemRoot.self::CSS_EXPORT_PATH.self::getFilename().".css";
	}
	
	/**
	 * Get the hashed filename of layout css file
	 * 
	 * @return	string
	 * 		The hashed filename.
	 */
	public static function getFilename()
	{
		return "lt.".hash("md5", 'redback.system.layoutstyles');
	}
	
	/**
	 * Exports all system layout styles in one css file at a standard location.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function export()
	{
		// Init total css coce
		$css = "";
		
		// Load layout list
		$pgl = new pageLayout("system");
		$layouts = $pgl->getAllLayouts();
		foreach($layouts as $layout)
		{
			$layout = new pageLayout("system", $layout);
			$css .= $layout->getCSS();
		}		
		//Save css file in given path
		$css = cssParser::format($css, TRUE);
		$status = fileManager::create(self::getFilePath(), $css);	
		
		return $status;
	}	
}
//#section_end#
?>