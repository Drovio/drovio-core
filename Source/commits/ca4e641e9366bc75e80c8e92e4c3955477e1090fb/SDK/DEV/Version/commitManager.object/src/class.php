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

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Security", "account");
importer::import("API", "Geoloc", "datetimer");
importer::import("API", "Literals", "literal");
importer::import("UI", "Forms", "templates::simpleForm");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "dataGridList");
importer::import("UI", "Presentation", "frames::windowFrame");
importer::import("DEV", "Version", "vcs");

use \API\Resources\DOMParser;
use \API\Resources\storage\session;
use \API\Security\account;
use \API\Geoloc\datetimer;
use \API\Literals\literal;
use \UI\Forms\templates\simpleForm;
use \UI\Html\DOM;
use \UI\Presentation\dataGridList;
use \UI\Presentation\frames\windowFrame;
use \DEV\Version\vcs;

/**
 * VCS Commit Manager
 * 
 * Displayes all the items that must be commited.
 * 
 * @version	{empty}
 * @created	February 12, 2014, 12:14 (EET)
 * @revised	May 20, 2014, 10:52 (EEST)
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
	 * 		The project's title.
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
		
		// Commit Item List
		$dataList = new dataGridList();
		$commitList = $dataList->build($id = "commitItems", $checkable = TRUE)->get();
		
		$ratios = array();
		$ratios[] = 0.6;
		$ratios[] = 0.2;
		$ratios[] = 0.2;
		$dataList->setColumnRatios($ratios);
		
		$headers = array();
		$headers[] = literal::get("sdk.DEV.Version", "lbl_commitManager_itemPath");
		$headers[] = literal::get("sdk.DEV.Version", "lbl_commitManager_lastAuthor");
		$headers[] = literal::get("sdk.DEV.Version", "lbl_commitManager_lastUpdate");
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
		$ph = literal::get("sdk.DEV.Version", "lbl_commitManager_summary_ph", array(), FALSE);
		DOM::attr($commitSumm, "placeholder", $ph);
		$form->append($commitSumm);
		
		// Commit Description
		$commitDesc = $form->getTextarea($name = "description", $value = "", $class = "uiCommitDescText", $autofocus = FALSE);
		$ph = literal::get("sdk.DEV.Version", "lbl_commitManager_desc_ph", array(), FALSE);
		DOM::attr($commitDesc, "placeholder", $ph);
		$form->append($commitDesc);
		
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
		
		// Get vcs info
		$info = $this->vcs->getInfo();
		$author = account::getAccountTitle();
		
		$attr = array();
		$attr['version'] = "2.2";
		$attr['author'] = $author;
		$title = literal::get("sdk.DEV.Version", "lbl_commitManager_versionSign", $attr);
		$sign = DOM::create("span", $title, "", "sign");
		DOM::append($footer, $sign);
	}
}
//#section_end#
?>