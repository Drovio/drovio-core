<?php
//#section#[header]
// Namespace
namespace DEV\Tools\parsers;

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
 * @package	Tools
 * @namespace	\parsers
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Tools", "codeParser");

use \DEV\Tools\codeParser;

/**
 * Javascript Parser
 * 
 * Parses and formats javascript files.
 * 
 * @version	{empty}
 * @created	March 24, 2014, 14:56 (EET)
 * @revised	March 24, 2014, 14:56 (EET)
 */
class jsParser extends codeParser
{
	/**
	 * Formats the given javascript code.
	 * For now, it just removes comments.
	 * 
	 * @param	string	$code
	 * 		The javascript code to be formatted.
	 * 
	 * @return	string
	 * 		The formatted js code.
	 */
	public static function format($code)
	{
		// Remove all comments
		//$code = preg_replace("/(?<![\\])(\/\/.*(?=[\n\r])|\/\*[\w\W]*?\*\/|\/\*[\w\W]*)/", "", $code);
		
		// Remove all proceeding whitespaces and tabs
		//$code = preg_replace("/^[ \t]*/m", "", $code);
		
		return trim($code);
	}
}
//#section_end#
?>