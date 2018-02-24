<?php
//#section#[header]
// Namespace
namespace UI\Layout;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Layout
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Presentation", "gridSplitter");
importer::import("API", "Platform", "DOM::DOM");

use \UI\Presentation\gridSplitter;
use \API\Platform\DOM\DOM;


/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	June 1, 2013, 14:02 (EEST)
 * @revised	June 28, 2013, 10:40 (EEST)
 * @throws	sgsgs
 * 
 * @deprecated	Use \UI\Presentation\gridSplitter instead.
 */
class slider extends gridSplitter
{
	/**
	 * dikudkfgdlfdgfd " < gg
	 * 
	 * @type	String
	 */
	private static $vSliderClass = "vSlider";
	/**
	 * d <>
	 * 
	 * @type	{empty}
	 */
	private static $hSliderClass = "hSlider";
	/**
	 * desc1 ' test
	 * 
	 * @type	String
	 */
	const MAIN_FIRST = "MAIN_FIRST";
	/**
	 * desc2 uhk  hjkb jh bg jkgh ih ik h kijh h jk  jk jk ghj gb jhg hjg jh ghjk g jk
	 * 
	 * @type	String
	 */
	const SIDE_FIRST = "SIDE_FIRST";
	/**
	 * skgsjfksdf
	 * 
	 * @type	String
	 */
	protected $sideFirstClass = "invertedLayout";
	/**
	 * dklfgndkljfgndkfg
	 * 
	 * @type	String
	 */
	protected $containerClass = "sliderContainer";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $containerVerticalLayoutClass = "vertical";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $containerHorizontalLayoutClass = "horizontal";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $mainClass = "sliderMain";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $mainContentClass = "sliderMainContent";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $sideExpanderClass = "sideExpander";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $sideClass = "sliderSide";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $sideContentClass = "sliderSideContent";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $container = NULL;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $mainContent = NULL;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $sideContent = NULL;

	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $orientation;
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $layout;

	/**
	 * Constructor Method
	 * 
	 * @param	String	$orientation
	 * 		test
	 * 
	 * @param	String	$layout
	 * 		eik
	 * 
	 * @return	void
	 * 
	 * @throws	dsfs, fsdfd
	 * 
	 * @deprecated	Use \UI\Presentation\gridSplitter construct method instead.
	 */
	public function __construct($orientation = "horizontal", $layout = self::MAIN_FIRST)
	{
		$this->orientation = $orientation;
		$this->layout = ($layout == self::MAIN_FIRST ? parent::SIDE_RIGHT : parent::SIDE_LEFT );
	}
	 
	/**
	 * Get slider mechanism (container | elements | slider)
	 * 
	 * @param	{type}	$closed
	 * 		{description}
	 * 
	 * @return	DOMElement
	 * 		{description}
	 * 
	 * @deprecated	Use \UI\Presentation\gridSplitter::build() and \UI\Presentation\gridSplitter::get() instead.
	 */
	public function get_container($closed = FALSE)
	{
		return parent::build($this->orientation, $this->layout, $closed)->get();
	}
	
	/**
	 * Append to main content
	 * 
	 * @param	{type}	$elem
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use \UI\Presentation\gridSplitter::appendToMain() instead.
	 */
	public function append_to_main($elem)
	{
		parent::appendToMain($elem);
	}
	
	/**
	 * Append to side content
	 * 
	 * @param	{type}	$elem
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use \UI\Presentation\gridSplitter::appendToSide() instead.
	 */
	public function append_to_side($elem)
	{
		parent::appendToSide($elem);
	}
}
//#section_end#
?>