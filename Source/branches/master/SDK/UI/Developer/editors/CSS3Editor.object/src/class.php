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
 * @copyright	Copyright (C) 2016 Drovio. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("UI", "Developer", "codeMirror");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");

use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\filesystem\fileManager;
use \UI\Developer\codeMirror;
use \UI\Html\DOM;
use \UI\Html\HTML;

/**
 * CSS3 Editor
 * 
 * A complete CSS3 editor.
 * It allows the user to edit a css file. It supports having multiple media queries.
 * 
 * @version	0.1-3
 * @created	July 16, 2015, 13:05 (CEST)
 * @updated	April 3, 2016, 18:29 (CEST)
 */
class CSS3Editor extends UIObjectPrototype
{
	/**
	 * Name of the css editing area.
	 * 
	 * @type	string
	 */
	private $cssName;
	
	/**
	 * Create a new instance of the CSS3Editor.
	 * 
	 * @param	string	$cssName
	 * 		The name of the css editing area.
	 * 		This is used for the form submit.
	 * 
	 * @return	void
	 */
	public function __construct($cssName = "cssEditor")
	{
		// Initialize object properties
		$this->cssName = $cssName;
	}
	
	/**
	 * Builds the CSS3Editor object.
	 * 
	 * @param	string	$css
	 * 		The css content.
	 * 
	 * @param	string	$id
	 * 		The object's id.
	 * 		It is empty by default.
	 * 
	 * @param	string	$class
	 * 		The object's class.
	 * 		It is empty by default.
	 * 
	 * @return	CSS3Editor
	 * 		The CSS3Editor object.
	 */
	public function build($css = "", $id = "", $class = "")
	{
		// Normalize css
		$css = trim($css);
		
		// Create holder
		$id = (empty($id) ? "css3ed_".mt_rand() : $id);
		$holder = DOM::create("div", "", $id, "css3Editor".(empty($class) ? "" : " ".$class));
		$this->set($holder);
		
		// Build code editor
		$cm = new codeMirror($type = codeMirror::CSS, $this->cssName);
		$cssEditorElement = $cm->build($css, "", "css3Editor_cm")->get();
		$this->append($cssEditorElement);
		
		return $this;
	}
}
//#section_end#
?>