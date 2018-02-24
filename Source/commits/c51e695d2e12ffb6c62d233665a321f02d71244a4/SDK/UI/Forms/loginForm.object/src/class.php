<?php
//#section#[header]
// Namespace
namespace UI\Forms;

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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Security", "authentication");
importer::import("API", "Geoloc", "lang::mlgContent");
importer::import("API", "Platform", "DOM::DOM");
importer::import("UI", "Forms", "simpleForm");

use \API\Security\authentication;
use \API\Geoloc\lang\mlgContent;
use \API\Platform\DOM\DOM;
use \UI\Forms\simpleForm;

/**
 * {title}
 * 
 * Usage
 * 
 * @version	{empty}
 * @created	April 18, 2013, 12:10 (EEST)
 * @revised	April 18, 2013, 12:10 (EEST)
 * 
 * @deprecated	Use \UI\Forms\templates\loginForm instead.
 */
class loginForm extends simpleForm
{
	/**
	 * {description}
	 * 
	 * @param	{type}	$action
	 * 		{description}
	 * 
	 * @param	{type}	$keepLoggedIn
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function create_form($action, $keepLoggedIn = FALSE)
	{
		//##_____ Code _____##//
		$form_action = "";
		if (gettype($action) == "string") {
			$form_action = $action;
		}

		$login_form = parent::create_form($id = "login_form", $form_action, "login", $controls = FALSE);

		$title = mlgContent::get_literal("global::dictionary", "username");
		$username = parent::get_form_input($tag = "input", $title, $name = "username", $value = "", $type = "text", $class = "", $required = TRUE, $autofocus = TRUE);
		parent::insert_to_body($username['group']);
		
		$title = mlgContent::get_literal("global::dictionary", "password");
		$password = parent::get_form_input($tag = "input", $title, $name = "password", $value = "", $type = "password", $class = "", $required = TRUE, $autofocus = FALSE);
		parent::insert_to_body($password['group']);
		
		if ($keepLoggedIn)
		{
			$title = mlgContent::get_literal("global::forms::login", "lbl_keepLoggedIn");
			
			$resource = array();
			$resource['1'] = $title;
			$keyring = parent::get_rsrc_option($name = "remember", $resource, "checkbox", $value = "", $required = FALSE);
			parent::insert_to_body($keyring['group']);
		}
		
		$title = mlgContent::get_literal("global::dictionary", "login");
		
		if (gettype($action) == "array") {
			// Loading Ico
			$loading_ico = DOM::create("div", "", "", "ico form_loading_ico f-left");
			DOM::append($this->form_controls, $loading_ico);
			
			$button_group = parent::get_group();
			DOM::append($this->form_controls, $button_group);
			
			$acc = authentication::get_access($action['id']);
			$login_button = parent::get_actionButton($title);
			DOM::data($login_button, "action", $action);
			DOM::append($button_group, $login_button);
			
		}

		return $login_form;
	}
}
//#section_end#
?>