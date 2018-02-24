<?php
//#section#[header]
// Namespace
namespace DEV\Version;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Version
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Security", "account");
importer::import("API", "Geoloc", "datetimer");
importer::import("UI", "Forms", "templates::simpleForm");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "dataGridList");
importer::import("DEV", "Version", "vcs");

use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\DOMParser;
use \API\Resources\storage\session;
use \API\Security\account;
use \API\Geoloc\datetimer;
use \UI\Forms\templates\simpleForm;
use \UI\Html\DOM;
use \UI\Presentation\dataGridList;
use \DEV\Version\vcs;

/**
 * VCS Commit Manager
 * 
 * Displayes all the items that must be commited.
 * 
 * @version	{empty}
 * @created	February 12, 2014, 12:14 (EET)
 * @revised	May 10, 2014, 15:05 (EEST)
 */
class commitManager extends UIObjectPrototype
{
	/**
	 * The object id.
	 * 
	 * @type	string
	 */
	private $id;
	/**
	 * The vcs control object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * The body container.
	 * 
	 * @type	DOMElement
	 */
	private $body;
	/**
	 * The footer container.
	 * 
	 * @type	DOMElement
	 */
	private $footer;
	
	/**
	 * Initializes the control.
	 * 
	 * @param	string	$id
	 * 		The control id.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @return	void
	 */
	public function __construct($id, $projectID = "")
	{
		// Set VCS ID
		$this->id = $id;
		
		// Init control
		if (!empty($projectID))
			$this->init($projectID);
		else
			$this->load();
	}
	
	/**
	 * Gets the vcs object.
	 * 
	 * @return	vcs
	 * 		The vcs object.
	 */
	public function getVcs()
	{
		return $this->vcs;
	}
	
	/**
	 * Inits the control.
	 * 
	 * @param	{type}	$projectID
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function init($projectID)
	{
		// Create vcs object
		$this->vcs = new vcs($projectID);
		
		// Set session variables
		session::set("projectID", $projectID, "commitManager_".$this->id);
	}
	
	/**
	 * Loads the information from the session.
	 * 
	 * @return	void
	 */
	private function load()
	{
		// Load session variables
		$projectID = session::get("projectID", NULL, "commitManager_".$this->id);
		
		// Create vcs object
		$this->vcs = new vcs($projectID);
	}
	
	/**
	 * Builds the commit Manager ui object.
	 * 
	 * @param	string	$title
	 * 		The project's title.
	 * 
	 * @return	commitManager
	 * 		The commitManager object.
	 */
	public function build($title = "")
	{
		// Build container
		$vcsControl = DOM::create("div", "", $this->id, "uiVCSControl");
		$this->set($vcsControl);
		
		// Build body
		$this->body = DOM::create("div", "", "", "body");
		DOM::append($vcsControl, $this->body);
		$this->buildBody($title);
		
		// Build footer
		$this->footer = DOM::create("div", "", "", "footer");
		DOM::append($vcsControl, $this->footer);
		$this->buildFooter();
		
		// Return object
		return $this;
	}
	
	/**
	 * Builds the control's body.
	 * 
	 * @param	string	$title
	 * 		The object header title.
	 * 
	 * @return	void
	 */
	private function buildBody($title = "")
	{
		$body = $this->body;
		
		// Build Title
		$title = (empty($title) ? "Version Control Commit Manager" : $title);
		$titleHeader = DOM::create("h2", $title);
		
		// Build header
		$header = DOM::create("div", $titleHeader, "", "header");
		DOM::append($body, $header);
		
		// Commit Section
		$this->buildCommitSection();
	}
	
	/**
	 * Builds the commit section.
	 * 
	 * @return	void
	 */
	private function buildCommitSection()
	{
		$body = $this->body;
		
		// Get working items
		$workingItems = $this->vcs->getWorkingItems();
		$authors = $this->vcs->getAuthors();
		
		// Commit Header
		$commitTitle = DOM::create("span", "Commit Working Items (".count($workingItems).")");
		$commitHeader = DOM::create("h3", "", "", "vcsHeader");
		DOM::append($commitHeader, $commitTitle);
		DOM::append($body, $commitHeader);
		
		if (count($workingItems) == 0)
		{
			$noItemsDesc = DOM::create("p", "There are no working items to commit.");
			DOM::append($body, $noItemsDesc);
			return;
		}
		
		// Create commit form
		$form = new simpleForm("commitForm");
		$commitForm = $form->build("", "/ajax/resources/sdk/vcs/commit.php", FALSE)->get();
		DOM::append($body, $commitForm);
		
		// Set vcs id
		$vcsID = $form->getInput($type = "hidden", $name = "vcs_id", $value = $this->id, $class = "", $autofocus = FALSE, $required = FALSE);
		$form->append($vcsID);
		
		// Commit Item List
		$dataList = new dataGridList();
		$commitList = $dataList->build($id = "commitItems", $checkable = TRUE)->get();
		
		$ratios = array();
		$ratios[] = 0.6;
		$ratios[] = 0.2;
		$ratios[] = 0.2;
		$dataList->setColumnRatios($ratios);
		
		$headers = array();
		$headers[] = "Item Path";
		$headers[] = "Last Author";
		$headers[] = "Last Edit Time";
		$dataList->setHeaders($headers);
		
		foreach ($workingItems as $id => $item)
		{
			$rowContents = array();
			$rowContents[] = $item['path'];
			$rowContents[] = $item['last-edit-author'];
			$rowContents[] = datetimer::live($item['last-edit-time'], $format = 'd F, Y \a\t H:i');
			$dataList->insertRow($rowContents, $checkName = "citem[".$id."]", $checked = $item['force_commit']);
		}
		
		$form->append($commitList);
		
		// Commit Summary
		$commitSumm = $form->getInput($type = "text", $name = "summary", $value = "", $class = "uiCommitSummText", $autofocus = TRUE, $required = FALSE);
		DOM::attr($commitSumm, "placeholder", "Type here your commit summary...");
		$form->append($commitSumm);
		
		// Commit Description
		$commitDesc = $form->getTextarea($name = "description", $value = "", $class = "uiCommitDescText", $autofocus = FALSE);
		DOM::attr($commitDesc, "placeholder", "Type here your commit description...");
		$form->append($commitDesc);
		
		// Commit
		$title = DOM::create("span", "commit");
		$commitBtn = $form->getSubmitButton($title, $id = "commitBtn");
		$form->append($commitBtn);
		
	}
	
	/**
	 * Builds the control's footer.
	 * 
	 * @return	void
	 */
	private function buildFooter()
	{
		$footer = $this->footer;
		
		// Get vcs info
		$info = $this->vcs->getInfo();
		$author = account::getAccountTitle();
		
		$sign = DOM::create("span", "Version Control v".$info['version']." | ".$author, "", "sign");
		DOM::append($footer, $sign);
		
		$closeBtn = DOM::create("span", "Close", "", "closeBtn");
		DOM::append($footer, $closeBtn);
	}
}
//#section_end#
?>