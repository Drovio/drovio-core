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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("INU", "Developer", "codeEditor");

use \INU\Developer\codeEditor as oldCodeEditor;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	0.1-1
 * @created	April 2, 2014, 14:15 (EEST)
 * @revised	September 8, 2014, 16:55 (EEST)
 */
class codeEditor extends oldCodeEditor {}
/*
lass codeEditor extends UIObjectPrototype
{
	const PHP = "php";
	const XML = "xml";
	const CSS = "css";
	const JS = "js";
	const SQL = "sql";
	const NO_TYPE = "generic";
	public function build($type = self::NO_TYPE, $content = "", $name = "code", $id = "", $editable = TRUE)
	{
		// Create code editor holder
		$id = (empty($id) ? "ce_".rand() : $id);
		$editorWrapper = DOM::create('div', '', $id, 'codeEditor');
		DOM::attr($editorWrapper, 'data-type', $type);
		$this->set($editorWrapper);
		
		// Create main container
		$ce_main = DOM::create("div", "", "", "ce_main");
		DOM::append($editorWrapper, $ce_main);
		
		// Create script lines container
		$scriptLines = DOM::create('div', '', '', 'ce_lines');
		DOM::append($ce_main, $scriptLines);
		
		// Create script wrapper
		$scriptWrapper = DOM::create('div', '', '', 'scriptWrapper');
		DOM::append($ce_main, $scriptWrapper);
		
		// Presentation area
		$presentationArea = DOM::create('div', '', 'presentationArea', 'presentationArea');
		if ($type == "css")
			DOM::appendAttr($presentationArea, "class", $type);
		DOM::append($scriptWrapper, $presentationArea);
		
		// Add dummy line
		$line = DOM::create("div", "", "", "codeLine");
		DOM::append($presentationArea, $line);
		$number = DOM::create("span", "1", "", "lineNumber");
		DOM::append($line, $number);
		$code = DOM::create("pre", "Test Code", "", "code");
		DOM::append($line, $code);
		
		// Actual script area
		$scriptArea = DOM::create('div', '', 'scriptArea', 'scriptArea');
		if ($editable)
			DOM::attr($scriptArea, 'contenteditable', 'true');
		DOM::append($scriptWrapper, $scriptArea);
		
		// Add code to script area
		$fLine = DOM::create('div', nl2br($content), '', 'code');
		DOM::append($scriptArea, $fLine);
		
		// Add textarea form control
		$textarea = DOM::create('textarea', $content, 'txt', 'contentArea');
		$txtName = (empty($name) ? 'wideContent' : $name);
		DOM::attr($textarea, 'name', $txtName);
		DOM::attr($textarea, 'style', 'display:none;');
		DOM::append($scriptWrapper, $textarea);
		
		
		// Add toolbar
		$this->buildToolbar();
		
		return $this;
	}
	private function buildToolbar()
	{
		// Create toolbar container
		$toolbar = DOM::create("div", "", "", "ce_toolbar");
		DOM::append($this->get(), $toolbar);
		
		
		// Add toolbar information
		
		// Add settings button
		
		// Add code map buttonå
	}
	private function addCodeMap()
	{
		// Line map
		$lineMapTool = DOM::create('div', '', '', 'lineMapTool');
		DOM::append($editorWrapper, $lineMapTool);
		$lineMapSpan = DOM::create('span', 'Code Map');
		DOM::append($lineMapTool, $lineMapSpan);
		// Line map Context
		$lineMap = DOM::create('div', '', '', 'lineMap');
		DOM::append($lineMapTool, $lineMap);
		// ___ Toolbar
		$lineMapTlbr = DOM::create('div', '', '', 'lineMapToolbar');
		DOM::append($lineMap, $lineMapTlbr);
		
		$ffactory = new formFactory();
		$input = $ffactory->getInput("text", "", "", "lineMapSearch", FALSE);
		DOM::attr($input, "placeholder", "Search Code Map...");
		DOM::attr($input, "title", "All space separated keywords must be contained in an entry.");
		DOM::append($lineMapTlbr, $input);
		$typeButton = DOM::create('div', 'Active Line', '', 'lineMapButton activeLine');
		DOM::append($lineMapTlbr, $typeButton);
		$typeButton = DOM::create('div', 'Type', '', 'lineMapButton sortType selected');
		DOM::append($lineMapTlbr, $typeButton);
		$flowButton = DOM::create('div', 'Flow', '', 'lineMapButton sortFlow');
		DOM::append($lineMapTlbr, $flowButton);
		// ___ Type view
		$typeView = DOM::create('div', '', '', 'byType selected');
		DOM::append($lineMap, $typeView);
		$typedCodeMap = $this->getTypedCodeMap($type);
		DOM::append($typeView, $typedCodeMap);
		// ___ Flow view
		$flowView = DOM::create('div', '', '', 'byFlow');
		DOM::append($lineMap, $flowView);
	}
	private function getTypedCodeMap($type)
	{
		$acc = new accordion();
		$acc->build();
		
		$xmlParser = new DOMParser();
		$typeGroups = self::getParsersInfo($type, "codemap");
		
		if (empty($typeGroups))
			return $acc->get();
		
		$xmlParser->loadContent($typeGroups, "XML");
		$groups = $xmlParser->evaluate("/codemap/group");
		$selected = ($groups->length == 1 ? TRUE : FALSE);
		
		foreach ($groups as $group)
		{
			$id = "cm".$type."accs".rand();
			$class = $xmlParser->attr($group, "name");
			$title = $xmlParser->attr($group, "title");
			$head = DOM::create("span", $title);
			$content = DOM::create("div", "", "", $class);
			$acc->addSlice($id, $head, $content, $selected);
		}
		
		return $acc->get();
	}
	public static function getParsersInfo($parser = "", $content = "")
	{
		$xmlParser = new DOMParser();
		
		$path = NULL;
		if (!empty($parser) && !empty($content))
			$path = resources::PATH."/SDK/codeEditor/".$parser."/".$content.".xml";
		if ($content == "list")
			$path = resources::PATH."/SDK/codeEditor/parsers.xml";
		
		if (!is_null($path) && file_exists(systemRoot.$path))
			$xmlParser->load($path);
		
		return $xmlParser->getXML();
	}
	public static function getWorker($name)
	{
		if (!is_string($name))
			return;
		
		$path = resources::PATH."/SDK/codeEditor/workers/".$name.".js";
		
		if (!file_exists(systemRoot.$path))
			return;
		
		return fileManager::get_contents(systemRoot.$path);
	}
}
*/
//#section_end#
?>