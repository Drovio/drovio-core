<?php
//#section#[header]
// Namespace
namespace INU\Developer\documentation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	INU
 * @package	Developer
 * @namespace	\documentation
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("INU", "Developer", "codeEditor");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "gridSplitter");
importer::import("UI", "Presentation", "togglers::toggler");


use \ESS\Prototype\UIObjectPrototype;
use \INU\Developer\codeEditor;
use \UI\Html\DOM;
use \UI\Presentation\gridSplitter;
use \UI\Presentation\togglers\toggler;

// Temporary For Compability reasons
importer::import("API", "Developer", "resources::documentation::classDocumentor");
importer::import("API", "Developer", "resources::documentation::classDocComments");


use \API\Developer\resources\documentation\classDocComments;
use \API\Developer\resources\documentation\classDocumentor as documentor;

/**
 * Class Documentor
 * 
 * Handles the documentation process of the classes.
 * 
 * @version	{empty}
 * @created	June 27, 2013, 12:04 (EEST)
 * @revised	July 4, 2014, 13:32 (EEST)
 * 
 * @deprecated	Use UI\Developer\classDocumentor instead.
 */
class classDocumentor extends UIObjectPrototype
{	
	/**
	 * The editor with the code that needs to be documented
	 * 
	 * @type	DOMElement
	 */
	private $linkedEditor = "";
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $classDocumentor;
	
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
		
		$this->classDocumentor = new documentor();
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
	 * @return	classDocumentor
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
	
		// Get editor
		//$redDoc = new codeEditor();
		//$editor = $redDoc->get_editor("xml", nl2br($manual), $name);
		
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
	
	/**
	 * Checks if the manual's structure is correct
	 * 
	 * @param	string	$manual
	 * 		The manual to check
	 * 
	 * @return	boolean
	 * 		The validation state of the documentation
	 * 
	 * @deprecated	Use  API\Devaloper\resources\documentation\classDocumentor::isValidDocumentation
	 */
	public static function isValidDocumentation($manual)
	{
		return documentor::isValidDocumentation($manual);
	}
	
	/**
	 * Returns class details in an array
	 * 
	 * @param	string	$manual
	 * 		Object's documentation in xml format
	 * 
	 * @return	array
	 * 		Class details:
	 * 		name -> (string) Class name,
	 * 		abstract -> (boolean) Class is/isn't abstract,
	 * 		namespace -> (string) Class namespace,
	 * 		version -> Class version,
	 * 		datecreated -> Class creation timestamp,
	 * 		daterevised -> Class last revision timestamp,
	 * 		title -> Class title,
	 * 		description -> Class description,
	 * 		deprecated -> (mixed) Class deprication description or FALSE,
	 * 		extends -> array of extended object names,
	 * 		implements -> array of implemented object names.
	 * 
	 * @deprecated	Use  API\Devaloper\resources\documentation\classDocumentor::getClassDetails
	 */
	public static function getClassDetails($manual)
	{
		return documentor::getClassDetails($manual);
	}
	
	/**
	 * Strips specific comments from the code
	 * 
	 * @param	string	$sourceCode
	 * 		The code from which to strip comments
	 * 
	 * @return	string
	 * 		The stripped source code
	 * 
	 * @deprecated	Use  API\Devaloper\resources\documentation\classDocComments::stripSourceCode
	 */
	public static function stripSourceCode($sourceCode)
	{
		return classDocComments::stripSourceCode($sourceCode);
	}
	
	/**
	 * Position comment blocks in specific spots in the given code
	 * 
	 * @param	string	$sourceCode
	 * 		The code to comment.
	 * 
	 * @param	string	$manual
	 * 		The code's manual from which the comment blocks will be created.
	 * 
	 * @param	string	$library
	 * 		Code class' library
	 * 
	 * @param	string	$package
	 * 		Code class' package
	 * 
	 * @param	string	$namespace
	 * 		Code class' namespace
	 * 
	 * @return	string
	 * 		The pretified source code
	 * 
	 * @deprecated	Use  API\Devaloper\resources\documentation\classDocComments::pretifySourceCode
	 */
	public static function pretifySourceCode($sourceCode, $manual, $library, $package, $namespace)
	{
		return classDocComments::pretifySourceCode($sourceCode, $manual, $library, $package, $namespace);
	}
	
	/**
	 * Acquires the documentation model of an object as an xml document string.
	 * 
	 * @param	string	$libName
	 * 		Name of the object's library
	 * 
	 * @param	string	$packageName
	 * 		Name of the object's package
	 * 
	 * @param	string	$nsName
	 * 		Name of the object's namespace
	 * 
	 * @param	string	$objectName
	 * 		Name of the object
	 * 
	 * @return	string
	 * 		The document's xml string
	 * 
	 * @deprecated	Use  API\Devaloper\resources\documentation\classDocumentor:
	 */
	public static function loadDoc($libName, $packageName, $nsName, $objectName)
	{
		$classDocumentor = new documentor();
		
		// Filepath
		$filepath = "/System/Resources/SDK/Documentation/";
		$filepath .= $libName."/".$packageName."/".str_replace("_", "/", $nsName)."/";
		$filepath .= $objectName.".xml";
		
		// Load Documentation
		$classDocumentor->loadFile($filepath);
		return $classDocumentor->getDoc();
		
		/*
		$this->classDocumentor->load($filepath);
		return $this->classDocumentor->getDoc();
		*/
	}
	
	/**
	 * Returns the documentation model's xml.
	 * 
	 * @return	string
	 * 		The model's xml string
	 * 
	 * @deprecated	Use  API\Devaloper\resources\documentation\classDocumentor::getModel
	 */
	public static function getModel()
	{
		return documentor::getModel();
	}
	
	
}
//#section_end#
?>