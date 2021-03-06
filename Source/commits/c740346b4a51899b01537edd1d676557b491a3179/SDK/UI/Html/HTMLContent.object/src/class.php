<?php
//#section#[header]
// Namespace
namespace UI\Html;

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
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("UI", "Developer", "logController");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "pageComponents::htmlComponents::weblink");
importer::import("DEV", "Profiler", "logger");

use \ESS\Protocol\server\HTMLServerReport;
use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \ESS\Prototype\ActionFactory;
use \ESS\Prototype\html\ModuleContainerPrototype;
use \API\Resources\filesystem\fileManager;
use \UI\Developer\logController;
use \UI\Html\DOM;
use \UI\Html\pageComponents\htmlComponents\weblink;
use \DEV\Profiler\logger;

/**
 * HTML Content Object
 * 
 * Creates an html content object for the modules.
 * 
 * @version	{empty}
 * @created	April 5, 2013, 12:41 (EEST)
 * @revised	December 20, 2013, 13:23 (EET)
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
	 * @param	boolean	$loadHtml
	 * 		Load inner html from external file.
	 * 
	 * @return	HTMLContent
	 * 		{description}
	 */
	public function build($id = "", $class = "", $loadHtml = FALSE)
	{
		// Clear Report
		HTMLServerReport::clear();
		
		// Initialize UI Element
		$element = DOM::create("div", "", $id, $class);
		$this->set($element);
		
		// Append object to DOM
		DOM::append($this->get());
		
		// Return object in case of not loading html
		if (!$loadHtml)
			return $this;
		
		return $this->loadHTML();
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
	 * Loads the html content of the php file.
	 * 
	 * @return	HTMLContent
	 * 		The HTMLContent object.
	 */
	private function loadHTML()
	{
		// Get html file
		$parentFilename = $this->getParentFilename();
		$htmlDirectory = dirname($parentFilename);
		$htmlFileName = str_replace(".php", ".html", basename($parentFilename));
		$htmlFileName = $htmlDirectory."/".$htmlFileName;

		// Return html content
		$htmlContent = fileManager::get($htmlFileName);
		
		// Append to root element if not empty
		if (!empty($htmlContent))
			DOM::innerHTML($this->get(), $htmlContent);
		
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
	 * Builds a weblink and gets the DOMElement.
	 * 
	 * @param	string	$href
	 * 		The weblink href attribute.
	 * 
	 * @param	mixed	$content
	 * 		The weblink content. It can be text or DOMElement.
	 * 
	 * @param	string	$target
	 * 		The target attribute. It is "_self" by default.
	 * 
	 * @param	string	$moduleID
	 * 		The module id for module action.
	 * 
	 * @param	string	$action
	 * 		The view name for the module action, if any.
	 * 
	 * @return	DOMElement
	 * 		The DOMElement object.
	 */
	public function getWeblink($href, $content = "", $target = "_self", $moduleID = "", $action = "")
	{
		// Build the weblink
		$weblink = new weblink();
		$element = $weblink->build($href, $target, $content)->get();
		
		// Set module action if any
		if (!empty($moduleID))
		{
			$actionFactory = $this->getActionFactory();
			$actionFactory->setModuleAction($element, $moduleID, $action);
		}
		
		return $element;
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
	 * Adds a report data content to the server report.
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
	 * Adds a report action content to the server report.
	 * 
	 * @param	string	$type
	 * 		The action type.
	 * 
	 * @param	string	$value
	 * 		The action value (if any, empty by default).
	 * 
	 * @return	void
	 */
	public function addReportAction($type, $value = "")
	{
		HTMLServerReport::addAction($type, $value);
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
		// Get logs
		if (logger::status())
		{
			$logContext = logController::getLogs($id = "log_".time(), $head = "Log ".date("H:i:s", time()));
			$this->addReportContent($logContext, "#pageHelper", HTMLServerReport::APPEND_METHOD);
		}

		// Add this object as content
		$this->addReportContent($this->get(), $holder, $method);
		
		// Return Report
		return HTMLServerReport::get();
	}
	
	/**
	 * Gets a DOMElement with a navigation group attribute.
	 * 
	 * @param	string	$id
	 * 		The element id.
	 * 
	 * @param	string	$groupSelector
	 * 		The navigation group selector.
	 * 
	 * @return	DOMElement
	 * 		The navigation group DOMElement.
	 */
	public static function getNavigationGroup($id, $groupSelector)
	{
		// Create Navigation Group
		$navGroup = DOM::create("div", "", $id);
		NavigatorProtocol::selector($navGroup, $groupSelector);
		
		return $navGroup;
	}
	
	/**
	 * Gets the parent's file name.
	 * 
	 * @return	string
	 * 		The parent's script name.
	 */
	protected function getParentFilename()
	{
		$stack = debug_backtrace();
		return $stack[2]['file'];
	}
}
//#section_end#
?>