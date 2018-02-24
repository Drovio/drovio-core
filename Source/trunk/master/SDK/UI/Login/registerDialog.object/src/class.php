<?php
//#section#[header]
// Namespace
namespace UI\Login;

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
 * @package	Login
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Environment", "url");
importer::import("API", "Literals", "literal");
importer::import("API", "Profile", "account");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Html", "components/weblink");
importer::import("UI", "Forms", "templates/simpleForm");
importer::import("UI", "Presentation", "popups/popup");
importer::import("UI", "Prototype", "UIObjectPrototype");

use \ESS\Environment\url;
use \API\Literals\literal;
use \API\Profile\account;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Html\components\weblink;
use \UI\Forms\templates\simpleForm;
use \UI\Presentation\popups\popup;
use \UI\Prototype\UIObjectPrototype;

/**
 * Platform Register Dialog
 * 
 * Creates a register dialog for use in pages and in applications.
 * 
 * @version	0.1-10
 * @created	May 13, 2015, 12:07 (BST)
 * @updated	November 29, 2015, 13:54 (GMT)
 */
class registerDialog extends UIObjectPrototype
{
	/**
	 * The page registration type. It reloads the page after the registration.
	 * 
	 * @type	string
	 */
	const REG_TYPE_PAGE = "page";
	
	/**
	 * The application registration type. It reloads the page after the registration.
	 * 
	 * @type	string
	 */
	const REG_TYPE_APP = "app";
	
	/**
	 * The header element.
	 * 
	 * @type	DOMElement
	 */
	private $header;
	
	/**
	 * Builds the registration dialog object.
	 * 
	 * @param	string	$regtype
	 * 		The registration dialog type.
	 * 		See class constants for more information.
	 * 
	 * @param	string	$return_url
	 * 		Provide a redirect url after successful registration.
	 * 		Leave empty for default action (reload or redirect to my).
	 * 		It is empty by default.
	 * 
	 * @return	registerDialog
	 * 		The register dialog object.
	 */
	public function build($regtype = self::REG_TYPE_PAGE, $return_url = "")
	{
		// Build main sections
		$registerDialog = DOM::create("div", "", "", "registerDialog");
		$this->set($registerDialog);
		
		// Build header
		$this->buildHeader();
		
		// Build main body
		$this->buildMainForm($regtype, $return_url);
		
		// Build footer
		$this->buildFooter();
		
		return $this;
	}
	
	/**
	 * Build the dialog header.
	 * 
	 * @return	void
	 */
	private function buildHeader()
	{
		// Header container
		$this->header = DOM::create("div", "", "", "header");
		$this->append($this->header);
		
		// Login Dialog Title
		$title = literal::get("sdk.UI.register", "title");
		$stitle = DOM::create("div", $title, "", "ltitle");
		DOM::append($this->header, $stitle);
	}
	
	/**
	 * Build the main dialog form.
	 * 
	 * @param	string	$regtype
	 * 		The registration dialog type.
	 * 		See class constants for more information.
	 * 
	 * @param	string	$return_url
	 * 		Provide a redirect url after successful registration.
	 * 		Leave empty for default action (reload or redirect to my).
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	private function buildMainForm($regtype = self::REG_TYPE_PAGE, $return_url = "")
	{
		// Form Container
		$formContainer = DOM::create("div", "", "", "main formContainer");
		$this->append($formContainer);
		
		// Build social login
		$socialLoginContainer = DOM::create("div", "", "", "social");
		DOM::append($formContainer, $socialLoginContainer);
		
		// Facebook login
		$wl = new weblink();
		$params = array();
		$params['client_id'] = "1528296957491399";
		$params['scope'] = "email,public_profile";
		$params['redirect_uri'] = url::resolve("www", "/ajax/account/oauth/facebook.php");
		$href = url::get("https://www.facebook.com/dialog/oauth", $params);
		$btn_social = $wl->build($href, $target = "_self", $content = "", $class = "btn_social fb")->get();
		DOM::append($socialLoginContainer, $btn_social);
		
		// Google login
		$wl = new weblink();
		$params = array();
		$params['client_id'] = "1088239925512-t6knpm8l3q1cclfpobpg834sl9v9fo5r.apps.googleusercontent.com";
		$params['response_type'] = "code";
		$params['redirect_uri'] = url::resolve("www", "/ajax/account/oauth/google.php");
		$params['scope'] = "profile https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/plus.me email https://www.googleapis.com/auth/plus.profile.emails.read";
		$href = url::get("https://accounts.google.com/o/oauth2/auth", $params);
		$btn_social = $wl->build($href, $target = "_self", $content = "", $class = "btn_social gp")->get();
		DOM::append($socialLoginContainer, $btn_social);
		
		// Github login
		$wl = new weblink();
		$params = array();
		$params['client_id'] = "62d4412050f4713087bc";
		$params['scope'] = "user, user:email";
		$params['redirect_uri'] = url::resolve("www", "/ajax/account/oauth/github.php");
		$params['state'] = "";
		$href = url::get("https://github.com/login/oauth/authorize", $params);
		$btn_social = $wl->build($href, $target = "_self", $content = "", $class = "btn_social gh")->get();
		DOM::append($socialLoginContainer, $btn_social);
		
		// Or
		$title = literal::get("sdk.UI.login", "lbl_login_or");
		$p = DOM::create("p", $title, "", "login_or");
		DOM::append($formContainer, $p);
		
		// Build login form
		$form = new simpleForm();
		$loginURL = url::resolve("www", "/ajax/account/register.php");
		$loginForm = $form->build($loginURL, FALSE)->get();
		DOM::append($formContainer, $loginForm);
		
		// Set register type or return url to dialog
		if (empty($return_url))
		{
			$input = $form->getInput($type = "hidden", $name = "regtype", $value = $regtype, $class = "", $autofocus = FALSE, $required = FALSE);
			$form->append($input);
		}
		else
		{
			$input = $form->getInput($type = "hidden", $name = "return_url", $value = $return_url, $class = "", $autofocus = FALSE, $required = FALSE);
			$form->append($input);
		}
		
		// Firstname
		$input = $form->getInput($type = "text", $name = "firstname", $value = "", $class = "uiRegInput small", $autofocus = TRUE, $required = TRUE);
		$ph = literal::get("sdk.UI.register", "lbl_firstname", array(), FALSE);
		DOM::attr($input, "placeholder", $ph);
		$formRow = DOM::create("div", $input, "", "reg formRow");
		$form->append($formRow);
		
		// Lastname
		$input = $form->getInput($type = "text", $name = "lastname", $value = "", $class = "uiRegInput small f", $autofocus = FALSE, $required = TRUE);
		$ph = literal::get("sdk.UI.register", "lbl_lastname", array(), FALSE);
		DOM::attr($input, "placeholder", $ph);
		DOM::append($formRow, $input);
		
		
		// Email
		$input = $form->getInput($type = "text", $name = "email", $value = "", $class = "uiRegInput reg formRow", $autofocus = FALSE, $required = TRUE);
		$ph = literal::get("sdk.UI.register", "lbl_email", array(), FALSE);
		DOM::attr($input, "placeholder", $ph);
		$form->append($input);
		
		// Re-enter your email
		$input = $form->getInput($type = "text", $name = "email2", $value = "", $class = "uiRegInput reg formRow", $autofocus = FALSE, $required = TRUE);
		$ph = literal::get("sdk.UI.register", "lbl_reenterEmail", array(), FALSE);
		DOM::attr($input, "placeholder", $ph);
		$form->append($input);
		
		// Password
		$input = $form->getInput($type = "password", $name = "password", $value = "", $class = "uiRegInput reg formRow", $autofocus = FALSE, $required = TRUE);
		$ph = literal::get("sdk.UI.register", "lbl_password", array(), FALSE);
		DOM::attr($input, "placeholder", $ph);
		$form->append($input);
		
		// Submit Button
		$title = literal::get("sdk.UI.register", "lbl_register");
		$input = $form->getSubmitButton($title, $id = "regSubmit");
		$form->append($input);
	}
	
	/**
	 * Build the dialog footer.
	 * 
	 * @return	void
	 */
	private function buildFooter()
	{
		// Footer container
		$footer = DOM::create("div", "", "", "footer");
		$this->append($footer);
		
		$title = literal::get("sdk.UI.register", "lbl_legal");
		$htitle = DOM::create("h4", $title, "", "terms");
		DOM::append($footer, $htitle);
		
		$menu = DOM::create("div", "", "", "menu");
		DOM::append($footer, $menu);
		
		// Terms link
		$wl = new weblink();
		$href = url::resolve("www", "/help/Policies/Terms");
		$title = literal::get("sdk.UI.register", "lbl_termsTitle");
		$rlink = $wl->build($href, $target = "_blank", $content = $title)->get();
		DOM::append($menu, $rlink);
		
		// Bull
		$bull = DOM::create("span");
		DOM::append($menu, $bull);
		DOM::innerHTML($bull, " • ");
		
		// Privacy link
		$wl = new weblink();
		$href = url::resolve("www", "/help/Policies/Privacy");
		$title = literal::get("sdk.UI.register", "lbl_privacyTitle");
		$rlink = $wl->build($href, $target = "_blank", $content = $title)->get();
		DOM::append($menu, $rlink);
		
		// Bull
		$bull = DOM::create("span");
		DOM::append($menu, $bull);
		DOM::innerHTML($bull, " • ");
		
		// Cookies link
		$wl = new weblink();
		$href = url::resolve("www", "/help/Policies/Privacy#cookies");
		$title = literal::get("sdk.UI.register", "lbl_cookiesTitle");
		$rlink = $wl->build($href, $target = "_blank", $content = $title)->get();
		DOM::append($menu, $rlink);
	}
	
	/**
	 * Get the registration dialog server report in popup form.
	 * 
	 * @param	boolean	$background
	 * 		Whether the popup will have a background overlay.
	 * 		It is TRUE by default.
	 * 
	 * @param	boolean	$fade
	 * 		Whether the popup will show with a fade.
	 * 		It is TRUE by default.
	 * 
	 * @return	string
	 * 		The server report context.
	 */
	public function getReport($background = TRUE, $fade = TRUE)
	{
		// Add logo and close button to header
		HTML::addClass($this->header, "dialog");
		
		$logo = DOM::create("div", "", "", "logo");
		DOM::prepend($this->header, $logo);
		
		$close = DOM::create("div", "", "", "close");
		DOM::append($this->header, $close);
		
		
		// Create popup
		$popup = new popup();
		
		// Set popup attributes
		$popup->type(popup::TP_PERSISTENT, FALSE);
		$popup->background($background);
		$popup->fade($fade);
		
		// Append the login dialog as content
		$popup->build($this->get());
		
		// Get popup report
		return $popup->getReport();
	}
}
//#section_end#
?>