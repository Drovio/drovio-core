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
importer::import("ESS", "Environment", "url");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Literals", "literal");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Forms", "templates/simpleForm");
importer::import("UI", "Presentation", "popups/popup");

use \ESS\Environment\url;
use \ESS\Prototype\UIObjectPrototype;
use \API\Literals\literal;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Forms\templates\simpleForm;
use \UI\Presentation\popups\popup;

class loginDialog extends UIObjectPrototype
{
	/**
	 * Builds the notification.
	 * 
	 * @param	string	$type
	 * 		The notification's type. See class constants for better explanation.
	 * 
	 * @param	boolean	$header
	 * 		Specified whether the notification will have header
	 * 
	 * @param	boolean	$timeout
	 * 		Sets the notification to fadeout after 1.5 seconds.
	 * 
	 * @param	boolean	$disposable
	 * 		Lets the user to be able to close the notification.
	 * 
	 * @return	notification
	 * 		The notification object.
	 */
	public function build()
	{
		// Build main sections
		$loginDialog = DOM::create("div", "", "", "loginDialog");
		$this->set($loginDialog);
		
		return $this;
	}
	
	private function getForm()
	{
		$form = new simpleForm();
		$loginURL = url::resolve("www", "/ajax/account/login.php");
		$form->build(NULL, $loginURL, FALSE);
		
		// Set login type to page
		$input = $form->getInput($type = "hidden", $name = "logintype", $value = "page", $class = "", $autofocus = FALSE, $required = FALSE);
		$form->append($input);
		
		// Add sides into form
		$rememberMeContainer = HTML::select(".loginPopup .main .leftSide")->item(0);
		$form->append($rememberMeContainer);
		
		$formInputContainer = HTML::select(".loginPopup .main .formInputContainer")->item(0);
		$form->append($formInputContainer);
		
		$usernameValue = "";
		$input = $form->getInput($type = "text", $name = "username", $value = $usernameValue, $class = "lpinp", $autofocus = TRUE, $required = TRUE);
		$ph = literal::dictionary("username", FALSE);
		DOM::attr($input, "placeholder", ucfirst($ph));
		DOM::append($formInputContainer, $input);
		
		$input = $form->getInput($type = "password", $name = "password", $value = "", $class = "lpinp", $autofocus = FALSE, $required = TRUE);
		$ph = literal::dictionary("password", FALSE);
		DOM::attr($input, "placeholder", ucfirst($ph));
		DOM::append($formInputContainer, $input);
		
		
		$rcont = HTML::select("#rsession .ricnt")->item(0);
		$input = $form->getInput($type = "radio", $name = "rememberme", $value = "off", $class = "lpchk", $autofocus = FALSE, $required = FALSE);
		DOM::attr($input, "checked", TRUE);
		DOM::append($rcont, $input);
		$text = literal::get("login", "lbl_noTrust");
		$forID = "remember_me_off";
		DOM::attr($input, "id", $forID);
		$label = $form->getLabel($text, $forID, $class = "lplbl");
		DOM::append($rcont, $label);
		
		$rcont = HTML::select("#rtrust .ricnt")->item(0);
		$input = $form->getInput($type = "radio", $name = "rememberme", $value = "on", $class = "lpchk", $autofocus = FALSE, $required = FALSE);
		DOM::append($rcont, $input);
		$text = literal::get("login", "lbl_trustComputer");
		$forID = "remember_me_on";
		DOM::attr($input, "id", $forID);
		$label = $form->getLabel($text, $forID, $class = "lplbl");
		DOM::append($rcont, $label);
		
		
		$title = literal::dictionary("login");
		$input = $form->getSubmitButton($title);
		DOM::append($formInputContainer, $input);
		
		// Return form object
		return $form->get();
	}
	
	public function getReport()
	{
		// Create popup
		$popup = new popup();
		$popup->type(popup::TP_PERSISTENT, FALSE);
		$popup->background(TRUE);
		$popup->fade(TRUE);
		$popup->build($pageContent->get());
		
		// Get popup report
		return $popup->getReport();
	}
}
//#section_end#
?>