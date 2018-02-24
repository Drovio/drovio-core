<?php
//#section#[header]
// Namespace
namespace UI\Developer;

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
 * @package	Developer
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports::HTMLServerReport");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "tabControl");

use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;
use \UI\Presentation\tabControl;

/**
 * Developer's Dynamic Tab Control
 * 
 * It creates a virtual 'WIDE - Web Integrated Development Environment'.
 * It is a tab-oriented container for handling multiple data objects asynchronously.
 * You can load editable tabs with any content you like. It can handle saving data with Redback's codeEditor.
 * 
 * @version	0.1-1
 * @created	September 8, 2014, 15:21 (EEST)
 * @revised	September 8, 2014, 15:21 (EEST)
 */
class devTabber extends UIObjectPrototype
{
	/**
	 * The tabControl manager object.
	 * 
	 * @type	tabControl
	 */
	protected $tabber;
	
	/**
	 * Builds the developer's dynamic tab control
	 * 
	 * @param	string	$id
	 * 		The tabber's id.
	 * 
	 * @return	devTabber
	 * 		The devTabber object.
	 */
	public function build($id = "redWIDE")
	{
		// Create outer Wrapper
		$id = ($id == "" ? "wd".mt_rand() : $id);
		$holder = DOM::create("div", "", $id, "wide");
		$this->set($holder);
		
		// Build Receiver Pool
		$pool = DOM::create("div", "", "", "dropPool");
		DOM::append($holder, $pool);
		
		// Build Notification receiver
		$pool = DOM::create("div", "", "", "result");
		DOM::append($holder, $pool);
		
		// Create outer tabControl
		$this->tabber = new tabControl(TRUE);
		
		$tabber = $this->tabber->build($id."_tabber", $full = FALSE)->get();
		DOM::append($holder, $tabber);
		
		return $this;
	}
	
	/**
	 * Ge the tabControl manager object.
	 * 
	 * @return	tabControl
	 * 		The tabControl manager object.
	 */
	public function getTabber()
	{
		return $this->tabber;
	}
	
	/**
	 * Builds a specific tab page for this 'environment'.
	 * 
	 * @param	string	$id
	 * 		The id of the tab page.
	 * 
	 * @param	string	$header
	 * 		The page header.
	 * 
	 * @param	DOMElement	$content
	 * 		The contents of the tab page.
	 * 
	 * @return	DOMElement
	 * 		A tab page ready for this control.
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
	 * Returns a report ready for notification.
	 * 
	 * @param	DOMElement	$notification
	 * 		The notification element.
	 * 
	 * @param	boolean	$updateStatus
	 * 		Whether wide updates document status.
	 * 
	 * @param	string	$id
	 * 		The control's id to handle this notification.
	 * 
	 * @return	string
	 * 		The report string as the prototype reporting protocol define.
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
	 * Returns an HTMLServerReport containing a tab page content.
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
	 * @param	string	$containerID
	 * 		The control's id to get this content.
	 * 
	 * @return	string
	 * 		The report string as the prototype reporting protocol define.
	 */
	public function getReportContent($id, $header, $content, $containerID = "redWIDE")
	{
		// Get Tab Page Container
		$tabContainer = $this->getContent($id, $header, $content);
		
		// Clear Report
		HTMLServerReport::clear();
		
		// Add this modulePage as content
		$holder = "#".$containerID.".wide > .dropPool";
		HTMLServerReport::addContent($tabContainer, "data", $holder, "append");
		
		// Return Report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>