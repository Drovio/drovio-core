<?php
//#section#[header]
// Namespace
namespace UI\Core;

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
 * @package	Core
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports/HTMLServerReport");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "notification");

use \ESS\Protocol\reports\HTMLServerReport;
use \UI\Html\DOM;
use \UI\Presentation\notification;

/**
 * Redback Core Page Report
 * 
 * Generates an automated report notification when there is an access problem or when there is an exception caught during execution.
 * 
 * @version	0.1-3
 * @created	June 11, 2014, 9:07 (EEST)
 * @updated	January 9, 2015, 14:47 (EET)
 */
class RCPageReport
{
	/**
	 * The report content to send to RCServerReport.
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
	 * @return	RCPageReport
	 * 		The RCPageReport object.
	 */
	public function build($type = notification::ERROR, $msgType, $msgId, $extraContext = NULL)
	{
		// Build notification
		$ntf = new notification();
		$ntf->build($type, $header = TRUE, $timeout = FALSE, $disposable = TRUE);
		
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
		HTMLServerReport::addContent($this->reportContent, HTMLServerReport::CONTENT_HTML, $holder = "#pageReport", HTMLServerReport::REPLACE_METHOD, $key = "report");

		// Return Report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>