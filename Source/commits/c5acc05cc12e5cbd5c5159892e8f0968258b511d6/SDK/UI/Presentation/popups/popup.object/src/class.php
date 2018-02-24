<?php
//#section#[header]
// Namespace
namespace UI\Presentation\popups;

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
 * @namespace	\popups
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports::HTMLServerReport");
importer::import("ESS", "Prototype", "html::PopupPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Prototype\html\PopupPrototype;
use \UI\Html\DOM;

/**
 * Redback Popup
 * 
 * This class builds a simple popup. It extends the system's popup prototype.
 * 
 * @version	1.0-2
 * @created	June 19, 2013, 15:47 (EEST)
 * @revised	December 12, 2014, 11:08 (EET)
 */
class popup extends PopupPrototype
{
	/**
	 * Builds the popup with the given content.
	 * 
	 * @param	DOMElement	$content
	 * 		The content of the popup.
	 * 
	 * @return	popup
	 * 		The popup object.
	 */
	public function build($content = NULL)
	{
		// Create container
		$popupContainer = DOM::create("div", "", "", "popup");
		if (!empty($content))
			DOM::append($popupContainer, $content);
		
		// Build popup
		return parent::build($popupContainer);
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
		HTMLServerReport::addContent($content, $type = HTMLServerReport::CONTENT_HTML, $holder, $method);
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
	 * Returns the html server report.
	 * 
	 * @return	string
	 * 		The html server report.
	 */
	public function getReport()
	{
		// Add this modulePage as content
		HTMLServerReport::addContent($this->get(), HTMLServerReport::CONTENT_POPUP);
		
		// Return Report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>