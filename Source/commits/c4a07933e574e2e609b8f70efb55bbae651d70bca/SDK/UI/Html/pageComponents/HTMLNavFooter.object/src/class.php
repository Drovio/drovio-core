<?php
//#section#[header]
// Namespace
namespace UI\Html\pageComponents;

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
 * @namespace	\pageComponents
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::NavigatorProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Resources", "literals::literal");
importer::import("API", "Resources", "geoloc::locale");
importer::import("API", "Resources", "url");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "pageComponents::htmlComponents::weblink");

use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\literals\literal;
use \API\Resources\geoloc\locale;
use \API\Resources\url;
use \UI\Html\DOM;
use \UI\Html\pageComponents\htmlComponents\weblink;

/**
 * HTML Navigation Footer
 * 
 * The footer of the page with some basic navigation to the site.
 * 
 * @version	{empty}
 * @created	April 26, 2013, 15:31 (EEST)
 * @revised	January 17, 2014, 11:07 (EET)
 */
class HTMLNavFooter extends UIObjectPrototype
{
	/**
	 * Builds the navigation footer
	 * 
	 * @return	HTMLNavFooter
	 * 		{description}
	 */
	public function build()
	{
		// Create holder
		$footerHolder = DOM::create("div", "", "", "uiMainFooter");
		$this->set($footerHolder);
		
		$footer = DOM::create("div", "", "", "content");
		DOM::append($footerHolder, $footer);

		// Copyright and Brand
		$item = DOM::create("span", "", "", "faded");
		DOM::innerHTML($item, "Redback&trade; &copy; ".date('Y'));
		DOM::append($footer, $item);
		
		$this->addBull($footer);
		
		// Footer Navigation Menu
		$wb = new weblink();
		$url = url::resolve("www", "/help/legal/terms/");
		$content = literal::get("global::legal", "lbl_termsOfService");
		$item = $wb->build($url, $target = "_blank", $content)->get();
		DOM::append($footer, $item);
		
		$this->addBull($footer);
		
		$url = url::resolve("www", "/help/privacy/");
		$content = literal::get("global::legal", "lbl_dataUsePolicy");
		$item = $wb->build($url, $target = "_blank", $content)->get();
		DOM::append($footer, $item);
		
		$this->addBull($footer);
		
		$url = url::resolve("www", "/help/");
		$content = literal::get("global::notifications::center", "helpCenter");
		$item = $wb->build($url, $target = "_blank", $content)->get();
		DOM::append($footer, $item);
		
		$this->addBull($footer);
		
		$url = url::resolve("my", "/settings/");
		$localeInfo = locale::info();
		$content = DOM::create("span", $localeInfo['friendlyName']);
		$item = $wb->build($url, $target = "_blank", $content)->get();
		DOM::append($footer, $item);
		
		return $this;
	}
	
	/**
	 * Adds a &bull; character to the given container.
	 * 
	 * @param	DOMElement	$container
	 * 		The given container
	 * 
	 * @return	void
	 */
	private function addBull($container)
	{
		$bull = $this->getBull();
		DOM::append($container, $bull);
	}
	
	/**
	 * Creates a span with &bull; as value.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	private function getBull()
	{
		$bull = DOM::create("span");
		DOM::innerHTML($bull, " &bull; ");
		
		return $bull;
	}
}
//#section_end#
?>