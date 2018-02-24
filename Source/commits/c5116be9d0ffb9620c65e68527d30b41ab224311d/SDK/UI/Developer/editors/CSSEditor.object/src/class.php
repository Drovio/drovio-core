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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("UI", "Developer", "codeEditor");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Forms", "formFactory");
importer::import("UI", "Presentation", "gridSplitter");
importer::import("UI", "Presentation", "tabControl");
importer::import("UI", "Navigation", "navigationBar");
importer::import("DEV", "Resources", "paths");

use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \UI\Developer\codeEditor;
use \UI\Html\DOM;
use \UI\Forms\formFactory;
use \UI\Presentation\gridSplitter;
use \UI\Presentation\tabControl;
use \UI\Navigation\navigationBar;
use \DEV\Resources\paths;

/**
 * CSS Editor control
 * 
 * This is a simple html editor with a preview and a css code part.
 * 
 * @version	0.1-2
 * @created	September 8, 2014, 15:59 (EEST)
 * @updated	January 22, 2015, 11:37 (EET)
 */
class CSSEditor extends UIObjectPrototype
{
	/**
	 * Name of the css editing area
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
	 * Structure of the html model on which the css will be projected
	 * 
	 * @type	string
	 */
	private $structure;
	
	/**
	 * CSS code to load when initializing
	 * 
	 * @type	string
	 */
	private $cssCode;
	
	
	/**
	 * The formFactory object for building all the form items.
	 * 
	 * @type	formFactory
	 */
	private $formFactory;
	
	/**
	 * Constructor Method.
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
		// Put your constructor method code here.
		$this->cssName = $cssName;
		$this->htmlName = $htmlName;
		$this->formFactory = new formFactory();
	}
	
	/**
	 * Sets the structure of the model of HTML to work with.
	 * 
	 * @param	string	$structure
	 * 		Structure of the html model on which the css will be projected.
	 * 
	 * @param	string	$htmlName
	 * 		The html code name for posting data.
	 * 
	 * @return	void
	 */
	public function setStructure($structure, $htmlName = "")
	{
		if ((!$this->hasModel()) && empty($htmlName))
			return FALSE;
			
		if (!empty($htmlName))
			$this->htmlName = $htmlName;
			
		$this->structure = $structure;
		return TRUE;
	}
	
	/**
	 * Set the css code to work with.
	 * 
	 * @param	string	$cssCode
	 * 		CSS code to load on initialize.
	 * 
	 * @return	void
	 */
	public function setCss($cssCode)
	{
		$this->cssCode = $cssCode;
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
		// Path to browser's default properties file
		$cssPath = systemRoot.paths::getSDKRsrcPath()."/cssEditor/".$browser.".css";
		
		// Get Content
		$content = fileManager::get($cssPath);
		
		return $content;
	}
	
	/**
	 * Builds the CSSEditor object.
	 * 
	 * @param	string	$structure
	 * 		Structure of the html model on which the css will be projected.
	 * 
	 * @param	string	$css
	 * 		CSS code to load on initialize.
	 * 
	 * @return	CSSEditor
	 * 		The CSSEditor object.
	 */
	public function build($structure = "", $css = "")
	{
		$this->setStructure($structure);
		$this->setCss($css);
		
		// Create outer Container
		$container = DOM::create("div", "", "", "cssEditor");
		$this->set($container);
		
		// Load css XML file into an Arrays
		list( $measurement, $browsers, $tabs, $tiles ) = $this->getCssInfo();
		
		if (!$this->hasModel())
		{
			// Get css area without model
			$cssContainer = $this->getCssCoder($measurement, $tabs, $tiles);
			
			DOM::append($container, $cssContainer);
			return $this;
		}
		
		// Get css area
		$cssContainer = $this->getCssContainer($measurement, $tabs, $tiles);
		
		// Get structure area
		$structureView = $this->getStructureContainer();
		
		// Get preview components
		list( $previewContainer, $htmlCodeContainer, $previewBar ) = $this->getPreviewContainer($browsers);
		// Create Main Container (horizontal slider)
		$outerSlider = new gridSplitter();
		$outerSliderContainer = $outerSlider->build($orientation = "horizontal")->get();
		DOM::append($container, $outerSliderContainer);
		
		// Build vertical slider
		$sideSlider = new gridSplitter();
		$sideSliderContainer = $sideSlider->build($orientation = "vertical")->get();
	
		// Append wrappers to sliders		
		// ______ First goes the preview of the applied css
		$outerSlider->appendToMain($previewContainer);
		$outerSlider->appendToMain($htmlCodeContainer);
		$outerSlider->appendToMain($previewBar);
		$outerSlider->appendToSide($sideSliderContainer);
		// ______ Second goes the css form/code container
		$sideSlider->appendToMain($cssContainer);
		// ______ Last is the model wrapper
		$sideSlider->appendToSide($structureView);
		
		// Return cssEditor container
		return $this;
	}
	
	/**
	 * Create the preview container.
	 * 
	 * @param	array	$browsers
	 * 		All the browsers that can be supported.
	 * 
	 * @return	DOMElement
	 * 		The preview container element.
	 */
	private function getPreviewContainer($browsers)
	{
		$previewView = DOM::create("div", "", "", "previewWrapper");
		
		// Context menu Wrapper
		$context = DOM::create("div", "", "", "viewOptions");
		
		// Create htmlEditor codeEditor container
		$htmlCodeContainer = DOM::create("div", "", "", "modelEditor noDisplay");
		// Create htmlEditor codeEditor
		$htmlEditor = new codeEditor();
		$htmlEditorElement = $htmlEditor->build("xml", trim($this->structure), $this->htmlName)->get();
		DOM::append($htmlCodeContainer, $htmlEditorElement);
		
		$previewContainer = DOM::create("div", "", "", "previewContainer");
		DOM::append($previewContainer, $context);
		DOM::append($previewContainer, $previewView);
		
		$previewBar = DOM::create("div", "", "", "previewBar");
		
		// Selection for browsing viewport
		$browserViewport = DOM::create("div", "", "", "selectBrowser previewTool");
		$optionGroups = array();
		$options = array();
		$options[] = $this->formFactory->getOption("active", "active");
		foreach ($browsers as $name => $prefix)
			$options[] = $this->formFactory->getOption($name, $name);
		$optionGroups[] = $this->formFactory->getOptionGroup("Browsers", $options);
		
		$browsersSelect = $this->formFactory->getSelect("", FALSE, "", $optionGroups);
		DOM::attr($browsersSelect, "title", "Select browser");
		DOM::append($browserViewport, $browsersSelect);
		DOM::append($previewBar, $browserViewport);
		
		// Toggle button between preview and html code
		$showHTMLPage = DOM::create("div", "", "", "showHTMLPage previewTool active");
		$showHTMLPageText = DOM::create("span", "Page");
		DOM::append($showHTMLPage, $showHTMLPageText);
		
		// Toggle button between preview and html code
		$showHTMLEditor = DOM::create("div", "", "", "showHTMLEditor previewTool");
		$showHTMLText = DOM::create("span", "Model");
		DOM::append($showHTMLEditor, $showHTMLText);
		
		DOM::append($previewBar, $showHTMLPage);
		DOM::append($previewBar, $showHTMLEditor);
		
		return array( $previewContainer, $htmlCodeContainer, $previewBar );
	}
	
	/**
	 * Get the structure viewer container.
	 * 
	 * @return	DOMElement
	 * 		The structure viewer element.
	 */
	private function getStructureContainer()
	{
		$structureView = DOM::create("div", "", "", "structureWrapper");

		$activePath = DOM::create("div", "", "", "path");
		DOM::append($structureView, $activePath);
		
		$structureViewer = DOM::create("div", "", "", "structureViewer");
		DOM::append($structureView, $structureViewer);
		
		$txtarea = $this->formFactory->getTextarea($name = "structure", $value = trim($this->structure));
		DOM::append($structureViewer, $txtarea);
		
		return $structureView;
	}
	
	/**
	 * Get the css viewer/editor container.
	 * 
	 * @param	string	$measurement
	 * 		Info relative to css measurement units.
	 * 
	 * @param	array	$tabs
	 * 		The editor's tabs.
	 * 
	 * @param	array	$tiles
	 * 		All the editor's tiles.
	 * 
	 * @return	DOMElement
	 * 		The css viewer/editor element.
	 */
	private function getCssContainer($measurement, $tabs, $tiles)
	{
		// Create cssCoder Container (with toggle buttons on a toolbar)
		$cssContainer = DOM::create("div", "", $this->cssName."_cssContainer", "cssContainer");
		
		//_____ Create toolbar navigation
		$cssToolbar = new navigationBar();
		$cssToolbarElement = $cssToolbar->build($dock = "T")->get();
		$cssToolbar->setParent($cssContainer, $dock = "T");
		
		// Insert Toolbar tools
		//_____ Toggle View
		$cssToggleView = DOM::create("div", "Code", "", "cssTool toggleCoder");
		DOM::attr($cssToggleView, "data-toggle-name", "Form");
		$cssToolbar->insertTool($cssToggleView);
		
		//_____ Search CSS
		$searchCss = $this->formFactory->getInput();
		DOM::attr($searchCss, "class", "searchCss");
		DOM::attr($searchCss, "placeholder", "Type CSS Rule");
		$cssToolbar->insertTool($searchCss);
		
		//_____ Previous Collection
		$cssPreviousCollection = DOM::create("div", "Restore", "", "cssTool restore noDisplay");
		DOM::attr($cssPreviousCollection, "data-toggle-name", "Undo");
		$cssToolbar->insertTool($cssPreviousCollection);
		
		// Create cssEditor codeEditor container
		$codeContainer = DOM::create("div", "", $this->cssName."_codeView", "cssCoder");
		// Create cssEditor codeEditor
		$cssEditor = new codeEditor();
		$cssEditorElement = $cssEditor->build("css", $this->cssCode, $this->cssName)->get();
		DOM::append($codeContainer, $cssEditorElement);
		
		// Create cssEditor formView container
		$formContainer = DOM::create("div", "", $this->cssName."_formView", "cssCoder selected");
		
		// Create css template page holder
		$templatePageHolder = DOM::create("div", "", "", "propertiesView defaults");
		DOM::attr($templatePageHolder, "data-properties-template", "true");
		//$cssForm->append($templatePageHolder);
		DOM::append($formContainer, $templatePageHolder);
		
		// Create TabControl for css Properties Groups
		$cssTabber = new tabControl();
		$cssPropertiesGroup = $cssTabber->build($id = $this->cssName."_propertiesTabber", $full = FALSE)->get();
		DOM::append($templatePageHolder, $cssPropertiesGroup);
		
		// Build Tabs
		$selected = TRUE;
		foreach ($tabs as $gid => $tab)
		{
			// Create Tab Page Content
			$page = NULL;
			
			// Build Tab Page
			$page = $this->getTilePage($tiles[$gid], $measurement);
			
			// Insert Tab Page
			$cssTabber->insertTab($gid, $tab, $page, $selected);
			$selected = FALSE;
		}
		
		DOM::append($cssContainer, $cssToolbarElement);
		DOM::append($cssContainer, $codeContainer);
		DOM::append($cssContainer, $formContainer);
		return $cssContainer;
	}
	
	/**
	 * Get the css coder editor.
	 * 
	 * @param	string	$measurement
	 * 		Info relative to css measurement units.
	 * 
	 * @param	array	$tabs
	 * 		The editor's tabs.
	 * 
	 * @param	array	$tiles
	 * 		All the editor's tiles.
	 * 
	 * @return	DOMElement
	 * 		The css editor element.
	 */
	private function getCssCoder($measurement, $tabs, $tiles)
	{
		// Create cssCoder Container (with toggle buttons on a toolbar)
		$cssContainer = DOM::create("div", "", $this->cssName."_cssContainer", "cssContainer");
		
		//_____ Create toolbar navigation
		$cssToolbar = new navigationBar();
		$cssToolbarElement = $cssToolbar->build($dock = "T")->get();
		$cssWrapper = DOM::create("div", "", "", "cssWrapper");
		$cssToolbar->setParent($cssWrapper, $dock = "T");
		DOM::append($cssWrapper, $cssToolbarElement);
		
		// Insert Toolbar tools
		//_____ CSS Selector
		$cssSelector = DOM::create("div");
		DOM::attr($cssSelector, "contentEditable", "true");
		DOM::attr($cssSelector, "class", "currentSelectorPath");
		DOM::attr($cssSelector, "placeholder", "Type CSS Selector");
		$cssToolbar->insertTool($cssSelector);
		
		// Create cssEditor codeEditor container
		$codeContainer = DOM::create("div", "", $this->cssName."_codeView", "cssCoder");
		// Create cssEditor codeEditor
		$cssEditor = new codeEditor();
		$cssEditorElement = $cssEditor->build("css", $this->cssCode, $this->cssName)->get();
		DOM::append($codeContainer, $cssEditorElement);
		
		// Create cssEditor formView container
		$formContainer = DOM::create("div", "", $this->cssName."_formView", "cssCoder selected");
		DOM::append($cssWrapper, $formContainer);
		
		// Create css template page holder
		$templatePageHolder = DOM::create("div", "", "", "propertiesView defaults");
		DOM::attr($templatePageHolder, "data-properties-template", "true");
		//$cssForm->append($templatePageHolder);
		DOM::append($formContainer, $templatePageHolder);
		
		// Create TabControl for css Properties Groups
		$cssTabber = new tabControl();
		$cssPropertiesGroup = $cssTabber->get_control($id = $this->cssName."_propertiesTabber", $full = FALSE);
		DOM::append($templatePageHolder, $cssPropertiesGroup);
		
		// Build Tabs
		$selected = TRUE;
		foreach ($tabs as $gid => $tab)
		{
			// Create Tab Page Content
			$page = NULL;
			
			// Build Tab Page
			$page = $this->getTilePage($tiles[$gid], $measurement);
			
			// Insert Tab Page
			$cssTabber->insertTab($gid, $tab, $page, $selected);
			$selected = FALSE;
		}
		
		// Create Main Container (horizontal slider)
		$slider = new gridSplitter();
		$sliderContainer = $slider->build($orientation = "horizontal")->get();
		DOM::append($cssContainer, $sliderContainer);
	
		// Append wrappers to slider
		$slider->appendToMain($codeContainer);
		$slider->appendToSide($cssWrapper);
		
		return $cssContainer;
	}
	
	/**
	 * Creates and returns a page tile for the cssEditor.
	 * It represents a grouping of css properties.
	 * 
	 * @param	array	$tile
	 * 		Info for a specific grouping.
	 * 
	 * @param	array	$measurement
	 * 		Info relative to css measurement units.
	 * 
	 * @return	DOMelement
	 * 		A tile page element.
	 */
	private function getTilePage($tile, $measurement)
	{
		$container = DOM::create("div", "", "", "propertiesContainer");
		
		// Append Tiles
		$bodies = array();
		foreach ($tile['titles'] as $class => $title)
		{
			$tileWrapper = DOM::create("div", "", "", $class." cssTile");
			// Create Tiles
			$header = DOM::create("span", $title, "", "cssTileHeader");
			DOM::append($tileWrapper, $header);
			$bodies[$class] = DOM::create("div", "", "", "cssTileBody");
			DOM::append($tileWrapper, $bodies[$class]);
			
			DOM::append($container, $tileWrapper);
		}
		
		// Populate Tiles
		foreach ($tile['properties'] as $tile => $props)
		{
			foreach ($props as $name => $prop)
			{
				$wrapper = DOM::create("div", "", "", "propertyWrapper");
				DOM::append($bodies[$tile], $wrapper);
				$label = $this->formFactory->getLabel($prop['name']);
				DOM::append($wrapper, $label);				
					
				// Check type and append accordingly
				switch ($prop['type'])
				{
				
					case "select":
						$select = $this->getSelectProperty($prop['values'], FALSE, $name, $prop['type'], $prop['browser-support'], $prop['syntax'], $prop['syntax']);
						DOM::append($wrapper, $select);
						break;
					case "time":
					case "length":
					case "angle":
						$div = DOM::create("div", "", "", $prop['type']."Input");
						DOM::append($wrapper, $div);
						$input = $this->getInputProperty($prop['default'], $prop['type'], $name, $prop['syntax'], $prop['browser-support']);
						DOM::append($div, $input);
						$select = $this->getSelectProperty($measurement[$prop['type']]);
						DOM::append($div, $select);
						break;
					case "mixed":
					case "input":
					default:
						$input = $this->getInputProperty($prop['default'], $prop['type'], $name, $prop['syntax'], $prop['browser-support']);
						DOM::append($wrapper, $input);
				}
				
				
				// Browser support icons for each property
				$browserClasses = array();
				$browserClasses['firefox'] = array('firefox');
				$browserClasses['webkit'] = array('chrome', 'safari');
				$browserClasses['opera'] = array('opera');
				$browserClasses['iexplorer'] = array('iexplorer');
				
				$support = json_decode($prop['browser-support'], TRUE);
				$bs = DOM::create("div", "", "", "browserSupport");
				DOM::append($wrapper, $bs);
				// Every Browser (needs tweak in css)
				foreach ($browserClasses as $browser => $values )
					foreach ($values as $class)
					{
						$b = DOM::create("span", "", "", $class.(!isset($support[$browser]) ? " unsupported" : ""));
						DOM::append($bs, $b);
					}
			}
		}
		
		return $container;
	}
	
	/**
	 * Creates, initializes, and returns a select DOM element.
	 * 
	 * @param	array	$values
	 * 		CSS property's available values
	 * 
	 * @param	boolean	$firstEmpty
	 * 		If set to TRUE first value should be empty
	 * 
	 * @param	string	$cssName
	 * 		Name of the css property
	 * 
	 * @param	string	$type
	 * 		Type of the property. This can be "input", "select", "length", "time", "angle", "mixed".
	 * 
	 * @param	string	$browserSupport
	 * 		A JSON string that holds a css property's browser support.
	 * 
	 * @param	string	$placeholder
	 * 		Placeholder of the css property value
	 * 
	 * @param	string	$title
	 * 		Title of the css property value
	 * 
	 * @return	DOMElement
	 * 		The select DOMElement.
	 */
	private function getSelectProperty($values, $firstEmpty = FALSE, $cssName = "", $type = "", $browserSupport = "", $placeholder = "", $title = "")
	{
		$options = array();
		if ($firstEmpty)
			$options[] = $this->formFactory->getOption("");
		foreach ($values as $value)
		{
			$options[] = $this->formFactory->getOption($value, $value);
		}
		
		$select = $this->formFactory->getSelect("", FALSE, "", $options);
		
		if (!empty($cssName))
		{
			DOM::attr($select, "data-css-property", $cssName);
			DOM::attr($select, "class", "default");
			DOM::attr($select, "title", $title);
			DOM::attr($select, "data-type", $type);
			DOM::attr($select, "data-browser-support", $browserSupport);
		}
		if (!empty($placeholder))
			DOM::attr($select, "placeholder", $placeholder);
		
		return $select;
	}
	
	/**
	 * Creates, initializes, and returns an input DOM element.
	 * 
	 * @param	string	$value
	 * 		CSS property's value
	 * 
	 * @param	string	$type
	 * 		Type of the property. This can be "input", "select", "length", "time", "angle", "mixed".
	 * 
	 * @param	string	$name
	 * 		Name of the CSS property.
	 * 
	 * @param	string	$title
	 * 		Title of the CSS property
	 * 
	 * @param	string	$browserSupport
	 * 		A JSON string that holds a css property's browser support.
	 * 
	 * @return	DOMelement
	 * 		The input DOMElement.
	 */
	private function getInputProperty($value, $type, $name, $title, $browserSupport)
	{
		$input = $this->formFactory->getInput();
		DOM::attr($input, "class", "default");
		DOM::attr($input, "data-css-property", $name);
		DOM::attr($input, "data-default-value", $value);
		DOM::attr($input, "placeholder", $value);
		DOM::attr($input, "title", $title);
		DOM::attr($input, "data-type", $type);
		DOM::attr($input, "data-browser-support", $browserSupport);
		
		return $input;
	}
	
	/**
	 * Checks whether this object is loaded with a model.
	 * 
	 * @return	boolean
	 * 		True if there is a loaded model, false otherwise.
	 */
	private function hasModel()
	{
		return !empty($this->htmlName);
	}
	
	/**
	 * Get css info for every property known.
	 * 
	 * @return	array
	 * 		An array of all css information.
	 */
	private function getCssInfo()
	{
		// Load css XML file into an Array
		$cssParser = new DOMParser();
		$cssParser->load(paths::getSDKRsrcPath()."/cssEditor/properties.xml");
		
		$measurement = array();
		$units = $cssParser->evaluate("/css/units/*");
		foreach ($units as $type)
		{
			$name = $type->tagName;
			$values = $cssParser->evaluate("unit", $type);
			foreach ($values as $v)
			{
				$val = $cssParser->attr($v, "value");
				$measurement[$name][$val] = $val;
			}
		}
		
		$browsers = array();
		$b = $cssParser->evaluate("/css/browsers/browser");
		foreach ($b as $browser)
		{
			$name = $cssParser->attr($browser, "name");
			$prefix = $cssParser->attr($browser, "prefix");
			$browsers[$name] = $prefix;
		}
		
		// Parse XML and initialize arrays
		// Properties Group Tabs
		$tabs = array();
		$tiles = array();
		$categories = $cssParser->evaluate("//properties/category");
		foreach ($categories as $cat)
		{
			$groupID = $cssParser->attr($cat, "group-id");
			$tabs[$groupID] = htmlentities($cssParser->attr($cat, "group-title"));
			$catClass = $cssParser->attr($cat, "class");
			$tiles[$groupID]['titles'][$catClass] = htmlentities($cssParser->attr($cat, "title"));
			$catName = $cssParser->attr($cat, "name");
			$props = $cssParser->evaluate("property", $cat);
			
			$properties = array();
			foreach ($props as $p)
			{
				$prop = array();
				$prop['name'] = $cssParser->attr($p, "name");
				$prop['default'] = $cssParser->attr($p, "default-value");
				$prop['type'] = $cssParser->attr($p, "type");
				$prop['syntax'] = $cssParser->attr($p, "syntax");
				$values = $cssParser->evaluate("values/value", $p);
				$prop['values'] = array();
				foreach ($values as $v)
				{
					$prop['values'][] = $v->nodeValue;
				}
				$prop['browser-support'] = $cssParser->attr($p, "browser-support");
				$properties[$prop['name']] = $prop;
			}
			
			if (!isset($tiles[$groupID]['properties'][$catClass]))
				$tiles[$groupID]['properties'][$catClass] = array();
			
			$tiles[$groupID]['properties'][$catClass] = array_merge($tiles[$groupID]['properties'][$catClass], $properties);
		}
		
		return array( $measurement, $browsers, $tabs, $tiles );
	}
}
//#section_end#
?>