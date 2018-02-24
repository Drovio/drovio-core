<?php
//#section#[header]
// Namespace
namespace INU\Developer\documentation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	INU
 * @package	Developer
 * @namespace	\documentation
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Developer", "resources::documentation::documentor");
importer::import("API", "Resources", "storage::session");
importer::import("ESS", "Protocol", "server::HTMLServerReport");
importer::import("ESS", "Protocol", "server::JSONServerReport");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Forms", "templates::simpleForm");
importer::import("UI", "Forms", "formControls::formButton");
importer::import("UI", "Navigation", "navigationBar");
importer::import("UI", "Navigation", "toolbarComponents::toolbarItem");
importer::import("UI", "Navigation", "toolbarComponents::toolbarLabel");
importer::import("UI", "Navigation", "toolbarComponents::toolbarMenu");

use \UI\Forms\templates\simpleForm;
use \UI\Forms\formControls\formButton;
use \ESS\Protocol\server\HTMLServerReport;
use \ESS\Protocol\server\JSONServerReport;
use \UI\Html\DOM;
use \ESS\Prototype\UIObjectPrototype;
use \API\Developer\resources\documentation\documentor as documentParser;
use \API\Resources\storage\session;
use \UI\Navigation\toolbarComponents\toolbarItem;
use \UI\Navigation\toolbarComponents\toolbarLabel;
use \UI\Navigation\toolbarComponents\toolbarMenu;
use \UI\Navigation\navigationBar;

importer::import("UI", "Presentation", "popups::popup");
use \UI\Presentation\popups\popup;
/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	October 10, 2013, 11:08 (EEST)
 * @revised	October 15, 2013, 16:33 (EEST)
 */
class documentor extends UIObjectPrototype
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const PLAIN = 'plain';
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const PHP = 'php';
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const JS = 'js';
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $pool;
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $editor;
	

	private	$saveAction = '/ajax/resources/sdk/documentor/saveSection.php';
	
	private $path;

	/**
	 * {description}
	 * 
	 * @param	{type}	$editor
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function __construct($editor = FALSE, $path = NULL)
	{
		$this->editor = $editor;
		if($this->editor)
		{
			$this->path = $path;
		}
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$document
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function build($id = '', $document = '')
	{
		// Create outer Wrapper
		$holder = DOM::create("div", "", $id, "documentor");
		$this->set($holder);
		
		if(empty($document))
			return $this;
			
		$sForm = new simpleForm();
			
		// Append Hidden Values
		$hidden = $sForm->getInput("hidden", "id", $id, $class = "", $autofocus = FALSE);
		DOM::append($holder, $hidden);
			

		// Load Document		
		$documentParser = new documentParser();
		$documentParser->loadContent($document);
	
		if($this->editor)
		{
			// Build Receiver Pool
			$reports = DOM::create("div", "", "", "reportPool noDisplay");
			DOM::append($holder, $reports);
			
			$navigationBar = new navigationBar();
			$tlbItemBuilder = new toolbarItem();
			
			$tlbLaberBuilder = new toolbarLabel();
			$tlbMenuBuilder = new toolbarMenu();
			
			// Build control Bar
			$navigationBar->build($dock = "T", $holder);
			$controlBar = $navigationBar->get();
			DOM::append($holder, $controlBar);
			
			// Control : "Add New" - menu root
			$content = DOM::create("span", "Add New");			
			$addNew = $tlbItemBuilder->build($content)->get();
			DOM::attr($addNew, 'data-ref', 'ddMenuGroup');
			DOM::appendAttr($addNew, 'class', 'ddMenuRoot');
			DOM::append($controlBar, $addNew);
			
			// Menu Group
			$menuGroup = DOM::create('div', '', 'ddMenuGroup', 'ddMenuGroup noDisplay');
			DOM::append($controlBar, $menuGroup);
			
			// Menu Item : "Add Text"
			$content = DOM::create("span", "Add Text", "addNewControl", "");
			$addNew = $tlbItemBuilder->build($content)->get();
			DOM::attr($addNew, 'data-clickAct', self::PLAIN);
			DOM::append($menuGroup, $addNew);
			
			// Menu Item : "Add PHP"
			$content = DOM::create("span", "Add PHP", "", "");
			$addNew = $tlbItemBuilder->build($content)->get();
			//DOM::attr($addNew, 'data-clickAct', self::PHP);
			DOM::append($menuGroup, $addNew);
			
			// Menu Item : "Add JS"
			$content = DOM::create("span", "Add JS", "", "");
			$addNew = $tlbItemBuilder->build($content)->get();
			//DOM::attr($addNew, 'data-clickAct', self::JS);
			DOM::append($menuGroup, $addNew);	
			
			
			//$addNew = $tlbMenuBuilder->insertMenuItem($content, $id = "");
			
			
			// Add save path to session
			$varNamespace = 'documentor';
			srand((double) microtime() * 1000000); 
			$key = rand();
			session::set($key, $this->path, $varNamespace);
						
			// Append Hidden Values
			$hidden = $sForm->getInput("hidden", "key", $key, $class = "", $autofocus = FALSE);
			DOM::append($holder, $hidden);
			
		}
		
		// Build Receiver Pool
		$this->pool = DOM::create("div", "", "", "dropPool");
		DOM::append($holder, $this->pool);
		
		
		$sectionArray = $documentParser->getSections();
		ksort($sectionArray);
		foreach($sectionArray as $pos=>$section)
		{
			if($this->editor)
			{
				// Build Documentor For editing Content
				$viewer = $this->buildPresenter($section['type'], $section['content'], $pos);
			}
			else
			{
				// Build Documentor only in view mode
				$viewer = $this->buildViewer($section['type'], $section['content'], $pos);
			}
			DOM::append($this->pool, $viewer);
		}
		
		return $this;
	}
	
	/**
	 * {description}
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function getEditor($type = self::PLAIN, $pos = '')
	{
		// Clear Report
		HTMLServerReport::clear();
		
		$content = $this->buildEditor($type, $pos);
		
		// Add status update action
		/*
		if ($updateStatus)
			HTMLServerReport::addAction("content.saved");
		*/
		
		// Add the notification as content
		//$holder = "#".$id.".documentor> .result";
		$holder = "documentor > .dropPool";
		HTMLServerReport::addContent($content, "data", $holder, "append");
		
		// Return Report
		return HTMLServerReport::get();
	}
	
	/**
	 * {description}
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getPresenter($type = self::PLAIN)
	{
		// Clear Report
		HTMLServerReport::clear();
		
		$content = $this->buildPresenter($type, '', '', TRUE);
				
		
		// Add the notification as content
		//$holder = "#".$id.".documentor> .result";
		$holder = "documentor > .dropPool";
		HTMLServerReport::addContent($content, "data", $holder, "append");
		
		// Return Report
		return HTMLServerReport::get();
	}
	
	public function getDeleteConfirmation($pos)
	{
		// Clear Report
		HTMLServerReport::clear();
				
		// Create form
		$sForm = new simpleForm();
		$sForm->build('', "/ajax/resources/sdk/documentor/deleteSection.php", FALSE);
		
		// [Hidden] Section Position
		$hidden = $sForm->getInput("hidden", "pos", $pos, $class = "", $autofocus = FALSE);
		$sForm->append($hidden);
		
		//
		$prompt = DOM::create('span', 'Really ???');
		$sForm->append($prompt);
		
		$controls = DOM::create('div');
		$sForm->append($controls);
		
		// Save 
		$formButton = new formButton();
		$formButton->build("Delete","submit", '', '', FALSE);
		$button = $formButton->get();
		DOM::append($controls , $button);
		
		// Dissmiss
		$formButton = new formButton();
		$formButton->build("Dismiss","button", '', '', FALSE);
		$button = $formButton->get();
		DOM::attr($button, 'data-formDissmiss', 'delete');
		DOM::append($controls , $button);
		
		// Get Form Content
		$content = $sForm->get();
		
		
		// Add the notification as content
		//$holder = "#".$id.".documentor> .result";
		$holder = "documentor > .dropPool > .itemControlBar";
		HTMLServerReport::addContent($content, "data", NULL, "append");
		
		// Return Report
		return HTMLServerReport::get();
		
	}
	
	public function getDeleteReport($frmId, $pos, $newCount, $id)
	{	
		/*
		// Clear Report
		JSONServerReport::clear();
		
		//JSONServerReport::addHeader('frmId');
		//JSONServerReport::addHeader('pos');
		//JSONServerReport::addHeader('act');
		
		$content = array();
		$content['frmId'] = $frmId;
		$content['pos'] = $pos;
		
		JSONServerReport::addContent($content);
		
		// Add status update action
		if ($updateStatus)
			JSONServerReport::addAction("section.saved");
		
		// Return Report
		return JSONServerReport::get();
		*/
		
		// Clear Report
		HTMLServerReport::clear();
		
		$content = DOM::create('div', '', '', 'deleteReport');		
		DOM::appendAttr($content, 'data-frmId', $frmId);
		DOM::appendAttr($content, 'data-pos', $pos);
		DOM::appendAttr($content, 'data-count', $newCount);
				
		// Add status update action
		HTMLServerReport::addAction("documentor.sectionDelete");
		
		// Add the notification as content
		$holder = "#".$id.".documentor > .reportPool";
		HTMLServerReport::addContent($content, "data", $holder, "append");
		
		// Return Report
		return HTMLServerReport::get();
	}
	
	/**
	 * {description}
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getSaveReport($frmId, $pos, $id)
	{	
		/*
		// Clear Report
		JSONServerReport::clear();
		
		//JSONServerReport::addHeader('frmId');
		//JSONServerReport::addHeader('pos');
		//JSONServerReport::addHeader('act');
		
		$content = array();
		$content['frmId'] = $frmId;
		$content['pos'] = $pos;
		
		JSONServerReport::addContent($content);
		
		// Add status update action
		if ($updateStatus)
			JSONServerReport::addAction("section.saved");
		
		// Return Report
		return JSONServerReport::get();
		*/
		
		// Clear Report
		HTMLServerReport::clear();
		
		$content = DOM::create('div', '', '', 'saveReport');		
		DOM::appendAttr($content, 'data-frmId', $frmId);
		DOM::appendAttr($content, 'data-pos', $pos);
				
		// Add status update action
		HTMLServerReport::addAction("documentor.sectionSave");
		
		// Add the notification as content
		$holder = "#".$id.".documentor > .reportPool";
		HTMLServerReport::addContent($content, "data", $holder, "append");
		
		// Return Report
		return HTMLServerReport::get();
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$contentType
	 * 		{description}
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function buildViewer($contentType = self::PLAIN, $content = '', $pos = '')
	{
		$container = DOM::create('div', '', '', 'viewer');
		
		DOM::innerHTML($container, $content);
		
		return $container;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$contentType
	 * 		{description}
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$editable
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function buildPresenter($contentType = self::PLAIN, $content = '', $pos = '', $editable = FALSE)
	{
		$container = DOM::create('div', '', '', 'presenter');
		DOM::attr($container, 'data-type', $contentType);		 
		DOM::attr($container, 'data-pos', $pos);		 
		
		$contentWrapper = DOM::create('div');
		DOM::append($container, $contentWrapper);	
		
		// Build Content Viewer
		$viewerWrapper = DOM::create('div', '', '', 'viewerWrapper');
		DOM::append($contentWrapper, $viewerWrapper);
		
		$itemTopBar = DOM::create('div', '', '', 'itemTopBar');
		DOM::append($viewerWrapper, $itemTopBar );
		
		$prompt= DOM::create('div', '', '', 'prompt'); 
		DOM::append($itemTopBar , $prompt);
		
		
		// Toggle view/edit control
		$controlBar = DOM::create('div', '', '', 'itemControlBar');
		DOM::append($itemTopBar, $controlBar);
		
		
		
		// Edit control
		
		$control = DOM::create('div', '', '', 'control');
		DOM::attr($control, "data-ctrlType", "edit");
		DOM::append($controlBar, $control);
		$controlContent = DOM::create('span', 'Edit');
		DOM::append($control, $controlContent);
		
		// Control Separator
		$control = DOM::create('div', '', '', 'separator');
		DOM::append($controlBar, $control);
		$controlContent = DOM::create('span', '|');
		DOM::append($control, $controlContent);
		
		// Delete control
		$control = DOM::create('div', '', '', 'control');		
		DOM::attr($control, "data-ctrlType", "delete");
		DOM::append($controlBar, $control);
		$controlContent = DOM::create('span', 'Delete');		
		DOM::append($control, $controlContent);
		
		// Add Viewer contet
		$viewer = $this->buildViewer($contentType, $content, $pos);
		DOM::append($viewerWrapper, $viewer);
		
		
		// Build Content Editor
		$editorWrapper = DOM::create('div', '', '', 'editorWrapper');
		if($editable)
		{
			$editor = $this->buildEditor($contentType, $pos);
			DOM::append($editorWrapper, $editor);
			
			DOM::appendAttr($viewerWrapper, 'class', 'noDisplay');
		}
		else
		{
			DOM::appendAttr($editorWrapper, 'class', 'noDisplay');
		}
		DOM::append($contentWrapper, $editorWrapper);
		
		return $container;
	}
	
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function buildEditor($type = self::PLAIN, $pos= '')
	{
		$container = DOM::create('div');		
		
		$sForm = new simpleForm();
		$sForm->build("", $this->saveAction, $controls = FALSE);
		DOM::append($container, $sForm->get());
		
		$formContent = DOM::create('div'); 
		$sForm->append($formContent); 

		// Append Hidden Values 
		$hidden = $sForm->getInput("hidden", "type", $type, $class = "", $autofocus = FALSE);
		//$sForm->append($hidden);
		DOM::append($formContent, $hidden);	
 
		$hidden = $sForm->getInput("hidden", "pos", $pos, $class = "", $autofocus = FALSE);
		//$sForm->append($hidden);
		DOM::append($formContent, $hidden);
		  
		$bodyContent = DOM::create('div');
		DOM::append($formContent, $bodyContent);		 
		
		if($type == self::PLAIN)
		{		 
			// Content Editor
			$editor = $this->buildHtmlEditor();

		}
		else
		{
			// Content Editor
			$editor = $this->buildCOdeEditor();
		}
		DOM::append($bodyContent, $editor);
		
		$controls = DOM::create('div');
		DOM::append($formContent, $controls);
		
		// Save 
		$formButton = new formButton();
		$formButton->build("Save","submit", '', '', FALSE);
		$button = $formButton->get();
		DOM::append($controls , $button);
		
		// Dissmiss
		$formButton = new formButton();
		$formButton->build("Dismiss","button", '', '', FALSE);
		$button = $formButton->get();
		DOM::attr($button, 'data-formDissmiss', 'save');
		DOM::append($controls , $button);
		
		
		return $container;
	}
	
	
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function buildHtmlEditor()
	{
		$sForm = new simpleForm();
		$input = $sForm->getTextarea($name = "sectionBody", '', $class = "");
		
		return $input;
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function buildCodeEditor()
	{
	
	}
}
//#section_end#
?>