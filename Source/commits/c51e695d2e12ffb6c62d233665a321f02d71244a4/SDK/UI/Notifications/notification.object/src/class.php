<?php
//#section#[header]
// Namespace
namespace UI\Notifications;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Notifications
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "DOM::DOM");
importer::import("API", "Geoloc", "lang::mlgContent");
importer::import("UI", "Notifications", "message");

use \API\Platform\DOM\DOM;
use \API\Geoloc\lang\mlgContent;
use \UI\Notifications\message;

/**
 * {title}
 * 
 * Usage
 * 
 * @version	{empty}
 * @created	March 11, 2013, 13:05 (EET)
 * @revised	March 11, 2013, 13:05 (EET)
 * 
 * @deprecated	Use \UI\Presentation\notification instead.
 */
class notification extends message
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const support_path = "http://support.redback.gr/notifications/";
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $holder;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $body;
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @param	{type}	$header
	 * 		{description}
	 * 
	 * @param	{type}	$footer
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function build_notification($type = "default", $header = FALSE, $footer = TRUE)
	{
		$type = ($type != "" ? $type : "default");
		
		//____________________ Notification Container
		$holder = DOM::create("div", "", "", "uiNotification ".$type);
		
		//_____ Insert Global Data Container
		$message = parent::get_container();
		DOM::append($holder, $message);
		
		if ($header)
		{
			$title = mlgContent::get_literal("global::dictionary", $type);
			$ntf_head = parent::build_head($title);
			DOM::append($message, $ntf_head);
		}
		
		$ntf_body = parent::build_body();
		DOM::append($message, $ntf_body);
		
		if ($footer)
		{
			$ntf_footer = parent::build_footer();
			$footer_content = $this->_get_footerContent();
			DOM::append($ntf_footer, $footer_content);
			DOM::append($message, $ntf_footer);
		}
		
		$this->holder = $holder;
		$this->body = $ntf_body;
	}
	
	/**
	 * Sets the content of the body
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function append_content($content)
	{
		DOM::append($this->body, $content);
	}
	
	/**
	 * Gets the notification holder
	 * 
	 * @return	void
	 */
	public function get_notification()
	{
		return $this->holder;
	}
	
	/**
	 * Loads a system notification
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$locale
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_message($type, $id, $locale = "")
	{
		$ntf_path = "global::notifications::messages";
		return mlgContent::get_literal($ntf_path."::".$type, $id, TRUE, $locale);
	}
	
	/**
	 * Constructs the notification footer
	 * 
	 * @return	void
	 */
	private function _get_footerContent()
	{
		$footer = DOM::create("div", "", "", "notificationFooter");
				
		// Notification Footer Info
		$info = DOM::create("div", "", "", "sign");
		DOM::append($footer, $info);
		
		$info_id = DOM::create("span","Redback Notification Center &bull; ");
		DOM::append($info, $info_id);
		
		$info_help = DOM::create("a");
		DOM::attr($info_help, 'href', self::support_path);
		DOM::attr($info_help, 'target', "_blank");
		DOM::attr($info_help, 'tabindex', "-1");
		
		$title = mlgContent::get_literal("global::dictionary", "help");
		DOM::append($info_help, $title);
		DOM::append($info, $info_help);
		
		return $footer;
	}
	
	/**
	 * Loads a system notification
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$locale
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function load_notification_message($type, $id, $locale = "")
	{
		$ntf_path = "global::notifications::messages";
		return mlgContent::get_literal($ntf_path."::".$type, $id, TRUE, $locale);
	}
}
//#section_end#
?>