<?php
//#section#[header]
// Namespace
namespace UI\Html\pageComponents\htmlComponents;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Html
 * @namespace	\pageComponents\htmlComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::NavigatorProtocol");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\client\NavigatorProtocol;
use \UI\Html\DOM;

/**
 * Hyperlink Factory
 * 
 * It is used for building fast weblinks
 * 
 * @version	{empty}
 * @created	March 12, 2013, 16:01 (EET)
 * @revised	March 12, 2013, 16:01 (EET)
 */
class weblink
{
	/**
	 * Builds the weblink DOMElement
	 * 
	 * @param	string	$href
	 * 		The href attribute value.
	 * 
	 * @param	string	$target
	 * 		The target value.
	 * 
	 * @param	mixed	$content
	 * 		The content of the hyperlink.
	 * 
	 * @return	DOMELement
	 */
	public static function get($href, $target, $content)
	{
		// Create element
		$element = DOM::create("a");
		
		// Set Navigation
		NavigatorProtocol::web($element, $href, $target);
		
		// Populate content
		$webContent = $content;
		if (gettype($content) == "string")
			$webContent = DOM::create("span", $content);
		DOM::append($element, $webContent);
		
		// Return weblink
		return $element;
	}
}
//#section_end#
?>