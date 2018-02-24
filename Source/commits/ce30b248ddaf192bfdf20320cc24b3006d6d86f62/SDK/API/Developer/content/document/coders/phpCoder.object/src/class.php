<?php
//#section#[header]
// Namespace
namespace API\Developer\content\document\coders;

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
 * @namespace	\content\document\coders
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * PHP Coder Helper
 * 
 * Helps with the php code handling within the system.
 * 
 * @version	{empty}
 * @created	April 23, 2013, 13:47 (EEST)
 * @revised	March 24, 2014, 14:46 (EET)
 * 
 * @deprecated	Use \DEV\Tools\coders\phpCoder instead.
 */
class phpCoder
{
	/**
	 * An array of forbidden functions and elements to comment out
	 * 
	 * @type	array
	 */
	private static $forbidden = array();
	
	/**
	 * Wraps a php code section to a delimited section
	 * 
	 * @param	string	$content
	 * 		The php code content
	 * 
	 * @param	string	$title
	 * 		The section title
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function section($content, $title)
	{
		return "//#section#[$title]\n".$content."\n"."//#section_end#\n";
	}
	
	/**
	 * Get all sections of a php code content in an array [section_name] = section_content
	 * 
	 * @param	string	$source
	 * 		The source code
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function sections($source)
	{
		// Section's regular expression
		$sectionRegexp = '/\/\/\#section\#\[\b([\w]+)\b\]([\w\W]*?)^[\t ]*\/\/\#section_end\#/m';
		
		// Create segments array
		$segments = array();
		
		// After this preg_match_all
		// $segments[0] has the matches
		// $segments[1] has the indicies <- we need this as section indicies
		// $segments[2] has the contents <- we need this as section contents
		preg_match_all($sectionRegexp, $source, $segments);
		
		// Form Sections (trim contents)
		$sections = array();
		foreach ($segments[1] as $key => $index)
			$sections[$index] = trim($segments[2][$key]);
		
		return $sections;
	}
	
	/**
	 * Comments forbidden elements from script
	 * 
	 * @param	string	$source
	 * 		The source code
	 * 
	 * @return	void
	 */
	public static function forbidden($source)
	{
	}
}
//#section_end#
?>