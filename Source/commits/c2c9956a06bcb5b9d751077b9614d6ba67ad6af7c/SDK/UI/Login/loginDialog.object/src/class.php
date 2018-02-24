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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Environment", "url");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Literals", "literal");
importer::import("API", "Profile", "account");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Html", "components/weblink");
importer::import("UI", "Forms", "templates/simpleForm");
importer::import("UI", "Presentation", "popups/popup");

use \ESS\Environment\url;
use \ESS\Prototype\UIObjectPrototype;
use \API\Literals\literal;
use \API\Profile\account;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Html\components\weblink;
use \UI\Forms\templates\simpleForm;
use \UI\Presentation\popups\popup;

/**
 * Platform Login Dialog
 * 
 * Creates a login dialog for use in pages and in applications.
 * 
 * @version	0.4-2
 * @created	February 17, 2015, 12:39 (EET)
 * @updated	May 20, 2015, 13:11 (EEST)
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
		$header = DOM::create("div", "", "", "header");
		$this->append($header);
		
		$lside = DOM::create("div", "", "", "lside");
		DOM::append($header, $lside);
		
		// Logo
		$logo = DOM::create("div", "", "", "logo");
		DOM::append($lside, $logo);
		
		$lgtext = DOM::create("h2", "Redback", "", "text");
		DOM::append($lside, $lgtext);
		
		// Login Dialog Title
		$title = literal::get("sdk.UI.login", "title");
		$stitle = DOM::create("div", $title, "", "small");
		DOM::append($header, $stitle);
		
		// Close button
		$close = DOM::create("div", "", "", "close");
		DOM::append($header, $close);
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
		
		// Left side (remember me)
		$leftSide = DOM::create("div", "", "", "leftSide side");
		$form->append($leftSide);
		
		$title = literal::get("sdk.UI.login", "lbl_rememberYou");
		$htitle = DOM::create("h3", $title, "", "title");
		DOM::append($leftSide, $htitle);
		
		$title = literal::get("sdk.UI.login", "lbl_rememberSub");
		$hsub = DOM::create("h4", $title, "", "sub");
		DOM::append($leftSide, $hsub);
		
		// Remember me container
		$rcont = DOM::create("div", "", "", "rcont");
		DOM::append($leftSide, $rcont);
		
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
		DOM::append($leftSide, $rnotes);
		
		$title = literal::get("sdk.UI.login", "lbl_sessionNotes");
		$nt = DOM::create("div", $title, "", "nt rsession selected");
		DOM::append($rnotes, $nt);
		
		$title = literal::get("sdk.UI.login", "lbl_trustNotes");
		$nt = DOM::create("div", $title, "", "nt rtrust");
		DOM::append($rnotes, $nt);
		
		
		// Form container
		$formContainer = DOM::create("div", "", "", "formContainer side");
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
		
		$title = literal::get("sdk.UI.login", "lbl_assistance");
		$htitle = DOM::create("h3", $title, "", "assistance");
		DOM::append($footer, $htitle);
		
		$menu = DOM::create("div", "", "", "menu");
		DOM::append($footer, $menu);
		
		// Register link
		$wl = new weblink();
		$params = array();
		$params['return_url'] = $return_url;
		$href = url::resolve("my", "/register/", $params);
		$title = literal::get("sdk.UI.login", "lbl_not_a_member");
		$rlink = $wl->build($href, $target = "_blank", $content = $title)->get();
		$hlink = DOM::create("h4", $rlink, "", "register");
		DOM::append($menu, $hlink);
		
		// Bull
		$bull = DOM::create("span");
		DOM::append($menu, $bull);
		DOM::innerHTML($bull, " &bull; ");
		
		// Forgot password link
		$wl = new weblink();
		$href = url::resolve("login", "/reset.php");
		$title = literal::get("sdk.UI.login", "lbl_forgot_pw");
		$flink = $wl->build($href, $target = "_blank", $content = $title)->get();
		$hlink = DOM::create("h4", $flink, "", "forgot");
		DOM::append($menu, $hlink);
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