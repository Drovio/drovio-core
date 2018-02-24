<?php
//#section#[header]
// Namespace
namespace UI\Presentation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Presentation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "DOM::DOM");

use \API\Platform\DOM\DOM;

/**
 * Heading Element
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	May 23, 2013, 14:59 (EEST)
 * @revised	May 23, 2013, 14:59 (EEST)
 * 
 * @deprecated	This class is no longer used.
 */
class heading
{
	/**
	 * Creates the header for the form
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function get($title, $type = "1")
	{
		$hdr = DOM::create('h'.$type, "", "", "lhd hd".$type);
		
		if (gettype($title) == "string")
		{
			$h_title = DOM::create('span', $title);
			DOM::append($hdr, $h_title);
		}
		else
			DOM::append($hdr, $title);
			
		return $hdr;
	}
	
}
//#section_end#
?>