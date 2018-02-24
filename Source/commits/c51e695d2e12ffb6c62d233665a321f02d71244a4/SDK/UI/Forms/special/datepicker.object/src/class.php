<?php
//#section#[header]
// Namespace
namespace UI\Forms\special;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Forms", "formFactory");
importer::import("ESS", "Prototype", "html::PopupPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \ESS\Prototype\html\PopupPrototype;
use \UI\Forms\formFactory;
use \UI\Html\DOM;

class datepicker extends UIObjectPrototype
{
	// Constructor Method
	public function __construct()
	{
		
	}
	
	public function build($id = "")
	{
		$ff = new formFactory();
		
		// Create captcha placeholder
		$datepickerWrapper = DOM::create("div");
		$this->set($datepickerWrapper);
		
		// Hidden Value Holders
		$dayValueHolder = $ff->getInput($type = "hidden", $name = "day", $value = "0", $class = "", $autofocus = FALSE, $required = FALSE);
		DOM::append($datepickerWrapper, $dayValueHolder);
		$monthValueHolder = $ff->getInput($type = "hidden", $name = "month", $value = "0", $class = "", $autofocus = FALSE, $required = FALSE);
		DOM::append($datepickerWrapper, $monthValueHolder);
		$yearValueHolder = $ff->getInput($type = "hidden", $name = "year", $value = "0", $class = "", $autofocus = FALSE, $required = FALSE);
		DOM::append($datepickerWrapper, $yearValueHolder);
		
		//
		$controlWrapper = DOM::create();
		DOM::append($datepickerWrapper, $controlWrapper);
		$date = $ff->getInput($type = "text", $name = "date", $value = "0", $class = "", $autofocus = FALSE, $required = FALSE);
		DOM::append($datepickerWrapper, $date);
		$pickerControl = DOM::create();
		DOM::append($datepickerWrapper, $pickerControl);
		
		//
		$pickerWrapper = DOM::create('div', '', '', 'popup noDisplay');
		DOM::append($datepickerWrapper, $pickerWrapper);
		
		return $this;
	}
}
//#section_end#
?>