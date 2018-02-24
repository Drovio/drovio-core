<?php
//#section#[header]
// Namespace
namespace UI\Forms\special;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Forms
 * @namespace	\special
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::CaptchaProtocol");
importer::import("ESS", "Protocol", "client::environment::Url");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\client\CaptchaProtocol;
use \ESS\Protocol\client\environment\Url;
use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * Form CAPTCHA
 * 
 * Builds a captcha control.
 * 
 * @version	{empty}
 * @created	March 11, 2013, 12:14 (EET)
 * @revised	October 24, 2013, 12:11 (EEST)
 */
class formCaptcha extends UIObjectPrototype
{
	/**
	 * Builds the captcha control.
	 * 
	 * @param	string	$formID
	 * 		The form's id where the captcha will be placed.
	 * 
	 * @param	string	$id
	 * 		The captcha element id.
	 * 
	 * @return	formCaptcha
	 * 		{description}
	 */
	public function build($formID = "", $id = "")
	{
		// Create captcha placeholder
		$captchaWrapper = DOM::create("div", "", $id, "captchaWrapper");
		$this->set($captchaWrapper);
		
		// Set Captcha Value
		$captchaValue = "";
		CaptchaProtocol::set($formID, $captchaValue);
		
		// Build Captcha Image
		$captcha = DOM::create("img");
		DOM::attr($captcha, "width", "100px");
		DOM::attr($captcha, "height", "40px");
		$src = Url::resolve("www", "/ajax/resources/sdk/forms/formCaptcha.php?fid=".$formID);
		DOM::attr($captcha, "src", $src);
		DOM::append($captchaWrapper, $captcha);
		
		return $this;
	}
	
	/**
	 * Performs the Captcha Protocol validation process and returns the result.
	 * 
	 * @param	string	$formID
	 * 		The form's id.
	 * 
	 * @param	string	$value
	 * 		The user's input captcha value.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function validate($formID, $value)
	{
		return CaptchaProtocol::validate($formID, $value);
	}
}
//#section_end#
?>