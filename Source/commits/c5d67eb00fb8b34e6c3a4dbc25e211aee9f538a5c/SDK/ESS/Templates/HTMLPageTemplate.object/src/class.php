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
 * @revised	March 24, 2014, 12:17 (EET)
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
		$this->addIcon(Url::resource("/Library/Media/images/logos/RB_favicon.ico"));
		
		// No Javascript
		$this->addNoScript();
		
		// Set Links
		$this->setScripts();
		$this->setStyles();
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
	
	/**
	 * Inserts the default and global scripts. In the future, here will be determined the safe-mode state.
	 * 
	 * @return	void
	 */
	private function setScripts()
	{
		$this->addScript(Url::resource("/ajax/resources/prime/js.php"));
		$this->addScript(Url::resource("/ajax/resources/global/js.php"));
	}
	
	/**
	 * Inserts the default and global styles. In the future, here will be determined the basic styles for the safe-mode.
	 * 
	 * @return	void
	 */
	private function setStyles()
	{
		$this->addStyle(Url::resource("/ajax/resources/prime/css.php"));
		$this->addStyle(Url::resource("/ajax/resources/global/css.php"));
	}
}
//#section_end#
?>