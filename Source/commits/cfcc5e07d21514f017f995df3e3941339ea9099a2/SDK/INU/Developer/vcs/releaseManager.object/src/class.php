<?php
//#section#[header]
// Namespace
namespace INU\Developer\vcs;

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
 * @namespace	\vcs
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Resources", "literals::literal");
importer::import("UI", "Forms", "templates::simpleForm");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "dataGridList");

use \ESS\Prototype\UIObjectPrototype;
use \API\Developer\misc\vcs;
use \API\Resources\DOMParser;
use \API\Resources\storage\session;
use \API\Resources\literals\literal;
use \UI\Forms\templates\simpleForm;
use \UI\Html\DOM;
use \UI\Presentation\dataGridList;

/**
 * VCS Release Manager
 * 
 * Displays a release wizard for repository branches.
 * 
 * @version	{empty}
 * @created	January 16, 2014, 17:12 (EET)
 * @revised	February 12, 2014, 12:19 (EET)
 * 
 * @deprecated	Use DEV\Version\releaseManager instead.
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
	 * Builds the release Manager ui object.
	 * 
	 * @param	string	$title
	 * 		The header title.
	 * 
	 * @return	object
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
		$title = (empty($title) ? "Version Control Release Manager" : $title);
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
		$header = DOM::create("div", "Current Releases", "", "relHeader");
		$form->append($header);
		foreach ($branches as $branchData)
		{
			$releaseRow = DOM::create("div", "", "", "bV");
			$form->append($releaseRow);
			
			$branchInfo = DOM::create("span", "[".$branchData['name']."]", "", "bVTitle");
			DOM::append($releaseRow, $branchInfo);
			
			$currentRelease = $releases[$branchData['name']]['current'];
			$currentBuild = $releases[$branchData['name']]['build'];
			$currentRelease = (empty($currentRelease) ? "No Release" : $currentRelease);
			$rv = DOM::create("span", $currentRelease.".".$currentBuild, "", "rV");
			DOM::append($releaseRow, $rv);
			
		}
		
		$header = DOM::create("div", "Release Branch", "", "relHeader");
		$form->append($header);
		
		$branchResource = array();
		foreach ($branches as $branchData)
			$branchResource[$branchData['name']] = $branchData['name'];
		$title = literal::get("sdk.INU.Developer", "lbl_releaseManager_chooseBranch");
		$input = $form->getResourceSelect($name = "branchName", $multiple = FALSE, $class = "", $branchResource, $selectedValue = "");
		$form->insertRow($title, $input, $required = TRUE, $notes = "");
		
		
		
		// Release version
		// TEMP - It should be loaded async on branch selection.
		$relVersion = $releases['master']['current'];
		$input = $form->getInput($type = "text", $name = "version", $value = $relVersion, $class = "", $autofocus = FALSE, $required = TRUE);
		$title = "Release Version";
		$form->insertRow($title, $input, $resuired = TRUE);
		
		// Release Title
		$input = $form->getInput($type = "text", $name = "title", $value = "", $class = "uiReleaseTitleText", $autofocus = TRUE, $required = TRUE);
		DOM::attr($input, "placeholder", "Release Title");
		$form->append($input);
		
		// Commit Description
		$input = $form->getTextarea($name = "description", $value = "", $class = "uiReleaseDescText", $autofocus = FALSE);
		DOM::attr($input, "placeholder", "Release Description");
		$form->append($input);
		
		// Commit
		$title = DOM::create("span", "Release");
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
		$author = $this->vcs->getCurrentAuthor();
		
		$sign = DOM::create("span", "Version Control v".$info['version']." | ".$author, "", "sign");
		DOM::append($footer, $sign);
		
		$closeBtn = DOM::create("span", "Close", "", "closeBtn");
		DOM::append($footer, $closeBtn);
	}
}
//#section_end#
?>