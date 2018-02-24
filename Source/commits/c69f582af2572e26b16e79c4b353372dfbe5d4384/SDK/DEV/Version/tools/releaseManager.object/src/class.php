<?php
//#section#[header]
// Namespace
namespace DEV\Version\tools;

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
 * @namespace	\tools
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Environment", "session");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Profile", "account");
importer::import("API", "Literals", "literal");
importer::import("UI", "Forms", "templates::simpleForm");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "dataGridList");
importer::import("UI", "Presentation", "frames::windowFrame");
importer::import("DEV", "Version", "vcs");

use \ESS\Environment\session;
use \API\Resources\DOMParser;
use \API\Profile\account;
use \API\Literals\literal;
use \UI\Forms\templates\simpleForm;
use \UI\Html\DOM;
use \UI\Presentation\dataGridList;
use \UI\Presentation\frames\windowFrame;
use \DEV\Version\vcs;

/**
 * Version Control Release Manager
 * 
 * This is a dialog for creating releases in project repositories.
 * You can choose branch and the version to release to.
 * 
 * @version	0.1-1
 * @created	June 26, 2014, 10:17 (EEST)
 * @revised	November 12, 2014, 10:50 (EET)
 */
class releaseManager extends windowFrame
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
		
		parent::__construct("uiReleaseManager_prj_".$id);
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
	 * @param	integer	$projectID
	 * 		The project id.
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
		// Build frame
		$title = (empty($title) ? literal::get("sdk.DEV.Version", "lbl_releaseManager_title") : $title);
		parent::build($title, $class = "uiReleaseManager");
		
		// Version Section
		$this->buildVersionSection();
		
		// Build footer
		$this->buildFooter();
		
		// Return object
		return $this;
	}
	
	/**
	 * Builds the release version controller section.
	 * 
	 * @return	void
	 */
	private function buildVersionSection()
	{
		$versionSection = DOM::create("div", "", "", "versionSection");
		$this->append($versionSection);
		
		// Get releases
		$releases = $this->vcs->getReleases();
		
		// Create release form
		$form = new simpleForm("releaseForm");
		$releaseForm = $form->build("", "/ajax/resources/sdk/vcs/release.php", FALSE)->get();
		DOM::append($versionSection, $releaseForm);
		
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
			$rv = DOM::create("span", $currentRelease."-".$currentBuild, "", "rV");
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
		$footer = DOM::create("div", "", "", "footer");
		$this->append($footer);
		
		$attr = array();
		$attr['version'] = "2.2";
		$title = literal::get("sdk.DEV.Version", "lbl_releaseManager_versionSign", $attr);
		$sign = DOM::create("span", $title, "", "sign");
		DOM::append($footer, $sign);
	}
}
//#section_end#
?>