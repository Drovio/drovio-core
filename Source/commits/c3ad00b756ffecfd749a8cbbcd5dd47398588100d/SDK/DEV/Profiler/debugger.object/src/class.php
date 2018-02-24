<?php
//#section#[header]
// Namespace
namespace DEV\Profiler;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Profiler
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "storage::cookies");
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Profiler", "logger");

use \API\Resources\storage\cookies;
use \API\Resources\DOMParser;
use \DEV\Profiler\logger;

/**
 * System Debugger
 * 
 * Reports and handles all system errors
 * 
 * @version	0.1-1
 * @created	February 10, 2014, 10:43 (EET)
 * @revised	September 16, 2014, 16:36 (EEST)
 */
class debugger
{
	/**
	 * Initializes debugger and if debugger mode is on, it activates the error reporting.
	 * 
	 * @return	void
	 */
	public static function init()
	{
		// Set error reporting
		error_reporting(E_ALL & ~(E_STRICT|E_NOTICE|E_WARNING));
		
		// Set Server to display errors
		if (self::status())
		{
			logger::log("Activating debugger.", logger::INFO);
			ini_set('display_errors', 'On');
		}
	}
	
	/**
	 * Load a debugger code error message from resources.
	 * 
	 * @param	string	$code
	 * 		The error code.
	 * 
	 * @return	string
	 * 		The error code description.
	 */
	public static function getErrorMessage($code = "")
	{
		// Load system debug code
		$parser = new DOMParser();
		$parser->load("/System/Resources/SDK/debugger/codes.xml", true);
		
		// Load message item
		$msg_item = $parser->find($code);
		
		// If no message found, return NOT_FOUND
		if (is_null($msg_item))
			return "404: ERR_NOT_FOUND";
		
		// Form Code
		return $msg_item->parentNode->getAttribute("code").".".$msg_item->getAttribute("code").": ".$code;
	}
	
	/**
	 * Activate debugger mode.
	 * 
	 * @return	void
	 */
	public static function activate()
	{
		cookies::set("dbgr", TRUE);
	}
	
	/**
	 * Deactivate debugger mode.
	 * 
	 * @return	void
	 */
	public static function deactivate()
	{
		cookies::delete("dbgr");
	}
	
	/**
	 * Get debugger status.
	 * 
	 * @return	boolean
	 * 		True if debugger is activated, false otherwise.
	 */
	public static function status()
	{
		return (is_null(cookies::get("dbgr")) ? FALSE : cookies::get("dbgr"));
	}
}
//#section_end#
?>