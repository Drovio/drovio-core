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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Navigation", "navigationBar");
importer::import("UI", "Presentation", "togglers/accordion");
importer::import("UI", "Forms", "Form");
importer::import("DEV", "Resources", "paths");

use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \UI\Html\DOM;
use \UI\Navigation\navigationBar;
use \UI\Presentation\togglers\accordion;
use \UI\Forms\Form;
use \DEV\Resources\paths;

/**
 * Code Editor
 * 
 * This is a simple code editor tool.
 * It can be used for editing php, sql, javascript, css and other formats of code.
 * 
 * @version	1.0-2
 * @created	April 2, 2014, 14:15 (EEST)
 * @updated	May 2, 2015, 11:00 (EEST)
 */
class codeEditor extends UIObjectPrototype
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
	const NO_PARSER = "generic";
	
	/**
	 * Directory where the code web workers reside
	 * 
	 * @type	string
	 */
	const CODE_WORKERS_DIR = "codeEditor/workers/";
	
	/**
	 * Directory for the code editor resources
	 * 
	 * @type	string
	 */
	const CODE_RESOURCES = "codeEditor/";
	
	/**
	 * Index for the code editor parsers inside its resources
	 * 
	 * @type	string
	 */
	const CODE_PARSERS = "parsers.xml";

	/**
	 * Builds a code editor container.
	 * 
	 * @param	string	$type
	 * 		The type of the editor.
	 * 		See class constants.
	 * 		Default value is for php.
	 * 
	 * @param	string	$content
	 * 		The editor initial code.
	 * 
	 * @param	string	$name
	 * 		The name of the editor.
	 * 		This is the name of the textarea that will be used in the form for posting.
	 * 		Default is 'wideContent'.
	 * 
	 * @param	boolean	$editable
	 * 		Sets the code editor as editable.
	 * 		Set to FALSE for preview mode.
	 * 
	 * @return	codeEditor
	 * 		The codeEditor object
	 */
	public function build($type = self::NO_PARSER, $content = "", $name = "wideContent", $editable = TRUE)
	{
		// Create code editor holder
		$id = 'ce_'.rand();
		$editorWrapper = DOM::create('div', '', $id, 'codeEditor noDisplay');
		$this->set($editorWrapper);
		
		DOM::attr($editorWrapper, 'data-type', $type);

		$WIDEhabitat = DOM::create('div', '', '', 'ce_habitat');
		DOM::append($editorWrapper, $WIDEhabitat);
		
		// Add bottom toolbar
		$navbar = new navigationBar();
		//$toolbar = $navbar->build(navigationBar::BOTTOM, $editorWrapper)->get();
		//DOM::append($editorWrapper, $toolbar);
		
		
		
		$scriptLines = DOM::create('div', '', '', 'scriptLines');
		DOM::append($WIDEhabitat, $scriptLines);
		
		$fLineNum = DOM::create('div', '1');
		DOM::append($scriptLines, $fLineNum);
		
		$scriptWrapper = DOM::create('div', '', '', 'scriptWrapper');
		DOM::append($WIDEhabitat, $scriptWrapper);
		
		/*
		// Line map
		$lineMapTool = DOM::create('div', '', '', 'lineMapTool');
		DOM::append($WIDEhabitat, $lineMapTool);
		$lineMapSpan = DOM::create('span', 'Code Map');
		DOM::append($lineMapTool, $lineMapSpan);
		// Line map Context
		$lineMap = DOM::create('div', '', '', 'lineMap');
		DOM::append($lineMapTool, $lineMap);
		// ___ Toolbar
		$lineMapTlbr = DOM::create('div', '', '', 'lineMapToolbar');
		DOM::append($lineMap, $lineMapTlbr);
		$ffactory = new Form();
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
		*/
		$typingWrapper = DOM::create('div', '', '', 'typingWrapper');
		DOM::append($scriptWrapper, $typingWrapper);
		
		$presentationArea = DOM::create('div', '', 'presentationArea', 'presentationArea');
		if ($type == "css")
			DOM::appendAttr($presentationArea, "class", $type);
		DOM::append($typingWrapper, $presentationArea);
		
		$scriptArea = DOM::create('div', '', 'scriptArea', 'scriptArea');
		if ($editable)
			DOM::attr($scriptArea, 'contenteditable', 'true');
		DOM::append($typingWrapper, $scriptArea);

		$fLine = DOM::create('div', nl2br($content), '', 'ce_code', TRUE);
		DOM::append($scriptArea, $fLine);
		
		$textarea = DOM::create('textarea', $content, 'txt', 'contentArea');
		$txtName = (empty($name) ? 'wideContent' : $name);
		DOM::attr($textarea, 'name', $txtName);
		DOM::attr($textarea, 'style', 'display:none;');
		DOM::append($typingWrapper, $textarea);
		
		return $this;
	}
	
	/**
	 * Returns the typed code map element.
	 * 
	 * @param	string	$type
	 * 		The type of the code editor.
	 * 
	 * @return	DOMElement
	 * 		The typed Code map element with the proper groups.
	 */
	private function getTypedCodeMap($type)
	{
		$acc = new accordion();
		$acc->build();
		
		$xmlParser = new DOMParser();
		$typeGroups = $this->getParsersInfo($type, "codemap");
		
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
	
	/**
	 * Acquires the available codeEditor's parsers and returns them as an xml document string.
	 * 
	 * @param	string	$parser
	 * 		Type of the parser.
	 * 		Can be "php", "xml", "css", "js", "sql".
	 * 		If empty, all available parsers will be returned.
	 * 
	 * @param	string	$content
	 * 		Specific parser's info. 
	 * 		Can be "regulars" or "tokens".
	 * 
	 * @return	string
	 * 		The available parsers in xml format
	 */
	public static function getParsersInfo($parser = "", $content = "")
	{
		$xmlParser = new DOMParser();
		
		$path = NULL;
		if (!empty($parser) && !empty($content))
			$path = paths::getSDKRsrcPath().self::CODE_RESOURCES.$parser."/".$content.".xml";
		if ($content == "list")
			$path = paths::getSDKRsrcPath().self::CODE_RESOURCES.self::CODE_PARSERS;
		
		try
		{
			$xmlParser->load($path);
			return $xmlParser->getXML();
		}
		catch (Exception $ex)
		{
		}
		
		return NULL;
	}
	
	/**
	 * Acquires a codeEditor's worker and returns its contents.
	 * 
	 * @param	string	$name
	 * 		The name of the worker
	 * 
	 * @return	string
	 * 		The available workers in xml format.
	 */
	public static function getWorker($name)
	{
		// Check worker name
		if (!is_string($name))
			return;
		
		// Get worker path
		$path = paths::getSDKRsrcPath().self::CODE_WORKERS_DIR.$name.".js";
		if (!file_exists(systemRoot.$path))
			return NULL;
		
		// Get worker's javascript code
		return fileManager::get(systemRoot.$path);
	}
}
//#section_end#
?>