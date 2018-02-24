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
importer::import("UI", "Prototype", "UIObjectPrototype");

use \UI\Forms\Form;
use \UI\Html\DOM;
use \UI\Prototype\UIObjectPrototype;

/**
 * CodeMirror object
 * 
 * This class creates a code mirror html instance which will be initialized
 * 
 * @version	0.1-1
 * @created	October 12, 2015, 18:07 (EEST)
 * @updated	October 12, 2015, 18:07 (EEST)
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
	 * Builds a code mirror instance.
	 * 
	 * @param	string	$type
	 * 		The code type.
	 * 		See class constants.
	 * 		Default value is no parser.
	 * 
	 * @param	string	$code
	 * 		The initial code.
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
	 * @return	codeMirror
	 * 		The codeMirror object.
	 */
	public function build($type = self::NO_PARSER, $code = "", $name = "wideContent", $readOnly = FALSE)
	{
		// Create the code mirror container
		$id = "cm".mt_rand();
		$codeMirrorHolder = DOM::create("div", "", $id, "code-mirror-holder initialize");
		$this->set($codeMirrorHolder);
		
		// Set type
		$type = (empty($type) ? "php" : $type);
		DOM::data($codeMirrorHolder, "cmtype", $type);
		
		// Set read-only attribute
		DOM::data($codeMirrorHolder, "cmro", $readOnly);
		
		// Create code text area
		$textarea = DOM::create('textarea', $code, $id.'-cm-textarea', 'code-mirror-textarea');
		$name = (empty($name) ? 'wideContent' : $name);
		DOM::attr($textarea, 'name', $name);
		DOM::attr($textarea, 'style', "display: none;");
		DOM::append($codeMirrorHolder, $textarea);
		
		// return code mirror object
		return $this;
	}
}
//#section_end#
?>