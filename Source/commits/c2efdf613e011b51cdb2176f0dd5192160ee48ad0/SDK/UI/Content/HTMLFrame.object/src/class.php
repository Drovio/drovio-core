<?php
//#section#[header]
// Namespace
namespace UI\Content;

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
 * @package	Content
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "html::HTMLPagePrototype");
importer::import("API", "Resources", "DOMParser");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\html\HTMLPagePrototype;
use \API\Resources\DOMParser;
use \UI\Html\DOM;

/**
 * HTML iframe content
 * 
 * Creates an iframe based on the HTMLPagePrototype.
 * 
 * @version	1.0-1
 * @created	October 20, 2014, 15:39 (EEST)
 * @revised	October 20, 2014, 16:25 (EEST)
 */
class HTMLFrame extends HTMLPagePrototype
{
	/**
	 * Initialize the iframe document.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct(FALSE);
	}
	
	/**
	 * Append a DOMElement to the frame body.
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended.
	 * 
	 * @return	void
	 */
	public function append($element)
	{
		return parent::appendToBody($element);
	}
	
	/**
	 * Get the iframe DOMElement object.
	 * 
	 * @param	string	$name
	 * 		The frame name attribute.
	 * 
	 * @param	string	$id
	 * 		The frame id attribute.
	 * 
	 * @param	string	$class
	 * 		The frame class attribute.
	 * 
	 * @param	array	$sandbox
	 * 		iframe sandbox attributes in array values.
	 * 		You can include the allowed values:
	 * 		allow-forms
	 * 		allow-same-origin
	 * 		allow-scripts
	 * 		allow-top-navigation
	 * 
	 * @return	DOMElement
	 * 		The iframe DOMElement.
	 */
	public function get($name = "", $id = "", $class = "", $sandbox = array())
	{
		// Create iframe
		$iframe = DOM::create("iframe", "", $id, $class);
		DOM::attr($iframe, "frameborder", "none");
		DOM::attr($iframe, "name", $name);
		DOM::attr($iframe, "sandbox", explode(" ", $sandbox));
		DOM::attr($iframe, "seamless", TRUE);
		
		// Add context to parser to convert to html
		$parser = new DOMParser();
		$base = $parser->import(parent::get());
		$parser->append($base);

		// Add context
		DOM::innerHTML($iframe, $parser->getHTML());
		
		// Get frame
		return $iframe;
	}
}
//#section_end#
?>