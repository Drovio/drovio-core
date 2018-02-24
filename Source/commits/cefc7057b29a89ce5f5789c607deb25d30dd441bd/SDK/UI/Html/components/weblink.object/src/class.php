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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Protocol", "client/NavigatorProtocol");
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
 * @version	0.2-1
 * @created	June 11, 2014, 10:00 (EEST)
 * @updated	March 28, 2015, 21:10 (EET)
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
	 * @param	string	$class
	 * 		The weblink class attribute.
	 * 
	 * @return	weblink
	 * 		The weblink object.
	 */
	public function build($href = "", $target = "_self", $content = "", $class = "")
	{
		// Create element
		$element = DOM::create("a", $content, "", $class);
		$this->set($element);
		
		// Set Navigation
		NavigatorProtocol::web($element, $href, $target);
		
		// Return weblink
		return $this;
	}
}
//#section_end#
?>