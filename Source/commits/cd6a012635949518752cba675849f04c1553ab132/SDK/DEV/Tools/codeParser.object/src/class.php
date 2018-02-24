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
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

/**
 * Generic code parser.
 * 
 * Generic functions on coding, clearing and others.
 * 
 * @version	0.2-6
 * @created	March 24, 2014, 14:41 (EET)
 * @updated	September 27, 2015, 14:19 (EEST)
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

		// Clear any unwanted characters (if any)
		$code = htmlentities($code);
		$code = str_replace(chr(160), "", $code);
		$code = self::decode($code);
		
		// Return cleared code
		return $code;
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
		$code = trim(html_entity_decode($code, $flags = ENT_COMPAT | ENT_HTML5));
		
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