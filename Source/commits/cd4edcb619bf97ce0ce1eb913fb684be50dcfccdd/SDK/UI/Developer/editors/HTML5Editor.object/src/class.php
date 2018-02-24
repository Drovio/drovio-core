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
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Developer", "codeEditor");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Navigation", "navigationBar");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Developer\codeEditor;
use \UI\Html\DOM;
use \UI\Navigation\navigationBar;

/**
 * HTML5 Editor.
 * 
 * A complete WYSIWYG HTML5 Editor.
 * It allows the user to edit an HTML content with all the available features of HTML5.
 * 
 * @version	0.1-2
 * @created	July 16, 2015, 14:04 (EEST)
 * @updated	July 16, 2015, 14:27 (EEST)
 */
class HTML5Editor extends UIObjectPrototype
{
	/**
	 * Name of the html editing area.
	 * 
	 * @type	string
	 */
	private $htmlName;
	
	/**
	 * Create a new instance of the HTML5Editor.
	 * 
	 * @param	string	$htmlName
	 * 		The name of the html editing area.
	 * 		This is used for the form submit.
	 * 
	 * @return	void
	 */
	public function __construct($htmlName = "htmlEditor")
	{
		// Set html input name
		$this->htmlName = $htmlName;
	}
	
	/**
	 * Builds the HTML editor as a UI object.
	 * 
	 * @param	string	$html
	 * 		The html content.
	 * 
	 * @param	string	$id
	 * 		The object's id.
	 * 		It is empty by default.
	 * 
	 * @param	string	$class
	 * 		The object's class.
	 * 		It is empty by default.
	 * 
	 * @return	HTML5Editor
	 * 		The HTML5Editor object.
	 */
	public function build($html = "", $id = "", $class = "")
	{
		// Normalize html
		$html = trim($html);
		
		// Create cssEditor holder
		$id = (empty($id) ? "html5ed_".mt_rand() : $id);
		$holder = DOM::create("div", "", $id, "html5Editor".(empty($class) ? "" : " ".$class));
		$this->set($holder);
		
		// Create and add navigation toolbar
		$navBar = new navigationBar();
		$htmlNavigationBar = $navBar->build($dock = navigationBar::TOP, $holder)->get();
		$this->append($htmlNavigationBar);
		
		// Add html code view item
		$codeView = DOM::create("span", "", "", "htmlTool toggleView");
		$navBar->insertToolbarItem($codeView);
		
		// Add html preview container
		$previewContainer = $this->getPreviewContainer($html);
		$this->append($previewContainer);
		
		// Return htmlEditor object
		return $this;
	}
	
	/**
	 * Create the preview container which includes the front and the back side of the editor.
	 * 
	 * @param	string	$html
	 * 		The html content.
	 * 
	 * @return	DOMElement
	 * 		The preview container element.
	 */
	private function getPreviewContainer($html)
	{
		// Create preview container
		$htmlPreviewContainer = DOM::create("div", "", "", "htmlPreviewContainer");
		
		// Create codeEditor container
		$htmlCodePanel = DOM::create("div", "", "", "htmlCodePanel noDisplay");
		DOM::append($htmlPreviewContainer, $htmlCodePanel);
		
		// Create htmlEditor codeEditor
		$htmlEditor = new codeEditor();
		$htmlEditorElement = $htmlEditor->build($type = codeEditor::XML, $html, $this->htmlName)->get();
		DOM::append($htmlCodePanel, $htmlEditorElement);
		
		// HTML preview wrapper
		$previewPanel = DOM::create("div", "", "", "previewPanel");
		DOM::attr($previewPanel, "contenteditable", TRUE);
		DOM::innerHTML($previewPanel, $html);
		DOM::append($htmlPreviewContainer, $previewPanel);
		
		// Return container
		return $htmlPreviewContainer;
	}
}
//#section_end#
?>