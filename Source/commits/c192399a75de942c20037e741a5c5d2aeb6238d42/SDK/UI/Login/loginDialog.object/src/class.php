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
 * Platform Login Dialog
 * 
 * Creates a login dialog for use in pages and in applications.
 * 
 * @version	0.4-11
 * @created	February 17, 2015, 10:39 (GMT)
 * @updated	November 12, 2015, 19:23 (GMT)
 */
class loginDialog extends UIObjectPrototype
{
	/**
	 * The page login type. It reloads the page after the login.
	 * 
	 * @type	string
	 */
	const LGN_TYPE_PAGE = "page";
	
	/**
	 * The application login type. It reloads the page after the login.
	 * 
	 * @type	string
	 */
	const LGN_TYPE_APP = "app";
	
	/**
	 * The header element.
	 * 
	 * @type	DOMElement
	 */
	private $header;
	
	/**
	 * Builds the login dialog object.
	 * 
	 * @param	string	$username
	 * 		The default username value for the input.
	 * 		It is empty by default.
	 * 
	 * @param	string	$logintype
	 * 		The login dialog type.
	 * 		See class constants for more information.
	 * 
	 * @param	string	$return_url
	 * 		Provide a redirect url after successful login.
	 * 		Leave empty for default action (reload or redirect to my).
	 * 		It is empty by default.
	 * 
	 * @return	loginDialog
	 * 		The login dialog object.
	 */
	public function build($username = "", $logintype = self::LGN_TYPE_PAGE, $return_url = "")
	{
		// Build main sections
		$loginDialog = DOM::create("div", "", "", "loginDialog");
		$this->set($loginDialog);
		
		// Build header
		$this->buildHeader();
		
		// Build main body
		$this->buildMainForm($username, $logintype, $return_url);
		
		// Build footer
		$this->buildFooter($return_url);
		
		return $this;
	}
	
	/**
	 * build the dialog header.
	 * 
	 * @return	void
	 */
	private function buildHeader()
	{
		// Header container
		$this->header = DOM::create("div", "", "", "header");
		$this->append($this->header);
		
		// Login Dialog Title
		$title = literal::get("sdk.UI.login", "title");
		$stitle = DOM::create("div", $title, "", "ltitle");
		DOM::append($this->header, $stitle);
	}
	
	/**
	 * Build the main dialog form.
	 * 
	 * @param	string	$usernameValue
	 * 		The default username value for the input.
	 * 		It is empty by default.
	 * 
	 * @param	string	$logintype
	 * 		The login dialog type.
	 * 		See class constants for more information.
	 * 
	 * @param	string	$return_url
	 * 		Provide a redirect url after successful login.
	 * 		Leave empty for default action (reload or redirect to my).
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	private function buildMainForm($usernameValue = "", $logintype = self::LGN_TYPE_PAGE, $return_url = "")
	{
		// Main Container
		$mainContainer = DOM::create("div", "", "", "main");
		$this->append($mainContainer);
		
		// Build social login
		$socialLoginContainer = DOM::create("div", "", "", "social");
		DOM::append($mainContainer, $socialLoginContainer);
		
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
		DOM::append($mainContainer, $p);
		
		// Build login form
		$form = new simpleForm();
		$loginURL = url::resolve("www", "/ajax/account/login.php");
		$loginForm = $form->build($loginURL, FALSE)->get();
		DOM::append($mainContainer, $loginForm);
		
		// Set login type or return url to dialog
		if (empty($return_url))
		{
			$input = $form->getInput($type = "hidden", $name = "logintype", $value = $logintype, $class = "", $autofocus = FALSE, $required = FALSE);
			$form->append($input);
		}
		else
		{
			$input = $form->getInput($type = "hidden", $name = "return_url", $value = $return_url, $class = "", $autofocus = FALSE, $required = FALSE);
			$form->append($input);
		}
		
		// Form container
		$formContainer = DOM::create("div", "", "", "formContainer");
		$form->append($formContainer);
		
		// Username
		$input = $form->getInput($type = "text", $name = "username", $value = $usernameValue, $class = "lpinp", $autofocus = TRUE, $required = TRUE);
		$ph = literal::dictionary("username", FALSE);
		DOM::attr($input, "placeholder", ucfirst($ph));
		DOM::append($formContainer, $input);
		
		// Password
		$input = $form->getInput($type = "password", $name = "password", $value = "", $class = "lpinp", $autofocus = FALSE, $required = TRUE);
		$ph = literal::dictionary("password", FALSE);
		DOM::attr($input, "placeholder", ucfirst($ph));
		DOM::append($formContainer, $input);
		
		// Remember me container
		$rcont = DOM::create("div", "", "", "rcont");
		DOM::append($formContainer, $rcont);
		
		// Public session
		$rsession = DOM::create("div", "", "rsession", "rocnt selected");
		DOM::append($rcont, $rsession);
		
		$ricnt = DOM::create("div", "", "", "ricnt");
		DOM::append($rsession, $ricnt);
		
		$input = $form->getInput($type = "radio", $name = "rememberme", $value = "off", $class = "lpchk", $autofocus = FALSE, $required = FALSE);
		DOM::attr($input, "checked", TRUE);
		DOM::append($ricnt, $input);
		$text = literal::get("sdk.UI.login", "lbl_noTrust");
		$inputID = DOM::attr($input, "id");
		$label = $form->getLabel($text, $inputID, $class = "lplbl");
		DOM::append($ricnt, $label);
		
		// Private session
		$rsession = DOM::create("div", "", "rtrust", "rocnt");
		DOM::append($rcont, $rsession);
		
		$ricnt = DOM::create("div", "", "", "ricnt");
		DOM::append($rsession, $ricnt);
		
		$input = $form->getInput($type = "radio", $name = "rememberme", $value = "on", $class = "lpchk", $autofocus = FALSE, $required = FALSE);
		DOM::append($ricnt, $input);
		$text = literal::get("sdk.UI.login", "lbl_trustComputer");
		$inputID = DOM::attr($input, "id");
		$label = $form->getLabel($text, $inputID, $class = "lplbl");
		DOM::append($ricnt, $label);
		
		// Remember me notes
		$rnotes = DOM::create("div", "", "", "rnotes");
		DOM::append($formContainer, $rnotes);
		
		$title = literal::get("sdk.UI.login", "lbl_sessionNotes");
		$nt = DOM::create("div", $title, "", "nt rsession selected");
		DOM::append($rnotes, $nt);
		
		$title = literal::get("sdk.UI.login", "lbl_trustNotes");
		$nt = DOM::create("div", $title, "", "nt rtrust");
		DOM::append($rnotes, $nt);
		
		// Login button
		$title = literal::dictionary("login");
		$input = $form->getSubmitButton($title);
		DOM::append($formContainer, $input);
	}
	
	/**
	 * Build the dialog footer.
	 * 
	 * @param	string	$return_url
	 * 		Sets the register dialog link with the given return url after registration.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	private function buildFooter($return_url = "")
	{
		// Footer container
		$footer = DOM::create("div", "", "", "footer");
		$this->append($footer);
		
		// Register link
		$wl = new weblink();
		$params = array();
		$params['return_url'] = $return_url;
		$href = url::resolve("www", "/register/", $params);
		$title = literal::get("sdk.UI.login", "lbl_not_a_member");
		$rlink = $wl->build($href, $target = "_self", $content = $title)->get();
		$hlink = DOM::create("h4", $rlink, "", "register");
		DOM::append($footer, $hlink);
		
		// Bull
		$bull = DOM::create("span");
		DOM::append($footer, $bull);
		DOM::innerHTML($bull, " • ");
		
		// Forgot password link
		$wl = new weblink();
		$href = url::resolve("www", "/login/forgot.php");
		$title = literal::get("sdk.UI.login", "lbl_forgot_pw");
		$flink = $wl->build($href, $target = "_self", $content = $title)->get();
		$hlink = DOM::create("h4", $flink, "", "forgot");
		DOM::append($footer, $hlink);
	}
	
	/**
	 * Get the login dialog server report in popup form.
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