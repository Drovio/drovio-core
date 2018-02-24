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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Literals", "literal");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");

use \ESS\Prototype\UIObjectPrototype;
use \API\Literals\literal;
use \UI\Html\DOM;
use \UI\Html\HTML;

/**
 * System Notification
 * 
 * Creates a UI notification for all usages.
 * It can be used to notify the user for changes and updates, show warning messages or show succeed messages after a successful post.
 * 
 * @version	0.1-2
 * @created	March 11, 2013, 13:14 (EET)
 * @updated	March 20, 2015, 16:38 (EET)
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
	 * @param	boolean	$disposable
	 * 		Lets the user to be able to close the notification.
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
		if ($timeout)
			HTML::addClass($notificationHolder, "timeout");
		$this->set($notificationHolder);
		
		// Build Header (if any)
		if ($header)
			$this->buildHead(literal::dictionary($type), $disposable);

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
		$customMessage = DOM::create("div", $message, "", "customMessage");
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