<?php
//#section#[header]
// Namespace
namespace UI\Html;

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
 * @package	Html
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "DOMPrototype");

use \DOMDocument;
use \DOMXPath;
use \ESS\Prototype\DOMPrototype;

/**
 * Document Object Model
 * 
 * Extends the DOM Prototype and is the base class for building all the html for the system pages.
 * 
 * @version	{empty}
 * @created	March 15, 2013, 15:08 (EET)
 * @revised	January 28, 2014, 16:31 (EET)
 */
class DOM extends DOMPrototype
{
	/**
	 * The page document
	 * 
	 * @type	DOMDocument
	 */
	protected static $document;
	
	/**
	 * Creates and returns a DOMElement with the specified tagName and the given attributes
	 * 
	 * @param	string	$tag
	 * 		The tag of the element.
	 * 
	 * @param	mixed	$content
	 * 		The content of the element. It can be a string or a DOMElement.
	 * 
	 * @param	string	$id
	 * 		The id attribute
	 * 
	 * @param	string	$class
	 * 		The class attribute
	 * 
	 * @param	boolean	$escapedChars
	 * 		If TRUE, the characters of the content given (in case of string) will be escaped before setting the value.
	 * 
	 * @return	DOMElement
	 * 		The DOMElement
	 */
	public static function create($tag = "div", $content = "", $id = "", $class = "", $escapedChars = FALSE)
	{
		// Check if the content is string or a DOMElement
		if (gettype($content) == "string")
		{
			if ($escapedChars)
			{
				$elem = self::$document->createElement($tag);
				$txtNode = self::$document->createTextNode($content);
				$elem->appendChild($txtNode);
			}
			else
				$elem = self::$document->createElement($tag, $content);
		}
		else
		{
			$elem = self::$document->createElement($tag);
			if (gettype($content) == "object")
				$elem->appendChild($content);
		}
		

		if (!is_null($id) && !empty($id))
			self::attr($elem, "id", $id);
			
		if (!is_null($class) && !empty($class))
			self::attr($elem, "class", $class);
		
		return $elem;
	}
	
	/**
	 * Evaluate an XPath Query
	 * 
	 * @param	string	$query
	 * 		The XPath query to be evaluated
	 * 
	 * @param	DOMElement	$context
	 * 		{description}
	 * 
	 * @return	DOMNodeList
	 * 		{description}
	 */
	public static function evaluate($query, $context = NULL)
	{
		$xpath = new DOMXPath(self::$document);
		return $xpath->evaluate($query, $context);
	}
	
	/**
	 * Find an element by id (using the evaluate function).
	 * 
	 * @param	string	$id
	 * 		The id of the element
	 * 
	 * @param	string	$nodeName
	 * 		The node name of the element. If not set, it searches for all nodes (*).
	 * 
	 * @return	mixed
	 * 		Returns the DOMElement or NULL if it doesn't exist.
	 */
	public static function find($id, $nodeName = "*")
	{
		$nodeName = (empty($nodeName) ? "*" : $nodeName);
		$q = "//".$nodeName."[@id='$id']";
		$list = self::evaluate($q);
		
		if ($list->length > 0)
			return $list->item(0);
			
		return NULL;
	}
	
	/**
	 * Create an html comment and returns the element.
	 * 
	 * @param	string	$content
	 * 		The comment content.
	 * 
	 * @return	DOMNode
	 * 		{description}
	 */
	public static function comment($content)
	{
		return self::$document->createComment($content);
	}
	
	/**
	 * Imports a node to this document. Returns the new node.
	 * 
	 * @param	DOMNode	$node
	 * 		The node to be imported
	 * 
	 * @param	boolean	$deep
	 * 		Defines whether all the children of this node will be imported
	 * 
	 * @return	DOMNode
	 * 		{description}
	 */
	public static function import($node, $deep = TRUE)
	{
		if (empty($node))
			return NULL;
		return self::$document->importNode($node, $deep);
	}
	
	/**
	 * Returns the HTML form of the document
	 * 
	 * @param	boolean	$format
	 * 		Indicates whether to format the output.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getHTML($format = FALSE)
	{
		self::$document->formatOutput = $format;
		return self::$document->saveHTML();
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
		self::$document->formatOutput = $format;
		return self::$document->saveXML();
	}
	
	/**
	 * Initializes and clears the  DOMDocument
	 * 
	 * @return	void
	 */
	public static function initialize()
	{
		self::$document = new DOMDocument("1.0", "UTF-8");
	}
	
	/**
	 * Clears the DOMDocument
	 * 
	 * @return	void
	 */
	public static function clear()
	{
		$root = self::evaluate("/")->item(0);
		foreach ($root->childNodes as $child)
			self::replace($child, NULL);
	}
	
	/**
	 * Get the DOMDocument
	 * 
	 * @return	DOMDocument
	 * 		{description}
	 */
	public static function document()
	{
		return self::$document;
	}
}
//#section_end#
?>