<?php
//#section#[header]
// Namespace
namespace DEV\Documentation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Documentation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "gridSplitter");
importer::import("UI", "Presentation", "togglers/toggler");
importer::import("UI", "Forms", "formFactory");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;
use \UI\Presentation\gridSplitter;
use \UI\Presentation\togglers\toggler;
use \UI\Forms\formFactory;

/**
 * Class Documentation Editor
 * 
 * Handles the documentation process of the classes. Used to display the documentor tool and as a bridge between user and documentor classes
 * 
 * @version	0.1-2
 * @created	July 4, 2014, 16:33 (EEST)
 * @updated	April 22, 2015, 11:41 (EEST)
 */
class classDocEditor extends UIObjectPrototype
{
	/**
	 * The editor with the code that needs to be documented
	 * 
	 * @type	DOMElement
	 */
	private $linkedEditor = "";
	
	/**
	 * Creates and initializes a classDocumentor object.
	 * 
	 * @param	DOMElement	$editor
	 * 		The associated editor which contents need to be documented
	 * 
	 * @return	void
	 */
	public function __construct($editor)
	{
		$this->linkedEditor = $editor;
	}
	
	/**
	 * Builds and returns a classDocumentor object
	 * 
	 * @param	string	$manual
	 * 		Existing manual for the editor's code
	 * 
	 * @param	string	$name
	 * 		Name of the documenting area
	 * 
	 * @return	classDocEditor
	 * 		The built object [this]
	 */
	public function build($manual = "", $name = "docuContent")
	{  
		$xmlAreaModel = "classXMLModel";
	
		$splitter = new gridSplitter();
		$classDocumentorWrapper = $splitter->build("horizontal", gridSplitter::SIDE_RIGHT, TRUE, "Documentation")->get();
		DOM::attr($classDocumentorWrapper, "data-wrapper", "documentor");
		DOM::appendAttr($classDocumentorWrapper, "class", "init");
		
		$this->set($classDocumentorWrapper);
		
		$codeContainer = DOM::create("div", "", "", "codeContainer");
		DOM::append($codeContainer, $this->linkedEditor);
		DOM::attr($codeContainer, "style", "height:100%;");
		$splitter->appendToMain($codeContainer);
		
		$docContainer = DOM::create("div", "", "", "documentorContainer");
		
		// Refresh temp button
		$documentTool = DOM::create("div", "", "", "docuTool");
		DOM::append($docContainer, $documentTool);
		
		$refreshIcon = DOM::create("div", "", "", "refresh");
		DOM::append($documentTool, $refreshIcon);
		$refreshText = DOM::create("span", "Manual");
		DOM::append($documentTool, $refreshText);
		/*
		$documentorWrapper = DOM::create("div", "", "", "documentorWrapper");
		DOM::append($docContainer, $documentorWrapper);
		*/
		
		// Class model
		$documentor = DOM::create("div", "", "", "documentor");
		DOM::append($docContainer, $documentor);
		
		// A textarea that holds the xml from the model
		$hiddenArea = DOM::create("textarea", $manual, "", "", TRUE);
		DOM::attr($hiddenArea, "name", $xmlAreaModel);
		DOM::append($documentor, $hiddenArea);
		
		$helpers = self::getTemplates();
		DOM::append($docContainer, $helpers);
		
		$splitter->appendToSide($docContainer);
		
		return $this;
	}
	
	/**
	 * Fetches the templates to be used by the documentation editor
	 * 
	 * @return	DOMElement
	 * 		Returns a hidden pool with the templates
	 */
	private static function getTemplates(){
		$tileTemplates = DOM::create("div", "", "cde_templates", "cld_pool noDisplay");
	
		DOM::append($tileTemplates, self::getInfoTemplate());
		DOM::append($tileTemplates, self::getConstantTemplate());
		DOM::append($tileTemplates, self::getPropertyTemplate());
		DOM::append($tileTemplates, self::getMethodTemplate());
		
		// Append toggler template
		$twrap = DOM::create("div", "", "", "togglerTemplate");
		$toggler = new toggler();
		DOM::append($twrap, $toggler->build()->get());
		
		DOM::append($tileTemplates, $twrap);

		return $tileTemplates;
	}
	
	/**
	 * Creates a template for the class info
	 * 
	 * @return	DOMELement
	 * 		Returns the class info template
	 */
	private static function getInfoTemplate(){
		$form = new formFactory();
	
		// Template wrapper
		$itpl = DOM::create("div", "", "", "infoTemplate");
	
		// Tile wrapper
		$itw = DOM::create("div", "", "", "infoTileWrapper");
		DOM::append($itpl, $itw);
		
		DOM::append($itw, DOM::create("span", "", "", "className"));
		DOM::append($itw, DOM::create("div", "", "", "deprecatedOverlay"));
		
		// Top Right Info
		$topRight = DOM::create("div", "", "", "topRightInfo");
		DOM::append($topRight, DOM::create("div", "", "", "classCreated"));
		DOM::append($topRight, DOM::create("div", "", "", "classRevised"));
		DOM::append($itw, $topRight);
		
		// Class Relations
		$classRelations = DOM::create("div", "", "", "classRelations");
		DOM::append($classRelations, DOM::create("div", "", "", "classExtends"));
		DOM::append($classRelations, DOM::create("div", "", "", "classImplements"));
		DOM::append($itw, $classRelations);
		
		// Class Description
		$classDescription = DOM::create("div", "", "", "classDescription");
		$input = $form->getInput();
			DOM::attr($input, "title", "Title");
			DOM::attr($input, "placeholder", "Title");
		DOM::append($classDescription, $input);
		$text = $form->getTextarea();
			DOM::attr($text, "title", "Description");
			DOM::attr($text, "placeholder", "Description");
		DOM::append($classDescription, $text);
		$input = $form->getInput("text", "", "", "classThrows");
			DOM::attr($input, "title", "Exceptions");
			DOM::attr($input, "placeholder", "Exceptions Thrown");
		DOM::append($classDescription, $input);
		DOM::append($itw, $classDescription);
		
		// Deprecated Tile
		$deprTile = DOM::create("div", "", "", "deprecatedTile");
		$deprLabel = DOM::create("div", "", "", "deprecatedLabel");
			DOM::append($deprLabel, $form->getLabel("Deprecated"));
			DOM::append($deprLabel, $form->getInput("checkbox", "", "deprecated"));
		DOM::append($deprTile, $deprLabel);
		$input = $form->getInput("text", "", "", "deprecatedDescription");
			DOM::attr($input, "title", "Deprecate instructions");
			DOM::attr($input, "placeholder", "Deprecate instructions");
		DOM::append($deprTile, $input);
		DOM::append($itw, $deprTile);
		
		return $itpl;
	}
	
	/**
	 * Creates a template for the constants
	 * 
	 * @return	DOMElement
	 * 		Returns the constants template
	 */
	private static function getConstantTemplate(){
		$form = new formFactory();
	
		// Template wrapper
		$ctpl = DOM::create("div", "", "", "constantTemplate");
	
		// Tile wrapper
		$ctw = DOM::create("div", "", "", "constantTileWrapper");
		DOM::append($ctpl, $ctw);
		
		DOM::append($ctw, DOM::create("div", "", "", "discontinuedOverlay"));
		DOM::append($ctw, DOM::create("span", "", "", "constantName"));
		
		$input = $form->getInput("text", "", "", "constantType");
			DOM::attr($input, "title", "Type");
			DOM::attr($input, "placeholder", "Type");
		DOM::append($ctw, $input);
		$input = $form->getInput("text", "", "", "constantDescription");
			DOM::attr($input, "title", "Description");
			DOM::attr($input, "placeholder", "Description");
		DOM::append($ctw, $input);
		
		// Discontinued Tile
		$discTile = DOM::create("div", "", "", "discontinuedTile");
		DOM::append($discTile, $form->getLabel("Discontinued"));
		DOM::append($ctw, $discTile);
		
		return $ctpl;
	}
	
	/**
	 * Creates a template for the properties
	 * 
	 * @return	DOMElement
	 * 		Returns the properties template
	 */
	private static function getPropertyTemplate(){
		$form = new formFactory();
	
		// Template wrapper
		$ptpl = DOM::create("div", "", "", "propertyTemplate");
	
		// Tile wrapper
		$ptw = DOM::create("div", "", "", "propertyTileWrapper");
		DOM::append($ptpl, $ptw);
		
		DOM::append($ptw, DOM::create("div", "", "", "discontinuedOverlay"));
		DOM::append($ptw, DOM::create("span", "", "", "propertyName"));
		DOM::append($ptw, DOM::create("span", "STATIC", "", "propertyStatic"));
		
		$input = $form->getInput("text", "", "", "propertyType");
			DOM::attr($input, "title", "Type");
			DOM::attr($input, "placeholder", "Type");
		DOM::append($ptw, $input);
		$input = $form->getInput("text", "", "", "propertyDescription");
			DOM::attr($input, "title", "Description");
			DOM::attr($input, "placeholder", "Description");
		DOM::append($ptw, $input);
		
		// Discontinued Tile
		$discTile = DOM::create("div", "", "", "discontinuedTile");
		DOM::append($discTile, $form->getLabel("Discontinued"));
		DOM::append($ptw, $discTile);
		
		return $ptpl;
	}
	
	/**
	 * Creates a template for the methods
	 * 
	 * @return	DOMElement
	 * 		Returns the methods template
	 */
	private static function getMethodTemplate(){
		$form = new formFactory();
	
		// Template wrapper
		$mtpl = DOM::create("div", "", "", "methodTemplate");
	
		// Tile wrapper
		$mtw = DOM::create("div", "", "", "methodTileWrapper");
		DOM::append($mtpl, $mtw);
		
		DOM::append($mtw, DOM::create("div", "", "", "deprecatedOverlay"));
		DOM::append($mtw, DOM::create("div", "", "", "discontinuedOverlay"));
		
		// Method name
		$methodName = DOM::create("div", "", "", "methodName");
		$input = $form->getInput("text", "", "", "methodReturnType");
			DOM::attr($input, "title", "Return Type");
			DOM::attr($input, "placeholder", "Return Type");
		DOM::append($methodName, $input);
		DOM::append($methodName, DOM::create("span"));
		DOM::append($mtw, $methodName);
		
		DOM::append($mtw, DOM::create("span", "STATIC", "", "methodStatic"));
		DOM::append($mtw, DOM::create("span", "ABSTRACT", "", "methodAbstract"));
		
		// Method Descriptions
		$text = $form->getTextarea("", "", "methodDescription");
			DOM::attr($text, "title", "Description");
			DOM::attr($text, "placeholder", "Description");
		DOM::append($mtw, $text);
		$text = $form->getTextarea("", "", "methodReturnDescription");
			DOM::attr($text, "title", "Return Value Description");
			DOM::attr($text, "placeholder", "Return Value Description");
		DOM::append($mtw, $text);
		// Method throws
		$input = $form->getInput("text", "", "", "methodThrows");
			DOM::attr($input, "title", "Exceptions");
			DOM::attr($input, "placeholder", "Exceptions Thrown");
		DOM::append($mtw, $input);
		
		// Arguments List
		DOM::append($mtw, $form->getLabel("Parameters", "", "argumentsHeader"));
		
		$argList = DOM::create("div", "", "", "argumentList");
		$atw = DOM::create("div", "", "", "argumentTileWrapper");
		DOM::append($atw, DOM::create("span", "", "", "argumentName"));
		$input = $form->getInput("text", "", "", "argumentType");
			DOM::attr($input, "title", "Type");
			DOM::attr($input, "placeholder", "Type");
		DOM::append($atw, $input);
		DOM::append($atw, DOM::create("span", "", "", "argumentDefault"));
		$text = $form->getTextarea("", "", "argumentDescription");
			DOM::attr($text, "title", "Description");
			DOM::attr($text, "placeholder", "Description");
		DOM::append($atw, $text);
		DOM::append($argList, $atw);
		DOM::append($mtw, $argList);
		
		// Deprecated Tile
		$deprTile = DOM::create("div", "", "", "deprecatedTile");
		DOM::append($deprTile, $form->getLabel("Deprecated"));
		DOM::append($deprTile, $form->getInput("checkbox", "", "deprecated"));
		$input = $form->getInput("text", "", "", "deprecatedDescription");
			DOM::attr($input, "title", "Deprecate instructions");
			DOM::attr($input, "placeholder", "Deprecate instructions");
		DOM::append($deprTile, $input);
		DOM::append($mtw, $deprTile);
	
		// Discontinued Tile
		$discTile = DOM::create("div", "", "", "discontinuedTile");
		DOM::append($discTile, $form->getLabel("Discontinued"));
		DOM::append($mtw, $discTile);
	
		return $mtpl;
	}
}
//#section_end#
?>