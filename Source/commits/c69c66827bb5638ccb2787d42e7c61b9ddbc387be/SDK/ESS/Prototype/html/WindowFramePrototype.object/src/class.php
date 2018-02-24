<?php
//#section#[header]
// Namespace
namespace ESS\Prototype\html;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Prototype
 * @namespace	\html
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "ModuleProtocol");
importer::import("ESS", "Protocol", "reports::HTMLServerReport");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\ModuleProtocol;
use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * Window Frame Prototype
 * 
 * It's the window frame prototype for building frames (windows, dialogs etc.).
 * 
 * @version	1.0-1
 * @created	May 9, 2013, 15:51 (EEST)
 * @revised	August 8, 2014, 14:43 (EEST)
 */
class WindowFramePrototype extends UIObjectPrototype
{
	/**
	 * The frame's body container.
	 * 
	 * @type	DOMElement
	 */
	private $body;
	
	/**
	 * The frame id.
	 * 
	 * @type	string
	 */
	private $id;
	
	/**
	 * Initialize the frame.
	 * 
	 * @param	string	$id
	 * 		The frame id.
	 * 
	 * @return	void
	 */
	public function __construct($id = "")
	{
		$this->id = $id;
	}
	
	/**
	 * Builds the window frame structure.
	 * 
	 * @param	string	$title
	 * 		The frame's title.
	 * 
	 * @param	string	$class
	 * 		The frame's class.
	 * 
	 * @return	WindowFramePrototype
	 * 		The WindowFramePrototype object.
	 */
	public function build($title = "", $class = "")
	{
		// Create outer holder
		$windowFrame = DOM::create("div", "", $this->id, "wFrame".(empty($class) ? "" : " ".$class));
		$this->set($windowFrame);
		
		// Create header
		$frameHeader = DOM::create("div", "", "", "frameHeader");
		DOM::append($windowFrame, $frameHeader);
		
		// Header Title
		$frameTitle = DOM::create("span", $title, "", "frameTitle");
		DOM::append($frameHeader, $frameTitle);
		
		// Close button
		$closeBtn = DOM::create("span", "", "", "closeBtn");
		DOM::append($frameHeader, $closeBtn);
		
		// Create body
		$this->body = DOM::create("div", "", "", "frameBody");
		DOM::append($windowFrame, $this->body);
		
		return $this;
	}
	
	/**
	 * Appends a DOMElement to frame body.
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended.
	 * 
	 * @return	WindowFramePrototype
	 * 		The WindowFramePrototype object.
	 */
	public function append($element)
	{
		if (!is_null($element))
			DOM::append($this->body, $element);
		
		return $this;
	}
	
	/**
	 * Sets the popup action to a given DOMElement item of the page.
	 * 
	 * @param	DOMElement	$item
	 * 		The item that will trigger the popup.
	 * 
	 * @param	integer	$id
	 * 		The module's id.
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary (if any).
	 * 
	 * @param	array	$attr
	 * 		The arguments to pass to the module.
	 * 
	 * @return	void
	 */
	public static function setAction($item, $id, $action = "", $attr = array())
	{
		ModuleProtocol::addActionGET($item, $id, $action, $holder = "", $attr);
	}
	
	/**
	 * Adds a report data content to the server report.
	 * 
	 * @param	DOMElement	$content
	 * 		The DOMElement report content.
	 * 
	 * @param	string	$holder
	 * 		The holder of the content.
	 * 		This holder will be used to append or replace (according to the third parameter) the content.
	 * 		This holder will be used to append or replace (according to the third parameter) the content.
	 * 
	 * @param	string	$method
	 * 		The HTMLServerReport replace or append method (use const).
	 * 
	 * @return	void
	 */
	public function addReportContent($content, $holder = "", $method = HTMLServerReport::APPEND_METHOD)
	{
		HTMLServerReport::addContent($content, $type = "data", $holder, $method);
	}
	
	/**
	 * Adds a report action content to the server report.
	 * 
	 * @param	string	$type
	 * 		The action type.
	 * 
	 * @param	string	$value
	 * 		The action value (if any, empty by default).
	 * 
	 * @return	void
	 */
	public function addReportAction($type, $value = "")
	{
		HTMLServerReport::addAction($type, $value);
	}
	
	/**
	 * Gets the frame object with a server report.
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected function getFrame()
	{
		// Add this modulePage as content
		HTMLServerReport::addContent($this->get(), "popup");
		
		// Return Report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>