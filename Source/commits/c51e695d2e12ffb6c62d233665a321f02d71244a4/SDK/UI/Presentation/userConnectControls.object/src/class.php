<?php
//#section#[header]
// Namespace
namespace UI\Presentation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Presentation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "DOM::DOM");
importer::import("API", "Platform", "state::url");
importer::import("API", "Geoloc", "lang::mlgContent");

use \API\Platform\DOM\DOM;
use \API\Platform\state\url;
use \API\Geoloc\lang\mlgContent;

/**
 * {title}
 * 
 * Use
 * 
 * @version	{empty}
 * @created	May 23, 2013, 15:01 (EEST)
 * @revised	May 23, 2013, 15:01 (EEST)
 * 
 * @deprecated	This class is deprecated and the object is no longer needed. It is transferred to a module.
 */
class userConnectControls
{
	/**
	 * Constructor Method
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Put your constructor method code here.
	}
	
	/**
	 * Create and return the control
	 * 
	 * @return	void
	 */
	public static function get()
	{
		$container = DOM::create("div", "", "uiUserConnect", "uiUserConnect");
		
		// Connection Box Header
		$header = DOM::create("h2", "", "", "uiConnectHeader");
		DOM::append($container, $header);
		$headerTitle = mlgContent::get_literal("global::presentation::userConnect", "hd_userConnectHeader");
		DOM::append($header, $headerTitle);
		
		// Connect Box -  Register Box
		$registerBox = DOM::create("div", "", "userRegisterBox", "connectBox");
		DOM::append($container, $registerBox);
		// Header
		$registerHeader = DOM::create("div", "", "", "headerBox");
		DOM::append($registerBox, $registerHeader);
		$headerTitle = mlgContent::get_literal("global::presentation::userConnect", "lbl_newUser");
		DOM::append($registerHeader, $headerTitle);
		// Navigation Link
		$navLink = DOM::create("a", "", "", "navLink");
		DOM::attr($navLink, "target", "_self");
		$registerLink = url::resolve("my", $url = "/register/", $https = FALSE);
		DOM::attr($navLink, "href", $registerLink);
		DOM::append($registerBox, $navLink);
		$navTitle = mlgContent::get_literal("global::dictionary", "register");
		DOM::append($navLink, $navTitle);
		
		// Middle Box - Or Box
		$orBox = DOM::create("div", "", "", "connectSeparator");
		DOM::append($container, $orBox);
		
		// Connect Box - Login Box
		$loginBox = DOM::create("div", "", "userLoginBox", "connectBox");
		DOM::append($container, $loginBox);
		// Header
		$loginHeader = DOM::create("div", "", "", "headerBox");
		DOM::append($loginBox, $loginHeader);
		$headerTitle = mlgContent::get_literal("global::presentation::userConnect", "lbl_existingUser");
		DOM::append($loginHeader, $headerTitle);
		// Navigation Link
		$navLink = DOM::create("a", "", "", "navLink");
		DOM::attr($navLink, "target", "_self");
		$registerLink = url::resolve("login", $url = "", $https = FALSE);
		DOM::attr($navLink, "href", $registerLink);
		DOM::append($loginBox, $navLink);
		$navTitle = mlgContent::get_literal("global::dictionary", "login");
		DOM::append($navLink, $navTitle);
		
		return $container;	
	}
}
//#section_end#
?>