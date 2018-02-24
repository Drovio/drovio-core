<?php
//#section#[header]
// Namespace
namespace API\Model\protocol\reporting;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	{empty}
 * @package	{empty}
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "DOM::DOM");
importer::import("API", "Debug", "debugger");
importer::import("UI", "Notifications", "popup");
importer::import("UI", "Notifications", "notification");

use \API\Platform\DOM\DOM;
use \API\Debug\debugger;
use \UI\Notifications\popup;
use \UI\Notifications\notification;

/**
 * {title}
 * 
 * Usage
 * 
 * @version	{empty}
 * @created	{empty}
 * @revised	{empty}
 * 
 * @deprecated	Use \ESS\Protocol\server\Reporter instead.
 */
class reporter
{
	/**
	 * {description}
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @param	{type}	$ref
	 * 		{description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$context
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function get($type, $ref, $id, $context = "")
	{
		// Create Notification
		$notification = new notification();
		
		// Build Notification Container
		$notification->build_notification($type, $header = TRUE, $footer = TRUE);
		
		// Retrieve Message
		$message = $notification->get_message($ref, $id);
		$notification->append_content($message);
		
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
			$notification->append_content($customMessage);
		}
		
		// Return the notification holder
		return $notification->get_notification();
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$code
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function error($code)
	{
		// Retrive Error Code
		$err_code = debugger::load_message($code, $type = "ERR");
		
		// Return System Message
		return self::get("error", "debug", "dbg.sys_error", $err_code);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$status
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function statusReport($status)
	{
		// Build popup
		$report_popup = new popup();
		$report_popup->timeout(TRUE);
		$report_popup->fade(TRUE);
		$report_popup->position('top');
		
		// Build Notification
		$report_ntf = new notification();
		
		if ($status)
		{
			$report_ntf->build_notification($type = "success", $header = FALSE, $footer = FALSE);
			$report_message = $report_ntf->get_message("success", "success.save_success");
		}
		else
		{
			$report_ntf->build_notification($type = "error", $header = TRUE, $footer = FALSE);
			$report_message = $report_ntf->get_message("error", "err.save_error");
			$report_popup->timeout(FALSE);
		}
		
		$report_ntf->append_content($report_message);
		$ntf = $report_ntf->get_notification();
		
		return $report_popup->get($ntf);
	}
}
//#section_end#
?>