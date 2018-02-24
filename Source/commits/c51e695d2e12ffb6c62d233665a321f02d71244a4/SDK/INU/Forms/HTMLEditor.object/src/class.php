<?php
//#section#[header]
// Namespace
namespace INU\Forms;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Forms", "formFactory");
importer::import("INU", "Developer", "codeEditor");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;
use \UI\Forms\formFactory;
use \INU\Developer\codeEditor;

class HTMLEditor extends UIObjectPrototype
{

	const HTML_EDITOR = 1;
	const HTML_DESIGNER = 2;

	public function build($content = "", $name = "htmlContent", $type = self::HTML_EDITOR)
	{
		//create htmlEditor container and append to document
		$id = 'htmlEd_'.rand();
		$editorWrapper = DOM::create('div', '', $id, 'htmlEditor noDisplay');
		//$editorWrapper = DOM::create('div', '', $id, 'htmlEditor');
		DOM::attr($editorWrapper, 'data-type', "html");
		
		$this->set($editorWrapper);
		// Toolbars
		switch ($type)
		{
			case self::HTML_DESIGNER : 
				DOM::append($editorWrapper, $this->getDesignerToolsArea());
				break;
			default:
				DOM::append($editorWrapper, $this->getToolsArea());
		}
		
		// Editor
		DOM::append($editorWrapper, $this->getEditorArea($content, $name, $type != self::HTML_DESIGNER));
		
		// Footer
		DOM::append($editorWrapper, $this->getFooterArea());
		
		// StylingArea
		DOM::append($editorWrapper, $this->getStylingOptions());
		
		return $this;
	}
	
	private function getToolsArea()
	{
		// Container for the toolbars
		$toolbarContainer = DOM::create('div', '', '', 'htmlToolbars');
		
		// HTML Toolbar
		DOM::append($toolbarContainer, $this->getHTMLToolbar());
		
		// Elements Toolbar
		$ebar = DOM::create('div', '', '', 'htmlElementsBar');
		DOM::append($toolbarContainer, $ebar);
		
		// DIV button
		$divTool = DOM::create('div', 'DIV', '', 'htmlTool divElement');
		DOM::attr($divTool, "draggable", "true");
		DOM::append($ebar, $divTool);
		
		// SPAN button
		$spanTool = DOM::create('div', 'SPAN', '', 'htmlTool spanElement');
		DOM::attr($spanTool, "draggable", "true");
		DOM::append($ebar, $spanTool);
		
		// P button
		$paragraphTool = DOM::create('div', 'P', '', 'htmlTool paragraphElement');
		DOM::attr($paragraphTool, "draggable", "true");
		DOM::append($ebar, $paragraphTool);
		
		// A button
		$anchorTool = DOM::create('div', 'A', '', 'htmlTool anchorElement');
		DOM::attr($anchorTool, "draggable", "true");
		DOM::append($ebar, $anchorTool);
		
		return $toolbarContainer;
	}
	
	private function getDesignerToolsArea()
	{
		// Container for the toolbars
		$toolbarContainer = DOM::create('div', '', '', 'htmlToolbars');
		
		// HTML Toolbar
		DOM::append($toolbarContainer, $this->getHTMLToolbar());
		
		// Elements Toolbar
		$ebar = DOM::create('div', '', '', 'htmlElementsBar');
		DOM::append($toolbarContainer, $ebar);
		
		// DIV button
		$divTool = DOM::create('div', 'DIV', '', 'htmlTool divElement');
		DOM::attr($divTool, "draggable", "true");
		DOM::append($ebar, $divTool);
		
		// DIV Editable button
		$divEditableTool = DOM::create('div', 'DIV', '', 'htmlTool divElement');
		DOM::attr($divEditableTool, "draggable", "true");
		DOM::attr($divEditableTool, "data-editable", "editable");
		DOM::append($ebar, $divEditableTool);
		
		// DIV Global button
		$divGlobalTool = DOM::create('div', 'DIV', '', 'htmlTool divElement');
		DOM::attr($divGlobalTool, "draggable", "true");
		DOM::attr($divGlobalTool, "data-global", "global");
		DOM::append($ebar, $divGlobalTool);
		
		return $toolbarContainer;
	}
	
	private function getHTMLToolbar()
	{
		// HTML Toolbar
		$tbar = DOM::create('div', '', '', 'htmlBar');
		
		// Toggle View Button
		$toggle = DOM::create('div', 'Toggle View', '', 'htmlTool toggleView');
		DOM::append($tbar, $toggle);
		
		return $tbar;
	}
	
	private function getEditorArea($content, $name, $editable = TRUE)
	{
		// Container of the code / html areas.
		$edContainer = DOM::create('div', '', '', 'htmlWrapper');
		
		// Code area
		$codeSection = DOM::create('div', '', '', 'codeSection');
		DOM::append($edContainer, $codeSection);
		
		$htmlCode = new codeEditor();
		$xmlEditor = $htmlCode->build("xml", $content, $name, $editable)->get();
		
		DOM::append($codeSection, $xmlEditor);
		
		// Html area
		$htmlSection = DOM::create('div', '', '', 'htmlSection');
		if ($editable)
			DOM::attr($htmlSection, "contentEditable", "true");
		DOM::append($edContainer, $htmlSection);

		return $edContainer;
	}
	
	private function getFooterArea()
	{
		// Container for the footer
		$footerContainer = DOM::create('div', '', '', 'htmlFooter');
		
		$fbar = DOM::create('div', '', '', 'htmlPath');
		DOM::append($footerContainer, $fbar);
		
		return $footerContainer;
	}
	
	private function getStylingOptions()
	{
		$background = DOM::create('div', '', '', 'stylingOptionsWrapper');
		
		$styling = DOM::create('div', '', '', 'stylingOptions');
		DOM::append($background, $styling);
		
		$catalog = DOM::create('div', '', '', 'elementCatalogue');
		DOM::append($styling, $catalog);
		
		$options = DOM::create('div', '', '', 'elementOptions');
		DOM::append($styling, $options);
		
		$formFactory = new formFactory();
		// ID
		$idOption = DOM::create('div', '', '', 'idOption');
		DOM::append($options, $idOption);
		$idLabel = $formFactory->getLabel("Identifier:", $for = "idOption");
		$idInput = $formFactory->getInput($type = "text", $name = "idOption", $value = "", $class = "", $autofocus = FALSE);
		DOM::attr($idInput, "id", "idOption");
		DOM::attr($idInput, "placeholder", "Unique ID among elements");
		DOM::append($idOption, $idLabel);
		DOM::append($idOption, $idInput);
		
		// CLASS
		$classOption = DOM::create('div', '', '', 'classOption');
		DOM::append($options, $classOption);
		$classLabel = $formFactory->getLabel("Styling Classes:", $for = "classOption");
		$classInput = $formFactory->getInput($type = "text", $name = "classOption", $value = "", $class = "", $autofocus = FALSE);
		DOM::attr($classInput, "id", "classOption");
		DOM::attr($classInput, "placeholder", "Space separated CSS class list");
		DOM::append($classOption, $classLabel);
		DOM::append($classOption, $classInput);
		
		
		return $background;
	}
	
}
//#section_end#
?>