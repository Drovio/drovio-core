<?php
//#section#[header]
// Namespace
namespace API\Developer\content\document\parsers;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\content\document\parsers
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Generic code parser.
 * 
 * Generic functions on coding, clearing and others.
 * 
 * @version	{empty}
 * @created	November 30, 2013, 13:52 (EET)
 * @revised	March 24, 2014, 14:41 (EET)
 * 
 * @deprecated	Use \DEV\Tools\codeParser instead.
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
	 * Removes line or multiline slash comments from code.
	 * 
	 * @param	string	$code
	 * 		The code from which the comments are to be removed.
	 * 
	 * @return	string
	 * 		Returns the code without the comments
	 */
	public static function removeComments($code)
	{
		// Strip Comments
		return preg_replace("/\/\*(?:[^*]|[\r\n]|(?:\*+(?:[^*\/]|[\r\n])))*\*+\/|\/\/.*/", "", $code);
	}
}
//#section_end#
?>