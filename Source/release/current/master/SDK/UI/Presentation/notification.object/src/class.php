<?php
//#section#[header]
// Namespace
namespace UI\Presentation;

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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Literals", "literal");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Prototype", "UIObjectPrototype");

use \API\Literals\literal;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Prototype\UIObjectPrototype;

/**
 * System Notification
 * 
 * Creates a UI notification for all usages.
 * It can be used to notify the user for changes and updates, show warning messages or show succeed messages after a successful post.
 * 
 * @version	1.0-2
 * @created	March 11, 2013, 13:14 (EET)
 * @updated	October 18, 2015, 16:16 (EEST)
 */
class notification extends UIObjectPrototype
{
	/**
	 * The error notification type.
	 * 
	 * @type	string
	 */
	const ERROR = "error";
	
	/**
	 * The warning notification type.
	 * 
	 * @type	string
	 */
	const WARNING = "warning";
	
	/**
	 * The info notification type.
	 * 
	 * @type	string
	 */
	const INFO = "info";
	
	/**
	 * The success notification type.
	 * 
	 * @type	string
	 */
	const SUCCESS = "success";
	
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
	 * 		Use class constants to define this type.
	 * 		It is INFO by default.
	 * 
	 * @param	boolean	$header
	 * 		Specified whether the notification will have header or not.
	 * 		It is FALSE by default.
	 * 
	 * @param	boolean	$timeout
	 * 		If TRUE, sets the notification to fade out after 1.5 seconds.
	 * 		It is FALSE by default.
	 * 
	 * @param	boolean	$disposable
	 * 		Lets the user to be able to close the notification.
	 * 		It is FALSE by default.
	 * 
	 * @return	notification
	 * 		The notification object.
	 */
	public function build($type = self::INFO, $header = FALSE, $timeout = FALSE, $disposable = FALSE)
	{
		// Normalize type
		$type = (empty($type) ? self::INFO : $type);
		
		// Create notification holder
		$notificationHolder = DOM::create("div", "", "", "uiNotification");
		HTML::addClass($notificationHolder, $type);
		$this->set($notificationHolder);
		
		// Set timeout class
		if ($timeout)
			HTML::addClass($notificationHolder, "timeout");
		
		// Build Header (if any)
		if ($header)
			$this->buildHead(literal::dictionary($type), $disposable);

		// Build Body
		return $this->buildBody();
	}
	
	/**
	 * Appends a DOMElement to notification body
	 * 
	 * @param	DOMElement	$content
	 * 		The element to be appended.
	 * 
	 * @return	notification
	 * 		The notification object.
	 */
	public function append($content)
	{
		// Append a valid element to notification body
		if (!empty($content))		
			DOM::append($this->body, $content);
		
		// Return notification object
		return $this;
	}
	
	/**
	 * Creates and appends a custom notification message.
	 * 
	 * @param	mixed	$message
	 * 		The message content (string or DOMElement)
	 * 
	 * @return	object
	 * 		The notification object.
	 */
	public function appendCustomMessage($message)
	{
		$customMessage = DOM::create("div", $message, "", "customMessage");
		return $this->append($customMessage);
	}
	
	/**
	 * Get a notification specific message.
	 * See the full manual for more details.
	 * 
	 * @param	string	$type
	 * 		The notification type.
	 * 		See class constants for reference.
	 * 
	 * @param	string	$id
	 * 		The message name.
	 * 
	 * @return	DOMelement
	 * 		The notification message span.
	 */
	public function getMessage($type, $id)
	{
		return literal::get("global.notifications.messages.".$type, $id);
	}
	
	/**
	 * Builds the notification header.
	 * 
	 * @param	DOMElement	$title
	 * 		The header's title.
	 * 
	 * @param	boolean	$disposable
	 * 		Adds a close button to header and lets the user to be able to close the notification.
	 * 
	 * @return	notification
	 * 		The notification object.
	 */
	private function buildHead($title, $disposable = FALSE)
	{
		// Build Head Element
		$head = DOM::create("div", $title, "", "uiNtfHead");

		// Populate the close button
		if ($disposable)
		{
			$closeBtn = DOM::create("span", "", "", "closeBtn");
			DOM::append($head, $closeBtn);
		}

		// Append To Holder
		DOM::append($this->get(), $head);

		return $this;
	}
	
	/**
	 * Builds the notification body.
	 * 
	 * @return	notification
	 * 		The notification object.
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
}
//#section_end#
?>