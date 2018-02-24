<?php
//#section#[header]
// Namespace
namespace UI\Presentation\togglers;

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
 * @package	Presentation
 * @namespace	\togglers
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * Content Toggler
 * 
 * Displays a header with a toggle button. On click, the body shows up.
 * 
 * @version	0.1-1
 * @created	June 12, 2013, 14:11 (EEST)
 * @revised	September 22, 2014, 10:44 (EEST)
 */
class toggler extends UIObjectPrototype
{
	/**
	 * The header content container.
	 * 
	 * @type	DOMElement
	 */
	private $headerContent;
	/**
	 * The body content container.
	 * 
	 * @type	DOMElement
	 */
	private $togBody;
	
	/**
	 * Builds the togglder object.
	 * 
	 * @param	string	$id
	 * 		The element's id.
	 * 
	 * @param	DOMElement	$header
	 * 		The header content.
	 * 
	 * @param	DOMElement	$body
	 * 		The body content.
	 * 
	 * @param	boolean	$open
	 * 		Defines whether the toggler is open.
	 * 
	 * @return	toggler
	 * 		Returns the toggler object.
	 */
	public function build($id = "", $header = NULL, $body = NULL, $open = FALSE)
	{
		// Create container
		$holder = DOM::create("div", "", $id, "toggler".($open ? " open" : ""));
		$this->set($holder);
		
		// Create Header Container
		$togHeader = DOM::create("div", "", "", "togHeader");
		DOM::append($holder, $togHeader);
		
		// Create toggle icon
		$togIcon = DOM::create("div", "", "", "togIcon");
		DOM::append($togHeader, $togIcon);
		
		$this->headerContent = DOM::create("div", "", "", "headerContent");
		DOM::append($togHeader, $this->headerContent);
		$this->setHead($header);
		
		// Create body
		$this->togBody = DOM::create("div", "", "", "togBody");
		DOM::append($holder, $this->togBody);
		$this->appendToBody($body);
		
		return $this;
	}
	
	/**
	 * Sets the toggler's head content. It replaces the old one.
	 * 
	 * @param	DOMElement	$content
	 * 		The head content.
	 * 
	 * @return	toggler
	 * 		Returns the toggler object.
	 */
	public function setHead($content)
	{
		if (!is_null($content))
		{
			DOM::innerHTML($this->headerContent, "");
			DOM::append($this->headerContent, $content);
		}
		
		return $this;
	}
	
	/**
	 * Appends content to the toggler's body.
	 * 
	 * @param	DOMElement	$content
	 * 		The content to be appended.
	 * 
	 * @return	toggler
	 * 		Returns the toggler object.
	 */
	public function appendToBody($content)
	{
		if (!is_null($content))
			DOM::append($this->togBody, $content);
		
		return $this;
	}
}
//#section_end#
?>