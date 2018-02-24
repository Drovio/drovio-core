<?php
//#section#[header]
// Namespace
namespace UI\Forms\formReport;

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
 * @package	Forms
 * @namespace	\formReport
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports::HTMLServerReport");
importer::import("ESS", "Protocol", "client::FormProtocol");
importer::import("ESS", "Prototype", "html::FormPrototype");
importer::import("UI", "Presentation", "notification");

use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Protocol\client\FormProtocol;
use \ESS\Prototype\html\FormPrototype;
use \UI\Presentation\notification;

/**
 * Form Notification
 * 
 * Builds a form notification
 * 
 * @version	0.1-1
 * @created	March 11, 2013, 13:45 (EET)
 * @revised	August 8, 2014, 14:43 (EEST)
 */
class formNotification extends notification
{
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
	 * Returns the form's HTMLServerReport notification.
	 * 
	 * @param	boolean	$reset
	 * 		Specify whether the form will be reset after the notification arrives.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getReport($reset = TRUE)
	{
		// Set Report Action
		FormProtocol::addSubmitAction();
		if ($reset)
			FormProtocol::addResetAction();
			
		// Set Report Content
		HTMLServerReport::addContent($content = parent::get(), $type = "data", $holder = ".formReport", $method = "replace");
		
		// Return Report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>