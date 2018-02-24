<?php
//#section#[header]
// Namespace
namespace UI\Presentation;

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
 * @package	Presentation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Forms", "Form");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Forms\Form;

/**
 * Data Grid List
 * 
 * The Data Grid List can be used to present multiple data in the form of a grid.
 * 
 * @version	1.0-3
 * @created	April 25, 2013, 11:32 (EEST)
 * @updated	April 21, 2015, 13:26 (EEST)
 */
class dataGridList extends UIObjectPrototype
{
	/**
	 * Object's id
	 * 
	 * @type	string
	 */
	private $id;
	/**
	 * Object's root list element
	 * 
	 * @type	DOMElement
	 */
	private $gridList;
	
	/**
	 * Horizontal capacity
	 * 
	 * @type	integer
	 */
	private $hSize;
	/**
	 * If set to TRUE a checkbox will be prepended in each row
	 * 
	 * @type	boolean
	 */
	private $checkable;
	/**
	 * A list of the row checkboxes
	 * 
	 * @type	array
	 */
	private $checkList;
	
	/**
	 * Requested width ratios for the columns
	 * 
	 * @type	array
	 */
	private $columnRatios;
	
	
	/**
	 * The constructor class. Use this to create a dataGridList object.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$this->id = NULL;
		$this->gridList = NULL;
		$this->hSize = 0;
		$this->checkable = FALSE;
	}
	
	
	/**
	 * Creates the dataGridList container
	 * 
	 * @param	string	$id
	 * 		The id of the container
	 * 
	 * @param	boolean	$checkable
	 * 		If set to TRUE, the dataGridList will have a checkbox at the start of each row
	 * 
	 * @return	dataGridList
	 * 		The dataGridList object.
	 */
	public function build($id = "", $checkable = FALSE)
	{
		$this->id = ( $id == "" ? "dgl_".rand() : $id);
		$gridListWrapper = DOM::create("div", "", $this->id, "uiDataGridList initialize");
		$this->set($gridListWrapper);
		
		// Set gridList as checkable
		if ($checkable)
			HTML::addClass($gridListWrapper, "checkable");
		
		// Create grid list
		$this->gridList = DOM::create("ul", "", "", "gridList");
		DOM::append($gridListWrapper, $this->gridList);
		
		$this->checkable = (!$checkable ? FALSE : TRUE);
		$this->checkList = (!$checkable ? NULL : array());

		return $this;
	}
	
	/**
	 * Sets the ratios of the columns widths.
	 * 
	 * @param	array	$ratios
	 * 		The columns width ratios. Must contain numeric values between 0 and 1 (excluding) or else the requested ratios will be ignored.
	 * 
	 * @return	void
	 */
	public function setColumnRatios($ratios)
	{
		$r = array_filter($ratios, function($val)
		{
			return $val > 0;
		});
		
		if (count($r) != count($ratios))
			return;
		
		$r = array_map( function($val)
		{
			return intval($val * 100);
		}, $r);	
		
		$total = array_sum($r);
		if ($total != 100)
			return;
			
		$totalRatio = (!$this->checkable ? 1.0 : 0.92);
		
		$r = array_map( function($val) use ($totalRatio)
		{
			return $val * $totalRatio; 
		}, $r);
		
		DOM::data($this->gridList, "column-ratios", $r);
		$this->columnRatios = $r;
	}
	
	/**
	 * Creates headers in the dataGridList
	 * 
	 * @param	array	$headers
	 * 		An array with the header contents (can be text or DOMElement)
	 * 
	 * @return	void
	 */
	public function setHeaders($headers)
	{
		// Set horizontal size of element
		$this->hSize = count($headers);
		$gridListWrapper = $this->get();
		DOM::attr($gridListWrapper, "data-grid-size", $this->hSize);
		
		// Add headers
		$headers_row = $this->insertSimpleRow($headers, "dataGridHeader");
		// Add checkable headers
		if ($this->checkable)
			$this->insertCheckRow($headers_row, NULL, FALSE);
		
		// Create new grid content wrapper
		$gridListContentWrapper = DOM::create("div", "", "", "dataGridContentWrapper");
		DOM::append($this->gridList, $gridListContentWrapper);
		$this->gridList = $gridListContentWrapper;
	}
	
	/**
	 * Creates a grid row with the specified contents.
	 * If no arguments are passed, the returned value is FALSE.
	 * If more than three arguments are passed, those are ignored.
	 * 
	 * @param	array	$contents
	 * 		Array with text or DOMElements
	 * 
	 * @param	string	$checkName
	 * 		The name of the row's checkbox (This is ignored if list is not checkable).
	 * 
	 * @param	boolean	$checked
	 * 		If list is checkable initializes the row's checkbox in $checked state
	 * 
	 * @param	string	$checkValue
	 * 		The value of the row's checkbox (This is ignored if list is not checkable).
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function insertRow($contents = array(), $checkName = NULL, $checked = FALSE, $checkValue = "")
	{
		// Insert row contents
		$grid_row = $this->insertSimpleRow($contents, "");
		
		// Insert row checks
		if ($this->checkable)
			$this->insertCheckRow($grid_row, $checkName, $checked, $checkValue);
	}
	
	/**
	 * Assistant function in inserting a row into the dataGridList
	 * 
	 * @param	array	$contents
	 * 		Array with text or DOMElements
	 * 
	 * @param	string	$class
	 * 		Extra classes for styling specific rows (used for header)
	 * 
	 * @return	DOMElement
	 * 		The row inserted.
	 */
	private function insertSimpleRow($contents, $class = "")
	{
		if ($this->hSize == 0)
			return FALSE;
		
		$row_class = ($class == "" ? "dataGridRow" : $class);
		$grid_row = DOM::create("li", "", "", $row_class);

		// trim/pad $contents to $this->hSize number
		if ($this->hSize < count($contents))
			array_slice($contents, $this->hSize);
		for ($i = 0; $i < $this->hSize - count($contents); $i++)
			$contents[] = DOM::create("span");
		
		foreach ($contents as $key => $c)
		{
			$itemIdentifier = "";
			if (gettype($c) == "string") {
				$grid_text = DOM::create("span", $c, "", "dataGridTextWrapper");
				$itemIdentifier = strtolower(str_replace(" ", "", $c));
			}
			else if ($c->tagName == "span")
			{
				$grid_text = DOM::create("span", "", "", "dataGridTextWrapper");
				DOM::append($grid_text, $c);
				$itemIdentifier = strtolower(str_replace(" ", "", DOM::nodeValue($c)));
			}
			else
				$grid_text = $c;
			
			DOM::appendAttr($grid_text, "style", "max-width:100%;width:100%;box-sizing:border-box;");
			$grid_item = DOM::create("div", "", "", "dataGridCell");
			if ($class == "dataGridHeader" && (!empty($itemIdentifier)))
			{
				DOM::attr($grid_item, "data-column-name", $itemIdentifier);
				$sortintIcon = DOM::create("div", "", "", "sortingIcon");
				DOM::append($grid_text, $sortintIcon);
			}
			
			$w = 0;
			if (empty($this->columnRatios)) {
				$ratio = (!$this->checkable ? 100 : 100 - 8);
				$w = $ratio/$this->hSize;
			} else {
				$w = $this->columnRatios[$key];
			}
			DOM::attr($grid_item, "style", "width:".$w."%;");
			DOM::append($grid_item, $grid_text);
			
			DOM::append($grid_row, $grid_item);
		}
		
		DOM::append($this->gridList, $grid_row);
		
		return $grid_row;
	}
	
	/**
	 * Assistant function in prepending a checkbox in a row.
	 * 
	 * @param	DOMElement	$row
	 * 		Row to insert checkbox
	 * 
	 * @param	string	$checkName
	 * 		Name of the checkbox
	 * 
	 * @param	boolean	$checked
	 * 		Initial state of the checkbox
	 * 
	 * @param	string	$checkValue
	 * 		The value of the row's checkbox (This is ignored if list is not checkable).
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	private function insertCheckRow($row, $checkName, $checked, $checkValue = "")
	{
		// Create the ckeck item
		$check_item = DOM::create("div", "", "", "dataGridCheck");
		
		// Get the checkbox
		$form = new Form();
		$chk = $form->getInput($type = "checkbox", $name = $checkName, $value = $checkValue);
		if ($checked)
			DOM::attr($chk, "checked", "checked");
		DOM::append($check_item, $chk);
		
		// Add to checklist
		$this->checkList[] = $chk;
		DOM::prepend($row, $check_item);
	}
}
//#section_end#
?>