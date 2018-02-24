<?php
//#section#[header]
// Namespace
namespace ESS\Templates;

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
 * @package	Templates
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "html::HTMLPagePrototype");
importer::import("ESS", "Protocol", "client::environment::Url");
importer::import("API", "Resources", "storage::cookies");
importer::import("API", "Resources", "geoloc::region");
importer::import("API", "Resources", "geoloc::locale");
importer::import("API", "Resources", "literals::literal");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\html\HTMLPagePrototype;
use \ESS\Protocol\client\environment\Url;
use \API\Resources\storage\cookies;
use \API\Resources\geoloc\region;
use \API\Resources\geoloc\locale;
use \API\Resources\literals\literal;
use \UI\Html\DOM;

/**
 * Redback HTML Page Template
 * 
 * This is the base template for all redback pages.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 12:08 (EET)
 * @revised	March 28, 2014, 10:58 (EET)
 */
class HTMLPageTemplate extends HTMLPagePrototype
{	
	/**
	 * Builds the base template with all the necessary head tags. It doesn't include building anything about user interface.
	 * 
	 * @param	string	$title
	 * 		The page title head meta.
	 * 
	 * @param	string	$lang
	 * 		The current page language.
	 * 
	 * @param	boolean	$meta
	 * 		Indicates whether to include meta (description and keywords) in the page.
	 * 
	 * @return	HTMLPageTemplate
	 * 		This object
	 */
	public function build($title = "", $lang = "", $meta = FALSE)
	{
		// Build PagePrototype with settings Meta Content
		$title = ($title == "" ? "Redback" : $title);
		
		// Include meta
		if ($meta)
		{
			$metaDescription = literal::get("global.meta", "description", FALSE);
			$metaKeywords = literal::get("global.meta", "keywords", FALSE);
		}
		parent::build($title, $metaDescription, $metaKeywords);
		
		// Set HTML Attributes
		DOM::attr($this->HTML, "id", "redback");
		DOM::attr($this->HTML, "lang", $lang);
		
		// Set Head Content
		$this->setHead($meta);
		
		return $this;
	}
	
	/**
	 * Inserts a css line.
	 * 
	 * @param	string	$href
	 * 		The href attribute of the link
	 * 
	 * @param	boolean	$static
	 * 		Whether the css resource is static or not.
	 * 
	 * @return	DOMElement
	 * 		The css element.
	 */
	protected function addStyle($href, $static = TRUE)
	{
		$css = parent::addStyle($href);
		DOM::attr($css, "data-id", md5("css_".$href));
		DOM::attr($css, "data-static", $static);
		
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
	 * @param	boolean	$static
	 * 		Whether the js resource is static or not.
	 * 
	 * @return	DOMElement
	 * 		The js element.
	 */
	protected function addScript($src, $bottom = FALSE, $static = TRUE)
	{
		$script = parent::addScript($src, $bottom);
		DOM::attr($script, "data-id", ($static ? "static:" : "").md5("js_".$src));
		
		return $script;
	}
	
	/**
	 * Inserts all the meta tags, scripts and styles to head.
	 * 
	 * @return	void
	 */
	private function setHead()
	{
		// Create extra meta content (Identity)
		$this->addMeta($name = "author", literal::get("global.meta", "author", FALSE), $httpEquiv = "", $charset = "");
		
		// Robots Indexing
		$this->addMeta($name = "robots", $content = "FOLLOW, NOODP, NOYDIR", $httpEquiv = "", $charset = "");
		
		// Generator
		$this->addMeta($name = "generator", $content = "Redback Web Engine", $httpEquiv = "", $charset = "");
		
		// Redback Icon
		$this->addIcon(Url::resource("/Library/Media/c/logos/favicon.ico"));
		
		// No Javascript
		$this->addNoScript();
	}
	
	/**
	 * Inserts the noscript tag.
	 * 
	 * @return	void
	 */
	private function addNoScript()
	{
		if (!cookies::get("noscript") && !isset($_GET['_rb_noscript']))
		{
			// noscript tag
			$noScriptTag = DOM::create("noscript");
			$this->appendToHead($noScriptTag);
			
			// Meta Refresh Tag
			$sep = (strpos($_SERVER['REQUEST_URI'], "?") && strpos($_SERVER['REQUEST_URI'], "?") >= 0);		
			$content = "0; URL=".$_SERVER['REQUEST_URI'].($sep ? "&" : "?")."_rb_noscript=1";
			$noscriptMeta = $this->addMeta($name = "", $content, $httpEquiv = "refresh", $charset = "");
			DOM::append($noScriptTag, $noscriptMeta);
		}
		else if (isset($_GET['_rb_noscript']))
			cookies::set("noscript", 1);
	}
}
//#section_end#
?>