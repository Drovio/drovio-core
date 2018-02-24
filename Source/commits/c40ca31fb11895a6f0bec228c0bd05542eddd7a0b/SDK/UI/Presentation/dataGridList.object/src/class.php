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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Forms", "Form");
importer::import("UI", "Prototype", "UIObjectPrototype");

use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Forms\Form;
use \UI\Prototype\UIObjectPrototype;

/**
 * Data Grid List
 * 
 * The Data Grid List can be used to present multiple data in the form of a grid.
 * 
 * If you would like to use the javascript interface for adding, removing and selecting rows, identify each column (including ratios, headers and every row) with a key identifier.
 * However, the sequence of elements must be the same.
 * 
 * @version	1.2-5
 * @created	April 25, 2013, 11:32 (EEST)
 * @updated	October 15, 2015, 18:33 (EEST)
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
	private $hSize = 0;
	
	/**
	 * If set to TRUE a checkbox will be prepended in each row
	 * 
	 * @type	boolean
	 */
	private $checkable = FALSE;
	
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
	public function __construct() {}
	
	/**
	 * Creates the dataGridList container
	 * 
	 * @param	string	$id
	 * 		The id of the data grid list element.
	 * 
	 * @param	boolean	$checkable
	 * 		If set to TRUE, the dataGridList will have a checkbox at the start of each row.
	 * 		It is FALSE by default.
	 * 
	 * @param	boolean	$withBorder
	 * 		Defines whether the gridList will have visual border.
	 * 		It is TRUE by default.
	 * 
	 * @return	dataGridList
	 * 		The dataGridList object.
	 */
	public function build($id = "", $checkable = FALSE, $withBorder = TRUE)
	{
		// Set element id
		$this->id = (empty($id) ? "dgl_".mt_rand() : $id);
		
		// Set element class
		$gridClass = "uiDataGridList initialize";
		$gridClass .= ($checkable ? " checkable" : "");
		$gridClass .= ($withBorder ? " dgl_wb" : "");
		
		// Initialize grid list element holder
		$gridListWrapper = DOM::create("div", "", $this->id, $gridClass);
		$this->set($gridListWrapper);
		
		// Create grid list
		$this->gridList = DOM::create("ul", "", "", "gridList");
		DOM::append($gridListWrapper, $this->gridList);
		
		// Set object variables
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
		// Filter all values > 0
		$r = array_filter($ratios, function($val) { return $val > 0; });
		
		// Check if all values are valid
		if (count($r) != count($ratios))
			return;
		
		// Map all values to percent
		$r = array_map(function($val)
			{
				return intval($val * 100);
			}, $r);
			
		// Check if sums up to 100
		$total = array_sum($r);
		if ($total != 100)
			return;
		
		// Correct ratio for checkable grid lists
		$totalRatio = (!$this->checkable ? 1.0 : 0.92);
		$r = array_map(function($val) use ($totalRatio)
			{
				return $val * $totalRatio; 
			}, $r);
		
		// Set column ratios data to element
		$this->columnRatios = $r;
		DOM::data($this->gridList, "column-ratios", $this->columnRatios);
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
		$gridHeader = $this->insertSimpleRow($headers, "dataGridHeader", $header = TRUE);
		
		// Add checkable headers
		if ($this->checkable)
			$this->insertCheckRow($gridHeader, NULL, FALSE);
		
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
		$gridRow = $this->insertSimpleRow($contents, "");
		
		// Insert row checks
		if ($this->checkable)
			$this->insertCheckRow($gridRow, $checkName, $checked, $checkValue);
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
	 * @param	boolean	$header
	 * 		Whether this row is the header of the list.
	 * 
	 * @return	DOMElement
	 * 		The row inserted.
	 */
	private function insertSimpleRow($contents, $class = "", $header = FALSE)
	{
		// Check width size
		if ($this->hSize == 0)
			return FALSE;
		
		// Create grid row object
		$gridRow = DOM::create("li", "", "", ($class == "" ? "dataGridRow" : $class));

		// Trim contents to the column size
		if ($this->hSize < count($contents))
			array_slice($contents, $this->hSize);
		
		// Add extra columns
		$contentsCount = count($contents);
		for ($i = 0; $i < $this->hSize - $contentsCount; $i++)
			$contents[] = DOM::create("span");
		
		// Insert contents to row
		foreach ($contents as $key => $contentValue)
		{
			// Create grid text wrapper
			$gridTextWrapper = DOM::create("span", $contentValue, "", "dataGridTextWrapper");
			
			// Create item identifier
			$itemIdentifier = "";
			if (gettype($key) == "string")
				$itemIdentifier = strtolower(str_replace(" ", "", $key));
			else if (gettype($contentValue) == "string" || gettype($contentValue) == "integer")
				$itemIdentifier = strtolower(str_replace(" ", "", $contentValue));
			else if ($contentValue->tagName == "span")
				$itemIdentifier = strtolower(str_replace(" ", "", DOM::nodeValue($contentValue)));
			
			// Set item style
			DOM::appendAttr($gridTextWrapper, "style", "max-width:100%;width:100%;box-sizing:border-box;");
			
			// Create grid cell with given text
			$gridCell = DOM::create("div", $gridTextWrapper, "", "dataGridCell");
			DOM::append($gridRow, $gridCell);
			
			// Set header (if any)
			if ($header && !empty($itemIdentifier))
			{
				// Set identifier
				DOM::attr($gridCell, "data-column-name", $itemIdentifier);
				
				// Add sorting icon
				$sortingIcon = DOM::create("div", "", "", "sortingIcon");
				DOM::append($gridTextWrapper, $sortingIcon);
			}
			
			// Set grid item width
			$w = 0;
			if (empty($this->columnRatios))
			{
				$ratio = (!$this->checkable ? 100 : 100 - 8);
				$w = $ratio / $this->hSize;
			}
			else
				$w = $this->columnRatios[$key];
			DOM::attr($gridCell, "style", "width:".$w."%;");
		}
		
		// Append grid row to grid list and return the DOMElement
		DOM::append($this->gridList, $gridRow);
		return $gridRow;
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
		$gridCheck = DOM::create("div", "", "", "dataGridCheck");
		
		// Get the checkbox
		$form = new Form();
		$chk = $form->getInput($type = "checkbox", $name = $checkName, $value = $checkValue);
		if ($checked)
			DOM::attr($chk, "checked", "checked");
		DOM::append($gridCheck, $chk);
		
		// Add to checklist
		$this->checkList[] = $chk;
		DOM::prepend($row, $gridCheck);
	}
}
//#section_end#
?>