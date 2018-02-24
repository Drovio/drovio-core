<?php
//#section#[header]
// Namespace
namespace DEV\Version\repository;

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
 * @namespace	\repository
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::NavigatorProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Resources", "literals::literal");
importer::import("API", "Resources", "filesystem::directory");
importer::import("API", "Security", "account");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "togglers::toggler");
importer::import("INU", "Views", "fileExplorer");

use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \API\Developer\misc\vcs;
use \API\Resources\storage\session;
use \API\Resources\literals\literal;
use \API\Resources\filesystem\directory;
use \API\Security\account;
use \UI\Html\DOM;
use \UI\Presentation\togglers\toggler;
use \INU\Views\fileExplorer;

/**
 * Repository Overview Manager
 * 
 * Builds a repository overviewer, including commits, releases and traunk and branch explorer.
 * 
 * @version	{empty}
 * @created	February 12, 2014, 12:26 (EET)
 * @revised	February 12, 2014, 12:26 (EET)
 */
class repositoryOverviewer extends UIObjectPrototype
{
	/**
	 * The control id.
	 * 
	 * @type	string
	 */
	private $id;
	/**
	 * The vcs object.
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
	 * @param	string	$repository
	 * 		The repository path. If empty, it will be loaded from session.
	 * 
	 * @param	boolean	$includeRelease
	 * 		Sets the includeRelease attribute for the vcs. FALSE by default.
	 * 
	 * @return	void
	 */
	public function __construct($id, $repository, $includeRelease = FALSE)
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
		session::set("repository", $repository, "repositoryOverviewer_".$this->id);
		session::set("includeRelease", $includeRelease, "repositoryOverviewer_".$this->id);
	}
	
	/**
	 * Loads the information from the session.
	 * 
	 * @return	void
	 */
	private function load()
	{
		// Load session variables
		$repository = session::get("repository", NULL, "repositoryOverviewer_".$this->id);
		$includeRelease = session::get("includeRelease", NULL, "repositoryOverviewer_".$this->id);
		
		// Create vcs object
		$this->vcs = new vcs($repository, $includeRelease);
	}
	
	/**
	 * Builds the commit Manager ui object.
	 * 
	 * @param	string	$projectName
	 * 		The project name.
	 * 
	 * @return	void
	 */
	public function build($projectName = "")
	{
		// Object holder
		$holder = DOM::create("div", "", "", "repositoryOverviewer");
		$this->set($holder);
		
		// Header
		$headerBar = DOM::create("div", "", "", "headBar");
		DOM::append($this->get(), $headerBar);
		
		// Developer's Profile
		$devProfile = DOM::create("div", "", "", "devProfile");
		DOM::append($headerBar, $devProfile);
		
		$author = DOM::create("h4", account::getFirstname()." ".account::getLastname());
		DOM::append($devProfile, $author);
		
		// Header Title
		$title = literal::get("sdk.INU.Developer", "vcs_headerBar_title");
		$header = DOM::create("h2", $title, "", "headTitle");
		$betaContent = DOM::create("span", "BETA", "", "beta");
		DOM::append($header, $betaContent);
		DOM::append($headerBar, $header);
		
		// Global Container
		$detailsContainer = DOM::create("div", "", "", "detailsContainer");
		DOM::append($this->get(), $detailsContainer);
		
		// Get Repository info
		$vcsInfo = $this->vcs->getInfo();
		
		// VCS Version
		$vcsVersion = DOM::create("span", "Repository Controller v".$vcsInfo['version'], "", "vcsVersion");
		DOM::append($detailsContainer, $vcsVersion);
		
		// Application Title
		$projectNameTitle = DOM::create("h3", $projectName);
		DOM::append($detailsContainer, $projectNameTitle);
		
		// Navigation Bar
		$navBar = DOM::create("div", "", "", "navBar");
		DOM::append($detailsContainer, $navBar);
		
		// Navigation attributes
		$targetcontainer = "vcsSections";
		$targetgroup = "vcsNavGroup";
		$navgroup = "vcsNav";
		
		// Overview
		$navTitle = DOM::create("div", "Overview", "", "navTitle");
		NavigatorProtocol::staticNav($navTitle, "vcsOverview", $targetcontainer, $targetgroup, $navgroup, $display = "none");
		DOM::append($navBar, $navTitle);
		DOM::appendAttr($navTitle, "class", "selected");
		
		// Commits
		$navTitle = DOM::create("div", "Commits (".$vcsInfo['commits'].")", "", "navTitle");
		NavigatorProtocol::staticNav($navTitle, "vcsCommits", $targetcontainer, $targetgroup, $navgroup, $display = "none");
		DOM::append($navBar, $navTitle);
		
		// Branches
		$navTitle = DOM::create("div", "Branches (".$vcsInfo['branches'].")", "", "navTitle");
		NavigatorProtocol::staticNav($navTitle, "vcsBranches", $targetcontainer, $targetgroup, $navgroup, $display = "none");
		DOM::append($navBar, $navTitle);
		
		// Releases
		$navTitle = DOM::create("div", "Releases (".$vcsInfo['releases'].")", "", "navTitle");
		NavigatorProtocol::staticNav($navTitle, "vcsReleases", $targetcontainer, $targetgroup, $navgroup, $display = "none");
		DOM::append($navBar, $navTitle);
		
		
		$sectionsContainer = DOM::create("div", "", "vcsSections");
		DOM::append($detailsContainer, $sectionsContainer);
		
		// Overview Container
		$navContainer = $this->getOverviewSection($targetgroup, $projectName);
		DOM::append($sectionsContainer, $navContainer);
		
		// Commits Container
		$navContainer = $this->getCommitSection($targetgroup);
		DOM::append($sectionsContainer, $navContainer);
		
		// Branches Container
		$navContainer = $this->getBranchSection($targetgroup);
		DOM::append($sectionsContainer, $navContainer);
		
		// Releases Container
		$navContainer = $this->getReleaseSection($targetgroup);
		DOM::append($sectionsContainer, $navContainer);
		
		return $this;
	}
	
	/**
	 * Builds the overview section of the control.
	 * 
	 * @param	string	$targetgroup
	 * 		The navigator target group.
	 * 
	 * @param	string	$projectName
	 * 		The project name to be set as header.
	 * 
	 * @return	DOMelement
	 * 		The section container element.
	 */
	private function getOverviewSection($targetgroup, $projectName)
	{
		// Create navigation container
		$navContainer = $this->getNavigationGroup("vcsOverview", $targetgroup);
		
		// Build fileExplorer
		$repository = session::get("repository", NULL, "repositoryOverviewer_".$this->id);
		$headBranch = $this->vcs->getHeadBranch();
		$headPath = $repository."/branches/".$headBranch;
		$fExplorer = new fileExplorer($headPath, "vcsOvExplorer_".$this->id, $projectName, $showHidden = TRUE);
		$vcsOverview = $fExplorer->build("", FALSE)->get();
		DOM::appendAttr($vcsOverview, "class", "vcsOvExplorer");
		DOM::append($navContainer, $vcsOverview);
		
		// return container
		return $navContainer;
	}
	
	/**
	 * Builds the commit section of the control.
	 * 
	 * @param	string	$targetgroup
	 * 		The navigator target group.
	 * 
	 * @return	DOMElement
	 * 		The section container element.
	 */
	private function getCommitSection($targetgroup)
	{
		// Create navigation container
		$navContainer = $this->getNavigationGroup("vcsCommits", $targetgroup);
		
		// Get authors
		$authors = $this->vcs->getAuthors();
		
		// Get branches
		$branches = $this->vcs->getBranches();
		foreach ($branches as $branchName => $branchData)
		{
			// Get branch commits
			$commits = $this->vcs->getBranchCommits($branchName);
			$commits = array_reverse($commits, TRUE);
			
			foreach ($commits as $commitID => $commitData)
			{
				// Create toggler
				$tog = new toggler();
				
				// Set toggler data
				$commitDate = date("M j\, Y \a\\t H:i", $commitData['time']);
				$togHeader = DOM::create("span", "[".$commitDate."] - ");
				$cnt = DOM::create("span", $commitData['summary']." - by ");
				DOM::append($togHeader, $cnt);
				$cnt = DOM::create("b", $authors[$commitData['author']]);
				DOM::append($togHeader, $cnt);
				$togBody = DOM::create("div", "", "", "commitInfo");
				
				// Header
				$header = DOM::create("div", "", "", "infoHeader");
				DOM::append($togBody, $header);
				
				// Commit Description
				if (!empty($commitData['description']))
				{
					$title = DOM::create("p", "Commit Description:");
					DOM::append($togBody, $title);
					$cDesc = DOM::create("p", $commitData['description'], "", "cDesc");
					DOM::append($togBody, $cDesc);
				}
				
				// Item List
				$title = DOM::create("p", "Items affected:");
				DOM::append($togBody, $title);
				$itemList = DOM::create("ol", "", "", "commitItems");
				DOM::append($togBody, $itemList);
				
				// Get commit items
				$commitItems = $this->vcs->getCommitItems($commitID);
				foreach ($commitItems as $itemID => $itemInfo)
				{
					$path = directory::normalize("/".$itemInfo['path']."/".$itemInfo['name']);
					$itemLi = DOM::create("li", $path);
					DOM::append($itemList, $itemLi);
				}
				
				// Build toggler
				$commitViewer = $tog->build($id = $commitID, $togHeader, $togBody, $open = FALSE)->get();
				DOM::append($navContainer, $commitViewer);
			}
			
			if (count($commits) == 0)
			{
				// No commits notification
				$noCommits = DOM::create("p", "There are no commits in this repository yet.");
				DOM::append($navContainer, $noCommits);
			}
		}
		
		// return container
		return $navContainer;
	}
	
	/**
	 * Builds the branch section of the control.
	 * 
	 * @param	string	$targetgroup
	 * 		The navigator target group.
	 * 
	 * @return	DOMElement
	 * 		The section container element.
	 */
	private function getBranchSection($targetgroup)
	{
		// Create navigation container
		$navContainer = $this->getNavigationGroup("vcsBranches", $targetgroup);
		
		$title = DOM::create("p", "This BETA version doesn't support branch viewer yet.");
		DOM::append($navContainer, $title);
		
		// return container
		return $navContainer;
	}
	
	/**
	 * Builds the release section of the control.
	 * 
	 * @param	string	$targetgroup
	 * 		The navigator target group.
	 * 
	 * @return	DOMElement
	 * 		The section container element.
	 */
	private function getReleaseSection($targetgroup)
	{
		// Create navigation container
		$navContainer = $this->getNavigationGroup("vcsReleases", $targetgroup);
		
		// Get releases
		$releases = $this->vcs->getReleases();
		foreach ($releases as $branchName => $releaseData)
		{
			// Release Container
			$branchReleaseContainer = DOM::create("div", "", "", "branchReleaseContainer");
			DOM::append($navContainer, $branchReleaseContainer);
			
			// Branch name
			$branchTitle = DOM::create("h3", $branchName, "", "releaseBranchTitle");
			DOM::append($branchReleaseContainer, $branchTitle);
			
			// List all releases
			$releaseData['releases'] = array_reverse($releaseData['releases']);
			foreach ($releaseData['releases'] as $releaseID => $releaseInfo)
			{
				$releaseContainer = DOM::create("div", "", $branchName."_".$releaseID, "releaseContainer");
				DOM::append($branchReleaseContainer, $releaseContainer);
				
				$releaseTitle = DOM::create("h4", $releaseInfo['title']);
				DOM::append($releaseContainer, $releaseTitle);
				
				$version = DOM::create("div", "version: ".$releaseInfo['version']." (build ".$releaseInfo['build'].")");
				DOM::append($releaseContainer, $version);
				
				$date = date("M j\, Y \a\\t H:i", $releaseInfo['time']);
				$dateCreated = DOM::create("div", "date created: ".$date);
				DOM::append($releaseContainer, $dateCreated);
			}
		}
		
		// No releases notification
		if (count($releases) == 0)
		{
			$noReleases = DOM::create("p", "There are no releases in this repository yet.");
			DOM::append($navContainer, $noReleases);
		}
		
		// return container
		return $navContainer;
	}
	
	/**
	 * Gets a navigation group container for the control.
	 * 
	 * @param	string	$id
	 * 		The container id.
	 * 
	 * @param	string	$groupSelector
	 * 		The navigation group selector.
	 * 
	 * @return	DOMelement
	 * 		The navigation group container element.
	 */
	private function getNavigationGroup($id, $groupSelector)
	{
		// Create Navigation Group
		$navGroup = DOM::create("div", "", $id);
		NavigatorProtocol::selector($navGroup, $groupSelector);
		
		return $navGroup;
	}
}
//#section_end#
?>