<?php
//#section#[header]
// Namespace
namespace API\Resources;

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
 * @package	Resources
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "profiler::logger");
importer::import("API", "Security", "accessControl");
importer::import("ESS", "Prototype", "DOMPrototype");

use \DOMDocument;
use \DOMXPath;
use \API\Developer\profiler\logger;
use \API\Security\accessControl;
use \ESS\Prototype\DOMPrototype;

/**
 * DOMParse
 * 
 * Class for parsing xml files
 * 
 * @version	{empty}
 * @created	March 8, 2013, 10:42 (EET)
 * @revised	February 14, 2014, 10:50 (EET)
 */
class DOMParser extends DOMPrototype
{
	/**
	 * Describes the annotation of a document (HTML)
	 * 
	 * @type	string
	 */
	const TYPE_HTML = 'HTML';
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const TYPE_XML = 'XML';

	/**
	 * The document of the file being parsed
	 * 
	 * @type	DOMDocument
	 */
	protected $document;
	
	/**
	 * The filepath used to load the document.
	 * 
	 * @type	string
	 */
	private $filePath;
	
	/**
	 * Create a new instance of a DOMParser
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$this->document = new DOMDocument("1.0", "UTF-8");
	}
	
	/**
	 * Creates and returns a DOMElement with the specified tagName and the specified attributes
	 * 
	 * @param	string	$tag
	 * 		The element's tag
	 * 
	 * @param	mixed	$content
	 * 		The content of the element. It can be a string or a DOMElement.
	 * 
	 * @param	string	$id
	 * 		The element's id
	 * 
	 * @param	string	$class
	 * 		The element's class.
	 * 		Mostly for html elements
	 * 
	 * @return	DOMElement
	 * 		The DOMElement.
	 */
	public function create($tag = "div", $content = "", $id = "", $class = "")
	{
		// Check if the content is string or a DOMElement
		if (gettype($content) == "string")
		{
			$elem = $this->document->createElement($tag);
			$txtNode = $this->document->createTextNode($content);
			$elem->appendChild($txtNode);
		}
		else
		{
			$elem = $this->document->createElement($tag);
			if (gettype($content) == "object")
				$elem->appendChild($content);
		}
		
		if (!is_null($id) && !empty($id))
			$this->attr($elem, "id", $id);
			
		if (!is_null($class) && !empty($class))
			$this->attr($elem, "class", $class);
		
		return $elem;
	}
	
	/**
	 * Evaluate an XPath Query
	 * 
	 * @param	string	$query
	 * 		The XPath query to be evaluated
	 * 
	 * @param	DOMElement	$context
	 * 		The base to act as root for the query
	 * 
	 * @return	mixed
	 * 		The NodeList or false.
	 */
	public function evaluate($query, $context = NULL)
	{
		$xpath = new DOMXPath($this->document);
		return $xpath->evaluate($query, $context);
	}
	
	/**
	 * Evaluate an XPath Query
	 * 
	 * @param	string	$query
	 * 		The XPath query to be evaluated
	 * 
	 * @param	DOMElement	$context
	 * 		The base to act as root for the query
	 * 
	 * @return	mixed
	 * 		Returns a DOMNodeList containing all nodes matching the given XPath expression. An empty DOMNodeList, if XPath expression dont match anything.
	 * 		
	 * 		Returns FALSE If the expression is malformed or the context is invalid.
	 */
	public function query($query, $context = NULL)
	{
		$xpath = new DOMXPath($this->document);
		return $xpath->query($query, $context);
	}
	
	/**
	 * Find an element by id
	 * 
	 * @param	string	$id
	 * 		The id of the element
	 * 
	 * @param	string	$nodeName
	 * 		The nodename of the element. If not given, it searches all elements (*).
	 * 
	 * @return	mixed
	 * 		Returns the node or NULL if the node doesn't exist.
	 */
	public function find($id, $nodeName = "*")
	{
		$nodeName = (empty($nodeName) ? "*" : $nodeName);
		$q = "//".$nodeName."[@id='$id']";
		$list = $this->evaluate($q);
		
		if ($list->length > 0)
			return $list->item(0);
			
		return NULL;
	}
	
	/**
	 * Creates an xml comment tag
	 * 
	 * @param	string	$content
	 * 		The comment content
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function comment($content)
	{
		return $this->document->createComment($content);
	}
	
	/**
	 * Imports a node to this document
	 * 
	 * @param	DOMElement	$node
	 * 		The node to import
	 * 
	 * @param	boolean	$deep
	 * 		Defines whether all the children of this node will be imported
	 * 
	 * @return	void
	 */
	public function import($node, $deep = TRUE)
	{
		if (is_null($node))
			return NULL;
		return $this->document->importNode($node, $deep);
	}
	
	/**
	 * Returns the HTML form of the document
	 * 
	 * @param	boolean	$format
	 * 		Indicates whether to format the output.
	 * 
	 * @return	strubg
	 * 		{description}
	 */
	public function getHTML($format = FALSE)
	{
		$this->document->formatOutput = $format;
		return $this->document->saveHTML();
	}
	
	/**
	 * Returns the XML form of the document
	 * 
	 * @param	boolean	$format
	 * 		Indicates whether to format the output.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getXML($format = FALSE)
	{
		$this->document->formatOutput = $format;
		return $this->document->saveXML();
	}
	
	/**
	 * Loads an existing xml document
	 * 
	 * @param	string	$path
	 * 		The document path
	 * 
	 * @param	boolean	$rootRelative
	 * 		Indicates whether the path must be normalized in order to be root relative.
	 * 
	 * @param	boolean	$preserve
	 * 		Indicates whether the parser will preserve whitespaces during load
	 * 
	 * @return	DOMDocument
	 * 		{description}
	 * 
	 * @throws	Exception
	 */
	public function load($path, $rootRelative = TRUE, $preserve = FALSE)
	{
		// Check api functionality
		if (!accessControl::internalCall())
			return FALSE;

		// Normalizes path
		$filePath = ($rootRelative ? systemRoot : "").$path;

		// Check the file existance
		if (file_exists($filePath))
		{
			// Save filepath
			$this->filePath = $filePath;
			
			// Initialize Document
			$this->document = new DOMDocument("1.0", "UTF-8");
			$this->document->preserveWhiteSpace = $preserve;
			$this->document->load($this->filePath);
		}
		else
			throw new Exception("File '".$filePath."' doesn't exist for loading...");
	
		return $this->document;
	}
	
	/**
	 * Load source as HTML or XML
	 * 
	 * @param	string	$source
	 * 		The source code
	 * 
	 * @param	string	$type
	 * 		The type of the content given
	 * 
	 * @param	boolean	$preserve
	 * 		Indicates whether the parser will preserve whitespaces during load.
	 * 
	 * @return	void
	 */
	public function loadContent($source, $type = self::TYPE_HTML, $preserve = FALSE)
	{
		// Initialize document
		$this->document = new DOMDocument("1.0", "UTF-8");
		$this->document->preserveWhiteSpace = $preserve;
		
		// Load content
		if ($type == self::TYPE_HTML)
			$this->document->loadHTML($source);
		else
			$this->document->loadXML($source);
	}
	
	/**
	 * Saves the file in the given filepath.
	 * 
	 * @param	string	$path
	 * 		The filepath
	 * 
	 * @param	string	$fileName
	 * 		The filename
	 * 
	 * @param	boolean	$format
	 * 		Indicator whether the parser will save formated xml or unformatted.
	 * 
	 * @return	boolean
	 * 		True on success (logs bytes written)
	 * 		False elsewhere
	 */
	public function save($path, $fileName = "", $format = FALSE)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		// Update file path
		$savePath = $path.(empty($fileName) ? "" : "/".$fileName);
		$this->filePath = $savePath;
		
		// Save file
		$this->document->formatOutput = $format;
		$saveFlag = $this->document->save($this->filePath);
		if (is_bool($saveFlag))
		{
			// Log
			logger::log("Error writing to '".$this->filePath."'.", logger::ERROR);
			
			// save error
			return $saveFlag;
		}
		else
			return TRUE;
	}
	
	/**
	 * Updates the file loaded before by the load() function.
	 * 
	 * @param	boolean	$format
	 * 		Indicator whether the parser will save formated xml or not.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($format = FALSE)
	{
		if (empty($this->filePath))
			return FALSE;
		
		return $this->save($this->filePath, "", $format);
	}
}
//#section_end#
?>