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
 * @version	1.0-2
 * @created	March 24, 2014, 14:55 (EET)
 * @updated	May 29, 2015, 9:57 (EEST)
 */
class cssParser extends codeParser
{
	/**
	 * Minify the given css code according to parameters.
	 * 
	 * @param	string	$code
	 * 		The css code to minify.
	 * 
	 * @param	boolean	$oneLine
	 * 		If TRUE, the minified css will be only one line.
	 * 		It is FALSE by default.
	 * 
	 * @return	string
	 * 		The minified css code.
	 */
	public static function minify($code, $oneLine = FALSE)
	{
		// Format code
		$minified = self::format($code, $compact = TRUE);
		
		// Set one line or not
		if ($oneLine)
			$minified = str_replace("\n", "", $minified);
		
		// Return minified
		return $minified;
	}
	
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
		// Remove all comments
		$code = preg_replace("/\/\*[\w\W]*?\*\/|\/\*[\w\W]*/", "", $code);
		
		// Remove all proceeding whitespaces and tabs for every line
		$code = preg_replace("/^[ \t]*/m", "", $code);
		// Remove all trailing spaces after ":"
		$code = preg_replace("/:[ \t\n]+/", ":", $code);
		
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