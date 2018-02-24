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
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
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
 * Platform Register Dialog
 * 
 * Creates a register dialog for use in pages and in applications.
 * 
 * @version	0.1-5
 * @created	May 13, 2015, 14:07 (EEST)
 * @updated	August 23, 2015, 11:56 (EEST)
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
		$href = url::resolve("www", "/help/policies/terms.php");
		$title = literal::get("sdk.UI.register", "lbl_termsTitle");
		$rlink = $wl->build($href, $target = "_blank", $content = $title)->get();
		DOM::append($menu, $rlink);
		
		// Bull
		$bull = DOM::create("span");
		DOM::append($menu, $bull);
		DOM::innerHTML($bull, " &bull; ");
		
		// Privacy link
		$wl = new weblink();
		$href = url::resolve("www", "/help/policies/privacy.php");
		$title = literal::get("sdk.UI.register", "lbl_privacyTitle");
		$rlink = $wl->build($href, $target = "_blank", $content = $title)->get();
		DOM::append($menu, $rlink);
		
		// Bull
		$bull = DOM::create("span");
		DOM::append($menu, $bull);
		DOM::innerHTML($bull, " &bull; ");
		
		// Cookies link
		$wl = new weblink();
		$href = url::resolve("www", "/help/policies/privacy.php#cookies");
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