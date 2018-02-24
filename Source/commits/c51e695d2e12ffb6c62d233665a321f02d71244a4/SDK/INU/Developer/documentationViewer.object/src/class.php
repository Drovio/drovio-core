<?php
//#section#[header]
// Namespace
namespace INU\Developer;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	INU
 * @package	Developer
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Developer", "resources::documentation::classDocumentor");
importer::import("UI", "Html", "DOM");
importer::import("UI", 'Presentation', 'togglers::toggler');
importer::import("UI", 'Presentation', 'dataGridList');

use \ESS\Prototype\UIObjectPrototype;
use \API\Developer\resources\documentation\classDocumentor;
use \UI\Html\DOM;
use \UI\Presentation\togglers\toggler;
use \UI\Presentation\dataGridList;

/**
 * Class Documentation Viewer
 * 
 * Class for viewing the documentation of the system's SDK classes.
 * 
 * @version	{empty}
 * @created	April 15, 2013, 15:55 (EEST)
 * @revised	October 7, 2013, 12:35 (EEST)
 */
class documentationViewer extends UIObjectPrototype
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const SECID_INFO = "classInfo";
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const SECID_CONST = "classConstants";
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const SECID_PROPS = "classProperties";
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const SECID_METHS = "classMethods";

	
	/**
	 * The object name.
	 * 
	 * @type	string
	 */
	private $objectName;
	
	private $filepath;
	
	private $classDocPath;
	
	private $objectManualPath;
	
	/**
	 * The library name.
	 * 
	 * @type	string
	 */
	private $classDocumentor;

	/**
	 * This is the constructor method.
	 * Initializes the object variables.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$nsName
	 * 		The object namespace (separated by "_" or "::")
	 * 
	 * @param	string	$objectName
	 * 		The object name.
	 * 
	 * @param	{type}	$domain
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function __construct($libName, $packageName, $nsName, $objectName, $domain = "")
	{		
		$this->objectName = $objectName;
		
		$nsNorm = str_replace("_", "/", $nsName);
		$nsNorm = str_replace("::", "/", $nsNorm);
		$objectPath = str_replace("_", "/", $domain)."/".$libName."/".$packageName."/".$nsNorm;
		//$this->filepath = "/System/Resources/Documentation/".$objectPath."/".$objectName.".php.xml";
		
		$this->classDocPath = "/System/Resources/Documentation/".$objectPath."/".$objectName.".php.xml";
		$this->objectManualPath = "/System/Resources/Documentation/".$objectPath."/".$objectName.".php.xml";
		
		$this->classDocumentor = new classDocumentor();
	}
	
	/**
	 * Builds the entire documentation viewer.
	 * 
	 * @param	{type}	$level
	 * 		{description}
	 * 
	 * @return	documentationViewer
	 * 		{description}
	 */
	public function build($level = "class")
	{		
		// Build Outer Container
		$holder = DOM::create("div", "", "", "documentationViewer");
		$this->set($holder);
		
		// Check Existance
		if (!($this->classDocumentor->loadFile($this->classDocPath)))
		{
			$this->buildError("Not Found");
			return $this;
		}
		
		//Check empty
		
		// Get Class Info
		$info = $this->classDocumentor->getInfo();
		
		// Get Holder
		$holder = $this->get();
		
		// Build Head
		$documentHead = $this->buildInfo($info);		
		DOM::append($holder, $documentHead);		
		
		
		//Build Body
		$documentBody = DOM::create('div', '', 'documentBody', 'sectionWrapper');
		DOM::append($holder, $documentBody);
		
		// Check Deprecated
		if (!empty($info['deprecated']))
		{
			DOM::appendAttr($documentBody, "class", "deprecated");
		}
		
		// Class Description
		$description = $this->buildDescription();
		DOM::append($documentBody, $description);
		
		// Component Synopsis
		$synopsis = $this->buildExtended();
		DOM::append($documentBody, $synopsis);
		
		// Change log
		$changeLog = $this->buildChangeLog();
		DOM::append($documentBody, $changeLog);
		
		// Examples
		$examples = $this->buildExamples();
		DOM::append($documentBody, $examples);
		
		return $this;		
	}
	
	/**
	 * Builds the class information.
	 * 
	 * @param	{type}	$info
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function buildInfo($info)
	{		
		$documentHeader = DOM::create("div", "", "documentHead");
		
		$infoWrapper = DOM::create("div", "", "", "infoWrapper");
		DOM::append($documentHeader, $infoWrapper);
		
		$classInfo = DOM::create("div", "", "", "classDeclContainer");
		DOM::append($infoWrapper, $classInfo);
				
		// Class Name
		$className = DOM::create("div", "", "", "className");
			$span = DOM::create("span", $this->objectName);
			DOM::append($className, $span);
		DOM::append($classInfo, $className);
		
		// Class title and friendly description
		$section = DOM::create("div", "", "", "section");
		DOM::append($classInfo, $section);
		
		$paragraph = DOM::create("p");
		DOM::append($section, $paragraph);
		
		$classTitle = DOM::create("span", $info['title']." :", "", "classTitle");
		DOM::append($paragraph, $classTitle);
		
		$classDesc = DOM::create("span", $info['description'], "", "classDescription");
		DOM::append($paragraph, $classDesc);
		
		// Class throws
		if (!empty($info['throws'])) 
		{ 
			$throws = DOM::create("div", "", "", "classThrows section");
			DOM::append($classInfo, $throws);
			
			$title = DOM::create("span", "Throws: ");
			DOM::append($throws, $title);
			
			$text = DOM::create("span", $info['throws'], "", "");
			DOM::append($throws, $text);
		}
		
		
		
		$documentationInfo = DOM::create("div", "", "", "classInfo");
		DOM::append($infoWrapper, $documentationInfo);
				
		$version = DOM::create("div", (empty($info['version']) ? "0.0" : $info['version'])." (V)");
		DOM::append($documentationInfo, $version);
		
		$datecreated = DOM::create("div", date("F j, Y, G:i (T)", $info['datecreated'])." (C)");
		DOM::append($documentationInfo, $datecreated);
		
		$daterevised = DOM::create("div", date("F j, Y, G:i (T)", $info['daterevised'])." (R)");
		DOM::append($documentationInfo, $daterevised);
		
		if (!empty($info['deprecated']))
		{
			$deprecated = $this->buildDeprecated($info['deprecated']);
			DOM::append($documentHeader, $deprecated);
		}
		
		
		return $documentHeader;
		
		//$info['throws']
		
		/*
		// Class Declaration
		$classDeclaration = DOM::create("div", "", "", "classDeclaration");
		DOM::append($section, $classDeclaration);	
		
		
		// __Extends
		if (!empty($info['extends']))
		{
			$span = DOM::create("span", "extends : ", "", "modifier");
			DOM::append($classDeclaration, $span);
			
			$classExtends = DOM::create("span", $info['extends'], "", "classExtends");
			DOM::append($classDeclaration, $classExtends);
		}
		// __Implements
		if (!empty($info['implements']))
		{
			$span = DOM::create("span", "implements : ", "", "modifier");
			DOM::append($classDeclaration, $span);
			
			$classImplements = DOM::create("span", $info['implements'], "", "classImplements");
			DOM::append($classDeclaration, $classImplements);
		}
		// __Inheritance Tree
		*/		
	}
	
	/**
	 * Builds the deprecated class notification.
	 * 
	 * @param	string	$message
	 * 		The deprecated message.
	 * 
	 * @return	void
	 */
	private function buildDeprecated($message)
	{
		$deprecated = DOM::create("div", "", "", "deprecationMessage");	
		
		$deprecationHead = DOM::create("div");	
			$deprSign = DOM::create("span", "This Class is Deprecated", "", "deprSign");
			DOM::append($deprecationHead , $deprSign);
		DOM::append($deprecated , $deprecationHead );
		
		$deprecationBody = DOM::create("div");
			$deprDesc = DOM::create("p", $message, "", "deprDesc");
			DOM::append($deprecationBody, $deprDesc);		
		DOM::append($deprecated , $deprecationBody );
		
		return $deprecated;
	}
	
	
	
	/**
	 * Builds the extended specification of this class.
	 * 
	 * @return	void
	 */
	private function buildExtended()
	{
		$header = DOM::create("span", "Synopsis");		
		$extended = DOM::create("div", "", "", "body");
		
		// __Constants
		$subSection = DOM::create("div", "", "", "constantsContainer");
		DOM::append($extended, $subSection);		
		$constHeader = DOM::create("div", "Constants", "", "subHeader");
		DOM::append($subSection, $constHeader);
		
		$sectionBody = DOM::create("div", "", "", "body");
		DOM::append($subSection, $sectionBody);
		
		$allConstants = $this->classDocumentor->getConstants();
		
		$dtGridList = new dataGridList();
		$dtGridList->build();		
		
		$headers = array();
		$headers[] = "Type";
		$headers[] = "Name";
		$headers[] = "Description";
		
		$ratios = array();
		$ratios[] = 0.1;
		$ratios[] = 0.3;
		$ratios[] = 0.6;
		
		$dtGridList->setColumnRatios($ratios);		
		$dtGridList->setHeaders($headers);
		
		// __Constants
		foreach ($allConstants as $constant)
		{
			$gridRow = array();	
			
			// Type
			$elementWrapper = DOM::create("div", "", "", "decline");	
			$element = DOM::create("span", $constant['type'], "", "cType modifier");
			DOM::append($elementWrapper, $element);
			$gridRow[] = $elementWrapper;			
			// Name
			$elementWrapper = DOM::create("div", "", "", "decline");
			$element = DOM::create("span", $constant['name'], "", "cName token");
			DOM::append($elementWrapper, $element);
			$gridRow[] = $elementWrapper;			
			// Description
			$constantDesc = DOM::create("pre", $constant['description'], "", "cDesc");
			$gridRow[] = $constantDesc;
			
			$dtGridList->insertRow($gridRow);	
		}
		DOM::append($sectionBody, $dtGridList->get());
		
		// __Properties
		$subSection = DOM::create("div", "", "", "propertiesContainer");
		DOM::append($extended, $subSection);
		$propHeader = DOM::create("div", "Properties", "", "subHeader");
		DOM::append($subSection, $propHeader);
		
		$sectionBody = DOM::create("div", "", "", "body");
		DOM::append($subSection, $sectionBody);				
		
		$allProperties = $this->classDocumentor->getProperties();
		
		$dtGridList = new dataGridList();
		$dtGridList->build();	
				
		$headers = array();
		$headers[] = "Scope";
		$headers[] = "Type";
		$headers[] = "Name";
		$headers[] = "Description";
		
		$ratios = array();
		$ratios[] = 0.1;
		$ratios[] = 0.1;
		$ratios[] = 0.3;
		$ratios[] = 0.5;				
		
		$dtGridList->setColumnRatios($ratios);		
		$dtGridList->setHeaders($headers);
		
		foreach ($allProperties as $scope => $properties)
		{
			foreach ($properties as $propName => $propInfo) 
			{
				$gridRow = array();
				
				// Scope
				$elementWrapper = DOM::create("div", "", "", "decline");	
				$element = DOM::create("span", $scope, "", "pScope modifier");
				DOM::append($elementWrapper, $element);
				$gridRow[] = $elementWrapper;	
				// Type
				$elementWrapper = DOM::create("div", "", "", "decline");	
				$element = DOM::create("span", $propInfo['type'], "", "pType modifier");
				DOM::append($elementWrapper, $element);
				$gridRow[] = $elementWrapper;			
				// Name
				$elementWrapper = DOM::create("div", "", "", "decline");
				$element =  DOM::create("span", $propInfo['name'], "", "pName token");
				DOM::append($elementWrapper, $element);
				$gridRow[] = $elementWrapper;			
				// Description
				$propertyDesc = DOM::create("pre", $propInfo['description'], "", "pDesc");
				$gridRow[] = $propertyDesc;
				
				$dtGridList->insertRow($gridRow);
			}	
		}		
		DOM::append($sectionBody, $dtGridList->get());
		
		
		// __Methods 
		$subSection = DOM::create("div", "", self::SECID_METHS, "methodsContainer");
		DOM::append($extended, $subSection);
		
		$methodHeader = DOM::create("div", "Methods", "", "subHeader");
		DOM::append($subSection, $methodHeader);
		
		$methodsContainer = DOM::create("div", "", "", "body");
		DOM::append($subSection, $methodsContainer);
		
		$allMethods = $this->classDocumentor->getMethods();
		foreach ($allMethods as $scope => $methods)
			foreach ($methods as $methodName => $methodInfo)
			{
				$toggler = new toggler();
							
				$methodElement = DOM::create("div", "", "", "method");
				DOM::append($methodsContainer, $methodElement);
				
				$methodHead = DOM::create("div");
				
				$methodDeclaration = $this->getMethodDeclaration($scope, $methodInfo);
				DOM::append($methodHead, $methodDeclaration);	
				
				
				if (!empty($methodInfo['deprecated']))
				{
					$deprecated = DOM::create("div", "", "", "");
					DOM::append($methodElement, $deprecated);
					
					$deprSign = DOM::create("span", "This method is Deprecated"." : ", "", "deprSign");
					DOM::append($deprecated, $deprSign);
					$deprDesc = DOM::create("span", $methodInfo['deprecated'], "", "deprDesc");
					DOM::append($deprecated, $deprDesc);
					
					$methodBody = $deprecated;
				}
				else
				{
					$descElement = DOM::create("pre", $methodInfo['description'], "", "mDesc");
					DOM::append($methodHead, $descElement);
				
					$methodBody = DOM::create("div", "", "", "methodBody");
					
					// Return and Throws
					$methodAdditionals = DOM::create("div", "", "", "");
					DOM::append($methodBody, $methodAdditionals );
					
					if (!empty($methodInfo['returndescription']))
					{ 
						$returnDesc = DOM::create("div", "", "", "methReternDesc");
						DOM::append($methodAdditionals , $returnDesc );
						
						$title = DOM::create("span", "Returns: ");
						DOM::append($returnDesc , $title);
						
						$text = DOM::create("span", $methodInfo['returndescription'], "", "");
						DOM::append($returnDesc , $text);
					}
					if (!empty($methodInfo['throws']))
					{ 					
						$throws = DOM::create("div", "", "", "methThrows");
						DOM::append($methodAdditionals , $throws);
						
						$title = DOM::create("span", "Throws: ");
						DOM::append($throws, $title);
						
						$text = DOM::create("span", $methodInfo['throws'], "", "");
						DOM::append($throws, $text);
					}
					
					// Create parameters description
					$paramDescriptions = DOM::create("div", "", "", "parameters");
					DOM::append($methodBody, $paramDescriptions);
					if (array_key_exists('parameters', $methodInfo))
						foreach ($methodInfo['parameters'] as $pName => $paramInfo)
						{
							$paramElement = DOM::create("div", "", "", "parameter");
							DOM::append($paramDescriptions, $paramElement);
							
							$paramName = DOM::create("div", $pName, "", "paramName modifier");
							DOM::append($paramElement, $paramName);
							
							$paramDesc = DOM::create("p", $paramInfo['description'], "", "paramDesc");
							DOM::append($paramElement, $paramDesc);
						}
				}				
				$toggler->build($id = "", $methodHead, $methodBody, FALSE);
				DOM::append($methodElement, $toggler->get());
			}
		return $this->buildSection($header, $extended);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function buildDescription()
	{		
		$header = DOM::create("span", "Description");
		
		$content = '';
		
		return $this->buildSection($header, $content);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function buildChangeLog()
	{
		$header = DOM::create("span", "ChangeLog");
		
		$content = '';
		
		return $this->buildSection($header, $content);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function buildExamples()
	{
		$header = DOM::create("span", "Examples");
		
		$content = '';
		
		return $this->buildSection($header, $content);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$header
	 * 		{description}
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function buildSection($header, $content = '')
	{
		// Build Section Wrapper
		$section = DOM::create("div", "", "", "section");
		
		// Build Header
		$sectionHeader = DOM::create("div", "", "", "header");
		DOM::append($section, $sectionHeader);
		
		// Append Header Content
		//$headerContent = DOM::create("span", "Examples");
		DOM::append($sectionHeader, $header);
		
		// Build Content
		$sectionContent = DOM::create("div", "", "", "body");
		DOM::append($section, $sectionContent);
		
		if(empty($content))
		{
			$content = DOM::create("span", "No Content");
		}
		DOM::append($sectionContent, $content);
		
		return $section;
	}
	
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$message
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function buildError($message)
	{
		$error = DOM::create("div", "", "", "documentationError");
		DOM::append($this->get(), $error);		
		
		$deprSign = DOM::create("p", "An error occured while loading documentation file", "", "errorSign");
		DOM::append($error, $deprSign);
		$deprDesc = DOM::create("p", $message, "", "errorDesc");
		DOM::append($error, $deprDesc);
	}	
	
	/**
	 * Builds a property declaration DOMElement and returns it.
	 * 
	 * @param	string	$scope
	 * 		The property scope.
	 * 
	 * @param	array	$prop
	 * 		The property information.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	private function getPropertyDeclaration($scope, $prop)
	{
		$propertyElement = DOM::create("div", "", "", "propertyDeclaration decline");
				
		$scopeElement = DOM::create("span", $scope, "", "pScope modifier");
		DOM::append($propertyElement, $scopeElement);
		
		$typeElement = DOM::create("span", $prop['type'], "", "pType modifier");
		DOM::append($propertyElement, $typeElement);
		
		$nameElement = DOM::create("span", $prop['name'], "", "pName token");
		DOM::append($propertyElement, $nameElement);
		
		return $propertyElement;
	}
	
	/**
	 * Builds a constant declaration DOMElement and returns it.
	 * 
	 * @param	array	$const
	 * 		The constant information.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	private function getConstantDeclaration($const)
	{
		$constantElement = DOM::create("div", "", "", "constantDeclaration decline");
		
		$typeElement = DOM::create("span", $const['type'], "", "cType modifier");
		DOM::append($constantElement, $typeElement);
		
		$nameElement = DOM::create("span", $const['name'], "", "cName token");
		DOM::append($constantElement, $nameElement);
		
		//$property['description']
		
		return $constantElement;
	}
	
	/**
	 * Builds a method declaration DOMElement and returns it.
	 * 
	 * @param	string	$scope
	 * 		The scope of the method.
	 * 
	 * @param	array	$method
	 * 		The method information.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	private function getMethodDeclaration($scope, $method)
	{
		$methodDeclaration = DOM::create("div", "", "", "methodDeclaration decline");
		
		if(!empty($method['deprecated']))
		{
			$deprecationIdentifier = DOM::create("span", "{Deprecated}", "", "mDepr modifier");
			DOM::append($methodDeclaration, $deprecationIdentifier);
		}
		
		$scopeElement = DOM::create("span", $scope, "", "mScope modifier");
		DOM::append($methodDeclaration, $scopeElement);
		
		$typeElement = DOM::create("span", $method['returntype'], "", "mType modifier");
		DOM::append($methodDeclaration, $typeElement);
		
		$nameElement = DOM::create("span", $method['name'], "", "mName token");
		DOM::append($methodDeclaration, $nameElement);
		
		$parenthesisStart = DOM::create("span", " (", "", "modifier");
		DOM::append($methodDeclaration, $parenthesisStart);
		
		$counter = 0;
		if (array_key_exists('parameters', $method))
			foreach ($method['parameters'] as $pName => $paramInfo)
			{
				$paramElement = DOM::create("span", "", "", "parameter");
				DOM::append($methodDeclaration, $paramElement);
				
				$paramType = DOM::create("span", ($counter != 0 ? ", " : "").$paramInfo['type'], "", "paramType modifier");
				DOM::append($paramElement, $paramType);
				
				$paramName = DOM::create("span", $pName, "", "paramName token");
				DOM::append($paramElement, $paramName);
				$counter++;
			}
		
		$parenthesisEnd = DOM::create("span", ")", "", "modifier");
		DOM::append($methodDeclaration, $parenthesisEnd);
	
		return $methodDeclaration;
	}
}
//#section_end#
?>