<?php
//#section#[header]
// Namespace
namespace UI\Forms\special;

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
 * @namespace	\special
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::environment::Url");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("ESS", "Prototype", "html::PopupPrototype");
importer::import("UI", "Forms", "formFactory");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \ESS\Protocol\client\environment\Url;
use \ESS\Prototype\html\PopupPrototype;
use \UI\Forms\formFactory;
use \UI\Html\DOM;

/**
 * Date Picker
 * 
 * A UI control control, which gives the ability to select a day through a calendar.
 * 
 * @version	{empty}
 * @created	February 3, 2014, 20:56 (EET)
 * @revised	February 4, 2014, 19:36 (EET)
 */
class datepicker extends UIObjectPrototype
{
	/**
	 * The maximun accepted year
	 * 
	 * @type	integer
	 */
	private $maxYear;
	
	/**
	 * The year range that calendar will display (+/- yearRange), negative value means infinate. Default is 10.
	 * 
	 * @type	integer
	 */
	private $yearRange = 10;

	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$this->maxYear = 2013;
	}
	
	/**
	 * Build the date picker object
	 * 
	 * @param	string	$id
	 * 		object id
	 * 
	 * @return	object
	 * 		A datepicker instance
	 */
	public function build($id = '')
	{
		$ff = new formFactory();
		
		// Create captcha placeholder
		$datepickerWrapper = DOM::create("div", '', $id, 'datePickerWrapper');
		$this->set($datepickerWrapper);
		
		if(empty($id))
			return $this;
		
		// Hidden Value Holders
		$dayValueHolder = $ff->getInput($type = "hidden", $name = $id."_day", $value = "0", $class = "", $autofocus = FALSE, $required = FALSE);
		DOM::append($datepickerWrapper, $dayValueHolder);
		$monthValueHolder = $ff->getInput($type = "hidden", $name = $id."_month", $value = "0", $class = "", $autofocus = FALSE, $required = FALSE);
		DOM::append($datepickerWrapper, $monthValueHolder);
		$yearValueHolder = $ff->getInput($type = "hidden", $name = $id."_year", $value = "0", $class = "", $autofocus = FALSE, $required = FALSE);
		DOM::append($datepickerWrapper, $yearValueHolder);
		
		//
		$inputGroup = DOM::create('div', '', '', 'inputGroup');
		DOM::append($datepickerWrapper, $inputGroup);
		$date = $ff->getInput($type = "text", $name = "date", $value = "0", $class = "", $autofocus = FALSE, $required = FALSE);
		DOM::append($inputGroup, $date);
		$imgBtn =  DOM::create('input');
		DOM::attr($imgBtn, 'src', Url::resource("/Library/Media/c/UI/datepicker/calendar.png")); 
		DOM::attr($imgBtn, 'width', "30px");
		DOM::attr($imgBtn, 'height', "30px");
		DOM::attr($imgBtn, 'role', "button");
		DOM::attr($imgBtn, 'type', "image");
		DOM::attr($imgBtn, 'data-dppopup', "show");
		DOM::append($inputGroup, $imgBtn);
		
		// Create popUp contet
		$pickerControl = DOM::create('div', '', '', 'popup pickerPopup noDisplay');
		DOM::append($datepickerWrapper, $pickerControl);
		
		$controlId = $ff->getInput($type = "hidden", $name = "id", $value = $id, $class = "", $autofocus = FALSE, $required = FALSE);
		DOM::append($pickerControl, $controlId);
		
		$content = DOM::create('div', '');
		DOM::append($pickerControl, $content);	
		
		// Get Current day to initialize popUp
		$today = getdate();
		
		// day
		$resource = array();
		for($i = 1; $i <= 31; $i++)
		{
			//Selector values
			$resource[(string)$i] = (string)$i;
		}
		$input = $ff->getResourceSelect($name = "dayPicker", $multiple = FALSE, $class = "", $resource, $selectedValue = $today['mday']);
		DOM::append($content, $input);	
		
		// Month
		$resource = array();
		for($i = 1; $i <= 12; $i++)
		{
			//Selector values
			$resource[(string)$i] = (string)$i;
		}
		$input = $ff->getResourceSelect($name = "monthPicker", $multiple = FALSE, $class = "", $resource, $selectedValue = $today['mon']);
		DOM::append($content, $input);
		
		// Year
		$input = $ff->getInput($type = "text", $name = "yearPicker", $value= $today['year'], $class = "", $autofocus = FALSE);
		DOM::append($content, $input);
		
		return $this;
	}
	
	/**
	 * Return the selected daypicker day that return with the _REQUEST object
	 * 
	 * @param	array	$data
	 * 		The _GET or _POST array oblect
	 * 
	 * @param	string	$id
	 * 		object id
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function getDay($data, $id)
	{
		return $data[$id."_day"];
	}
	
	/**
	 * Return the selected daypicker month that return with the _REQUEST object
	 * 
	 * @param	array	$data
	 * 		The _GET or _POST array oblect
	 * 
	 * @param	string	$id
	 * 		object id
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function getMonth($data, $id)
	{
		return $data[$id."_month"];
	}
	
	/**
	 * Return the selected daypicker year that return with the _REQUEST object
	 * 
	 * @param	array	$data
	 * 		The _GET or _POST array oblect
	 * 
	 * @param	string	$id
	 * 		object id
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function getYear($data, $id)
	{
		return $data[$id."_year"];
	}
}
//#section_end#
?>