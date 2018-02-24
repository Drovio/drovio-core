<?php
//#section#[header]
// Namespace
namespace UI\Presentation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Presentation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * Grid View
 * 
 * Builds a table-like, box-like grid for visual presentation of elements.
 * 
 * @version	{empty}
 * @created	May 2, 2013, 14:03 (EEST)
 * @revised	May 2, 2013, 14:51 (EEST)
 */
class gridView extends UIObjectPrototype
{
	/**
	 * The object's id.
	 * 
	 * @type	string
	 */
	private $id;
	/**
	 * The number of columns in the grid.
	 * 
	 * @type	integer
	 */
	protected $columns = 0;
	/**
	 * The number of rows in the grid.
	 * 
	 * @type	integer
	 */
	protected $rows = 0;
	/**
	 * The gridList element.
	 * 
	 * @type	DOMElement
	 */
	protected $gridList;
	/**
	 * Two-dimension array of cells for appending content at any time.
	 * 
	 * @type	array
	 */
	protected $cells;
	
	/**
	 * Constructor method.
	 * Initializes this object.
	 * 
	 * @param	string	$id
	 * 		The object's id. If none given, random is generated.
	 * 
	 * @return	void
	 */
	public function __construct($id = "")
	{
		// Create random id if not given
		$this->id = ($id == "" ? "gridView_".rand() : $id);
	}
	
	/**
	 * Builds the body of the entire gridView.
	 * 
	 * @param	integer	$rows
	 * 		The number of rows in the grid.
	 * 
	 * @param	integer	$columns
	 * 		The number of columns in the grid.
	 * 
	 * @return	gridView
	 */
	public function build($rows = 1, $columns = 1)
	{
		// Build outer container
		$holder = DOM::create("div", "", $this->id, "gridView");
		$this->set($holder);
		
		// Create gridList
		$this->gridList = DOM::create("ul", "", "", "gridViewList");
		DOM::append($holder, $this->gridList);
		
		$this->createStructure($columns, $rows);
		
		return $this;
	}
	
	/**
	 * Creates the structure of the cells.
	 * 
	 * @param	integer	$columns
	 * 		The number of columns.
	 * 
	 * @param	string	$rows
	 * 		The number of rows.
	 * 
	 * @return	void
	 */
	protected function createStructure($columns = 1, $rows = 1)
	{
		// Set Dimensions
		$this->columns = $columns;
		$this->rows = $rows;
		
		// Set Object Style Attributes
		$dim = array();
		$dim['w'] = $columns;
		$dim['h'] = $rows;
		DOM::data($this->get(), "dim", $dim);
		
		// Create Structure
		$this->cells = array();
		for ($i=0; $i<$this->rows; $i++)
		{
			// Create row
			$gridRow = DOM::create("li", "", "", "outerCellView");
			DOM::append($this->gridList, $gridRow);
			
			// Create Cell list
			$gridCellList = DOM::create("ul", "", "", "innerCellView");
			DOM::append($gridRow, $gridCellList);
			
			// Create cells
			for ($j=0; $j<$this->columns; $j++)
			{
				$gridCell = DOM::create("li", "", "", "gridCell");
				DOM::append($gridCellList, $gridCell);
				
				// Create cell content receptor
				$gridCellContent = DOM::create("div", "", "", "cell");
				DOM::append($gridCell, $gridCellContent);
				
				$this->cells[$i][$j] = $gridCellContent;
			}
		}
	}
	
	/**
	 * Appends content to a grid cell.
	 * 
	 * @param	integer	$row
	 * 		The row of the cell.
	 * 
	 * @param	integer	$column
	 * 		The column of the cell.
	 * 
	 * @param	DOMElement	$content
	 * 		The content of the cell.
	 * 
	 * @return	gridView
	 */
	public function append($row, $column, $content)
	{
		// Check dimensions
		if ($row < 0 || $row >= $this->rows)
			return FALSE;
		if ($column < 0 || $column >= $this->columns)
			return FALSE;
		
		// Append content
		DOM::append($this->cells[$row][$column], $content);
		
		return $this;
	}
	
	/**
	 * Creates the dataGridList container
	 * 
	 * @param	{type}	$width
	 * 		{description}
	 * 
	 * @param	{type}	$height
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use build()->get() instead.
	 */
	public function create($width, $height)
	{
		return $this->build($height, $width)->get();
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @param	{type}	$row
	 * 		{description}
	 * 
	 * @param	{type}	$column
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use append() instead.
	 */
	public function set_content($content, $row, $column)
	{
		$this->append($row, $column, $content);
	}
}
//#section_end#
?>