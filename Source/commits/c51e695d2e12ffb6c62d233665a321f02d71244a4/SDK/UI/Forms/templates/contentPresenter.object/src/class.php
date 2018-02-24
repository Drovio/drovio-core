<?php
//#section#[header]
// Namespace
namespace UI\Forms\templates;

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
 * @namespace	\templates
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Forms", "templates::simpleForm");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;
use \UI\Forms\templates\simpleForm;


/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	August 15, 2013, 21:57 (EEST)
 * @revised	August 15, 2013, 21:57 (EEST)
 * 
 * @deprecated	This class is deprecated.
 */
class contentPresenter extends UIObjectPrototype
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $id;
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
	private $title;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $presenter;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $editor;
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function __construct($title)
	{
		$this->id = "presenter_".rand();
		$this->form = new simpleForm($this->id);
		$this->title = $title;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$moduleID
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
	public function build($moduleID = "", $action = "", $editable = FALSE)
	{
		// Presenter holder
		$holder = DOM::create("div", "", $this->id, "contentPresenter");
		$this->set($holder);
		
		// Presenter Title
		$title = DOM::create("div", $this->title, "", "title");
		DOM::append($holder, $title_div);
		
		// Presenter
		$this->presenter = DOM::create("div", "", "", "presenter");
		DOM::append($holder, $this->presenter);

		if (!$this->editable)
			return;
		
		// Insert "edit" button
		$btn_edit = DOM::create("div", "", "", "control edit");
		DOM::attr($btn_edit, 'role', "edit");
		DOM::append($holder, $btn_edit);
		
		// Insert "cancel" button
		$btn_cancel = DOM::create("div", "", "", "control cancel");
		DOM::attr($btn_cancel, 'role', "cancel");
		DOM::append($holder, $btn_cancel);
		
		// Insert editor's form container
		$editorFormContainer = DOM::create("div", "", "", "editor");
		DOM::attr($editorFormContainer, 'style', "display:none;");
		DOM::append($holder, $editorFormContainer);
		
		// Editor's form
		$this->editor = $this->form->build($moduleID, $action, $controls = TRUE);
		DOM::append($editorFormContainer, $this->editor);
		
		return $this;
	}
	
	/**
	 * {description}
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
	public function insertContent($tag, $title, $name, $value = "", $type = "", $class = "", $required = FALSE, $autofocus = FALSE, $hidden = FALSE)
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
	 * {description}
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
	 * {description}
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
	private function getPresenterRow($name, $title, $value, $hidden = FALSE)
	{
		// Create the form row div
		$presentRow = $this->form->getRow();
		
		// Create Label
		$label = $this->form->getLabel($title, $name);
		DOM::append($presentRow, $label);
		
		// Create Content Label
		$contentLabel = $this->getContentLabel($value, $name);
		DOM::append($presentRow, $contentLabel);
		
		return $presentRow;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function getContentLabel($title, $name)
	{
		// Create Label and input
		$title = DOM::create("span", $title);
		
		$label = $this->form->getLabel($title, $for, $class = "uiContentLabel");
		DOM::attr($label, 'data-ref', "inp_".$name."_".$this->id);
		DOM::attr($label, 'data-content', $title);
				
		return $label;
	}
}
//#section_end#
?>