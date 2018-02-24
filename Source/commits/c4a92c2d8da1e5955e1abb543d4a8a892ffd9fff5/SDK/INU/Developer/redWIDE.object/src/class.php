<?php
//#section#[header]
// Namespace
namespace INU\Developer;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	INU
 * @package	Developer
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::HTMLServerReport");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "tabControl");

use \ESS\Protocol\server\HTMLServerReport;
use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;
use \UI\Presentation\tabControl;

/**
 * Web Integrated Development Environment
 * 
 * A tab-oriented object for handling multiple data objects.
 * 
 * @version	{empty}
 * @created	April 26, 2013, 13:38 (EEST)
 * @revised	September 21, 2013, 15:25 (EEST)
 */
class redWIDE extends UIObjectPrototype
{
	/**
	 * The tabControl object of the environment
	 * 
	 * @type	tabControl
	 */
	protected $WIDETabber;
	
	/**
	 * Builds a redWIDE object
	 * 
	 * @param	string	$id
	 * 		The wide's id.
	 * 
	 * @return	redWIDE
	 * 		redWIDE object.
	 */
	public function build($id = "redWIDE")
	{
		// Create outer Wrapper
		$holder = DOM::create("div", "", $id, "wide");
		$this->set($holder);
		
		// Build Receiver Pool
		$pool = DOM::create("div", "", "", "dropPool");
		DOM::append($holder, $pool);
		
		// Build Notification receiver
		$pool = DOM::create("div", "", "", "result");
		DOM::append($holder, $pool);
		
		// Create outer tabControl
		$this->WIDETabber = new tabControl(TRUE);
		
		$tabber = $this->WIDETabber->build($id."_WIDETabber", $full = FALSE)->get();
		DOM::append($holder, $tabber);
		
		return $this;
	}
	
	/**
	 * Returns the associated WIDETabber of the WIDE
	 * 
	 * @return	tabControl
	 * 		tabControl object.
	 */
	public function getTabber()
	{
		return $this->WIDETabber;
	}
	
	/**
	 * Get a tab page content for the WIDETabber
	 * 
	 * @param	string	$id
	 * 		The id of the tab page
	 * 
	 * @param	string	$header
	 * 		The header of the tab page
	 * 
	 * @param	DOMElement	$content
	 * 		The contents of the tab page
	 * 
	 * @return	DOMElement
	 * 		Returns a tab page ready for wide.
	 */
	public static function getContent($id, $header, $content)
	{
		// Build Container
		$container = DOM::create("div", "", "", "wideContainer");
		
		// Information Positioning Data
		$info = array();
		$info['id'] = $id;
		$info['title'] = $header;
		DOM::data($container, "info", $info);

		// Append Content
		DOM::append($container, $content);
		
		// Return Container
		return $container;
	}
	
	/**
	 * Returns a report ready for wide notification.
	 * 
	 * @param	DOMElement	$notification
	 * 		The notification element.
	 * 
	 * @param	boolean	$updateStatus
	 * 		Whether wide updates document status.
	 * 
	 * @param	string	$id
	 * 		The wide's id.
	 * 
	 * @return	string
	 * 		The HTMLServerReport string.
	 */
	public static function getNotificationResult($notification, $updateStatus = TRUE, $id = "redWIDE")
	{
		// Clear Report
		HTMLServerReport::clear();
		
		// Add status update action
		if ($updateStatus)
			HTMLServerReport::addAction("content.saved");
		
		// Add the notification as content
		$holder = "#".$id.".wide > .result";
		HTMLServerReport::addContent($notification, "data", $holder, "replace");
		
		// Return Report
		return HTMLServerReport::get();
	}
	
	/**
	 * Returns an HTMLServerReport containg the WIDE tab page content.
	 * 
	 * @param	string	$id
	 * 		The page id.
	 * 
	 * @param	string	$header
	 * 		The tab header.
	 * 
	 * @param	DOMElement	$content
	 * 		The content of the tab.
	 * 
	 * @param	string	$wideID
	 * 		The wide's id.
	 * 
	 * @return	string
	 * 		The HTMLServerReport string.
	 */
	public function getReportContent($id, $header, $content, $wideID = "redWIDE")
	{
		// Get Tab Page Container
		$tabContainer = $this->getContent($id, $header, $content);
		
		// Clear Report
		HTMLServerReport::clear();
		
		// Add this modulePage as content
		$holder = "#".$wideID.".wide > .dropPool";
		HTMLServerReport::addContent($tabContainer, "data", $holder, "append");
		
		// Return Report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>