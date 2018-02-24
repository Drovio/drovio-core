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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::NavigatorProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Resources", "literals::literal");
importer::import("API", "Resources", "geoloc::locale");
importer::import("API", "Resources", "url");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "components::weblink");

use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\literals\literal;
use \API\Resources\geoloc\locale;
use \API\Resources\url;
use \UI\Html\DOM;
use \UI\Html\components\weblink;

/**
 * Redback's Page Footer
 * 
 * It's a singleton pattern implementation for Redback Core page footer.
 * Builds the basic global footer for all pages across all domains in redback.
 * 
 * @version	{empty}
 * @created	June 11, 2014, 9:22 (EEST)
 * @revised	June 24, 2014, 10:16 (EEST)
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
	protected function __construct(){}
	
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
		
		$footer = DOM::create("div", "", "", "content");
		DOM::append($footerHolder, $footer);

		// Copyright and Brand
		$item = DOM::create("span", "", "", "faded");
		DOM::innerHTML($item, "Redback&trade; &copy; ".date('Y'));
		DOM::append($footer, $item);
		
		$bull = $this->getBull();
		DOM::append($footer, $bull);
		
		// Footer Navigation Menu
		$wb = new weblink();
		$url = url::resolve("www", "/help/legal/terms/");
		$content = literal::get("global::legal", "lbl_termsOfService");
		$item = $wb->build($url, $target = "_blank", $content)->get();
		DOM::append($footer, $item);
		
		$bull = $this->getBull();
		DOM::append($footer, $bull);
		
		$url = url::resolve("www", "/help/privacy/");
		$content = literal::get("global::legal", "lbl_dataUsePolicy");
		$item = $wb->build($url, $target = "_blank", $content)->get();
		DOM::append($footer, $item);
		
		$bull = $this->getBull();
		DOM::append($footer, $bull);
		
		$url = url::resolve("www", "/help/");
		$content = literal::get("global::notifications::center", "helpCenter");
		$item = $wb->build($url, $target = "_blank", $content)->get();
		DOM::append($footer, $item);
		
		$bull = $this->getBull();
		DOM::append($footer, $bull);
		
		$url = url::resolve("my", "/settings/");
		$localeInfo = locale::info();
		$content = DOM::create("span", $localeInfo['friendlyName']);
		$item = $wb->build($url, $target = "_blank", $content)->get();
		DOM::append($footer, $item);
		
		return $this;
	}
	
	/**
	 * Creates a span with &bull; as value.
	 * 
	 * @return	DOMElement
	 * 		The bull span.
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