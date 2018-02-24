<?php
//#section#[header]
// Namespace
namespace INU\Developer;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	INU
 * @package	Developer
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "togglers::accordion");
importer::import("UI", "Forms", "formFactory");
importer::import("DEV", "Resources", "paths");

use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \UI\Html\DOM;
use \UI\Presentation\togglers\accordion;
use \UI\Forms\formFactory;
use \DEV\Resources\paths;

/**
 * Code Editor
 * 
 * Object for code editing purposes.
 * 
 * @version	{empty}
 * @created	April 26, 2013, 13:23 (EEST)
 * @revised	May 20, 2014, 17:38 (EEST)
 * 
 * @deprecated	Use \UI\Developer\codeEditor instead.
 */
class codeEditor extends UIObjectPrototype
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const PHP = "php";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const XML = "xml";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const CSS = "css";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const JS = "js";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const SQL = "sql";
	/**
	 * {description}
	 * 
	 * @type	{empty}
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
	 * Builds and returns a Code Editor object
	 * 
	 * @param	string	$type
	 * 		The type of the editor. Can be  "php", "xml", "css", "js", "sql".
	 * 
	 * @param	string	$content
	 * 		Editor's text contents
	 * 
	 * @param	string	$name
	 * 		The name of the editor
	 * 
	 * @param	{type}	$editable
	 * 		{description}
	 * 
	 * @return	codeEditor
	 * 		The codeEditor object
	 */
	public function build($type = "php", $content = "", $name = "wideContent", $editable = TRUE)
	{
		// Create code editor holder
		$id = 'ce_'.rand();
		$editorWrapper = DOM::create('div', '', $id, 'codeEditor noDisplay');
		$this->set($editorWrapper);
		
		DOM::attr($editorWrapper, 'data-type', $type);

		$WIDEhabitat = DOM::create('div', '', '', 'codeEditor_habitat');
		DOM::append($editorWrapper, $WIDEhabitat);
		
		$scriptLines = DOM::create('div', '', '', 'scriptLines');
		DOM::append($WIDEhabitat, $scriptLines);
		
		$fLineNum = DOM::create('div', '1');
		DOM::append($scriptLines, $fLineNum);
		
		$scriptWrapper = DOM::create('div', '', '', 'scriptWrapper');
		DOM::append($WIDEhabitat, $scriptWrapper);
		
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

		$fLine = DOM::create('div', nl2br($content), '', 'code', TRUE);
		DOM::append($scriptArea, $fLine);

		/*if(!preg_match("/\n/", $content)){
			$br = DOM::create('br');
			DOM::append($fLine, $br);
		}*/
		
		$textarea = DOM::create('textarea', $content, 'txt', 'contentArea');
		$txtName = (empty($name) ? 'wideContent' : $name);
		DOM::attr($textarea, 'name', $txtName);
		DOM::attr($textarea, 'style', 'display:none;');
		DOM::append($typingWrapper, $textarea);
		
		return $this;
	}
	
	/**
	 * Returns the typed code map element
	 * 
	 * @param	string	$type
	 * 		The type of the code editor
	 * 
	 * @return	DOMElement
	 * 		The typed Code map element with the proper groups
	 */
	private function getTypedCodeMap($type)
	{
		$acc = new accordion();
		$acc->build();
		
		$xmlParser = new DOMParser();
		// Global sections
		//$globalGroups = $this->getParsersInfo("global", "codemap");
		// Type sections
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
	
	/**
	 * Acquires the available codeEditor's parsers and returns them as an xml document string
	 * 
	 * @param	string	$parser
	 * 		Type of the parser. Can be "php", "xml", "css", "js", "sql". If empty, all available parsers will be returned
	 * 
	 * @param	string	$content
	 * 		Specific parser's info. Can be "regulars" or "tokens"
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
		
		if (!is_null($path) && file_exists(systemRoot.$path))
			$xmlParser->load($path);
		
		return $xmlParser->getXML();
	}
	
	/**
	 * Acquires a codeEditor's worker and returns its contents
	 * 
	 * @param	string	$name
	 * 		The name of the worker
	 * 
	 * @return	string
	 * 		The available workers in xml format
	 */
	public static function getWorker($name)
	{
		if (!is_string($name))
			return;
		
		$path = paths::getSDKRsrcPath().self::CODE_WORKERS_DIR.$name.".js";
		
		if (!file_exists(systemRoot.$path))
			return;
		
		return fileManager::get_contents(systemRoot.$path);
	}
}
//#section_end#
?>