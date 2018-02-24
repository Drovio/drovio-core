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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("DEV", "Tools", "codeParser");

use \DEV\Tools\codeParser;

/**
 * CSS Parser
 * 
 * Parses and handles css files.
 * 
 * @version	0.1-1
 * @created	March 24, 2014, 14:55 (EET)
 * @updated	February 18, 2015, 15:59 (EET)
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
	 * 		The formatted css code.
	 */
	public static function format($code, $compact = TRUE)
	{
		// Disable full urls before format
		$code = str_replace("http://", "httpslsl", $code);
		$code = str_replace("https://", "httpsslsl", $code);
				
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
		
		// Re-enable full urls
		$code = str_replace("httpslsl", "http://", $code);
		$code = str_replace("httpsslsl", "https://", $code);
		
		return trim($code);
	}
}
//#section_end#
?>