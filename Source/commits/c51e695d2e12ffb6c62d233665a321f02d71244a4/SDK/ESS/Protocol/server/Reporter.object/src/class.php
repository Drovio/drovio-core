<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\server;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\server
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Debug", "debugger");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Notifications", "popup");
importer::import("UI", "Presentation", "notification");

use \API\Debug\debugger;
use \UI\Html\DOM;
use \UI\Notifications\popup;
use \UI\Presentation\notification;

/**
 * Redback Quick Reporter
 * 
 * Reports in a quick and easy way notifications and other messages to the user.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 9:19 (EET)
 * @revised	October 1, 2013, 11:26 (EEST)
 * 
 * @deprecated	This class is generally deprecated.
 */
class Reporter
{
	/**
	 * Builds a report message
	 * 
	 * @param	string	$type
	 * 		The type of the notification.
	 * 		Accepted values: All the available notification types.
	 * 
	 * @param	string	$ref
	 * 		The reference scope of the message
	 * 
	 * @param	string	$id
	 * 		The message id
	 * 
	 * @param	DOMElement | string	$context
	 * 		Any extra message the user must read
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public static function get($type, $ref, $id, $context = "")
	{
		// Create Notification
		$notification = new notification();
		
		// Build Notification Container
		$notification->build($type, $header = TRUE, $footer = TRUE);
		
		// Retrieve Message
		$message = $notification->getMessage($ref, $id);
		$notification->append($message);
		
		// Extra Message
		if ($context != "")
		{
			if (gettype($context) == "string")
				$customMessage = DOM::create("div", $context, "", "customMessage");
			else
			{
				$customMessage = DOM::create("div", "", "", "customMessage");
				DOM::append($customMessage, $context);
			}
			$notification->append($customMessage);
		}
		
		// Return the notification holder
		return $notification->get();
	}
	
	/**
	 * Returns an error notification with a system specific code for debugging reasons
	 * 
	 * @param	string	$code
	 * 		The debugger's code
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public static function error($code)
	{
		// Retrive Error Code
		$err_code = debugger::load_message($code, $type = "ERR");
		
		// Return System Message
		return self::get("error", "debug", "dbg.sys_error", $err_code);
	}
	
	/**
	 * Returns a specific notification (error or success) as a popup according to given flag.
	 * 
	 * @param	boolean	$status
	 * 		Indicates whether the notification will be error (false) or success (true)
	 * 
	 * @param	string	$message
	 * 		The message id for the notification
	 * 
	 * @param	{type}	$popup
	 * 		{description}
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public static function statusReport($status, $message = "", $popup = TRUE)
	{
		// Build popup
		$reportPopup = new popup();
		$reportPopup->timeout(TRUE);
		$reportPopup->fade(TRUE);
		$reportPopup->position('top');
		
		// Build Notification
		$reportNtf = new notification();
		
		if ($status)
		{
			// TEMP
			$message = "success.save_success";
			$reportNtf->build($type = "success", $header = FALSE, $footer = FALSE);
			$reportMessage = $reportNtf->getMessage("success", $message);
		}
		else
		{
			// TEMP
			$message = "err.save_error";
			$reportNtf->build($type = "error", $header = TRUE, $footer = FALSE);
			$reportMessage = $reportNtf->getMessage("error", $message);
			$reportPopup->timeout(FALSE);
		}

		$reportNtf->append($reportMessage);
		$notification = $reportNtf->get();

		if ($popup)
			return $reportPopup->get($notification);
		else
			return $notification;
	}
}
//#section_end#
?>