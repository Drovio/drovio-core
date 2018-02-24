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

importer::import("ESS", "Protocol", "reports/HTMLServerReport");
importer::import("ESS", "Protocol", "client/FormProtocol");
importer::import("ESS", "Prototype", "html/FormPrototype");
importer::import("UI", "Forms", "Form");
importer::import("UI", "Presentation", "notification");

use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Protocol\client\FormProtocol;
use \ESS\Prototype\html\FormPrototype;
use \UI\Forms\Form;
use \UI\Presentation\notification;

/**
 * Form Notification
 * 
 * Builds a specific form notification to be used for form reporting.
 * This class reports directly to forms with pre-defined attributes.
 * 
 * @version	0.1-3
 * @created	March 11, 2013, 13:45 (EET)
 * @updated	February 19, 2015, 10:03 (EET)
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
	 * 		The HTMLServerReport replace or append method.
	 * 		Use class constants.
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
	 * Returns the form's HTMLServerReport notification.
	 * 
	 * @param	boolean	$fullReset
	 * 		Specify whether the form will have a full reset after the notification arrives or just the passwords.
	 * 
	 * @return	string
	 * 		The server report content.
	 */
	public function getReport($fullReset = TRUE)
	{
		// Get form submit content
		return Form::getSubmitContent(parent::get(), $fullReset);
	}
}
//#section_end#
?>