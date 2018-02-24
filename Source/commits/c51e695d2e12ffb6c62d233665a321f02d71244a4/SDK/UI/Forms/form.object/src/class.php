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

importer::import("API", "Platform", "DOM::DOM");

use \API\Platform\DOM\DOM;

/**
 * Form Factory
 * 
 * Usage
 * 
 * @version	{empty}
 * @created	April 18, 2013, 11:07 (EEST)
 * @revised	April 18, 2013, 11:07 (EEST)
 * 
 * @deprecated	Use \ UI\Forms\formControls\formFactory instead.
 */
class form
{
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$action
	 * 		{description}
	 * 
	 * @param	{type}	$method
	 * 		{description}
	 * 
	 * @param	{type}	$role
	 * 		{description}
	 * 
	 * @param	{type}	$extra
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function form($id = "", $action = "", $method = "POST", $role = "", $extra = array())
	{
		$form = DOM::create("form", "", $id, "");
		DOM::data($form, $extra);
		
		// Attributes
		DOM::attr($form, "action", $action);
		DOM::attr($form, "method", $method);
		DOM::attr($form, "role", $role);
		
		return $form;
	}
	
	/**
	 * Creates a specified type input
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$value
	 * 		{description}
	 * 
	 * @param	{type}	$extra
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function input($type, $name, $id = "", $value = "", $extra = array())
	{
		// Check Input Type
		$types = array();
		$types[] = "button";
		$types[] = "checkbox";
		$types[] = "file";
		$types[] = "hidden";
		$types[] = "email";
		$types[] = "date";
		$types[] = "image";
		$types[] = "password";
		$types[] = "radio";
		$types[] = "reset";
		$types[] = "submit";
		$types[] = "text";
		
		$expression = implode("|", $types);
		$type_match = preg_match('/^('.$expression.')$/', $type);
		
		// If not match, return NULL
		if (!$type_match)
			return NULL;
		
		// Create element
		$element = DOM::create("input", "", $id, "");
		DOM::data($element, $extra);
		
		// Attributes
		DOM::attr($element, "type", $type);
		DOM::attr($element, "name", $name);
		DOM::attr($element, "value", $value);
		
		return $element;
	}
	
	/**
	 * Create a button (button | submit | reset)
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$value
	 * 		{description}
	 * 
	 * @param	{type}	$disabled
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function button($type = "button", $name = "", $id = "", $value = "", $disabled = FALSE)
	{
		// Check Input Type
		$types = array();
		$types[] = "button";
		$types[] = "reset";
		$types[] = "submit";
		
		$expression = implode("|", $types);
		$type_match = preg_match('/^('.$expression.')$/', $type);
		
		// If not match, return NULL
		if (!$type_match)
			return NULL;
		
		// Create element
		$element = DOM::create("button", "", $id, "");
		
		// Attributes
		DOM::attr($element, "type", $type);
		DOM::attr($element, "name", $name);
		DOM::attr($element, "value", $value);
		
		if ($disabled)
			DOM::attr($element, "disabled", "disabled");
		
		return $element;
	}
	
	/**
	 * Create a textarea input
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$value
	 * 		{description}
	 * 
	 * @param	{type}	$disabled
	 * 		{description}
	 * 
	 * @param	{type}	$readonly
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function textarea($name = "", $id = "", $value = "", $disabled = FALSE, $readonly = FALSE)
	{
		// Create element
		$element = DOM::create("textarea", $value, $id, "");
		DOM::data($element, $extra);
		
		// Attributes
		DOM::attr($element, "name", $name);
		
		if ($disabled)
			DOM::attr($element, "disabled", "disabled");
		
		if ($readonly)
			DOM::attr($element, "readonly", "readonly");
		
		return $element;
	}
	
	/**
	 * Create a label
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$for
	 * 		{description}
	 * 
	 * @param	{type}	$context
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function label($id = "", $for = "", $context = NULL)
	{
		// Create element
		if (gettype($context) == "string")
			$element = DOM::create("label", $context, $id);
		else
		{
			$element = DOM::create("label", "", $id);
			DOM::append($element, $context);
		}
		
		// Attributes
		DOM::attr($element, "for", $for);
		
		return $element;
	}
	
	/**
	 * Create a label
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$legend_context
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function fieldset($id = "", $legend_context = "")
	{
		// Create element
		$element = DOM::create("fieldset", "", $id);
		
		// Create legend
		$legend = DOM::create("legent", $legend_context);
		DOM::append($element, $legend);
		
		return $element;
	}
	
	/**
	 * Create a select list
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$size
	 * 		{description}
	 * 
	 * @param	{type}	$multiple
	 * 		{description}
	 * 
	 * @param	{type}	$options
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function select($name, $id = "", $size = "", $multiple = FALSE, $options = array())
	{
		// Create element
		$element = DOM::create("select", "", $id);
		
		// Attributes
		DOM::attr($element, "name", $name);
		DOM::attr($element, "size", $size);
		
		if ($multiple)
			DOM::attr($element, "multiple", "multiple");
		
		// Insert options if any
		foreach ($options as $option)
			DOM::append($element, $option);
		
		return $element;
	}
	
	/**
	 * Create a select option group
	 * 
	 * @param	{type}	$label
	 * 		{description}
	 * 
	 * @param	{type}	$options
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function optionGroup($label, $options = array())
	{
		// Create element
		$element = DOM::create("optgroup");
		
		// Attributes
		DOM::attr($element, "label", $label);
		
		// Insert options if any
		foreach ($options as $option)
			DOM::append($element, $option);
		
		return $element;
	}
	
	/**
	 * Create a select option
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$value
	 * 		{description}
	 * 
	 * @param	{type}	$context
	 * 		{description}
	 * 
	 * @param	{type}	$selected
	 * 		{description}
	 * 
	 * @param	{type}	$label
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function option($id, $value, $context, $selected = FALSE, $label = "")
	{
		// Create element
		if (gettype($context) == "string")
			$element = DOM::create("option", $context, $id);
		else
		{
			$element = DOM::create("option", "", $id);
			DOM::append($element, $context);
		}
		DOM::attr($element, 'value', $value);
		
		// Attributes
		DOM::attr($element, "label", $label);
		
		if ($selected)
			DOM::attr($element, "selected", "selected");
		
		return $element;
	}
}
//#section_end#
?>