<?php
//#section#[header]
// Namespace
namespace UI\Html;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Html
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::HTMLServerReport");
importer::import("ESS", "Protocol", "client::NavigatorProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("ESS", "Prototype", "ActionFactory");
importer::import("ESS", "Prototype", "html::ModuleContainerPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\server\HTMLServerReport;
use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \ESS\Prototype\ActionFactory;
use \ESS\Prototype\html\ModuleContainerPrototype;
use \UI\Html\DOM;

/**
 * HTML Content Object
 * 
 * Creates an html content object for the modules.
 * 
 * @version	{empty}
 * @created	April 5, 2013, 12:41 (EEST)
 * @revised	December 11, 2013, 12:28 (EET)
 */
class HTMLContent extends UIObjectPrototype
{
	/**
	 * Build the outer html content container.
	 * 
	 * @param	string	$id
	 * 		The element's id.
	 * 
	 * @param	string	$class
	 * 		The element's class.
	 * 
	 * @return	HTMLContent
	 * 		{description}
	 */
	public function build($id = "", $class = "")
	{
		// Clear Report
		HTMLServerReport::clear();
		
		// Initialize UI Element
		$element = DOM::create("div", "", $id, $class);
		$this->set($element);
		
		return $this;
	}
	
	/**
	 * Build the HTMLContent element with an element that the user built.
	 * 
	 * @param	DOMElement	$element
	 * 		The user's element.
	 * 
	 * @return	HTMLContent
	 * 		{description}
	 */
	public function buildElement($element)
	{
		$this->set($element);
		return $this;
	}
	
	/**
	 * Appends an element to the HTMLContent root element.
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended.
	 * 
	 * @return	mixed
	 * 		Returns NULL if the element given is empty. Returns the HTMLContent object otherwise.
	 */
	public function append($element)
	{
		if (!empty($element))
			DOM::append($this->get(), $element);
		
		return $this;
	}
	
	/**
	 * Creates a new Action Factory.
	 * 
	 * @return	ActionFactory
	 * 		Returns an instance of the Action Factory.
	 */
	public function getActionFactory()
	{
		return new ActionFactory();
	}
	
	/**
	 * Builds a module container and returns the DOMElement.
	 * 
	 * @param	integer	$moduleID
	 * 		The module ID.
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module (if any).
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the module with GET method during loading.
	 * 
	 * @param	boolean	$startup
	 * 		Sets the module to load at startup (next content.modified).
	 * 
	 * @param	string	$containerID
	 * 		The id of the module container as a DOM Object.
	 * 
	 * @return	DOMElement
	 * 		The outer container.
	 */
	public static function getModuleContainer($moduleID, $action = "", $attr = array(), $startup = TRUE, $containerID = "")
	{
		$moduleContainer = new ModuleContainerPrototype($moduleID, $action);
		return $moduleContainer->build($attr, $startup, $containerID)->get();
	}
	
	/**
	 * Adds a report content to the server report.
	 * 
	 * @param	DOMElement	$content
	 * 		The DOMElement report content.
	 * 
	 * @param	string	$holder
	 * 		The holder of the content (as a css selector).
	 * 
	 * @param	string	$method
	 * 		The HTMLServerReport replace or append method (use const).
	 * 
	 * @return	void
	 */
	public function addReportContent($content, $holder = "", $method = HTMLServerReport::APPEND_METHOD)
	{
		HTMLServerReport::addContent($content, $type = "data", $holder, $method);
	}
	
	/**
	 * Returns the HTML Report for this html content.
	 * 
	 * @param	string	$holder
	 * 		The holder to get the content.
	 * 
	 * @param	string	$method
	 * 		The report method. For more information, see HTMLServerReport.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getReport($holder = "", $method = HTMLServerReport::REPLACE_METHOD)
	{
		// Add this object as content
		$this->addReportContent($this->get(), $holder, $method);
		
		// Return Report
		return HTMLServerReport::get();
	}
	
	/**
	 * Creates a static navigation group.
	 * For more information, see the NavigatorProtocol.
	 * 
	 * @param	string	$id
	 * 		The id of the navigation group.
	 * 
	 * @param	string	$groupSelector
	 * 		The static navigation selector for the group.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public static function getNavigationGroup($id, $groupSelector)
	{
		// Create Navigation Group
		$navGroup = DOM::create("div", "", $id);
		NavigatorProtocol::selector($navGroup, $groupSelector);
		
		return $navGroup;
	}
}
//#section_end#
?>