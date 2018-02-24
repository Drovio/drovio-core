<?php
//#section#[header]
// Namespace
namespace UI\Forms\formReport;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Forms
 * @namespace	\formReport
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::HTMLServerReport");
importer::import("ESS", "Protocol", "client::FormProtocol");
importer::import("ESS", "Prototype", "html::FormPrototype");
importer::import("UI", "Presentation", "notification");

use \ESS\Protocol\server\HTMLServerReport;
use \ESS\Protocol\client\FormProtocol;
use \ESS\Prototype\html\FormPrototype;
use \UI\Presentation\notification;

/**
 * Form Notification
 * 
 * Builds a form notification
 * 
 * @version	{empty}
 * @created	March 11, 2013, 13:45 (EET)
 * @revised	April 22, 2013, 12:28 (EEST)
 */
class formNotification extends notification
{
	/**
	 * Returns the form's HTMLServerReport notification.
	 * 
	 * @param	boolean	$reset
	 * 		Specify whether the form will be reset after the notification arrives.
	 * 
	 * @return	string
	 */
	public function getReport($reset = TRUE)
	{
		// Clear Report
		HTMLServerReport::clear();
		
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