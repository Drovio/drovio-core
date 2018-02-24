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
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "gridSplitter");
importer::import("UI", "Presentation", "togglers::toggler");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;
use \UI\Presentation\gridSplitter;
use \UI\Presentation\togglers\toggler;

/**
 * Class Documentation Editor
 * 
 * Handles the documentation process of the classes. Used to display the documentor tool and as a bridge between user and documentor classes
 * 
 * @version	{empty}
 * @created	July 4, 2014, 16:33 (EEST)
 * @revised	July 4, 2014, 16:35 (EEST)
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
		
		$helpers = DOM::create("div", "", "", "cld_pool noDisplay");
		DOM::append($docContainer, $helpers);
		
		$toggler = new toggler();
		DOM::append($helpers, $toggler->build()->get());
		
		$splitter->appendToSide($docContainer);
		
		return $this;
	}
}
//#section_end#
?>