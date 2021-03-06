<?php
//#section#[header]
// Namespace
namespace UI\Content;

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
 * @package	Content
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports::HTMLServerReport");
importer::import("ESS", "Protocol", "client::NavigatorProtocol");
importer::import("ESS", "Protocol", "loaders::ModuleLoader");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("ESS", "Prototype", "ActionFactory");
importer::import("ESS", "Prototype", "html::ModuleContainerPrototype");
importer::import("SYS", "Resources", "url");
importer::import("API", "Literals", "literal");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "components::weblink");

use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Protocol\loaders\ModuleLoader;
use \ESS\Prototype\UIObjectPrototype;
use \ESS\Prototype\ActionFactory;
use \ESS\Prototype\html\ModuleContainerPrototype;
use \SYS\Resources\url;
use \API\Literals\literal;
use \API\Resources\filesystem\fileManager;
use \UI\Html\DOM;
use \UI\Html\components\weblink;

/**
 * HTML Content Object
 * 
 * Creates an html content object for async communication.
 * 
 * @version	0.1-2
 * @created	July 30, 2014, 12:47 (EEST)
 * @revised	July 30, 2014, 12:48 (EEST)
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
	 * @param	boolean	$loadHTML
	 * 		Indicator whether to load html from the designer file.
	 * 
	 * @return	HTMLContent
	 * 		The HTMLContent object.
	 */
	public function build($id = "", $class = "", $loadHTML = FALSE)
	{
		// Clear Report
		HTMLServerReport::clear();
		
		// Initialize UI Element
		$element = DOM::create("div", "", $id, $class);
		$this->set($element);
		
		// Append object to DOM
		DOM::append($this->get());
		
		// Load html and return element
		if ($loadHTML)
			return $this->loadHTML();
		
		return $this;
	}
	
	/**
	 * Build the HTMLContent element with an element that the user built.
	 * 
	 * @param	DOMElement	$element
	 * 		The user's element.
	 * 
	 * @return	HTMLContent
	 * 		The HTMLContent object.
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
		
		// Set literals
		$this->loadLiterals();
		
		// Set urls
		$this->resolveUrls();
		
		return $this;
	}
	
	/**
	 * Loads the literals inside the html.
	 * 
	 * @return	HTMLContent
	 * 		The HTMLContent object.
	 */
	private function loadLiterals()
	{
		// Search for data-literal
		$containers = DOM::evaluate("//*[@data-literal]");
		foreach ($containers as $container)
		{
			// Get literal attributes
			$value = DOM::attr($container, "data-literal");
			$attributes = json_decode($value, true);
			
			// Literal must have scope value
			if (!isset($attributes['scope']))
				continue;
			
			// Get literal
			$literal = literal::get($attributes['scope'], $attributes['name']);
			
			// If literal is valid, append to container
			if (!empty($literal))
				DOM::append($container, $literal);
			
			// Remove literal attribute
			DOM::attr($container, "data-literal", null);
		}
		
		return $this;
	}
	
	/**
	 * Resolves all data-href attributes to href attributes with a resolved url.
	 * 
	 * @return	HTMLContent
	 * 		The HTMLContent object.
	 */
	private function resolveUrls()
	{
		// Search for data-url
		$containers = DOM::evaluate("//*[@data-href]");
		foreach ($containers as $container)
		{
			// Get url attributes
			$value = DOM::attr($container, "data-href");
			$attributes = json_decode($value, true);
			
			// Resolve Url
			$page = $attributes['page'];
			$domain = $attributes['domain'];
			if (empty($domain))
				$domain = "www";
			
			// Resolve url and set href
			$url = url::resolve($domain, $page);
			DOM::attr($container, "href", $url);
			
			// Remove href attribute
			DOM::attr($container, "data-href", null);
		}
		
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
	 * 		An instance of the Action Factory object.
	 */
	public function getActionFactory()
	{
		return new ActionFactory();
	}
	
	/**
	 * Builds an html weblink.
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
	 * @param	integer	$moduleID
	 * 		The module id for module action.
	 * 		This can be used to load a page module async and redirect.
	 * 		It can be used only for urls of the same subdomain.
	 * 
	 * @param	string	$viewName
	 * 		The view name for the module action, if any.
	 * 		If empty, get the default module view.
	 * 		It is empty by default.
	 * 
	 * @return	DOMElement
	 * 		The DOMElement object.
	 */
	public function getWeblink($href, $content = "", $target = "_self", $moduleID = "", $viewName = "")
	{
		// Build the weblink
		$weblink = new weblink();
		$element = $weblink->build($href, $target, $content)->get();
		
		// Set module action if any
		if (!empty($moduleID))
		{
			$actionFactory = $this->getActionFactory();
			$actionFactory->setModuleAction($element, $moduleID, $viewName);
		}
		
		return $element;
	}
	
	/**
	 * Builds an async module container and returns the DOMElement.
	 * 
	 * @param	integer	$moduleID
	 * 		The module ID to load async.
	 * 
	 * @param	string	$viewName
	 * 		The view name for the module action, if any.
	 * 		If empty, get the default module view.
	 * 		It is empty by default.
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the module with GET method during loading.
	 * 		It is an array (attrName => attrValue)
	 * 
	 * @param	boolean	$startup
	 * 		Sets the module to load at startup (next content.modified).
	 * 
	 * @param	string	$containerID
	 * 		The id attribute of the module container DOM Object.
	 * 
	 * @return	DOMElement
	 * 		The outer module receiver container.
	 */
	public static function getModuleContainer($moduleID, $viewName = "", $attr = array(), $startup = TRUE, $containerID = "")
	{
		$moduleContainer = new ModuleContainerPrototype($moduleID, $viewName);
		return $moduleContainer->build($attr, $startup, $containerID)->get();
	}
	
	/**
	 * Adds a report data content to the server report.
	 * 
	 * @param	DOMElement	$content
	 * 		The DOMElement report content.
	 * 
	 * @param	string	$holder
	 * 		The holder of the content.
	 * 		This holder will be used to append or replace (according to the third parameter) the content.
	 * 		It works as a css selector.
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
	 * 		The report method.
	 * 		For more information, see HTMLServerReport.
	 * 
	 * @return	string
	 * 		The html server report output.
	 */
	public function getReport($holder = "", $method = HTMLServerReport::REPLACE_METHOD)
	{
		// Support loading a module inside another module
		// Check the ModuleLoader's depth if it is bigger than 1
		// If more than one, it's an inner loading and return DOMElement
		if (ModuleLoader::getLoadingDepth() > 1)
		{
			ModuleLoader::decLoadingDepth();
			return $this->get();
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
	 * Sets a navigation group attribute to a given nav group container.
	 * 
	 * @param	DOMElement	$navGroup
	 * 		The navigation group DOMElement.
	 * 
	 * @param	string	$groupSelector
	 * 		The navigation group selector.
	 * 
	 * @return	void
	 */
	public static function setNavigationGroup($navGroup, $groupSelector)
	{
		// Set Navigation Group
		NavigatorProtocol::selector($navGroup, $groupSelector);
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