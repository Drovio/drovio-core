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

// Import logger
importer::import("API", "Developer", "profiler::logger");
use \API\Developer\profiler\logger as loggr;
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
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * Hyperlink Factory
 * 
 * It is used for building fast weblinks
 * 
 * @version	{empty}
 * @created	March 12, 2013, 16:01 (EET)
 * @revised	December 16, 2013, 12:00 (EET)
 */
class weblink extends UIObjectPrototype
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
	 * 		It can be text or a DOMElement.
	 * 
	 * @return	object
	 * 		The weblink object.
	 */
	public static function build($href = "", $target = "_self", $content = "")
	{
		// Create element
		$element = DOM::create("a");
		$this->set($element);
		
		// Set Navigation
		NavigatorProtocol::web($element, $href, $target);
		
		// Populate content
		$webContent = $content;
		if (gettype($content) == "string")
			$webContent = DOM::create("span", $content);
		DOM::append($element, $webContent);
		
		// Return weblink
		return $this;
	}
}
//#section_end#
?>