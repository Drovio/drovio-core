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
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Environment", "session");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Profile", "account");
importer::import("API", "Geoloc", "datetimer");
importer::import("API", "Literals", "literal");
importer::import("UI", "Forms", "templates/simpleForm");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "dataGridList");
importer::import("UI", "Presentation", "frames/windowFrame");
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
 * Version Control History Manager
 * 
 * This is a dialog for restoring items from project repositories to working branch's trunk.
 * Displays all the commits in order to choose the items to restore to trunk.
 * 
 * @version	0.2-1
 * @created	June 28, 2014, 21:32 (EEST)
 * @updated	July 3, 2015, 0:35 (EEST)
 */
class historyManager extends windowFrame
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
		
		parent::__construct("uiHistoryManager_prj_".$id);
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
		session::set("projectID", $projectID, "historyManager_".$this->id);
	}
	
	/**
	 * Loads the information from the session.
	 * 
	 * @return	void
	 */
	private function load()
	{
		// Load session variables
		$projectID = session::get("projectID", NULL, "historyManager_".$this->id);
		
		// Create vcs object
		$this->vcs = new vcs($projectID);
	}
	
	/**
	 * Builds the history Manager ui object.
	 * 
	 * @param	string	$title
	 * 		The dialog's title.
	 * 
	 * @return	historyManager
	 * 		The historyManager object.
	 */
	public function build($title = "")
	{
		// Build frame
		$title = (empty($title) ? literal::get("sdk.DEV.Version", "lbl_historyManager_title") : $title);
		parent::build($title, $class = "uiHistoryManager");
		
		// History Section
		$this->buildHistorySection();
		
		// Return object
		return $this;
	}
	
	/**
	 * Builds the history list section.
	 * 
	 * @return	void
	 */
	private function buildHistorySection()
	{
		$historySection = DOM::create("div", "", "", "historySection");
		$this->append($historySection);
		
		// Commit Header
		$attr = array();
		$commitTitle = literal::get("sdk.DEV.Version", "lbl_commitManager_historyCommits", $attr);
		$commitHeader = DOM::create("h3", $commitTitle, "", "vcsHeader");
		DOM::append($historySection, $commitHeader);
		
		// Create commit form
		$form = new simpleForm("historyForm");
		$historyForm = $form->build("", "/ajax/resources/sdk/vcs/history/restore.php", FALSE)->get();
		DOM::append($historySection, $historyForm);
		
		// Set vcs id
		$vcsID = $form->getInput($type = "hidden", $name = "hmid", $value = $this->id, $class = "", $autofocus = FALSE, $required = FALSE);
		$form->append($vcsID);
		
		// Header
		$title = literal::get("sdk.DEV.Version", "lbl_historyManager_selectItems");
		$header = DOM::create("h4", $title);
		$form->append($header);
		
		$commitsContainer = DOM::create("div", "", $this->id, "cmContainer");
		$form->append($commitsContainer);
		
		// Restore Button
		$title = literal::get("sdk.DEV.Version", "lbl_commitManager_restore");
		$commitBtn = $form->getSubmitButton($title, $id = "restoreBtn");
		$form->append($commitBtn);
	}
}
//#section_end#
?>