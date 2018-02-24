<?php
//#section#[header]
// Namespace
namespace API\Developer\content\document\parsers;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\content\document\parsers
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "content::document::parsers::codeParser");

use \API\Developer\content\document\parsers\codeParser;

/**
 * CSS Parser
 * 
 * Parses and handles css files.
 * 
 * @version	{empty}
 * @created	April 5, 2013, 10:30 (EEST)
 * @revised	April 23, 2013, 13:48 (EEST)
 */
class cssParser extends codeParser
{
	/**
	 * Formats the given css code according to parameters.
	 * 
	 * @param	string	$code
	 * 		The css code.
	 * 
	 * @param	boolean	$compact
	 * 		If TRUE, sets each selector with its properties in a single line.
	 * 
	 * @return	string
	 */
	public static function format($code, $compact = TRUE)
	{
		// Remove all comments
		$code = preg_replace("/\/\/.*(?=[\n\r])|\/\*[\w\W]*?\*\/|\/\*[\w\W]*/", "", $code);
		
		// Remove all proceeding whitespaces and tabs for every line
		$code = preg_replace("/^[ \t]*/m", "", $code);
		
		
		// Compact all code
		$code = str_replace("\n", "", $code);
		$code = str_replace("\r", "", $code);
		
		// Stretch again
		$code = str_replace("}", "}\n", $code);
		
		// Each property different line
		if (!$compact)
		{
			$code = str_replace("{", "{\n\t", $code);
			$code = str_replace(";", ";\n\t", $code);
		}
		
		return trim($code);
	}
}
//#section_end#
?>