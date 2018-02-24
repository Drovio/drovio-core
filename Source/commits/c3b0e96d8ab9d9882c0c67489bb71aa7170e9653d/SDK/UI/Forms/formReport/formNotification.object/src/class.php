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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
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
 * @version	0.1-2
 * @created	March 11, 2013, 13:45 (EET)
 * @updated	January 23, 2015, 14:31 (EET)
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
	 * @param	boolean	$fullReset
	 * 		Specify whether the form will have a full reset after the notification arrives or just the passwords.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getReport($fullReset = TRUE)
	{
		// Set Report Action
		FormProtocol::addSubmitAction();
		FormProtocol::addResetAction($fullReset);
			
		// Set Report Content
		HTMLServerReport::addContent($content = parent::get(), $type = "data", $holder = ".formReport", $method = "replace");
		
		// Return Report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>