<?php
//#section#[header]
// Namespace
namespace UI\Forms\formControls;

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
 * @namespace	\formControls
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("UI", "Forms", "formControls/formInput");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Prototype", "UIObjectPrototype");

use \UI\Forms\formControls\formInput;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Prototype\UIObjectPrototype;

/**
 * Switch Button
 * 
 * Builds a switch button control input item.
 * It is like a checkbox but with better looks.
 * 
 * @version	0.1-1
 * @created	November 16, 2015, 15:05 (GMT)
 * @updated	November 16, 2015, 15:05 (GMT)
 */
class switchButton extends UIObjectPrototype
{
	/**
	 * Build a switch button.
	 * 
	 * @param	boolean	$active
	 * 		Whether the switch button will be active or not.
	 * 
	 * @param	string	$name
	 * 		The input name of the checkbox.
	 * 
	 * @param	string	$value
	 * 		The checkbox value.
	 * 
	 * @param	string	$class
	 * 		The extra class for the switch button.
	 * 
	 * @return	switchButton
	 * 		The switchButton object.
	 */
	public function build($active = FALSE, $name = "", $value = "", $class = "")
	{
		// Create the switch button
		$switchButton = DOM::create("div", "", "", "uiSwitchButton");
		HTML::addClass($switchButton, $class);
		if ($active)
			HTML::addClass($switchButton, "on");
		$this->set($switchButton);
		
		// Create hidden checkbox
		$fi = new formInput();
		$input = $fi->build($type = "checkbox", $name, $id = "", $value = "", $required = FALSE)->get();
		HTML::addClass($input, "swt_chk");
		$this->append($input);
		
		// Set checked value for checkbox
		if ($active)
			HTML::attr($input, "checked", "checked");
		
		// Add switch
		$switch = HTML::create("div", "", "", "switch");
		$this->append($switch);
		
		return $this;
	}
}
//#section_end#
?>