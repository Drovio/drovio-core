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
 * Form Input
 * 
 * Builds a form control input item
 * 
 * @version	{empty}
 * @created	March 11, 2013, 15:05 (EET)
 * @revised	October 4, 2013, 14:17 (EEST)
 */
class formInput extends formItem
{
	/**
	 * All the accepted input types.
	 * 
	 * @type	array
	 */
	private $inputTypes = array(
		'button',
		'checkbox',
		'file',
		'hidden',
		'image',
		'password',
		'radio',
		'reset',
		'submit',
		'text',
		'color',
		'date',
		'datetime',
		'datetime-local',
		'email',
		'month',
		'number',
		'range',
		'search',
		'tel',
		'time',
		'url',
		'week'
	);
	
	/**
	 * Builds the form input
	 * 
	 * @param	string	$type
	 * 		The input's type
	 * 
	 * @param	string	$name
	 * 		The input's name
	 * 
	 * @param	string	$id
	 * 		The input's id
	 * 
	 * @param	string	$value
	 * 		The input's default value
	 * 
	 * @param	boolean	$required
	 * 		Sets the input as required for the form.
	 * 
	 * @return	formInput
	 * 		{description}
	 */
	public function build($type = "text", $name = "", $id = "", $value = "", $required = FALSE)
	{
		// Check input type
		if (!$this->checkType($type))
			return $this;
			
		// Build form item
		parent::build("input", $name, $id, $value, "uiFormInput");
		
		// Set input type
		$input = $this->get();
		DOM::attr($input, "type", $type);
		
		// Set required input
		if ($required)
			DOM::attr($input, "required", "required");
		
		return $this;
	}
	
	/**
	 * Checks if the given input type is valid for HTML4 and HTML5.
	 * 
	 * @param	string	$type
	 * 		The input's type
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	private function checkType($type)
	{
		// Check input type
		$expression = implode("|", $this->inputTypes);
		$valid = preg_match('/^('.$expression.')$/', $type);
		
		return ($valid === 1);
	}
}
//#section_end#
?>