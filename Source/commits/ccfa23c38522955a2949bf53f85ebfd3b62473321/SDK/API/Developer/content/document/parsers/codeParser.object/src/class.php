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

/**
 * Generic code parser.
 * 
 * Generic functions on coding, clearing and others.
 * 
 * @version	{empty}
 * @created	November 30, 2013, 13:52 (EET)
 * @revised	November 30, 2013, 13:52 (EET)
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
}
//#section_end#
?>