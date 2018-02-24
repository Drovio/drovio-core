<?php
//#section#[header]
// Namespace
namespace API\Developer\profiler;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");

// Import logger
importer::import("API", "Developer", "profiler::logger");
use \API\Developer\profiler\logger as loggr;
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\profiler
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Content", "resources");
importer::import("API", "Resources", "storage::cookies");
importer::import("API", "Resources", "DOMParser");

use \API\Content\resources;
use \API\Resources\storage\cookies;
use \API\Resources\DOMParser;

/**
 * System Debugger
 * 
 * Reports and handles all system errors
 * 
 * @version	{empty}
 * @created	March 29, 2013, 11:02 (EET)
 * @revised	October 17, 2013, 14:47 (EEST)
 */
class debugger
{
	/**
	 * Initializes error reporting
	 * 
	 * @return	void
	 */
	public static function init()
	{
		// Log Activity
		loggr::log("Init debugger...", loggr::INFO);
		
		// Set Server to display errors
		if (self::status())
			ini_set('display_errors', 'On');
	}
	
	/**
	 * Load a debugger message from resources
	 * 
	 * @param	string	$code
	 * 		The code message
	 * 
	 * @return	void
	 */
	public static function getErrorMessage($code = "")
	{
		// Load system debug code
		$parser = new DOMParser();
		$parser->load(resources::PATH."/SDK/debugger/codes.xml", true);
		
		// Load message item
		$msg_item = $parser->find($code);
		
		// If no message found, return NOT_FOUND
		if (is_null($msg_item))
			return "404: ERR_NOT_FOUND";
		
		// Form Code
		return $msg_item->parentNode->getAttribute("code").".".$msg_item->getAttribute("code").": ".$code;
	}
	
	/**
	 * Activate debugger mode
	 * 
	 * @return	void
	 */
	public static function activate()
	{
		Cookies::set("dbgr", TRUE);
	}
	
	/**
	 * Deactivate debugger mode
	 * 
	 * @return	void
	 */
	public static function deactivate()
	{
		Cookies::delete("dbgr");
	}
	
	/**
	 * Get debugger status
	 * 
	 * @return	void
	 */
	public static function status()
	{
		return (is_null(Cookies::get("dbgr")) ? FALSE : Cookies::get("dbgr"));
	}
}
//#section_end#
?>