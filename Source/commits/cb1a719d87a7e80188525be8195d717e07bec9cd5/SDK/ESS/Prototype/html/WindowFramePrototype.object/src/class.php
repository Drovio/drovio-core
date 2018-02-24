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
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::HTMLServerReport");
importer::import("ESS", "Protocol", "client::PopupProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Developer", "logController");
importer::import("DEV", "Profiler", "logger");

use \ESS\Protocol\server\HTMLServerReport;
use \ESS\Protocol\client\PopupProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;
use \UI\Developer\logController;
use \DEV\Profiler\logger;

/**
 * Window Frame Prototype
 * 
 * It's the window frame prototype for building frames (windows, dialogs etc.).
 * 
 * @version	{empty}
 * @created	May 9, 2013, 15:51 (EEST)
 * @revised	October 23, 2013, 16:07 (EEST)
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
	 * 		{description}
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
		$frameTitle = DOM::create("div", "", "", "frameTitle");
		DOM::append($frameHeader, $frameTitle);

		$title = DOM::create("span", $title);
		DOM::append($frameTitle, $title);
		
		// Header Controls
		$frameControls = DOM::create("div", "", "", "frameControls");
		DOM::append($frameHeader, $frameControls);
		// Close button
		$closeBtn = DOM::create("span", "", "", "closeBtn");
		DOM::append($frameControls, $closeBtn);
		
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
	 * 		{description}
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
		PopupProtocol::addAction($item, $id, $action, $attr);
	}
	
	/**
	 * Gets the frame object with a server report.
	 * 
	 * @param	DOMElement	$content
	 * 		The report's content.
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected function getFrame($content)
	{
		// Clear Report
		HTMLServerReport::clear();
		
		// Get logs
		if (logger::status())
		{
			$logContext = logController::getLogs($id = "log_".time(), $head = "Log ".date("H:i:s", time()));
			HTMLServerReport::addContent($logContext, "data", "#pageHelper", HTMLServerReport::APPEND_METHOD);
		}
		
		// Add this modulePage as content
		HTMLServerReport::addContent($content, "popup");
		
		// Return Report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>