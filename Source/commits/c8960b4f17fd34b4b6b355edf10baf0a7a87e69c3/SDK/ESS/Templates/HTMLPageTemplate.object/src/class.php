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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Prototype", "html/HTMLPagePrototype");
importer::import("ESS", "Environment", "url");
importer::import("ESS", "Environment", "cookies");
importer::import("API", "Geoloc", "locale");
importer::import("API", "Literals", "literal");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Core", "coreProject");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Resources", "paths");

use \ESS\Prototype\html\HTMLPagePrototype;
use \ESS\Environment\url;
use \ESS\Environment\cookies;
use \API\Geoloc\locale;
use \API\Literals\literal;
use \UI\Html\DOM;
use \DEV\Core\coreProject;
use \DEV\Projects\projectLibrary;
use \DEV\Resources\paths;

/**
 * Redback HTML Page Template
 * 
 * This is the base template for all redback pages.
 * 
 * @version	1.0-3
 * @created	March 7, 2013, 12:08 (EET)
 * @updated	January 14, 2015, 10:35 (EET)
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
		$title = ($title == "" ? "Redback" : $title);
		
		// Include meta
		if ($meta)
		{
			$metaDescription = literal::get("global.meta", "description", array(), FALSE);
			$metaKeywords = literal::get("global.meta", "keywords", array(), FALSE);
		}
		parent::build($title, $metaDescription, $metaKeywords);
		
		// Set HTML Attributes
		$HTML = $this->get();
		DOM::attr($HTML, "id", "redback");
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
		if ($meta)
		{
			// Create extra meta content (Identity), robots, generator
			$this->addMeta($name = "author", literal::get("global.meta", "author", array(), FALSE), $httpEquiv = "", $charset = "");
		}
		
		// This is standard for all pages
		$this->addMeta($name = "robots", $content = "FOLLOW, NOODP, NOYDIR", $httpEquiv = "", $charset = "");
		$this->addMeta($name = "generator", $content = "Redback Web Engine", $httpEquiv = "", $charset = "");
		
		// Redback Icon
		$version = projectLibrary::getLastProjectVersion(coreProject::PROJECT_ID);
		$publishFolder = projectLibrary::getPublishedPath(coreProject::PROJECT_ID, $version);
		$iconPath = $publishFolder."/media/logos/logo_150.png";
		$iconPath = str_replace(paths::getPublishedPath(), "", $iconPath);
		$faviconUrl = url::resolve("lib", $iconPath);
		$this->addIcon($faviconUrl);
		
		// No Javascript
		$this->addNoScript();
		
		// Add standard open graph meta
		if ($og)
		{
			$ogData = array();
			$ogData["site_name"] = "Redback";
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