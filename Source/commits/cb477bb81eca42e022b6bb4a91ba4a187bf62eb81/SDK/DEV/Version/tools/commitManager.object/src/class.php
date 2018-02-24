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
importer::import("API", "Geoloc", "datetimer");
importer::import("API", "Literals", "literal");
importer::import("UI", "Forms", "templates::simpleForm");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "dataGridList");
importer::import("UI", "Presentation", "frames::windowFrame");
importer::import("DEV", "Version", "vcs");

use \ESS\Environment\session;
use \API\Resources\DOMParser;
use \API\Profile\account;
use \API\Geoloc\datetimer;
use \API\Literals\literal;
use \UI\Forms\templates\simpleForm;
use \UI\Html\DOM;
use \UI\Presentation\dataGridList;
use \UI\Presentation\frames\windowFrame;
use \DEV\Version\vcs;

/**
 * Version Control Commit Manager
 * 
 * This is a dialog for committing items in project repositories.
 * Displays all the working items of the current author, including the force-to-commit items in a separate list.
 * 
 * @version	0.1-4
 * @created	June 26, 2014, 10:11 (EEST)
 * @revised	December 18, 2014, 17:12 (EET)
 */
class commitManager extends windowFrame
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
	 * Initializes the control.
	 * 
	 * @param	string	$id
	 * 		The control's ui id.
	 * 
	 * @param	integer	$projectID
	 * 		The project id to load for commits.
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
		
		parent::__construct("uiCommitManager_prj_".$id);
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
	 * Builds the commit Manager ui object.
	 * 
	 * @param	string	$title
	 * 		The dialog's title.
	 * 
	 * @return	commitManager
	 * 		The commitManager object.
	 */
	public function build($title = "")
	{
		// Build frame
		$title = (empty($title) ? literal::get("sdk.DEV.Version", "lbl_commitManager_title") : $title);
		parent::build($title, $class = "uiCommitManager");
		
		// Commit Section
		$this->buildCommitSection();
		
		// Build footer
		$this->buildFooter();
		
		// Return object
		return $this;
	}
	
	/**
	 * Builds the commit section.
	 * 
	 * @return	void
	 */
	private function buildCommitSection()
	{
		$commitSection = DOM::create("div", "", "", "commitSection");
		$this->append($commitSection);
		
		// Get working items
		$workingItems = $this->vcs->getWorkingItems();
		$authors = $this->vcs->getAuthors();
		
		// Commit Header
		$attr = array();
		$attr['count'] = "".count($workingItems);
		$commitTitle = literal::get("sdk.DEV.Version", "lbl_commitManager_commitItems", $attr);
		$commitHeader = DOM::create("h3", $commitTitle, "", "vcsHeader");
		DOM::append($commitSection, $commitHeader);
		
		if (count($workingItems) == 0)
		{
			$desc = literal::get("sdk.DEV.Version", "lbl_commitManager_noItems");
			$noItemsDesc = DOM::create("p", $desc);
			DOM::append($commitSection, $noItemsDesc);
			return;
		}
		
		// Create commit form
		$form = new simpleForm("commitForm");
		$commitForm = $form->build("", "/ajax/resources/sdk/vcs/commit.php", FALSE)->get();
		DOM::append($commitSection, $commitForm);
		
		// Set vcs id
		$vcsID = $form->getInput($type = "hidden", $name = "vcs_id", $value = $this->id, $class = "", $autofocus = FALSE, $required = FALSE);
		$form->append($vcsID);
		
		// Create commit list container
		$commitListContainer = DOM::create("div", "", "", "commitListContainer");
		$form->append($commitListContainer);

		// Force commit items
		$forceItems = array();
		foreach ($workingItems as $id => $item)
			if ($item['force_commit'])
				$forceItems[$id] = $workingItems[$id];

		$ratios = array();
		$ratios[] = 0.6;
		$ratios[] = 0.2;
		$ratios[] = 0.2;
		
		$headers = array();
		$headers[] = literal::get("sdk.DEV.Version", "lbl_commitManager_itemPath", array(), FALSE);
		$headers[] = literal::get("sdk.DEV.Version", "lbl_commitManager_lastAuthor", array(), FALSE);
		$headers[] = literal::get("sdk.DEV.Version", "lbl_commitManager_lastUpdate", array(), FALSE);

		if (count($forceItems) > 0)
		{
			// Header
			$title = literal::get("sdk.DEV.Version", "lbl_commitManager_forceCommitItems");
			$header = DOM::create("h4", $title);
			DOM::append($commitListContainer, $header);
			
			// Force Commit Item List
			$dataList = new dataGridList();
			$fCommitList = $dataList->build($id = "fCommitItems", $checkable = FALSE)->get();
			DOM::append($commitListContainer, $fCommitList);
			
			$dataList->setColumnRatios($ratios);
			$dataList->setHeaders($headers);
			
			foreach ($forceItems as $id => $item)
			{
				$rowContents = array();
				$rowContents[] = $item['path'];
				$rowContents[] = $item['last-edit-author'];
				$rowContents[] = datetimer::live($item['last-edit-time'], $format = 'd F, Y \a\t H:i');
				$dataList->insertRow($rowContents);
			}
		}

		// Header
		$title = literal::get("sdk.DEV.Version", "lbl_commitManager_selectItems");
		$header = DOM::create("h4", $title);
		DOM::append($commitListContainer, $header);
		
		// Commit Item List
		$dataList = new dataGridList();
		$commitList = $dataList->build($id = "commitItems", $checkable = TRUE)->get();
		
		$dataList->setColumnRatios($ratios);
		$dataList->setHeaders($headers);

		foreach ($workingItems as $id => $item)
		{
			// Skip force commit items
			if (isset($forceItems[$id]))
				continue;
				
			$rowContents = array();
			$rowContents[] = $item['path'];
			$rowContents[] = $item['last-edit-author'];
			$rowContents[] = datetimer::live($item['last-edit-time'], $format = 'd F, Y \a\t H:i');
			$dataList->insertRow($rowContents, $checkName = "citem[".$id."]", $checked = FALSE);
		}
		
		DOM::append($commitListContainer, $commitList);
		
		// Commit Summary
		$commitSumm = $form->getInput($type = "text", $name = "summary", $value = "", $class = "uiCommitSummText", $autofocus = TRUE, $required = TRUE);
		$ph = literal::get("sdk.DEV.Version", "lbl_commitManager_summary_ph", array(), FALSE);
		DOM::attr($commitSumm, "placeholder", $ph);
		$form->append($commitSumm);
		
		// Commit Description
		$commitDesc = $form->getTextarea($name = "description", $value = "", $class = "uiCommitDescText", $autofocus = FALSE);
		$ph = literal::get("sdk.DEV.Version", "lbl_commitManager_desc_ph", array(), FALSE);
		DOM::attr($commitDesc, "placeholder", $ph);
		$form->append($commitDesc);
		
		// Commit Tags
		$commitTags = $form->getInput($type = "text", $name = "tags", $value = "", $class = "uiCommitSummText", $autofocus = TRUE, $required = TRUE);
		$ph = literal::get("sdk.DEV.Version", "lbl_commitManager_tags_ph", array(), FALSE);
		DOM::attr($commitTags, "placeholder", $ph);
		$form->append($commitTags);
		
		// Commit
		$title = literal::get("sdk.DEV.Version", "lbl_commitManager_commit");
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
		$footer = DOM::create("div", "", "", "footer");
		$this->append($footer);
		
		$attr = array();
		$attr['version'] = "2.2";
		$title = literal::get("sdk.DEV.Version", "lbl_commitManager_versionSign", $attr);
		$sign = DOM::create("span", $title, "", "sign");
		DOM::append($footer, $sign);
	}
}
//#section_end#
?>