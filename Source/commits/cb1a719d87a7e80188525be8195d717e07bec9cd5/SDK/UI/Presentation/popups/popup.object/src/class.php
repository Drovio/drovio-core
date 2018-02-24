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
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::HTMLServerReport");
importer::import("ESS", "Prototype", "html::PopupPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Developer", "logController");
importer::import("DEV", "Profiler", "logger");

use \ESS\Protocol\server\HTMLServerReport;
use \ESS\Prototype\html\PopupPrototype;
use \UI\Html\DOM;
use \UI\Developer\logController;
use \DEV\Profiler\logger;

/**
 * Redback Popup
 * 
 * This builds a simple popup. It extends the system's popup prototype.
 * 
 * @version	{empty}
 * @created	June 19, 2013, 15:47 (EEST)
 * @revised	August 30, 2013, 12:07 (EEST)
 */
class popup extends PopupPrototype
{
	/**
	 * Builds the popup content.
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
	 * Returns the html server report.
	 * 
	 * @return	string
	 * 		The html server report.
	 */
	public function getReport()
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
		HTMLServerReport::addContent(parent::get(), "popup");
		
		// Return Report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>