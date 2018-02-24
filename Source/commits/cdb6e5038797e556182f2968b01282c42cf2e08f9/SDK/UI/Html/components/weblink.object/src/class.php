<?php
//#section#[header]
// Namespace
namespace UI\Html\components;

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
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::NavigatorProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * Hyperlink Builder
 * 
 * Builds in one line a weblink with all the attributes.
 * 
 * @version	0.1-2
 * @created	June 11, 2014, 10:00 (EEST)
 * @revised	October 21, 2014, 11:31 (EEST)
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
	 * @return	weblink
	 * 		The weblink object.
	 */
	public function build($href = "", $target = "_self", $content = "")
	{
		// Normalize content
		if (!empty($content) && gettype($content) == "string")
			$content = DOM::create("span", $content);
			
		// Create element
		$element = DOM::create("a", $content);
		$this->set($element);
		
		// Set Navigation
		NavigatorProtocol::web($element, $href, $target);
		
		// Return weblink
		return $this;
	}
}
//#section_end#
?>