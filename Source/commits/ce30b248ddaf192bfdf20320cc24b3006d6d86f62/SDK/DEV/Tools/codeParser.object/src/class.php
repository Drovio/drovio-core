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
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Generic code parser.
 * 
 * Generic functions on coding, clearing and others.
 * 
 * @version	{empty}
 * @created	March 24, 2014, 14:41 (EET)
 * @revised	March 24, 2014, 14:41 (EET)
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
		// Decode and trim
		$code = trim(html_entity_decode($code));
		
		// Clear unescaped string
		$code = str_replace(chr(160), " ", $code);
		
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
		return preg_replace("/\/\*(?:[^*]|[\r\n]|(?:\*+(?:[^*\/]|[\r\n])))*\*+\/|\/\/.*/", "", $code);
	}
}
//#section_end#
?>