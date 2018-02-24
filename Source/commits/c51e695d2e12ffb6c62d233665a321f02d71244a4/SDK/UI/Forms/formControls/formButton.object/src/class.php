<?php
//#section#[header]
// Namespace
namespace UI\Forms\formControls;

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
 * @namespace	\formControls
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Forms", "formControls::formItem");
importer::import("UI", "Html", "DOM");

use \UI\Forms\formControls\formItem;
use \UI\Html\DOM;

/**
 * Universal Form Button
 * 
 * Builds a universal form button for all forms.
 * 
 * @version	{empty}
 * @created	March 11, 2013, 15:20 (EET)
 * @revised	April 18, 2013, 13:25 (EEST)
 */
class formButton extends formItem
{
	/**
	 * All the accepted button types.
	 * 
	 * @type	array
	 */
	private $buttonTypes = array(
		'button',
		'reset',
		'submit'
	);
	
	/**
	 * Builds the form button
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	string	$type
	 * 		The button's type.
	 * 		Accepted values:
	 * 		- "button"
	 * 		- "reset"
	 * 		- "submit"
	 * 
	 * @param	string	$id
	 * 		The button's id
	 * 
	 * @param	string	$name
	 * 		The button's name
	 * 
	 * @param	boolean	$positive
	 * 		Indicates a positive submit button.
	 * 
	 * @return	formButton
	 */
	public function build($title = "Submit", $type = "submit", $id = "", $name = "", $positive = FALSE)
	{
		// Check button type
		if (!$this->checkType($type))
			return $this;
		
		// Build Input
		parent::build("button", $name, $id, "", "uiFormButton".($positive ? " positive" : ""));
		
		// Attributes
		$button = $this->get();
		DOM::attr($button, "type", $type);
		
		if (gettype($title) == "string")
			$title = DOM::create("span", $title);
		DOM::append($button, $title);
		
		return $this;
	}
	
	/**
	 * Checks the buttons type
	 * 
	 * @param	string	$type
	 * 		The button's type
	 * 
	 * @return	boolean
	 */
	private function checkType($type)
	{
		// Check input type
		$expression = implode("|", $this->buttonTypes);
		$valid = preg_match('/^('.$expression.')$/', $type);
		
		return ($valid === 1);
	}
}
//#section_end#
?>