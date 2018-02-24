<?php
//#section#[header]
// Namespace
namespace DEV\Tools;

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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Generic code parser.
 * 
 * Generic functions on coding, clearing and others.
 * 
 * @version	0.2-3
 * @created	March 24, 2014, 14:41 (EET)
 * @revised	December 12, 2014, 16:55 (EET)
 */
class codeParser
{
	/**
	 * Clears a given code from unwanted entities and characters.
	 * 
	 * @param	string	$code
	 * 		The code to be cleared.
	 * 
	 * @return	string
	 * 		The cleared code.
	 */
	public static function clear($code)
	{
		// Decode context
		$code = self::decode($code);
		
		// Clear an unwanted specific character
		return str_replace(chr(160), " ", $code);
	}
	
	/**
	 * Decodes a given html context.
	 * 
	 * @param	string	$code
	 * 		The code to be decoded properly.
	 * 
	 * @return	string
	 * 		The decoded code.
	 */
	public static function decode($code)
	{
		// Decode and trim
		$code = trim(html_entity_decode($code, $flags = ENT_COMPAT | ENT_HTML5, $encoding = 'ISO-8859-1'));
		
		return $code;
	}
	
	/**
	 * Removes line and multiline slash comments from code.
	 * 
	 * @param	string	$code
	 * 		The code from which the comments are to be removed.
	 * 
	 * @return	string
	 * 		The new code without the comments.
	 */
	public static function removeComments($code)
	{
		// Strip Comments
		return preg_replace("/\/\*(?:[^*]|[\r\n]|(?:\*+(?:[^*\/]|[\r\n])))*\*+\/|\/\/.*[\n]/", "", $code);
	}
}
//#section_end#
?>