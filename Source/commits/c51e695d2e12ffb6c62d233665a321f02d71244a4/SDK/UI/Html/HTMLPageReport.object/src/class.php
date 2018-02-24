<?php
//#section#[header]
// Namespace
namespace UI\Html;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Html
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::HTMLServerReport");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "notification");

use \ESS\Protocol\server\HTMLServerReport;
use \UI\Html\DOM;
use \UI\Presentation\notification;

/**
 * HTML Page Report
 * 
 * Generates page reports.
 * 
 * @version	{empty}
 * @created	August 12, 2013, 11:22 (EEST)
 * @revised	August 12, 2013, 11:24 (EEST)
 */
class HTMLPageReport
{
	/**
	 * The report content to send to HTMLServerReport.
	 * 
	 * @type	DOMElement
	 */
	private $reportContent;
	
	/**
	 * Builds the report notification object.
	 * 
	 * @param	string	$type
	 * 		The notification type.
	 * 
	 * @param	string	$msgType
	 * 		The message type.
	 * 
	 * @param	string	$msgId
	 * 		The message id to be loaded.
	 * 
	 * @param	string	$extraContext
	 * 		Any extra context for the notification.
	 * 
	 * @return	HTMLPageReport
	 * 		The HTMLPageReport object.
	 */
	public function build($type, $msgType, $msgId, $extraContext = NULL)
	{
		// Build notification
		$ntf = new notification();
		$ntf->build($type, $header = TRUE);
		
		// Get Report Message
		$message = $ntf->getMessage($msgType, $msgId);
		$ntf->append($message);
		
		// Append Extra Context
		if (!is_null($extraContext))
			$ntf->appendCustomMessage($extraContext);
		
		$this->reportContent = $ntf->get();
		return $this;
	}
	
	/**
	 * Gets the HTMLServerReport.
	 * 
	 * @return	string
	 * 		The HTMLServerReport string.
	 */
	public function getReport()
	{
		// Clear Report
		HTMLServerReport::clear();

		// Add this modulePage as content
		HTMLServerReport::addContent($this->reportContent, $type = "data", $holder = "#pageReport", $method = "replace");

		// Return Report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>