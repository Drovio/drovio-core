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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Environment", "url");
importer::import("ESS", "Protocol", "reports/HTMLServerReport");
importer::import("ESS", "Protocol", "client/NavigatorProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("ESS", "Prototype", "ReportFactory");
importer::import("API", "Literals", "literal");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "components/weblink");

use \ESS\Environment\url;
use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \ESS\Prototype\ReportFactory;
use \API\Literals\literal;
use \API\Resources\filesystem\fileManager;
use \UI\Html\DOM;
use \UI\Html\components\weblink;

/**
 * HTML Content Object
 * 
 * Creates an html content object for async communication.
 * 
 * @version	4.0-1
 * @created	July 30, 2014, 12:47 (EEST)
 * @updated	March 28, 2015, 21:17 (EET)
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
	protected function loadHTML()
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
	protected function loadLiterals()
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
	protected function resolveUrls()
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
	 * Creates a new Report Factory instance.
	 * 
	 * @return	ReportFactory
	 * 		An instance of the Report Factory object.
	 */
	public function getActionFactory()
	{
		return new ReportFactory();
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
	 * @param	string	$class
	 * 		The weblink class attribute.
	 * 
	 * @return	DOMElement
	 * 		The DOMElement object.
	 */
	public function getWeblink($href, $content = "", $target = "_self", $class = "")
	{
		// Build the weblink
		$weblink = new weblink();
		return $weblink->build($href, $target, $content, $class)->get();
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
		HTMLServerReport::addContent($content, $type = HTMLServerReport::CONTENT_HTML, $holder, $method);
	}
	
	/**
	 * Adds a report action content to the server report.
	 * 
	 * @param	string	$name
	 * 		The action event name.
	 * 
	 * @param	string	$value
	 * 		The action value (if any, empty by default).
	 * 
	 * @return	void
	 */
	public function addReportAction($name, $value = "")
	{
		HTMLServerReport::addAction($name, $value);
	}
	
	/**
	 * Add a resource (js or css) header to the content.
	 * 
	 * @param	string	$rsrcID
	 * 		The resource unique id.
	 * 
	 * @param	array	$header
	 * 		The resource parameters.
	 * 
	 * @return	void
	 */
	public function addResourceHeader($rsrcID, $header)
	{
		// Add resource type context
		$header['header_type'] = "rsrc";
		$header['type'] = "rsrc";
		
		// Set resource id
		$header['id'] = $rsrcID;


		// Add header to server report
		HTMLServerReport::addHeader($header);
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
		self::setNavigationGroup($navGroup, $groupSelector);
		
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
	 * Set a navigation handler (static = javascript only) to an element.
	 * 
	 * @param	DOMElement	$element
	 * 		The element to receive the navigation handler.
	 * 
	 * @param	string	$ref
	 * 		The target's id to perform the action.
	 * 		The target will be shown.
	 * 
	 * @param	string	$targetcontainer
	 * 		The container's id of the group in which the target resides
	 * 
	 * @param	string	$targetgroup
	 * 		The group of the items to handle together when performing the action to the target.
	 * 		Set with setNavigationGroup();
	 * 
	 * @param	string	$navgroup
	 * 		The group of navigation items, among which the handler element will be selected.
	 * 
	 * @param	string	$display
	 * 		Defines the type of action for the rest items of the group.
	 * 		Accepted values:
	 * 		- "none" : hides all other items
	 * 		- "all" : shows all other items
	 * 		- "toggle" : toggles the appearance of the handler item.
	 * 
	 * @return	void
	 */
	public static function setStaticNav($element, $ref, $targetcontainer, $targetgroup, $navgroup, $display = "none")
	{
		// Set Navigation handler
		NavigatorProtocol::staticNav($element, $ref, $targetcontainer, $targetgroup, $navgroup, $display);
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