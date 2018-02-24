<?php
//#section#[header]
// Namespace
namespace UI\Core\notifications;

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
 * @namespace	\notifications
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Prototype", "UIObjectPrototype");

use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Prototype\UIObjectPrototype;

/**
 * Page notification
 * 
 * Displays notifications on the bottom left position of the page.
 * For system use only.
 * 
 * @version	0.1-1
 * @created	October 24, 2015, 15:37 (BST)
 * @updated	October 24, 2015, 15:37 (BST)
 */
class pageNotification extends UIObjectPrototype
{	
	/**
	 * Build the page notification object.
	 * 
	 * @param	string	$id
	 * 		The notification id.
	 * 
	 * @param	string	$title
	 * 		The notification title.
	 * 
	 * @param	string	$actionTitle
	 * 		The notification action title.
	 * 
	 * @return	pageNotification
	 * 		The pageNotification object.
	 */
	public function build($id, $title, $actionTitle = "")
	{
		// Build notification for async updates
		$ntf = DOM::create("div", "", "", "page-notification");
		$closeButton = DOM::create("div", "", "", "close_button");
		DOM::append($ntf, $closeButton);
		
		// Set notification title
		$hd = DOM::create("div", $title, "", "title");
		DOM::append($ntf, $hd);
		
		// Set action
		if (!empty($actionTitle))
		{
			$actionButton = DOM::create("div", $title, "", "action_button");
			DOM::append($ntf, $actionButton);
		}
		
		return $this;
	}
}
//#section_end#
?>