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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
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
 * HTML Editor.
 * 
 * A complete WYSIWYG HTML Editor.
 * It allows the user to edit an HTML content with all the available features of HTML5.
 * 
 * @version	1.0-1
 * @created	September 8, 2014, 16:09 (EEST)
 * @updated	May 2, 2015, 10:51 (EEST)
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
	 * The html content.
	 * 
	 * @type	string
	 */
	private $html;
	
	/**
	 * Create a new instance of the HTMLEditor.
	 * 
	 * @param	string	$htmlName
	 * 		Name of the html editing area.
	 * 
	 * @return	void
	 */
	public function __construct($htmlName = "htmlEditor")
	{
		// Set html input name
		$this->htmlName = $htmlName;
	}
	
	/**
	 * Buildes the UIObject (HTMLEditor).
	 * 
	 * @param	string	$html
	 * 		The html content.
	 * 
	 * @param	string	$id
	 * 		The object's id.
	 * 
	 * @param	string	$class
	 * 		The object's class.
	 * 
	 * @return	HTMLEditor
	 * 		The HTMLEditor object.
	 */
	public function build($html = "", $id = "", $class = "")
	{
		// Set html and css
		$this->html = trim($html);
		
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
		$previewContainer = $this->getPreviewContainer();
		$this->append($previewContainer);
		
		// Return htmlEditor object
		return $this;
	}
	
	/**
	 * Create the preview container which includes the front and the back side of the editor.
	 * 
	 * @return	DOMElement
	 * 		The preview container element.
	 */
	private function getPreviewContainer()
	{
		// Create preview container
		$previewContainer = DOM::create("div", "", "", "previewContainer");
		
		// Create codeEditor container
		$htmlCodeContainer = DOM::create("div", "", "", "modelEditor noDisplay");
		DOM::append($previewWrapper, $htmlCodeContainer);
		// Create htmlEditor codeEditor
		$htmlEditor = new codeEditor();
		$htmlEditorElement = $htmlEditor->build($type = codeEditor::XML, $this->html, $this->htmlName)->get();
		DOM::append($htmlCodeContainer, $htmlEditorElement);
		
		// HTML preview wrapper
		$previewWrapper = DOM::create("div", "", "", "previewWrapper");
		DOM::append($previewContainer, $previewWrapper);
		
		// Return container
		return $previewContainer;
	}
}
//#section_end#
?>