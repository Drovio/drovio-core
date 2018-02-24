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
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("DEV", "Tools", "codeParser");
importer::import("DEV", "Tools", "parsers/JShrink");

use \DEV\Tools\codeParser;
use \DEV\Tools\parsers\JShrink;

/**
 * Javascript Parser
 * 
 * Parses and formats javascript files.
 * 
 * @version	0.2-1
 * @created	November 2, 2015, 0:17 (GMT)
 * @updated	November 2, 2015, 0:20 (GMT)
 */
class jsParser extends codeParser
{
	/**
	 * Formats the given javascript code.
	 * It is currently deactivated.
	 * 
	 * @param	string	$code
	 * 		The javascript code to be formatted.
	 * 
	 * @param	boolean	$minify
	 * 		Option to minify the given code.
	 * 		It is TRUE by default.
	 * 
	 * @return	string
	 * 		The formatted js code.
	 */
	public static function format($code, $minify = TRUE)
	{
		// Trim code
		$code = trim($code);
		
		// Minify and return
		if ($minify)
			return self::minify($code);
		
		// Return unminified code
		return $code;
	}
	
	/**
	 * Minify the given javascript code.
	 * 
	 * @param	string	$code
	 * 		The javascript code to be minified.
	 * 
	 * @return	string
	 * 		The javascript code minified
	 */
	public static function minify($code)
	{
		// Minify using jShrink
		$options = array();
		return JShrink::minify($code, $options);
	}
}
//#section_end#
?>