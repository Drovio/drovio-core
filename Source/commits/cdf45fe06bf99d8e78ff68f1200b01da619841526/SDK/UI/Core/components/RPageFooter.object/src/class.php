<?php
//#section#[header]
// Namespace
namespace UI\Core\components;

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
 * @package	Core
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Environment", "url");
importer::import("API", "Literals", "literal");
importer::import("API", "Geoloc", "locale");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Html", "components/weblink");
importer::import("UI", "Prototype", "UIObjectPrototype");

use \ESS\Environment\url;
use \API\Literals\literal;
use \API\Geoloc\locale;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Html\components\weblink;
use \UI\Prototype\UIObjectPrototype;

/**
 * Redback's Page Footer
 * 
 * It's a singleton pattern implementation for Redback Core page footer.
 * Builds the basic global footer for all pages across all domains in redback.
 * 
 * @version	0.1-10
 * @created	June 11, 2014, 9:22 (EEST)
 * @updated	October 18, 2015, 14:10 (EEST)
 */
class RPageFooter extends UIObjectPrototype
{
	/**
	 * The singleton's instance.
	 * 
	 * @type	RPageFooter
	 */
	private static $instance;
	
	/**
	 * Constructor function for RPageFooter Instance.
	 * 
	 * @return	void
	 */
	final protected function __construct() {}
	
	/**
	 * Gets the instance of the RPageFooter.
	 * 
	 * @return	RPageFooter
	 * 		The RPageFooter unique instance.
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
			self::$instance = new RPageFooter();
		
		return self::$instance;
	}
	
	/**
	 * Builds the navigation footer.
	 * 
	 * @return	RPageFooter
	 * 		The page footer object.
	 */
	public function build()
	{
		// Create holder
		$footerHolder = DOM::create("div", "", "", "uiMainFooter");
		$this->set($footerHolder);
		
		// Create weblink builder
		$wb = new weblink();
		
		$lContainer = DOM::create("div", "", "", "lContainer");
		DOM::append($footerHolder, $lContainer);

		// Copyright and Brand
		$item = DOM::create("div", "", "", "logo");
		DOM::innerHTML($item, "Drovio™ © ".date('Y'));
		DOM::append($lContainer, $item);
		
		$bull = $this->getBull();
		DOM::append($lContainer, $bull);
		
		$url = url::resolve("www", "/profile/settings/");
		$localeInfo = locale::info();
		$content = DOM::create("span", $localeInfo['friendlyName']);
		$item = $wb->build($url, $target = "_self", $content)->get();
		HTML::addClass($item, "lcl");
		DOM::append($lContainer, $item);
		
		$rContainer = DOM::create("div", "", "", "rContainer");
		DOM::append($footerHolder, $rContainer);
		
		// Footer Navigation Menu
		$url = url::resolve("www", "/help/");
		$content = literal::get("sdk.UI.page.footer", "lbl_helpCenter");
		$item = $wb->build($url, $target = "_self", $content)->get();
		DOM::append($rContainer, $item);
		
		$bull = $this->getBull();
		DOM::append($rContainer, $bull);
		
		$url = url::resolve("www", "/help/Policies/Terms");
		$content = literal::get("sdk.UI.page.footer", "lbl_termsOfService");
		$item = $wb->build($url, $target = "_self", $content)->get();
		DOM::append($rContainer, $item);
		
		$bull = $this->getBull();
		DOM::append($rContainer, $bull);
		
		$url = url::resolve("www", "/help/Policies/Privacy");
		$content = literal::get("sdk.UI.page.footer", "lbl_dataUsePolicy");
		$item = $wb->build($url, $target = "_self", $content)->get();
		DOM::append($rContainer, $item);
		
		return $this;
	}
	
	/**
	 * Creates a span with • as value.
	 * 
	 * @return	DOMElement
	 * 		The bull span.
	 */
	private function getBull()
	{
		$bull = DOM::create("span");
		DOM::innerHTML($bull, " • ");
		
		return $bull;
	}
}
//#section_end#
?>