<?php
//#section#[header]
// Namespace
namespace UI\Html\pageComponents\htmlComponents;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Html
 * @namespace	\pageComponents\htmlComponents
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Html", "DOM");

use \UI\Html\DOM;

/**
 * System Header Factory
 * 
 * Builds headers.
 * 
 * @version	{empty}
 * @created	March 12, 2013, 15:54 (EET)
 * @revised	June 10, 2014, 12:40 (EEST)
 * 
 * @deprecated	No longer needed and used.
 */
class heading
{
	/**
	 * Creates a header with the given size
	 * 
	 * @param	integer	$size
	 * 		The header size, must be numeric and from 1 to 7.
	 * 
	 * @param	mixed	$content
	 * 		The header's content.
	 * 		It can be string or DOMElement.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public static function get($size, $content)
	{
		// Validate type
		if (!is_numeric($size) && $size < 1 && $size > 7)
			return NULL;
		
		// Create header
		if (gettype($content) == "string")
		{
			$header = DOM::create('h'.$type, $content, "", "lhd hd".$type);
		}
		else
		{
			$header = DOM::create('h'.$type, $content, "", "lhd hd".$type);
			DOM::append($header, $content);
		}
		
		// Return header
		return $header;
	}
}
//#section_end#
?>