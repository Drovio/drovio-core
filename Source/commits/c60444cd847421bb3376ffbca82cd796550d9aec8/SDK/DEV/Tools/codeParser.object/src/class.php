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
 * @version	0.1-1
 * @created	March 24, 2014, 14:41 (EET)
 * @revised	September 10, 2014, 11:46 (EEST)
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
	 * 
	 * @deprecated	Use decode() instead.
	 */
	public static function clear($code)
	{
		return self::decode($code);
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
		$code = trim(html_entity_decode($code));
		
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