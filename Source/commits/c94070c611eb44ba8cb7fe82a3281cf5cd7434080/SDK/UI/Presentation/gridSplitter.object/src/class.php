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
importer::import("UI", "Prototype", "UIObjectPrototype");

use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Prototype\UIObjectPrototype;

/**
 * Grid Splitter
 * 
 * Creates a two-column area with resizable sidebar.
 * 
 * @version	0.1-6
 * @created	April 22, 2013, 15:24 (EEST)
 * @updated	October 15, 2015, 15:40 (EEST)
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
	 * Horizontal splitter orientation
	 * 
	 * @type	string
	 */
	const ORIENT_HOZ = "horizontal";
	
	/**
	 * Vertical splitter orientation
	 * 
	 * @type	string
	 */
	const ORIENT_VER = "vertical";
	
	/**
	 * The mainContent content container.
	 * 
	 * @type	DOMElement
	 */
	protected $mainContent;
	/**
	 * The sidebar content container.
	 * 
	 * @type	DOMElement
	 */
	protected $sideContent;
	
	/**
	 * The dimension of the "side space" of the grid splitter
	 * 
	 * @type	string
	 */
	private $sideDimension = '';
	
	/**
	 * Grid splitter orientantion
	 * 
	 * @type	string
	 */
	private $orientation;
	
	/**
	 * The "side space" container.
	 * 
	 * @type	DOMElement
	 */
	private $side;
	
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
	 * 		The gridSplitter object.
	 */
	public function build($orientation = self::ORIENT_HOZ, $layout = self::SIDE_RIGHT, $closed = FALSE, $sideTitle = "")
	{
		// Mechanism wrapper
		$containerClass = "sliderContainer";
		$this->orientation = ($orientation == self::ORIENT_HOZ ? "horizontal" : "vertical");
		$selectedLayout = ($layout == self::SIDE_RIGHT || $layout == self::SIDE_BOTTOM ? "" : "invertedLayout");
		$container = DOM::create("div", "", "", $containerClass." ".$this->orientation." ".$selectedLayout);
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
		
		if ($this->orientation == self::ORIENT_HOZ)
			$expPos = ($layout == self::SIDE_RIGHT ? 1 : 2);
		else
			$expPos = ($layout == self::SIDE_TOP ? 3 : 1);
		DOM::attr($expander, "data-exp-pos", $expPos);
		
		// Side panel
		$side = DOM::create("div", "", "", "sliderSide");
		$this->side = $side;
		$this->setSideDimension($this->sideDimension);
		
		
		// Side container panel
		$this->sideContent = DOM::create("div", "", "", "sliderSideContent");
		DOM::append($side, $this->sideContent);
		
		// Slider
		$slider = DOM::create("div", "", "", ($this->orientation != self::ORIENT_HOZ ? "hSlider" : "vSlider"));
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
	 * Set the dimension of the "side space" according to the input
	 * 
	 * @param	string	$dimension
	 * 		The dimension of the "side space" in css like format (eg 54px or 20%)
	 * 
	 * @return	void
	 */
	public function setSideDimension($dimension = "")
	{
		$this->sideDimension = $dimension;
		if (!empty($dimension))
		{
			$obj = $this->side;
			if (!empty($obj))
			{
				$propName = ($this->orientation == self::ORIENT_HOZ ? "width" : "height" );
				DOM::appendAttr($obj, "style", $propName.":".$dimension);
			}
		}
	}
	
	/**
	 * Appends a given DOMElement to the mainContent container.
	 * 
	 * @param	DOMElement	$elem
	 * 		The element to be appended.
	 * 
	 * @return	gridSplitter
	 * 		The gridSplitter object.
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
	 * 		The gridSplitter object.
	 */
	public function appendToSide($elem)
	{		
		DOM::append($this->sideContent, $elem);
		return $this;
	}
}
//#section_end#
?>