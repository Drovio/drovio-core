<?php
//#section#[header]
// Namespace
namespace UI\Developer\editors;

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
 * @package	Developer
 * @namespace	\editors
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("UI", "Developer", "editors/HTML5Editor");
importer::import("UI", "Developer", "editors/CSS3Editor");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "gridSplitter");
importer::import("UI", "Prototype", "UIObjectPrototype");
importer::import("DEV", "Resources", "paths");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \UI\Developer\editors\HTML5Editor;
use \UI\Developer\editors\CSS3Editor;
use \UI\Html\DOM;
use \UI\Presentation\gridSplitter;
use \UI\Prototype\UIObjectPrototype;
use \DEV\Resources\paths;

/**
 * Web View Editor
 * 
 * It creates a complete editor for web views as Redback sees it.
 * It includes an HTMLEditor for the web view content and a CSSEditor for the content's looks.
 * 
 * @version	2.0-2
 * @created	May 2, 2015, 10:48 (EEST)
 * @updated	October 15, 2015, 14:22 (EEST)
 */
class WViewEditor extends UIObjectPrototype
{
	/**
	 * Name of the css editing area.
	 * 
	 * @type	string
	 */
	private $cssName;
	
	/**
	 * Name of the html editing area
	 * 
	 * @type	string
	 */
	private $htmlName;
	
	/**
	 * Constructs and initializes a cssEditor.
	 * 
	 * @param	string	$cssName
	 * 		Name of the css editing area.
	 * 
	 * @param	string	$htmlName
	 * 		Name of the html editing area.
	 * 
	 * @return	void
	 */
	public function __construct($cssName = "cssEditor", $htmlName = "htmlEditor")
	{
		// Initialize object properties
		$this->cssName = $cssName;
		$this->htmlName = $htmlName;
	}
	
	/**
	 * Acquire CSS properties info as an XML document string.
	 * 
	 * @return	string
	 * 		The xml document string.
	 */
	public static function getCssProperties()
	{
		$cssParser = new DOMParser();
		$cssParser->load(paths::getSDKRsrcPath()."/cssEditor/properties.xml");
		return $cssParser->getXML();
	}
	
	/**
	 * Get the given's user agent default css.
	 * 
	 * @param	string	$browser
	 * 		Name of the browser. Can be "w3c", "webkit", "firefox", "iexplorer", "opera".
	 * 
	 * @return	string
	 * 		The entire css collection of the default css rules.
	 */
	public static function getUserAgentCss($browser)
	{
		return fileManager::get(systemRoot.paths::getSDKRsrcPath()."/cssEditor/".$browser.".css");
	}
	
	/**
	 * Builds the CSSEditor object.
	 * 
	 * @param	string	$html
	 * 		The HTML code to load on initalize.
	 * 
	 * @param	string	$css
	 * 		The css code to load on initialize.
	 * 
	 * @param	boolean	$sideClosed
	 * 		Set the css code side as closed.
	 * 
	 * @return	WViewEditor
	 * 		The WViewEditor object.
	 */
	public function build($html = "", $css = "", $sideClosed = FALSE)
	{
		// Create cssEditor holder
		$id = "wve_".mt_rand();
		$holder = DOM::create("div", "", $id, "wViewEditor");
		$this->set($holder);
		
		// Create Main Container (horizontal slider)
		$outerSlider = new gridSplitter();
		$outerSliderContainer = $outerSlider->build($orientation = gridSplitter::ORIENT_HOZ, $layout = gridSplitter::SIDE_RIGHT, $closed = $sideClosed, $sideTitle = "CSS Stylesheet")->get();
		$this->append($outerSliderContainer);
		
		// Create HTML5Editor
		$htmle = new HTML5Editor($this->htmlName);
		$htmlEditor = $htmle->build($html)->get();
		$outerSlider->appendToMain($htmlEditor);

		// Create CSS3Editor
		$csse = new CSS3Editor($this->cssName);
		$cssEditor = $csse->build($css)->get();
		$outerSlider->appendToSide($cssEditor);

		// Return cssEditor object
		return $this;
	}
}
//#section_end#
?>