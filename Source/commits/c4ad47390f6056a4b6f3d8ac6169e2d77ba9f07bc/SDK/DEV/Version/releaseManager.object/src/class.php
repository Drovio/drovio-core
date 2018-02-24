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
importer::import("API", "Literals", "literal");
importer::import("API", "Security", "account");
importer::import("UI", "Forms", "templates::simpleForm");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "dataGridList");
importer::import("DEV", "Version", "vcs");

use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\DOMParser;
use \API\Resources\storage\session;
use \API\Literals\literal;
use \API\Security\account;
use \UI\Forms\templates\simpleForm;
use \UI\Html\DOM;
use \UI\Presentation\dataGridList;
use \DEV\Version\vcs;

/**
 * VCS Release Manager
 * 
 * Displays a release wizard for repository branches.
 * 
 * @version	{empty}
 * @created	February 12, 2014, 12:19 (EET)
 * @revised	May 10, 2014, 15:06 (EEST)
 */
class releaseManager extends UIObjectPrototype
{
	/**
	 * The object id.
	 * 
	 * @type	string
	 */
	private $id;
	/**
	 * The vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * The body element of the object.
	 * 
	 * @type	DOMElement
	 */
	private $body;
	/**
	 * The footer element of the object.
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
	 * Builds the release Manager ui object.
	 * 
	 * @param	string	$title
	 * 		The header title.
	 * 
	 * @return	releaseManager
	 * 		The releaseManager object.
	 */
	public function build($title = "")
	{
		// Build container
		$vcsControl = DOM::create("div", "", $this->id, "uiVCSReleaseControl");
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
	 * 		The header title.
	 * 
	 * @return	void
	 */
	private function buildBody($title = "")
	{
		$body = $this->body;
		
		// Build Title
		$title = (empty($title) ? literal::get("sdk.DEV.Version", "lbl_releaseManager_title") : $title);
		$titleHeader = DOM::create("h2", $title);
		
		// Build header
		$header = DOM::create("div", $titleHeader, "", "header");
		DOM::append($body, $header);
		
		// Commit Section
		$this->buildVersionSection();
	}
	
	/**
	 * Builds the release version controller section.
	 * 
	 * @return	void
	 */
	private function buildVersionSection()
	{
		$body = $this->body;
		
		// Get releases
		$releases = $this->vcs->getReleases();
		
		// Create release form
		$form = new simpleForm("releaseForm");
		$releaseForm = $form->build("", "/ajax/resources/sdk/vcs/release.php", FALSE)->get();
		DOM::append($body, $releaseForm);
		
		// Set vcs id
		$vcsID = $form->getInput($type = "hidden", $name = "vcs_id", $value = $this->id, $class = "", $autofocus = FALSE, $required = FALSE);
		$form->append($vcsID);
		
		// Get branches
		$branches = $this->vcs->getBranches();
		
		// Inform about release versions
		$title = literal::get("sdk.DEV.Version", "lbl_releaseManager_currentReleases");
		$header = DOM::create("div", $title, "", "relHeader");
		$form->append($header);
		foreach ($branches as $branchName => $branchData)
		{
			$releaseRow = DOM::create("div", "", "", "bV");
			$form->append($releaseRow);
			
			$branchInfo = DOM::create("span", "[".$branchName."]", "", "bVTitle");
			DOM::append($releaseRow, $branchInfo);
			
			$currentRelease = $releases[$branchName]['current'];
			$packageID = "v".$currentRelease;
			$currentBuild = $releases[$branchName]['packages'][$packageID]['build'];
			$currentRelease = (empty($currentRelease) ? "No Release" : $currentRelease);
			$rv = DOM::create("span", $currentRelease.".".$currentBuild, "", "rV");
			DOM::append($releaseRow, $rv);
			
		}
		
		$title = literal::get("sdk.DEV.Version", "lbl_releaseManager_releaseBranch");
		$header = DOM::create("div", $title, "", "relHeader");
		$form->append($header);
		
		$branchResource = array();
		foreach ($branches as $branchName => $branchData)
			$branchResource[$branchName] = $branchName;
		$title = literal::get("sdk.DEV.Version", "lbl_releaseManager_chooseBranch");
		$input = $form->getResourceSelect($name = "branchName", $multiple = FALSE, $class = "", $branchResource, $selectedValue = "");
		$form->insertRow($title, $input, $required = TRUE, $notes = "");
		
		
		
		// Release version
		// TEMP - It should be loaded async on branch selection.
		$relVersion = $releases['master']['current'];
		$input = $form->getInput($type = "text", $name = "version", $value = $relVersion, $class = "", $autofocus = FALSE, $required = TRUE);
		$title = literal::get("sdk.DEV.Version", "lbl_releaseManager_releaseVersion");
		$form->insertRow($title, $input, $resuired = TRUE);
		
		// Release Title
		$input = $form->getInput($type = "text", $name = "title", $value = "", $class = "uiReleaseTitleText", $autofocus = TRUE, $required = TRUE);
		DOM::attr($input, "placeholder", literal::get("sdk.DEV.Version", "lbl_releaseManager_releaseTitle", array(), FALSE));
		$form->append($input);
		
		// Commit Description
		$input = $form->getTextarea($name = "description", $value = "", $class = "uiReleaseDescText", $autofocus = FALSE);
		DOM::attr($input, "placeholder", literal::get("sdk.DEV.Version", "lbl_releaseManager_releaseChangelog", array(), FALSE));
		$form->append($input);
		
		// Commit
		$title = literal::get("sdk.DEV.Version", "lbl_releaseManager_release");
		$releaseBtn = $form->getSubmitButton($title, $id = "releaseBtn");
		$form->append($releaseBtn);
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
		
		$attr = array();
		$attr['version'] = $info['version'];
		$attr['author'] = $author;
		$title = literal::get("sdk.DEV.Version", "lbl_releaseManager_versionSign", $attr);
		$sign = DOM::create("span", $title, "", "sign");
		DOM::append($footer, $sign);
		
		$closeBtn = DOM::create("span", "Close", "", "closeBtn");
		DOM::append($footer, $closeBtn);
	}
}
//#section_end#
?>