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
importer::import("DEV", "Version", "vcs");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "storage::session");
importer::import("UI", "Forms", "templates::simpleForm");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "dataGridList");

use \ESS\Prototype\UIObjectPrototype;
use \DEV\Version\vcs;
use \API\Resources\DOMParser;
use \API\Resources\storage\session;
use \UI\Forms\templates\simpleForm;
use \UI\Html\DOM;
use \UI\Presentation\dataGridList;

/**
 * VCS Commit Manager
 * 
 * Displayes all the items that must be commited.
 * 
 * @version	{empty}
 * @created	February 12, 2014, 12:14 (EET)
 * @revised	February 18, 2014, 11:34 (EET)
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
	 * @param	string	$repository
	 * 		The repository path. If empty, it will be loaded from session.
	 * 
	 * @param	boolean	$includeRelease
	 * 		Sets the includeRelease attribute for the vcs. FALSE by default.
	 * 
	 * @return	void
	 */
	public function __construct($id, $repository = "", $includeRelease = FALSE)
	{
		// Set VCS ID
		$this->id = $id;
		
		// Init control
		if (!empty($repository))
			$this->init($repository, $includeRelease);
		else
			$this->load($id);
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
	 * @param	string	$repository
	 * 		The repository folder.
	 * 
	 * @param	boolean	$includeRelease
	 * 		The includeRelease indicator for the vcs.
	 * 
	 * @return	void
	 */
	private function init($repository, $includeRelease)
	{
		// Create vcs object
		$this->vcs = new vcs($repository, $includeRelease);
		
		// Set session variables
		session::set("repository", $repository, "commitManager_".$this->id);
		session::set("includeRelease", $includeRelease, "commitManager_".$this->id);
	}
	
	/**
	 * Loads the information from the session.
	 * 
	 * @return	void
	 */
	private function load()
	{
		// Load session variables
		$repository = session::get("repository", NULL, "commitManager_".$this->id);
		$includeRelease = session::get("includeRelease", NULL, "commitManager_".$this->id);
		
		// Create vcs object
		$this->vcs = new vcs($repository, $includeRelease);
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
			$rowContents[] = $authors[$item['last-edit-author']];
			$rowContents[] = date("Y-m-d H:i:s", $item['last-edit-time']);
			$dataList->insertRow($rowContents, $checkName = "citem[".$id."]", $checked = $item['force']);
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
		$author = $this->vcs->getCurrentAuthor();
		
		$sign = DOM::create("span", "Version Control v".$info['version']." | ".$author, "", "sign");
		DOM::append($footer, $sign);
		
		$closeBtn = DOM::create("span", "Close", "", "closeBtn");
		DOM::append($footer, $closeBtn);
	}
}
//#section_end#
?>