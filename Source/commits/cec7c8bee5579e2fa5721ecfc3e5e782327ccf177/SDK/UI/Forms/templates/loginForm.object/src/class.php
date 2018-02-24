<?php
//#section#[header]
// Namespace
namespace UI\Forms\templates;

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
 * @package	Forms
 * @namespace	\templates
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "literals::literal");
importer::import("UI", "Forms", "templates::simpleForm");

use \API\Resources\literals\literal;
use \UI\Forms\templates\simpleForm;

/**
 * Login Form
 * 
 * Create a system login form.
 * 
 * @version	0.1-1
 * @created	June 10, 2013, 12:16 (EEST)
 * @revised	July 17, 2014, 16:41 (EEST)
 * 
 * @deprecated	This class is deprecated and should not be used.
 */
class loginForm extends simpleForm
{
	/**
	 * Creates the login form.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id that this form posts to.
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module.
	 * 
	 * @param	string	$usernameValue
	 * 		The username (if its predefined).
	 * 
	 * @param	boolean	$rememberMe
	 * 		Indicator whether the form will have a checkbox to remember the user.
	 * 
	 * @return	loginForm
	 * 		Returns the loginForm object.
	 */
	public function build($moduleID = "", $action = "", $usernameValue = "", $rememberMe = TRUE)
	{
		// Build Form
		parent::build($moduleID, $action, $controls = FALSE);
		
		// Build Inputs
		// username
		$usernameTitle = literal::dictionary("username");
		$usernameInput = $this->getInput($type = "text", $name = "username", $value = $usernameValue, $class = "", $autofocus = empty($usernameValue));
		$this->insertRow($usernameTitle, $usernameInput, $required = TRUE, $notes = "");
		
		// password
		$passwordTitle = literal::dictionary("password");
		$passwordInput = $this->getInput($type = "password", $name = "password", $value = "", $class = "", $autofocus = !empty($usernameValue));
		$this->insertRow($passwordTitle, $passwordInput, $required = TRUE, $notes = "");
		
		if ($rememberMe)
		{
			// remember me
			$rememberTitle = literal::get("global::forms::login", "lbl_keepLoggedIn");
			$rememberCheck = $this->getInput($type = "checkbox", $name = "rememberme", $value = "", $class = "", $autofocus = FALSE);
			$this->insertRow($rememberTitle, $rememberCheck, $required = TRUE, $notes = "");
		}
		
		// Login Button
		$title = literal::dictionary("login");
		$loginButton = $this->getSubmitButton($title, $id = "login");
		$this->append($loginButton);
		
		return $this;
	}
}
//#section_end#
?>