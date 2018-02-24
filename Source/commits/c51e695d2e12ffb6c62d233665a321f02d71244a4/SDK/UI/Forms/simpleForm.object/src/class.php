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
importer::import("API", "Model", "protocol::ajax::ascop");
importer::import("API", "Geoloc", "locale");
importer::import("API", "Geoloc", "lang::mlgContent");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("UI", "Forms", "form");
importer::import("UI", "Presentation", "heading");

use \API\Platform\DOM\DOM;
use \API\Model\protocol\ajax\ascop;
use \API\Geoloc\locale;
use \API\Geoloc\lang\mlgContent;
use \API\Comm\database\connections\interDbConnection;
use \UI\Forms\form;
use \UI\Presentation\heading;

/**
 * Simple Form
 * 
 * Builds a simple system form.
 * 
 * @version	{empty}
 * @created	April 18, 2013, 11:38 (EEST)
 * @revised	April 18, 2013, 11:38 (EEST)
 * 
 * @deprecated	Use \UI\Forms\templates\simpleForm instead.
 */
class simpleForm
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $id;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $form;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $form_body;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $form_controls;
	
	/**
	 * Form Report container (for POST actions)
	 * 
	 * @return	void
	 */
	public static function get_reportHolder()
	{
		return "#".$_POST['formID']." > .form_report";
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$action
	 * 		{description}
	 * 
	 * @param	{type}	$role
	 * 		{description}
	 * 
	 * @param	{type}	$controls
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function create_form($id = "", $action = "", $role = "", $controls = TRUE)
	{
		//_____ form random id (if not given)
		$id = ($id == "" ? "frm_".rand() : $id);
		$this->id = $id;
		
		//_____ form action attribute (in case of string)
		$form_action = "";
		if (gettype($action) == "string")
			$form_action = $action;
		
		// Get Locale
		$current_locale = locale::get();
			
		// Create Form
		$this->form = form::form($id, $form_action, $method = "POST", $role, $extra = array());
		DOM::attr($this->form, 'locale', $current_locale);
		DOM::appendAttr($this->form, 'class', "uiSimpleForm");
		
		// Build Report Container
		$frm_report = DOM::create("div", "", "", "form_report reportContainer");
		DOM::append($this->form, $frm_report);
		
		// Build Form Body Container
		$this->form_body = DOM::create("div", "", "", "form_body");
		DOM::append($this->form, $this->form_body);
		
		// Build Dialog Controls Container (as a container for custom forms)
		$this->form_controls = DOM::create("div", "", "", "controls");
		DOM::append($this->form, $this->form_controls);
		
		// Create hidden form id
		$formID = $this->get_form_input("input", NULL, $name = "formID", $value = $this->id, $type = "hidden", $class = "", $required = FALSE, $autofocus = FALSE);
		$this->insert_to_body($formID['element']);
		
		// If the form has no default controls
		if (!$controls)
			return $this->form;
		
		// Controls Group
		$controls_group = $this->get_group();
		DOM::append($this->form_controls, $controls_group);
		
		// Create "Submit" Button
		$submit_title = mlgContent::get_literal("global::dictionary", "execute");
		$btn_submit = $this->get_actionButton($submit_title);
		if (is_array($action))
			ascop::add_actionReady($btn_submit, $action);
		DOM::append($controls_group, $btn_submit);
		
		// Create Reset Button
		$reset_title = mlgContent::get_literal("global::dictionary", "reset");
		$btn_reset = $this->get_form_item($tag = "button", $name = "", $value = "", $type = "reset", "", $required = FALSE);
		DOM::append($btn_reset, $reset_title);
		DOM::append($controls_group, $btn_reset);
		
		return $this->form;
	}
	
	/**
	 * Inserts a header into the form
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function insert_header($title, $type = "1")
	{
		$hdr = $this->get_header($title, $type);
		$this->insert_to_body($hdr);
		
		return $hdr;
	}
	/**
	 * Creates the header for the form
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_header($title, $type = "1")
	{
		return heading::get($title, $type);
	}
	
	/**
	 * Creates a form action button
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_actionButton($title)
	{
		$btn_submit = $this->get_form_item($tag = "button", $name = "", $value = "", $type = "submit", "positive", $required = FALSE);
		DOM::append($btn_submit, $title);
		
		return $btn_submit;
	}
	
	/**
	 * Creates a full input control (label (if any) and input)
	 * 
	 * @param	{type}	$tag
	 * 		{description}
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$value
	 * 		{description}
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @param	{type}	$required
	 * 		{description}
	 * 
	 * @param	{type}	$autofocus
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_form_input($tag, $title, $name, $value = "", $type = "", $class = "", $required = FALSE, $autofocus = FALSE)
	{
		// Create the form row div
		$form_group = $this->get_group();
		
		$name = ($name == "" ? "input_".rand() : $name);
		
		// Create Label
		if ($type != "hidden")
		{
			//create_label($id = "", $for = "", $value = "");
			$label = $this->get_label($title, $name, $required);

			// Append label
			DOM::append($form_group, $label);
		}
		
		$item = array();
		$item['group'] = $form_group;
		
		$item['element'] = $this->get_form_item($tag, $name, $value, $type, $class, $required, $autofocus);
		DOM::append($item['group'], $item['element']);
		
		return $item;
	}
	
	/**
	 * Creates a full resource (radio | checkbox) control group
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$resource
	 * 		{description}
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @param	{type}	$value
	 * 		{description}
	 * 
	 * @param	{type}	$required
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_rsrc_option($name, $resource, $type, $value = "", $required = FALSE)
	{
		// Create the form group div
		$form_group = $this->get_group();
		
		$item = array();
		$item['group'] = $form_group;
		
		// insert group
		$inputGroup = DOM::create("div", "", "", "uiSimpleFormInputGroup");
		$item['element'] = $inputGroup;
		
		// Append To Group
		DOM::append($form_group, $inputGroup);
		
		$counter = 0;
		foreach ($resource as $key => $val)
		{
			$rs_name = "rsrc_".$counter."_".$name;
			$counter++;
			// Form group
			$fgroup = $this->get_group("rsrc_group");
			// insert label
			$label = $this->get_label($val, $rs_name, $required);
			DOM::append($fgroup, $label);
			// insert item
			$input = $this->get_form_item("input", $name, $key, $type, "", $required);
			DOM::attr($input, 'id', 'inp_'.$rs_name."_".$this->id);
			
			if ($value == $val && $type = "radio")
				DOM::attr($input, 'checked', 'checked');
				
			DOM::append($fgroup, $input);
			
			DOM::append($inputGroup, $fgroup);
		}
		
		return $item;
	}
	
	/**
	 * Creates a full resource (select) control (label (if any) and resource)
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$resource
	 * 		{description}
	 * 
	 * @param	{type}	$value
	 * 		{description}
	 * 
	 * @param	{type}	$multi
	 * 		{description}
	 * 
	 * @param	{type}	$required
	 * 		{description}
	 * 
	 * @param	{type}	$autofocus
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_rsrc_select($title, $name, $resource, $value = "", $multi = FALSE, $required = FALSE, $autofocus = FALSE)
	{
		// Create the form group div
		$form_group = $this->get_group();
		
		// Create Label
		
		$label = $this->get_label($title, $name, $required);

		// Append label
		DOM::append($form_group, $label);
		
		$item = array();
		$item['group'] = $form_group;
		
		
		// Create Options
		$options = array();
		$counter = 0;
		foreach ($resource as $key=>$val)
		{
			$rs_name = "rsrc_".$counter."_".$name;
			$counter++;
			// Create Option
			$context = DOM::create("span", $val);
			$options[] = form::option('rsrc_'.$rs_name."_".$this->id, $key, $context, $selected = ($value == $key));
		}
		
		// Create select
		$inputGroup = form::select($name, "inp_".$name."_".$this->id, "", $multi, $options);
		DOM::appendAttr($inputGroup, "class", "uiSimpleFormInput".($required ? " required" : ""));
		if ($autofocus)
			DOM::attr($inputGroup, 'autofocus', "autofocus");
		$item['element'] = $inputGroup;
		
		// Append To Group
		DOM::append($form_group, $inputGroup);
		
		return $item;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$group
	 * 		{description}
	 * 
	 * @param	{type}	$directions
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function insertDirections($group, $directions)
	{
		if (gettype($directions) == "string")
			$directionsElement = DOM::create("span", $directions, "", "uiSimpleFormNotes");
		else
		{
			$directionsElement = $directions;
			DOM::appendAttr($directionsElement, "class", "uiSimpleFormNotes");
		}
		DOM::append($group['group'], $directionsElement);
	}
	
	/**
	 * ____________________ Auxiliary Items
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$required
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_label($title, $name, $required = FALSE)
	{
		// Create label context
		$context = DOM::create("span", "", "", "context");
		
		//_____ Create Label title
		if (gettype($title) == "string")
			$title = DOM::create("span", $title);
			
		DOM::append($context, $title);
		
		//_____ Create Label required span
		if ($required)
		{
			$span = DOM::create("span", "*", "", "required");
			DOM::append($context, $span);
		}
		
		$span = DOM::create("span", ":");
		DOM::append($context, $span);
		
		// Create Label
		$label = form::label("lbl_".$name."_".$this->id, "inp_".$name."_".$this->id, $context);
		DOM::appendAttr($label, 'class', "uiSimpleFormLabel".($required ? " required" : ""));
		
		return $label;
	}
	
	/**
	 * Creates a form item (input | button | text_area | etc.)
	 * 
	 * @param	{type}	$tag
	 * 		{description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$value
	 * 		{description}
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @param	{type}	$required
	 * 		{description}
	 * 
	 * @param	{type}	$autofocus
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_form_item($tag, $name, $value = "", $type = "", $class = "", $required = FALSE, $autofocus = FALSE)
	{
		switch ($tag)
		{
			case "input":
				$element = form::input($type, $name, $id = "inp_".$name."_".$this->id, $value);
				break;
			case "button":
				$element = form::button($type, $name, $id = "inp_".($name == "" ? "button" : $name)."_".$this->id, $value);
				break;
			case "textarea":
				$element = form::textarea($name, $id = "inp_".$name."_".$this->id, $value);
				break;
		}
		
		if (is_null($element))
			return NULL;
			
		DOM::appendAttr($element, 'class', "uiSimpleFormInput".($required ? " required" : "").($class == "" ? "" : " $class"));
		
		if ($autofocus)
			DOM::attr($element, 'autofocus', "autofocus");
		
		return $element;
	}
	
	/**
	 * ____________________ General
	 * 
	 * @param	{type}	$item
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function insert_to_body($item)
	{
		// If string given, create span
		if (gettype($item) == "string")
			$item = DOM::create("span", $item);
		
		DOM::append($this->form_body, $item);
	}
	
	/**
	 * Get form id
	 * 
	 * @return	void
	 */
	public function get_form_id()
	{
		return $this->id;
	}
	
	/**
	 * Get form group
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_group($class = "")
	{
		return DOM::create("div", "", "", "simpleFormGroup".($class = "" ? "" : " $class"));
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$resource
	 * 		{description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$value
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function transform_to_resource($resource, $id, $value)
	{
		$dbc = new interDbConnection();
		
		$result = array();
		while ($row = $dbc->fetch($resource))
			$result[$row[$id]] = $row[$value];
		
		$dbc->seek($resource, 0);
		return $result;
	}
	
}
//#section_end#
?>