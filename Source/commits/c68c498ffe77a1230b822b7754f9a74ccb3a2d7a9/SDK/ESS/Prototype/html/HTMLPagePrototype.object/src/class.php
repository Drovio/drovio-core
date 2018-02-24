<?php
//#section#[header]
// Namespace
namespace ESS\Prototype\html;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Prototype
 * @namespace	\html
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports/ServerReport");
importer::import("ESS", "Protocol", "http/HTTPResponse");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\reports\ServerReport;
use \ESS\Protocol\http\HTTPResponse;
use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * HTML Page Prototype/Builder
 * 
 * Helps building HTML Pages in HTML5 format
 * 
 * @version	3.0-3
 * @created	March 7, 2013, 12:04 (EET)
 * @updated	March 24, 2015, 11:51 (EET)
 */
class HTMLPagePrototype extends UIObjectPrototype
{
	/**
	 * The head tag object
	 * 
	 * @type	DOMElement
	 */
	protected $HTMLHead;
	/**
	 * The body tag object
	 * 
	 * @type	DOMElement
	 */
	protected $HTMLBody;
	
	/**
	 * Keeps the scripts to be inserted in the bottom of the page before exporting,
	 * 
	 * @type	Array
	 */
	private $bottomScripts;
	
	/**
	 * Constructor Method
	 * 
	 * @param	boolean	$initialize
	 * 		Whether to initialize the DOM or not.
	 * 
	 * @return	void
	 */
	protected function __construct($initialize = TRUE)
	{
		// Initialize-Clear Bottom Scripts
		$this->bottomScripts = array();
		
		// Initialize DOM
		if ($initialize)
			DOM::initialize();
	}
	
	/**
	 * Builds the spine of the page
	 * 
	 * @param	string	$title
	 * 		The title tag of the page. It is a required field for the document to be valid
	 * 
	 * @param	string	$description
	 * 		The description meta value
	 * 
	 * @param	string	$keywords
	 * 		The keywords meta value
	 * 
	 * @return	HTMLPagePrototype
	 * 		The HTMLPagePrototype object.
	 */
	public function build($title = "", $description = "", $keywords = "")
	{
		// Build HTML
		$HTML = DOM::create('html');
		$this->set($HTML);
		DOM::append($HTML);
		
		// Build HEAD
		$HTMLHead = DOM::create('head');
		DOM::append($HTML, $HTMLHead);
		$this->HTMLHead = $HTMLHead;
		
		// Build META
		$this->buildMeta($title, $description, $keywords);
		
		// Build BODY
		$HTMLBody = DOM::create('body');
		DOM::append($HTML, $HTMLBody);
		$this->HTMLBody = $HTMLBody;
		
		return $this;
	}
	
	/**
	 * Returns the entire HTML page in HTML5 format
	 * 
	 * @return	string
	 * 		The html output.
	 */
	public function getHTML()
	{
		// Insert Bottom Scripts (if any)
		$this->flushBottomScripts();
		
		// Set response headers
		ServerReport::setResponseHeaders(HTTPResponse::CONTENT_TEXT_HTML);
		
		// Return text/html
		$output .= "<!DOCTYPE html>";
		$output .= DOM::getHTML();
		return $output;
	}
	
	/**
	 * Returns the head tag object
	 * 
	 * @return	DOMElement
	 * 		The head element.
	 */
	public function getHead()
	{
		return $this->HTMLHead;
	}
	
	/**
	 * Returns the body tag object
	 * 
	 * @return	DOMElement
	 * 		The body element.
	 */
	public function getBody()
	{
		return $this->HTMLBody;
	}
	
	/**
	 * Append element to head
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended
	 * 
	 * @return	void
	 */
	protected function appendToHead($element)
	{
		if (is_null($element))
			return;
			
		DOM::append($this->HTMLHead, $element);
	}
	
	/**
	 * Append element to body
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended
	 * 
	 * @return	void
	 */
	protected function appendToBody($element)
	{
		if (is_null($element))
			return;
			
		DOM::append($this->HTMLBody, $element);
	}
	
	/**
	 * Add a meta description to head
	 * 
	 * @param	string	$name
	 * 		The meta name attribute
	 * 
	 * @param	string	$content
	 * 		The meta content attribute
	 * 
	 * @param	string	$httpEquiv
	 * 		The meta http-equiv attribute
	 * 
	 * @param	string	$charset
	 * 		The meta charset attribute
	 * 
	 * @return	DOMElement
	 * 		The meta element.
	 */
	protected function addMeta($name = "", $content = "", $httpEquiv = "", $charset = "")
	{
		$meta = DOM::create('meta');
		DOM::attr($meta, "name", $name);
		DOM::attr($meta, "http-equiv", $httpEquiv);
		DOM::attr($meta, "content", htmlspecialchars($content));
		DOM::attr($meta, "charset", $charset);
		
		$this->appendToHead($meta);
		
		return $meta;
	}
	
	/**
	 * Inserts a css line
	 * 
	 * @param	string	$href
	 * 		The href attribute of the link
	 * 
	 * @return	DOMElement
	 * 		The style element created.
	 */
	public function addStyle($href)
	{
		$css = $this->getLink("stylesheet", $href);
		$this->appendToHead($css);
		return $css;
	}
	
	/**
	 * Inserts a script line
	 * 
	 * @param	string	$src
	 * 		The URL source file of the script
	 * 
	 * @param	boolean	$bottom
	 * 		Indicator whether the script tag will be placed at the bottom of the page.
	 * 		The default value is FALSE.
	 * 
	 * @return	DOMElement
	 * 		The script element created.
	 */
	public function addScript($src, $bottom = FALSE)
	{
		$script = DOM::create("script");
		DOM::attr($script, "src", $src);
		
		if ($bottom)
			$this->addToBottomScripts($script);
		else
			$this->appendToHead($script);
		
		return $script;
	}
	
	/**
	 * Inserts a page icon
	 * 
	 * @param	string	$href
	 * 		The icon URL
	 * 
	 * @return	void
	 */
	public function addIcon($href)
	{
		$icon = $this->getLink("icon", $href);
		$this->appendToHead($icon);
		
		$shortIcon = $this->getLink("shortcut icon", $href);
		$this->appendToHead($shortIcon);
	}
	
	/**
	 * Sets the page title.
	 * 
	 * @param	string	$title
	 * 		The new page title.
	 * 
	 * @return	void
	 */
	public function setTitle($title)
	{
		// Check if title already exists
		$headTitle = DOM::evaluate("//title")->item(0);
		if (!is_null($headTitle))
		{
			$new_headTitle = DOM::create("title", $title);
			DOM::replace($headTitle, $new_headTitle);
		}
		else
		{
			$headTitle = DOM::create("title", $title);
			$this->appendToHead($headTitle);
		}
	}
	
	/**
	 * Add open graph meta properties to the page.
	 * 
	 * @param	array	$data
	 * 		An array of property => content open graph meta.
	 * 		The og: at the property name is inserted automatically.
	 * 
	 * @return	void
	 */
	public function addOpenGraphMeta($data = array())
	{
		foreach ($data as $property => $content)
		{
			$og = DOM::create("meta");
			DOM::attr($og, "property", "og:".$property);
			DOM::attr($og, "content", $content);
			$this->appendToHead($og);
		}
	}
	
	/**
	 * Creates and returns a link tag object
	 * 
	 * @param	string	$rel
	 * 		The rel attribute of the link
	 * 
	 * @param	string	$href
	 * 		The href URL of the link
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	private function getLink($rel, $href)
	{
		$link = DOM::create("link");
		DOM::attr($link, "href", $href);
		DOM::attr($link, "rel", $rel);
		
		return $link;
	}
	
	/**
	 * Builds all the meta tags along with the document title tag
	 * 
	 * @param	string	$title
	 * 		The title of the document
	 * 
	 * @param	string	$description
	 * 		The description meta
	 * 
	 * @param	string	$keywords
	 * 		The keywords meta
	 * 
	 * @return	void
	 */
	private function buildMeta($title, $description, $keywords)
	{
		// Create title tag
		$this->setTitle($title);
		
		// Create meta tags
		$this->addMeta($name = "", $content = "", $httpEquiv = "", $charset = "UTF-8");
		if (!empty($description))
			$this->addMeta($name = "description", $description);
		if (!empty($keywords))
			$this->addMeta($name = "keywords", $keywords);
	}
	
	/**
	 * Insert the given script tag to stack, in order to be inserted at the bottom of the page right before delivering the page
	 * 
	 * @param	DOMElement	$script
	 * 		The script tag element
	 * 
	 * @return	void
	 */
	private function addToBottomScripts($script)
	{
		$this->bottomScripts[] = $script;
	}
	
	/**
	 * Appends all scripts to the body
	 * 
	 * @return	void
	 */
	private function flushBottomScripts()
	{
		foreach ($this->bottomScripts as $script)
			$this->appendToBody($script);
	}
}
//#section_end#
?>