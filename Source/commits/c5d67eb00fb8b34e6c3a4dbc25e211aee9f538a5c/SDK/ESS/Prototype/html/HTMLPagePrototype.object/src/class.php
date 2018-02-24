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
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * HTML Page Prototype/Builder
 * 
 * Helps building HTML Pages in HTML5 format
 * 
 * @version	{empty}
 * @created	March 7, 2013, 12:04 (EET)
 * @revised	December 19, 2013, 12:40 (EET)
 */
class HTMLPagePrototype extends UIObjectPrototype
{
	/**
	 * The html tag object
	 * 
	 * @type	DOMElement
	 */
	protected $HTML;
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
	 * @return	void
	 */
	public function __construct()
	{
		// Initialize-Clear Bottom Scripts
		$this->bottomScripts = array();
		
		// Initialize DOM
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
	 * @return	void
	 */
	public function build($title = "", $description = "", $keywords = "")
	{
		// Build HTML
		$HTML = DOM::create('html');
		$this->set($HTML);
		DOM::append($HTML);
		
		$this->HTML = $HTML;
		
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
	 * 		{description}
	 */
	public function get()
	{
		// Insert Bottom Scripts (if any)
		$this->flushBottomScripts();
		
		// Return text/html
		$output .= "<!DOCTYPE html>";
		$output .= DOM::getHTML();
		return $output;
	}
	
	/**
	 * Returns the html tag object
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function getHTML()
	{
		return $this->HTML;
	}
	
	/**
	 * Returns the head tag object
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function getHead()
	{
		return $this->HTMLHead;
	}
	
	/**
	 * Returns the body tag object
	 * 
	 * @return	DOMElement
	 * 		{description}
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
	 * 		{description}
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
	 * 		{description}
	 */
	protected function addStyle($href)
	{
		$css = $this->getLink("stylesheet", $href);
		DOM::attr($css, "data-id", md5("css_".$href));
		
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
	 * 		{description}
	 */
	protected function addScript($src, $bottom = FALSE)
	{
		$script = DOM::create("script");
		DOM::attr($script, "src", $src);
		DOM::attr($script, "data-id", md5("js_".$src));
		
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
	protected function addIcon($href)
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
	protected function setTitle($title)
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
		DOM::attr($link, "rel", $rel);
		DOM::attr($link, "href", $href);
		
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