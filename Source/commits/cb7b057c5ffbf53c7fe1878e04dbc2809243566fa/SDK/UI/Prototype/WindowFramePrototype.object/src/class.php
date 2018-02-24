<?php
//#section#[header]
// Namespace
namespace UI\Prototype;

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
 * @package	Prototype
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Protocol", "ModuleProtocol");
importer::import("ESS", "Protocol", "reports/HTMLServerReport");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Prototype", "UIObjectPrototype");

use \ESS\Protocol\ModuleProtocol;
use \ESS\Protocol\reports\HTMLServerReport;
use \UI\Html\DOM;
use \UI\Prototype\UIObjectPrototype;

/**
 * Window Frame Prototype
 * 
 * It's the window frame prototype for building frames (windows, dialogs etc.).
 * 
 * @version	0.1-1
 * @created	July 28, 2015, 12:18 (EEST)
 * @updated	July 28, 2015, 12:18 (EEST)
 */
class WindowFramePrototype extends UIObjectPrototype
{
	/**
	 * The replace method identifier.
	 * 
	 * @type	string
	 */
	const REPLACE_METHOD = "replace";
	
	/**
	 * The append method identifier.
	 * 
	 * @type	string
	 */
	const APPEND_METHOD = "append";

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
	 * Create a new frame instance.
	 * 
	 * @param	string	$id
	 * 		The frame id.
	 * 
	 * @return	void
	 */
	public function __construct($id = "")
	{
		$this->id = (empty($id) ? "wf".mt_rand() : $id);
	}
	
	/**
	 * Builds the window frame structure.
	 * 
	 * @param	mixed	$title
	 * 		The frame's title.
	 * 		It can be string or DOMElement.
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
	 * 		The report method, replace or append (use class const).
	 * 
	 * @return	void
	 */
	public function addReportContent($content, $holder = "", $method = self::APPEND_METHOD)
	{
		HTMLServerReport::addContent($content, $type = HTMLServerReport::CONTENT_DATA, $holder, $method);
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
	 * 		The html server report.
	 */
	protected function getFrame()
	{
		// Add this modulePage as content
		HTMLServerReport::addContent($this->get(), HTMLServerReport::CONTENT_POPUP);
		
		// Return Report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>