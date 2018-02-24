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
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Literals", "literal");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \API\Literals\literal;
use \UI\Html\DOM;

/**
 * System Notification
 * 
 * Creates a ui system notification.
 * 
 * @version	{empty}
 * @created	March 11, 2013, 13:14 (EET)
 * @revised	May 15, 2014, 15:44 (EEST)
 */
class notification extends UIObjectPrototype
{
	/**
	 * The error notification indicator.
	 * 
	 * @type	string
	 */
	const ERROR = "error";
	/**
	 * The warning notification indicator.
	 * 
	 * @type	string
	 */
	const WARNING = "warning";
	/**
	 * The info notification indicator.
	 * 
	 * @type	string
	 */
	const INFO = "info";
	/**
	 * The success notification indicator.
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
	 * 		The notification's type. See class constants for better explanation.
	 * 
	 * @param	boolean	$header
	 * 		Specified whether the notification will have header
	 * 
	 * @param	boolean	$timeout
	 * 		Sets the notification to fadeout after 1.5 seconds.
	 * 
	 * @param	boolean	$timeout2
	 * 		It is used for backwards compatibility for the timeout attribute.
	 * 
	 * @return	notification
	 * 		The notification object.
	 */
	public function build($type = self::INFO, $header = FALSE, $timeout = FALSE, $timeout2 = FALSE)
	{
		// Normalize type
		$type = ($type != "" ? $type : "default");
		$timeout = ($timeout2 ? TRUE : $timeout);
		
		// Create notification holder
		$notificationHolder = DOM::create("div", "", "", "uiNotification ".$type.($timeout ? " timeout" : ""));
		$this->set($notificationHolder);
		
		// Build Header (if any)
		if ($header)
			$this->buildHead(literal::get("global::dictionary", $type));

		// Build Body
		$this->buildBody();
		
		return $this;
	}
	
	/**
	 * Appends a DOMElement to notification body
	 * 
	 * @param	DOMElement	$content
	 * 		The element to be appended
	 * 
	 * @return	object
	 * 		The notification object.
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
	 * @return	object
	 * 		The notification object.
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
	 * Returns a system notification message.
	 * 
	 * @param	string	$type
	 * 		The notification type.
	 * 
	 * @param	string	$id
	 * 		The message id
	 * 
	 * @return	DOMelement
	 * 		The notification message span.
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
	 * 		The notification object.
	 */
	private function buildHead($title)
	{
		// Build Head Element
		$head = DOM::create("div", "", "", "uiNtfHead");
		DOM::append($head, $title);

		// Populate the close button
		$closeBtn = DOM::create("span", "", "", "closeBtn");
		DOM::append($head, $closeBtn);

		// Append To Holder
		DOM::append($this->get(), $head);

		return $this;
	}
	
	/**
	 * Builds the notification body
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