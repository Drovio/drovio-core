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
importer::import("UI", "Forms", "form");
importer::import("UI", "Forms", "simpleForm");

use \API\Platform\DOM\DOM;
use \UI\Forms\form;
use \UI\Forms\simpleForm;

/**
 * {title}
 * 
 * Usage
 * 
 * @version	{empty}
 * @created	April 30, 2013, 12:49 (EEST)
 * @revised	April 30, 2013, 12:49 (EEST)
 * 
 * @deprecated	Use \UI\Forms\templates\contentPresenter instead.
 */
class contentPresenter
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $form_builder;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $holder_div;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $presenter_div;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $form_div;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $form;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $editable;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $id;
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$action
	 * 		{description}
	 * 
	 * @param	{type}	$editable
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function __construct($builder, $title, $action = "", $editable = TRUE)
	{
		$this->editable = $editable;
		$this->form_builder = new simpleForm();
		
		$this->id = "presenter_".rand();
		// Build the general structure
		$this->holder_div = DOM::create("div", "", $this->id, "contentPresenter");
		
		DOM::append($this->holder_div, $presenter_wrapper);
		
		$title_div = DOM::create("div", $title, "", "title");
		DOM::append($this->holder_div, $title_div);
		
		$this->presenter_div = DOM::create("div", "", "", "presenter");
		DOM::append($this->holder_div, $this->presenter_div);

		if (!$this->editable)
			return;
		
		// Insert "edit" button
		$btn_edit = DOM::create("div", "", "", "control edit");
		DOM::attr($btn_edit, 'role', "edit");
		DOM::append($this->holder_div, $btn_edit);
		
		// Insert "cancel" button
		$btn_cancel = DOM::create("div", "", "", "control cancel");
		DOM::attr($btn_cancel, 'role', "cancel");
		DOM::append($this->holder_div, $btn_cancel);
		
		// If content is editable, insert form for editing
		$this->form_div = DOM::create("div", "", "", "editor");
		DOM::attr($this->form_div, 'style', "display:none;");
		DOM::append($this->holder_div, $this->form_div);
		
		$this->form = $this->form_builder->create_form($this->id, $action, "editor" , $controls = TRUE);

		DOM::append($this->form_div, $this->form);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function get_full_presenter()
	{
		return $this->holder_div;
	}
	
	/**
	 * Insert simple content (text etc.)
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
	 * @param	{type}	$hidden
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function insert_content($tag, $title, $name, $value = "", $type = "", $class = "", $required = FALSE, $autofocus = FALSE, $hidden = FALSE)
	{
		if (gettype($title) == "string")
			$cnt_title = DOM::create('span', $title);
		else
			$cnt_title = $title;
		
		// Insert Item to Presenter
		$cnt_title_clone = $cnt_title->cloneNode(TRUE);
		$frow = $this->get_present_group($name, $cnt_title, $value, $hidden);
		DOM::append($this->presenter_div, $frow);
		
		if ($this->editable && !$hidden)
		{
			$input = $this->form_builder->get_form_input($tag, $cnt_title_clone, $name, $value, $type, $class, $required, $autofocus);
			$this->form_builder->insert_to_body($input['group']);
		}
	}
	/**
	 * Insert resource content (radio | checkbox | resource[select] etc.)
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
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @param	{type}	$class
	 * 		{description}
	 * 
	 * @param	{type}	$required
	 * 		{description}
	 * 
	 * @param	{type}	$hidden
	 * 		{description}
	 * 
	 * @param	{type}	$multi
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function insert_resource($title, $name, $resource, $value = "", $type = "", $class = "", $required = FALSE, $hidden = FALSE, $multi = FALSE)
	{
		if (gettype($title) == "string")
			$cnt_title = DOM::create('span', $title);
		else
			$cnt_title = $title;
		
		// Insert Item to Presenter
		$cnt_title_clone = $cnt_title->cloneNode(TRUE);
		$frow = $this->get_present_group($name, $cnt_title, $resource[$value], $hidden);
		DOM::append($this->presenter_div, $frow);
		
		if ($this->editable && !$hidden) {
			// Insert Item to Editor
			
			if ($type == "radio" || $type == "checkbox")
				$input = $this->form_builder->get_rsrc_option($name, $resource, $type, $value, $required = FALSE);
			else if ($type == "resource")
				$input = $this->form_builder->get_rsrc_select($title, $name, $resource, $value, $multi, $required);
			
			$this->form_builder->insert_to_body($input['group']);
		}
	}
	
	/**
	 * Inserts a header into the form
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function insert_header($title)
	{
		$hdr = $this->form_builder->get_header($title, "2");
		DOM::append($this->presenter_div, $hdr);
		
		if ($this->editable)
		{
			$hdr_clone = $hdr->cloneNode(TRUE);
			$this->form_builder->insert_to_body($hdr_clone);
		}
		
		return $hdr;
	}
	
	/**
	 * Returns data group for the presenter
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$value
	 * 		{description}
	 * 
	 * @param	{type}	$hidden
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_present_group($name, $title, $value, $hidden = FALSE)
	{
		// Create the form row div
		$form_row = $this->form_builder->get_group();
		
		// Create Label
		$label = $this->form_builder->get_label($title, $name);
		DOM::append($form_row, $label);
		
		// Create Value
		$label = $this->get_content_label($value, $name);
		DOM::append($form_row, $label);
		
		return $form_row;
	}
	
	/**
	 * Creates a label to show the content of the data
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function get_content_label($title, $name)
	{
		// Create Label and input
		$label_title = DOM::create("span", $title);
		$label = form::label($id = "clbl_".$name, $for = "", $label_title);
		DOM::attr($label, 'class', "uiContentLabel");
		DOM::attr($label, 'data-ref', "inp_".$name."_".$this->id);
		DOM::attr($label, 'data-content', $title);
				
		return $label;
	}
}
//#section_end#
?>