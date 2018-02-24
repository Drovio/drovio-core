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
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Prototype", "html/HTMLPagePrototype");
importer::import("ESS", "Environment", "url");
importer::import("ESS", "Environment", "cookies");
importer::import("API", "Geoloc", "locale");
importer::import("API", "Literals", "literal");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\html\HTMLPagePrototype;
use \ESS\Environment\url;
use \ESS\Environment\cookies;
use \API\Geoloc\locale;
use \API\Literals\literal;
use \UI\Html\DOM;

/**
 * Redback HTML Page Template
 * 
 * This is the base template for all redback pages.
 * 
 * @version	1.0-7
 * @created	March 7, 2013, 12:08 (EET)
 * @updated	July 16, 2015, 9:57 (EEST)
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
	 * @param	boolean	$og
	 * 		Indicates whether to include open graph website specific data in the page.
	 * 
	 * @return	HTMLPageTemplate
	 * 		This object
	 */
	public function build($title = "", $lang = "", $meta = FALSE, $og = FALSE)
	{
		// Build PagePrototype with settings Meta Content
		$title = ($title == "" ? "DrovIO" : $title);
		
		// Include meta
		if ($meta)
		{
			$metaDescription = literal::get("global.meta", "description", array(), FALSE);
			$metaKeywords = literal::get("global.meta", "keywords", array(), FALSE);
		}
		parent::build($title, $metaDescription, $metaKeywords);
		
		// Set HTML Attributes
		$HTML = $this->get();
		DOM::attr($HTML, "id", "drovio");
		DOM::attr($HTML, "lang", $lang);
		
		// Set Head Content
		$this->setHead($meta, $og);
		
		return $this;
	}
	
	/**
	 * Inserts all the meta tags, scripts and styles to head.
	 * 
	 * @param	boolean	$meta
	 * 		Indicates whether to include meta (description and keywords) in the page.
	 * 
	 * @param	boolean	$og
	 * 		Indicates whether to include open graph website specific data in the page.
	 * 
	 * @return	void
	 */
	private function setHead($meta = FALSE, $og = FALSE)
	{
		// Add viewport meta
		$this->addMeta($name = "viewport", $content = "width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0", $httpEquiv = "", $charset = "");
		
		// Create extra meta content (Identity), robots, generator
		if ($meta)
			$this->addMeta($name = "author", literal::get("global.meta", "author", array(), FALSE), $httpEquiv = "", $charset = "");
		
		// This is standard for all pages
		$this->addMeta($name = "robots", $content = "FOLLOW, NOODP, NOYDIR", $httpEquiv = "", $charset = "");
		$this->addMeta($name = "generator", $content = "Drovio Engine", $httpEquiv = "", $charset = "");
		
		// Redback Icon
		$faviconUrl = url::resolve("cdn", "/media/logos/favicon.png");
		$this->addIcon($faviconUrl);
		
		// No Javascript
		$this->addNoScript();
		
		// Add standard open graph meta
		if ($og)
		{
			$ogData = array();
			$ogData["site_name"] = "DrovIO";
			$ogData["type"] = "Website";
			$ogData["image"] = $faviconUrl;
			$ogData["locale"] = locale::get();
			$this->addOpenGraphMeta($ogData);
		}
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