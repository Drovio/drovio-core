<?php
//#section#[header]
// Namespace
namespace UI\Developer;

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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("UI", "Forms", "Form");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Prototype", "UIObjectPrototype");

use \UI\Forms\Form;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Prototype\UIObjectPrototype;

/**
 * CodeMirror object
 * 
 * This class creates a code mirror html instance which will be initialized
 * 
 * @version	1.0-5
 * @created	October 12, 2015, 16:07 (BST)
 * @updated	December 5, 2015, 13:44 (GMT)
 */
class codeMirror extends UIObjectPrototype
{
	/**
	 * php code editor type.
	 * 
	 * @type	string
	 */
	const PHP = "php";
	
	/**
	 * xml code editor type.
	 * 
	 * @type	string
	 */
	const XML = "xml";
	
	/**
	 * css code editor type.
	 * 
	 * @type	string
	 */
	const CSS = "css";
	
	/**
	 * js code editor type.
	 * 
	 * @type	string
	 */
	const JS = "js";
	
	/**
	 * sql code editor type.
	 * 
	 * @type	string
	 */
	const SQL = "sql";
	
	/**
	 * no specific code editor type.
	 * 
	 * @type	string
	 */
	const NO_PARSER = "";
	
	/**
	 * The editor type (syntax language).
	 * 
	 * @type	string
	 */
	private $type = self::NO_PARSER;
	
	/**
	 * The editor name for saving the code.
	 * 
	 * @type	string
	 */
	private $name;
	
	/**
	 * Read only flag.
	 * 
	 * @type	boolean
	 */
	private $readOnly;
	
	/**
	 * Create a codeMirror instance.
	 * 
	 * @param	string	$type
	 * 		The code type.
	 * 		See class constants.
	 * 		Default value is no parser.
	 * 
	 * @param	string	$name
	 * 		The name of the editor.
	 * 		This is the name of the textarea that will be used in the form posting.
	 * 		Default value is 'wideContent'.
	 * 
	 * @param	boolean	$readOnly
	 * 		Read only mode for the editor.
	 * 		Default value is false.
	 * 
	 * @return	void
	 */
	public function __construct($type = self::NO_PARSER, $name = "wideContent", $readOnly = FALSE)
	{
		$this->type = (empty($type) ? self::NO_PARSER : $type);
		$this->name = (empty($name) ? 'wideContent' : $name);
		$this->readOnly = $readOnly;
	}
	
	/**
	 * Builds a code mirror instance.
	 * 
	 * @param	string	$code
	 * 		The initial code.
	 * 
	 * @param	string	$id
	 * 		The element id.
	 * 
	 * @param	string	$class
	 * 		The element extra class.
	 * 
	 * @return	codeMirror
	 * 		The codeMirror object.
	 */
	public function build($code = "", $id = "", $class = "")
	{
		// Create the code mirror container
		$id = (empty($id) ? "cm".mt_rand() : $id);
		$codeMirrorHolder = DOM::create("div", "", $id, "code-mirror-holder initialize");
		$this->set($codeMirrorHolder);
		
		// Add extra class (if any)
		HTML::addClass($codeMirrorHolder, $class);
		
		// Set type
		DOM::data($codeMirrorHolder, "cmtype", $this->type);
		
		// Set read-only attribute
		DOM::data($codeMirrorHolder, "cmro", $this->readOnly);
		
		// Create code text area
		$textarea = DOM::create('textarea', $code, $id.'-cm-textarea', 'code-mirror-textarea');
		DOM::attr($textarea, 'name', $this->name);
		DOM::append($codeMirrorHolder, $textarea);
		
		// return code mirror object
		return $this;
	}
}
//#section_end#
?>