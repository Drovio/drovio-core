<?php
//#section#[header]
// Namespace
namespace API\Developer\resources\documentation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\resources\documentation
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "resources");
importer::import("API", "Developer", "profiler::logger");

use \API\Developer\profiler\logger;
use \API\Resources\DOMParser;
use \API\Resources\resources;
/**
 * Class Documentation Reader / Writer
 * 
 * This class is responsible for parsing (read / write) class documentation files (xml)
 * 
 * @version	{empty}
 * @created	October 7, 2013, 11:54 (EEST)
 * @revised	July 1, 2014, 22:21 (EEST)
 * 
 * @deprecated	Use DEV\Documentation\classDocumentor instead.
 */
class classDocumentor
{
	/**
	 * Path to the class documentation model
	 * 
	 * @type	string
	 */
	const DOC_MODEL = "/SDK/documentation/classDocumentorModel.xml";
		
	/**
	 * The parser used to read the documentation file.
	 * 
	 * @type	DOMParser
	 */
	private $parser;

	/**
	 * The contructor method
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$this->parser = NULL;
	}
		
	/**
	 * Load a documentation file into class object.
	 * 
	 * @param	string	$filepath
	 * 		Documentation fie path
	 * 
	 * @param	boolean	$rootRelative
	 * 		Inticate if the given path is root relative
	 * 
	 * @return	boolean
	 * 		True on success, False on failure
	 * 
	 * @throws	Exception
	 */
	public function loadFile($filepath, $rootRelative = TRUE)
	{
		// Load Object Documentation
		$this->parser = new DOMParser();
		$this->parser->load($filepath, $rootRelative);
		
		// Return status
		return TRUE;
	}
	
	/**
	 * Load a documentation content in string form into class object.
	 * 
	 * @param	string	$manual
	 * 		The documentation files content in xml format
	 * 
	 * @return	boolean
	 * 		True on success, False on failure
	 */
	public function loadContent($manual)
	{
		// Load Object Documentation
		$this->parser = new DOMParser();
		
		// Check Existance
		try
		{
			$this->parser->loadContent($manual, "XML");
		}
		catch(Exception $ex)
		{
			$this->parser = NULL;
			logger::log(get_class($this).": Document content not Loaded. (".$ex.")", logger::INFO);
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * Initalizes a basic form of documentation file into class object
	 * 
	 * @param	string	$library
	 * 		class library name
	 * 
	 * @param	string	$package
	 * 		class package name
	 * 
	 * @param	string	$namespace
	 * 		class namespace
	 * 
	 * @return	void
	 */
	public function create($library, $package, $namespace = "")
	{
		$this->parser = new DOMParser();
		$manual = $this->parser->create("manual");
		$this->parser->append($manual);			
		
		$this->parser->attr($manual, "library", $library);
		$this->parser->attr($manual, "package", $package);		
		
		$namespaceAttr = "\\".str_replace("::", "\\", $namespace);
		$this->parser->attr($manual, "namespace", $namespaceAttr);
		
	}
	
	/**
	 * Temporary method. Updates old files structure
	 * 
	 * @param	string	$library
	 * 		class library name
	 * 
	 * @param	string	$package
	 * 		class package name
	 * 
	 * @param	string	$namespace
	 * 		class namespace
	 * 
	 * @return	void
	 */
	public function structUpdate($library, $package, $namespace = "")
	{
		$manual = $this->parser->evaluate("//manual")->item(0);	
		
		$this->parser->attr($manual, "library", $library);
		$this->parser->attr($manual, "package", $package);		
		
		$namespaceAttr = "\\".str_replace("::", "\\", $namespace);
		$this->parser->attr($manual, "namespace", $namespaceAttr);
	}
	
	/**
	 * Return the class object's loaded documentation content in string
	 * 
	 * @return	string
	 * 		Loaded documentation content in string
	 */
	public function getDoc()
	{
		if(!(is_null($this->parser)))
		{
			return $this->parser->getXML();
		}
	}
	
	
	/**
	 * Retrieves and returns all the class info in an array.
	 * 
	 * @return	array
	 * 		title
	 * 		description
	 * 		datecreated
	 * 		daterevised
	 * 		version
	 * 		extends
	 * 		implements
	 * 		deprecated
	 * 		throws
	 */
	public function getInfo()
	{
		$info = array();
		$info['title'] = $this->parser->evaluate("//class/info/title")->item(0)->nodeValue;
		$info['description'] = $this->parser->evaluate("//class/info/description")->item(0)->nodeValue;
		$info['datecreated'] = $this->parser->evaluate("//class/info/datecreated")->item(0)->nodeValue;
		$info['daterevised'] = $this->parser->evaluate("//class/info/daterevised")->item(0)->nodeValue;
		$info['version'] = $this->parser->evaluate("//class/info/version")->item(0)->nodeValue;
		$info['extends'] = $this->parser->evaluate("//class/info/extends")->item(0)->nodeValue;
		$info['implements'] = $this->parser->evaluate("//class/info/implements")->item(0)->nodeValue;
		$info['deprecated'] = $this->parser->evaluate("//class/info/deprecated")->item(0)->nodeValue;
		
		$excepts = $this->parser->evaluate("//class/info/throws/exception");//->item(0)->nodeValue;
		$info['throws'] = "";
		foreach ($excepts as $ex)
		{
			$info['throws'] .= $ex->nodeValue.", ";
		}
		$info['throws'] = trim($info['throws'], " ,");
		
		return $info;
	}
	
	/**
	 * Retrieves and returns all the properties of the class.
	 * 
	 * @return	array
	 * 		public - array of public properties information
	 * 		protected - array of protected properties information
	 * 		private - array of private properties information
	 */
	public function getProperties()
	{
		$properties = array();
		
		// Get Public Properties
		$publicProperties = $this->parser->evaluate("//class/properties/scope[@type='public']/prop");
		foreach ($publicProperties as $prop)
			$properties['public'][$this->parser->attr($prop, "name")] = $this->getPropertyArray($prop);
		
		// Get Protected Properties
		$protectedProperties = $this->parser->evaluate("//class/properties/scope[@type='protected']/prop");
		foreach ($protectedProperties as $prop)
			$properties['protected'][$this->parser->attr($prop, "name")] = $this->getPropertyArray($prop);
		
		// Get Private Properties
		$privateProperties = $this->parser->evaluate("//class/properties/scope[@type='private']/prop");
		foreach ($privateProperties as $prop)
			$properties['private'][$this->parser->attr($prop, "name")] = $this->getPropertyArray($prop);
		
		return $properties;
	}
	
	/**
	 * Forms an array of property information and returns it.
	 * 
	 * @param	DOMElement	$prop
	 * 		The property element.
	 * 
	 * @return	array
	 * 		name
	 * 		type
	 * 		description
	 */
	public function getPropertyArray($prop)
	{
		$property = array();
		$property['name'] = $this->parser->attr($prop, "name");
		$property['type'] = $this->parser->attr($prop, "type");
		$property['description'] = $this->parser->evaluate("description", $prop)->item(0)->nodeValue;
		
		return $property;
	}
	
	/**
	 * Returns all class constants.
	 * 
	 * @return	array
	 * 		array of constant information
	 */
	public function getConstants()
	{
		$constants = array();
		
		$constantElements = $this->parser->evaluate("//class/constants/const");
		foreach ($constantElements as $const)
			$constants[] = $this->getPropertyArray($const);

		return $constants;
	}
	
	/**
	 * Returns all class methods
	 * 
	 * @return	array
	 * 		public - array of public methods information
	 * 		protected - array of protected methods information
	 * 		private - array of private methods information
	 */
	public function getMethods()
	{
		$methods = array();
		
		// Get Public Properties
		$publicMethods = $this->parser->evaluate("//class/methods/scope[@type='public']/method");
		foreach ($publicMethods as $method)
			$methods['public'][$this->parser->attr($method, "name")] = $this->getMethodArray($method);
		
		// Get Protected Properties
		$protectedMethods = $this->parser->evaluate("//class/methods/scope[@type='protected']/method");
		foreach ($protectedMethods as $method)
			$methods['protected'][$this->parser->attr($method, "name")] = $this->getMethodArray($method);
		
		// Get Private Properties
		$privateMethods = $this->parser->evaluate("//class/methods/scope[@type='private']/method");
		foreach ($privateMethods as $method)
			$methods['private'][$this->parser->attr($method, "name")] = $this->getMethodArray($method);
		
		return $methods;
	}
	
	/**
	 * Forms an array of method information and returns it.
	 * 
	 * @param	DOMElement	$meth
	 * 		The method element.
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function getMethodArray($meth)
	{
		$method = array();
		$method['name'] = $this->parser->attr($meth, "name");
		$method['returntype'] = $this->parser->attr($meth, "returntype");
		$method['description'] = $this->parser->evaluate("description", $meth)->item(0)->nodeValue;
		$method['returndescription'] = $this->parser->evaluate("returndescription", $meth)->item(0)->nodeValue;
		$method['deprecated'] = $this->parser->evaluate("deprecated", $meth)->item(0)->nodeValue;
		
		$excepts = $this->parser->evaluate("throws/exception", $meth);
		$method['throws'] = "";
		foreach ($excepts as $ex)
		{
			$method['throws'] .= $ex->nodeValue.", ";
		}
		$method['throws'] = trim($method['throws'], " ,");
		
		$params = array();
		$parameters = $this->parser->evaluate("parameters/parameter", $meth);
		foreach ($parameters as $param)
		{
			$params['name'] = $this->parser->attr($param, "name");
			$params['type'] = $this->parser->attr($param, "type");
			$params['defaultvalue'] = $this->parser->attr($param, "defaultvalue");
			$params['description'] = $this->parser->evaluate("description", $param)->item(0)->nodeValue;
			
			$method['parameters'][$params['name']] = $params;
		}
		
		return $method;
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
	 */
	public static function getClassDetails($manual)
	{
		$parser = new DOMParser();
		$parser->loadContent($manual, "XML");
		
		$details = array();
		$class = $parser->evaluate("/manual/class")->item(0);
		if (empty($class))
			return $details;
		
		$details['name'] = $parser->attr($class, "name");
		$abstract = $parser->attr($class, "abstract");
		$details['abstract'] = (empty($abstract) ? FALSE : TRUE );
		//$details['namespace'] = $parser->attr($class, "namespace");
		
		$classInfo = $parser->evaluate("info", $class)->item(0);
		if (empty($class))
			return $details;
		
		$v = $parser->evaluate("version", $classInfo)->item(0);
		$details['version'] = $parser->nodeValue($v);
		$dc = $parser->evaluate("datecreated", $classInfo)->item(0);
		$details['datecreated'] = $parser->nodeValue($dc);
		$dr = $parser->evaluate("daterevised", $classInfo)->item(0);
		$details['daterevised'] = $parser->nodeValue($dr);
		$t = $parser->evaluate("title", $classInfo)->item(0);
		$details['title'] = $parser->nodeValue($t);
		$d = $parser->evaluate("description", $classInfo)->item(0);
		$details['description'] = $parser->nodeValue($d);
		$depr = $parser->evaluate("deprecated", $classInfo)->item(0);
		$deprValue = $parser->nodeValue($depr);
		$details['deprecated'] = (empty($deprValue) ? FALSE : $deprValue);
		
		$extends = $parser->evaluate("extends", $classInfo);
		foreach ($extends as $obj)
			$details['extends'][] = $parser->nodeValue($obj);
		$implements = $parser->evaluate("implements", $classInfo);
		foreach ($implements as $obj)
			$details['implements'][] = $parser->nodeValue($obj);
	
		return $details;
	}
	
	/**
	 * Checks if the manual's structure is correct
	 * 
	 * @param	{type}	$manual
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		The validation state of the documentation
	 */
	public static function isValidDocumentation($manual)
	{
		return isset($manual) && is_string($manual) && preg_match("/^<[ \t]*class[^>]*>.*<[ \t]*\/[ \t]*class[^>]*>$/s", $manual);
	}
	
	/**
	 * Returns the documentation model's xml.
	 * 
	 * @return	string
	 * 		The model's xml string
	 */
	public static function getModel()
	{
		$xmlParser = new DOMParser();
		$xmlParser->load(resources::PATH.self::DOC_MODEL);
		
		return $xmlParser->getXML();
	}
	
	/**
	 * Update the documentation content
	 * 
	 * @param	string	$objectName
	 * 		The class object name
	 * 
	 * @param	string	$content
	 * 		Documentation content in string (xml formated)
	 * 
	 * @return	classDocumentor
	 * 		classDocumentor object
	 */
	public function update($objectName, $content = '')
	{
		if((is_null($this->parser)))
			return FALSE;
	
		/*
		*	For supporting Multiple classes in one mnual
		*
		
		// Check Class Doc existance in Manual File
		$root = $parser->evaluate("//manual")->item(0); 
		$class = $parser->evaluate("class", $root)->item(0);
		$objectNameAttr = $parser->attr($class, "objectName");
		
		if($objectNameAttr == $objectName)
		{
			// Class Exists Rewrite
		}
		else
		{
			// Create class into manual
		}
		
		*/
		
		$root = $this->parser->evaluate("//manual")->item(0);
		
		// Set inner HTML
		$this->parser->innerHTML($root, $content);				
		
		// Get datetime
		$now = time();
		
		// Set Date Created if needed
		$dateCreated = $this->parser->evaluate("class/info/datecreated", $root)->item(0);
		if (empty($dateCreated->nodeValue))
			$this->parser->nodeValue($dateCreated, $now);
		
		// For backward compatibility
		if (!is_numeric($dateCreated->nodeValue))
		{
			$dc = strtotime($dateCreated->nodeValue);
			$this->parser->nodeValue($dateCreated, $dc);
		}
		
		// Set Date Revised
		$dateRevised = $this->parser->evaluate("class/info/daterevised", $root)->item(0);
		$this->parser->nodeValue($dateRevised, $now);
						
		return $this;
		//return $this->parser->getXML();
	}
}
//#section_end#
?>