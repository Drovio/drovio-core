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
 * Grid Splitter
 * 
 * Creates a two-column area with resizable sidebar.
 * 
 * @version	{empty}
 * @created	April 22, 2013, 15:24 (EEST)
 * @revised	May 9, 2013, 13:40 (EEST)
 */
class gridSplitter extends UIObjectPrototype
{	
	/**
	 * Horizontal splitter with left sidebar.
	 * 
	 * @type	string
	 */
	const SIDE_LEFT = "SIDE_LEFT";
	
	/**
	 * Horizontal splitter with right sidebar.
	 * 
	 * @type	string
	 */
	const SIDE_RIGHT = "SIDE_RIGHT";
	
	/**
	 * Vertical splitter with top sidebar.
	 * 
	 * @type	string
	 */
	const SIDE_TOP = "SIDE_TOP";
	
	/**
	 * Vertical splitter with bottom sidebar.
	 * 
	 * @type	string
	 */
	const SIDE_BOTTOM = "SIDE_BOTTOM";
	
	/**
	 * The mainContent container.
	 * 
	 * @type	DOMElement
	 */
	protected $mainContent;
	/**
	 * The sidebar container.
	 * 
	 * @type	DOMElement
	 */
	protected $sideContent;
	
	
	/**
	 * Builds the entire element.
	 * 
	 * @param	string	$orientation
	 * 		Defines the orientation of the splitter.
	 * 		"horizontal" or "vertical".
	 * 
	 * @param	string	$layout
	 * 		Defines the position of the sidebar according to the layout. The possible values are the gridSplitter's contants.
	 * 
	 * @param	boolean	$closed
	 * 		Defines whether the sidebar will be closed on startup.
	 * 
	 * @param	string	$sideTitle
	 * 		Title of the Expander. Used as tooltip to identify what's in the collapsed side area of the gridSplitter.
	 * 
	 * @return	gridSplitter
	 */
	public function build($orientation = "horizontal", $layout = self::SIDE_RIGHT, $closed = FALSE, $sideTitle = "")
	{
		// Mechanism wrapper
		$containerClass = "sliderContainer";
		$selectedOrientation = ($orientation == "horizontal" ? "horizontal" : "vertical" );
		$selectedLayout = (($layout == self::SIDE_RIGHT || $layout == self::SIDE_BOTTOM) ? "" : "invertedLayout" );
		$container = DOM::create("div", "", "", $containerClass." ".$selectedOrientation." ".$selectedLayout);
		if ($closed)
			DOM::attr($container, "data-slider-close", "true");
			
		$this->set($container);
		
		// Main panel
		$content = DOM::create("div", "", "", "sliderMain");
		
		// Main container panel
		$this->mainContent = DOM::create("div", "", "", "sliderMainContent");
		DOM::append($content, $this->mainContent);
		
		// Expander
		$expander = DOM::create("div", "", "", "sideExpander noDisplay");
		if (!empty($sideTitle) && is_string($sideTitle))
			DOM::attr($expander, "data-side-title", $sideTitle);
		DOM::append($content, $expander);
		
		// Side panel
		$side = DOM::create("div", "", "", "sliderSide");
		
		// Side container panel
		$this->sideContent = DOM::create("div", "", "", "sliderSideContent");
		DOM::append($side, $this->sideContent);
		
		// Slider
		$slider = DOM::create("div", "", "", ($orientation != "horizontal" ? "hSlider" : "vSlider"));
		DOM::append($side, $slider);
		
		if ($layout == self::SIDE_LEFT || $layout == self::SIDE_TOP)
		{
			DOM::append($container, $side);
			DOM::append($container, $content);
			return $this;
		}
		
		DOM::append($container, $content);
		DOM::append($container, $side);
		
		return $this;
	}
	
	/**
	 * Appends a given DOMElement to the mainContent container.
	 * 
	 * @param	DOMElement	$elem
	 * 		The element to be appended.
	 * 
	 * @return	gridSplitter
	 */
	public function appendToMain($elem)
	{
		DOM::append($this->mainContent, $elem);
		return $this;
	}
	
	/**
	 * Appends a given DOMElement to the sidebar container.
	 * 
	 * @param	DOMElement	$elem
	 * 		The element to be appended.
	 * 
	 * @return	gridSplitter
	 */
	public function appendToSide($elem)
	{
		DOM::append($this->sideContent, $elem);
		return $this;
	}
}
//#section_end#
?>