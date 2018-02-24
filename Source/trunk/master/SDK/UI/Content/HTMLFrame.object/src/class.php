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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * HTML iframe content
 * 
 * Creates an iframe based on the HTMLPagePrototype.
 * 
 * @version	2.0-4
 * @created	October 20, 2014, 15:39 (EEST)
 * @updated	May 25, 2015, 15:49 (EEST)
 */
class HTMLFrame extends UIObjectPrototype
{
	/**
	 * Build the frame element.
	 * 
	 * @param	string	$src
	 * 		The address of the document to embed in the <iframe>.
	 * 		It is empty by default.
	 * 
	 * @param	string	$name
	 * 		The name attribute of the <iframe>.
	 * 		It is empty by default.
	 * 
	 * @param	string	$id
	 * 		The id attribute of the <iframe>.
	 * 		It is empty by default.
	 * 
	 * @param	string	$class
	 * 		The class attribute of the <iframe>.
	 * 		It is empty by default.
	 * 
	 * @param	array	$sandbox
	 * 		A list of extra restrictions for the content in the <iframe>. Includes:
	 * 		allow-forms
	 * 		allow-same-origin
	 * 		allow-scripts
	 * 		allow-top-navigation.
	 * 		
	 * 		It is empty by default.
	 * 
	 * @return	HTMLFrame
	 * 		The HTMLFrame object.
	 */
	public function build($src = "", $name = "", $id = "", $class = "", $sandbox = array())
	{
		// Create iframe
		$id = empty($id) ? "rdf".mt_rand() : $id;
		$iframe = DOM::create("iframe", "", $id, "redFrame ".$class);
		$this->set($iframe);
		
		// Set user attributes
		DOM::attr($iframe, "src", $src);
		DOM::attr($iframe, "name", $name);
		if (is_array($sandbox) && !empty($sandbox))
			DOM::attr($iframe, "sandbox", explode(" ", $sandbox));
		
		// Set default attributes
		DOM::attr($iframe, "frameborder", "none");
		DOM::attr($iframe, "seamless", TRUE);
		DOM::attr($iframe, "scrolling", "auto");
		
		return $this;
	}
}
//#section_end#
?>