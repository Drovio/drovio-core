<?php
//#section#[header]
// Namespace
namespace UI\Html\pageComponents;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Html
 * @namespace	\pageComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::NavigatorProtocol");
importer::import("ESS", "Protocol", "client::environment::Url");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Resources", "literals::literal");
importer::import("API", "Resources", "geoloc::locale");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Protocol\client\environment\Url;
use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\literals\literal;
use \API\Resources\geoloc\locale;
use \UI\Html\DOM;

/**
 * HTML Navigation Footer
 * 
 * The footer of the page with some basic navigation to the site.
 * 
 * @version	{empty}
 * @created	April 26, 2013, 15:31 (EEST)
 * @revised	April 26, 2013, 15:31 (EEST)
 */
class HTMLNavFooter extends UIObjectPrototype
{
	/**
	 * Builds the navigation footer
	 * 
	 * @return	HTMLNavFooter
	 */
	public function build()
	{
		// Create holder
		$footerHolder = DOM::create("div", "", "", "uiMainFooter");
		$this->set($footerHolder);
		
		$footer = DOM::create("div", "", "", "content");
		DOM::append($footerHolder, $footer);

		// Copyright and Brand
		$item = DOM::create("span", "Redback&trade; &copy; ".date('Y'), "", "faded");
		DOM::append($footer, $item);
		
		$this->addBull($footer);
		
		// Footer Navigation Menu
		$item = DOM::create("a");
		DOM::append($footer, $item);
		//--//
		NavigatorProtocol::web($item, Url::resolve("support", "/legal/terms/"), $target = "_blank");
		DOM::append($item, literal::get("global::legal", "lbl_termsOfService"));
		
		$this->addBull($footer);
		
		$item = DOM::create("a");
		DOM::append($footer, $item);
		//--//
		NavigatorProtocol::web($item, Url::resolve("support", "/about/privacy/"), $target = "_blank");
		DOM::append($item, literal::get("global::legal", "lbl_dataUsePolicy"));
		
		$this->addBull($footer);
		
		$item = DOM::create("a");
		DOM::append($footer, $item);
		//--//
		NavigatorProtocol::web($item, Url::resolve("support", "/help/"), $target = "_blank");
		DOM::append($item, literal::get("global::notifications::center", "helpCenter"));
		
		$this->addBull($footer);
		
		$item = DOM::create("a");
		DOM::append($footer, $item);
		//--//
		$localeInfo = locale::info();
		NavigatorProtocol::web($item, Url::resolve("my", "/settings/"), $target = "_blank");
		$regionCountry = DOM::create("span", $localeInfo['friendlyName']);
		DOM::append($item, $regionCountry);
		
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
	 */
	private function getBull()
	{
		return DOM::create("span", " &bull; ");
	}
}
//#section_end#
?>