<?php
//#section#[header]
// Namespace
namespace UI\Presentation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Presentation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Resources", "literals::literal");
importer::import("API", "Resources", "url");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\literals\literal;
use \API\Resources\url;
use \UI\Html\DOM;

/**
 * System Notification
 * 
 * Creates a ui system notification.
 * 
 * @version	{empty}
 * @created	March 11, 2013, 13:14 (EET)
 * @revised	October 30, 2013, 13:28 (EET)
 */
class notification extends UIObjectPrototype
{
	/**
	 * The notification's body.
	 * 
	 * @type	DOMElement
	 */
	private $body;
	
	/**
	 * Builds the notification.
	 * 
	 * @param	string	$type
	 * 		The notification's type.
	 * 		Accepted values:
	 * 		- "default"
	 * 		- "error"
	 * 		- "warning"
	 * 		- "success"
	 * 		- "info"
	 * 
	 * @param	boolean	$header
	 * 		Specified whether the notification will have header
	 * 
	 * @param	boolean	$footer
	 * 		Specified whether the notification will have footer
	 * 
	 * @param	boolean	$timeout
	 * 		Sets the notification to fadeout after 1.5 seconds.
	 * 
	 * @return	notification
	 * 		The notification object.
	 */
	public function build($type = "default", $header = FALSE, $footer = FALSE, $timeout = FALSE)
	{
		// Normalize type
		$type = ($type != "" ? $type : "default");
		
		// Create notification holder
		$notificationHolder = DOM::create("div", "", "", "uiNotification ".$type.($timeout ? " timeout" : ""));
		$this->set($notificationHolder);
		
		// Build Header (if any)
		if ($header)
			$this->buildHead(literal::get("global::dictionary", $type));

		// Build Body
		$this->buildBody();
		
		// Build Footer (if any)
		if ($footer)
			$this->buildFooter();
		
		return $this;
	}
	
	/**
	 * Appends a DOMElement to notification body
	 * 
	 * @param	DOMElement	$content
	 * 		The element to be appended
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function append($content)
	{
		DOM::append($this->body, $content);
		return $this;
	}
	
	/**
	 * Creates and appends a custom notification message.
	 * 
	 * @param	mixed	$message
	 * 		The message content (string or DOMElement)
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function appendCustomMessage($message)
	{
		if (gettype($message) == "string")
			$customMessage = DOM::create("div", $message, "", "customMessage");
		else
		{
			$customMessage = DOM::create("div", "", "", "customMessage");
			DOM::append($customMessage, $message);
		}
		
		return $this->append($customMessage);
	}
	
	/**
	 * Returns a system notification message
	 * 
	 * @param	string	$type
	 * 		The notification type.
	 * 
	 * @param	string	$id
	 * 		The message id
	 * 
	 * @return	DOMelement
	 * 		{description}
	 */
	public function getMessage($type, $id)
	{
		return literal::get("global::notifications::messages::".$type, $id);
	}
	
	/**
	 * Builds the notification header
	 * 
	 * @param	DOMElement	$title
	 * 		The header's title
	 * 
	 * @return	notification
	 * 		{description}
	 */
	private function buildHead($title)
	{
		// Build Head Element
		$head = DOM::create("div", "", "", "uiNtfHead");
		DOM::append($head, $title);

		// Populate the close button
		$closeBtn = DOM::create("a", "", "", "uiNtfAction");
		DOM::attr($closeBtn, 'href', "");
		DOM::attr($closeBtn, 'target', "");
		DOM::append($head, $closeBtn);

		// Append To Holder
		DOM::append($this->get(), $head);

		return $this;
	}
	
	/**
	 * Builds the notification body
	 * 
	 * @return	notification
	 * 		{description}
	 */
	private function buildBody()
	{
		// Build Body Element
		$body = DOM::create("div", "", "", "uiNtfBody");
		$this->body = $body;
		
		// Populate the notification icon
		$icon = DOM::create("span", "", "", 'uiNtfIcon');
		DOM::append($body, $icon);
		
		// Append To Holder
		DOM::append($this->get(), $body);
		
		return $this;
	}
	
	/**
	 * Builds the notification footer
	 * 
	 * @return	notification
	 * 		{description}
	 */
	private function buildFooter()
	{
		// Create Footer Element
		$footerHolder = DOM::create("div", "", "", "uiNtfFooter");
				
		// Populate Notification Footer
		$info = DOM::create("div", "", "", "sign");
		DOM::append($footerHolder, $info);
		
		$info_id = DOM::create("span", "Redback Notification Center &bull; ");
		DOM::append($info, $info_id);
		
		$info_help = DOM::create("a");
		DOM::attr($info_help, 'href', Url::resolve("support", "/notifications/"));
		DOM::attr($info_help, 'target', "_blank");
		DOM::attr($info_help, 'tabindex', "-1");
		
		$title = literal::get("global::dictionary", "help");
		DOM::append($info_help, $title);
		DOM::append($info, $info_help);
		
		// Append To Holder
		DOM::append($this->get(), $footerHolder);
		
		return $this;
	}
}
//#section_end#
?>