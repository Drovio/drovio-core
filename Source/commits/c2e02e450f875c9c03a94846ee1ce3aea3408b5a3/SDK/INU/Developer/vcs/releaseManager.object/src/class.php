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
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "storage::session");
importer::import("UI", "Forms", "templates::simpleForm");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "dataGridList");

use \ESS\Prototype\UIObjectPrototype;
use \API\Developer\misc\vcs;
use \API\Resources\DOMParser;
use \API\Resources\storage\session;
use \UI\Forms\templates\simpleForm;
use \UI\Html\DOM;
use \UI\Presentation\dataGridList;

class releaseManager extends UIObjectPrototype
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
	 * @param	string	$includeRelease
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
	 * Builts the commit Manager ui object.
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
	 * Builds the commit section.
	 * 
	 * @return	void
	 */
	private function buildVersionSection()
	{
		$body = $this->body;
		
		// Create release form
		$form = new simpleForm("releaseForm");
		$releaseForm = $form->build("", "/ajax/resources/sdk/vcs/release.php", FALSE)->get();
		DOM::append($body, $releaseForm);
		
		// Set vcs id
		$vcsID = $form->getInput($type = "hidden", $name = "vcs_id", $value = $this->id, $class = "", $autofocus = FALSE, $required = FALSE);
		$form->append($vcsID);
		
		/*
		
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
		*/
		
		
		// Release Title
		$input = $form->getInput($type = "text", $name = "title", $value = "", $class = "uiReleaseTitleText", $autofocus = TRUE, $required = FALSE);
		DOM::attr($input, "placeholder", "Release Title");
		$form->append($input);
		
		// Commit Description
		$input = $form->getTextarea($name = "description", $value = "", $class = "uiReleaseDescText", $autofocus = FALSE);
		DOM::attr($input, "placeholder", "Release Description");
		$form->append($input);
		
		// Commit
		$title = DOM::create("span", "commit");
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