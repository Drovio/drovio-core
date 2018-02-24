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

importer::import("API", "Platform", "DOM::DOM");
importer::import("UI", "Navigation", "navigator");

use \API\Platform\DOM\DOM;
use \UI\Navigation\navigator;

/**
 * {title}
 * 
 * Usage
 * 
 * @version	{empty}
 * @created	June 12, 2013, 13:35 (EEST)
 * @revised	June 12, 2013, 13:35 (EEST)
 * 
 * @deprecated	Use \UI\Presentation\togglers\accordion instead.
 */
class accordion
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
	protected $slices;
	
	/**
	 * Initialize
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function __construct($builder, $id = "")
	{
		$this->id = ($id == "" ? rand() : $id);
		
		$this->slices = array();
	}
	
	/**
	 * Creates the accordion and returns the DOMElement
	 * 
	 * @return	void
	 */
	public function get_container()
	{
		$accordion = DOM::create("div", "", "accordion_".$this->id, "accordion");
		$accSlices = DOM::create("ul", "", "", "slices");
		DOM::append($accordion, $accSlices);
		
		foreach ($this->slices as $slice)
			DOM::append($accSlices, $slice);
			
		return $accordion;
	}
	
	/**
	 * Adds a slice to the body of the accordion
	 * 
	 * @param	{type}	$ref
	 * 		{description}
	 * 
	 * @param	{type}	$header
	 * 		{description}
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function add_slice($ref, $header, $content)
	{
		$slice = $this->_get_slice($ref, $header, $content);
		$this->slices[] = $slice;
	}
	
	/**
	 * Creates a slice
	 * 
	 * @param	{type}	$ref
	 * 		{description}
	 * 
	 * @param	{type}	$header
	 * 		{description}
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @return	void
	 */
	protected function _get_slice($ref, $header, $content)
	{
		$slice = DOM::create("li", "", "", "slice");
		
		$sliceHeader = $this->_get_sliceHeader($ref, $header);
		DOM::append($slice, $sliceHeader);
		
		$sliceContent = $this->_get_sliceContent($ref, $content);
		DOM::append($slice, $sliceContent);
		
		return $slice;
	}
	
	/**
	 * Creates the header of the slice
	 * 
	 * @param	{type}	$ref
	 * 		{description}
	 * 
	 * @param	{type}	$header
	 * 		{description}
	 * 
	 * @return	void
	 */
	protected function _get_sliceHeader($ref, $header)
	{
		$sliceHeader = DOM::create("div", "", "", "sliceHeader");
		navigator::staticNav($sliceHeader, "slice_".$ref, "accordion_".$this->id, "accordion_".$this->id, "accNav_".$this->id, $nav_display = "none");
		DOM::append($sliceHeader, $header);
		
		return $sliceHeader;
	}
	
	/**
	 * Creates the content of the slice
	 * 
	 * @param	{type}	$ref
	 * 		{description}
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @return	void
	 */
	protected function _get_sliceContent($ref, $content)
	{
		$sliceContent = DOM::create("div", "", "slice_".$ref, "sliceContent");
		navigator::selector($sliceContent, "accordion_".$this->id);
		DOM::append($sliceContent, $content);
		
		return $sliceContent;
	}
}
//#section_end#
?>