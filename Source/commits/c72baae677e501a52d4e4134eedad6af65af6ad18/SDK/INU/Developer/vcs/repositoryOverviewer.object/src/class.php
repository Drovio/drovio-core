<?php
//#section#[header]
// Namespace
namespace INU\Developer\vcs;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	INU
 * @package	Developer
 * @namespace	\vcs
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
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

use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \API\Developer\misc\vcs;
use \API\Resources\storage\session;
use \API\Resources\literals\literal;
use \API\Resources\filesystem\directory;
use \API\Security\account;
use \UI\Html\DOM;
use \UI\Presentation\togglers\toggler;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	November 25, 2013, 11:16 (EET)
 * @revised	November 25, 2013, 11:16 (EET)
 */
class repositoryOverviewer extends UIObjectPrototype
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $id;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $vcs;
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$repository
	 * 		{description}
	 * 
	 * @param	{type}	$includeRelease
	 * 		{description}
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
	 * {description}
	 * 
	 * @param	{type}	$repository
	 * 		{description}
	 * 
	 * @param	{type}	$includeRelease
	 * 		{description}
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
	 * {description}
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
	 * {description}
	 * 
	 * @param	{type}	$projectName
	 * 		{description}
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
		$navTitle = DOM::create("div", "Commits", "", "navTitle");
		NavigatorProtocol::staticNav($navTitle, "vcsCommits", $targetcontainer, $targetgroup, $navgroup, $display = "none");
		DOM::append($navBar, $navTitle);
		
		// Branches
		$navTitle = DOM::create("div", "Branches", "", "navTitle");
		NavigatorProtocol::staticNav($navTitle, "vcsBranches", $targetcontainer, $targetgroup, $navgroup, $display = "none");
		DOM::append($navBar, $navTitle);
		
		// Releases
		$navTitle = DOM::create("div", "Releases", "", "navTitle");
		NavigatorProtocol::staticNav($navTitle, "vcsReleases", $targetcontainer, $targetgroup, $navgroup, $display = "none");
		DOM::append($navBar, $navTitle);
		
		
		$sectionsContainer = DOM::create("div", "", "vcsSections");
		DOM::append($detailsContainer, $sectionsContainer);
		
		// Overview Container
		$navContainer = $this->getOverviewSection($targetgroup);
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
	 * {description}
	 * 
	 * @param	{type}	$targetgroup
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function getOverviewSection($targetgroup)
	{
		// Create navigation container
		$navContainer = $this->getNavigationGroup("vcsOverview", $targetgroup);
		
		// Get Releases
		$vcsInfo = $this->vcs->getInfo();
		
		$header = DOM::create("h4", "Repository Overview");
		DOM::append($navContainer, $header);		
		
		// Repository Version
		$title = DOM::create("p", "Repository Controller version: ");
		$info = DOM::create("b", $vcsInfo['version']);
		DOM::append($title, $info);
		DOM::append($navContainer, $title);
		
		// Repository Branches
		$title = DOM::create("p", "Number of branches: ");
		$info = DOM::create("b", $vcsInfo['branches']);
		DOM::append($title, $info);
		DOM::append($navContainer, $title);
		
		// Repository Commits
		$title = DOM::create("p", "Number of commits: ");
		$info = DOM::create("b", $vcsInfo['commits']);
		DOM::append($title, $info);
		DOM::append($navContainer, $title);
		
		// Repository Releases
		$title = DOM::create("p", "Number of releases: ");
		$info = DOM::create("b", $vcsInfo['releases']);
		DOM::append($title, $info);
		DOM::append($navContainer, $title);
		
		// return container
		return $navContainer;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$targetgroup
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function getCommitSection($targetgroup)
	{
		// Create navigation container
		$navContainer = $this->getNavigationGroup("vcsCommits", $targetgroup);
		
		// Get authors
		$authors = $this->vcs->getAuthors();
		
		// Get branch commits
		$commits = $this->vcs->getBranchCommits();
		$commits = array_reverse($commits, TRUE);
		
		// Total commits
		if (count($commits) > 0)
		{
			$totalCommits = DOM::create("p", "There is a total of ".count($commits)." commits in this branch.");
			DOM::append($navContainer, $totalCommit);
		}
		
		foreach ($commits as $commitID => $commitData)
		{
			// Create toggler
			$tog = new toggler();
			
			// Set toggler data
			$commitDate = date("M j\, Y \a\\t H:i", $commitData['time']);
			$togHeader = DOM::create("span", "[".$commitDate."] - ");
			$cnt = DOM::create("span", $commitData['description']." - by ");
			DOM::append($togHeader, $cnt);
			$cnt = DOM::create("b", $authors[$commitData['author']]);
			DOM::append($togHeader, $cnt);
			$togBody = DOM::create("div", "", "", "commitInfo");
			
			// Header
			$header = DOM::create("div", "", "", "infoHeader");
			DOM::append($togBody, $header);
			
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
		
		// return container
		return $navContainer;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$targetgroup
	 * 		{description}
	 * 
	 * @return	void
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
	 * {description}
	 * 
	 * @param	{type}	$targetgroup
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function getReleaseSection($targetgroup)
	{
		// Create navigation container
		$navContainer = $this->getNavigationGroup("vcsReleases", $targetgroup);
		
		$title = DOM::create("p", "This BETA version doesn't support releases viewer yet.");
		DOM::append($navContainer, $title);
		
		// return container
		return $navContainer;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$groupSelector
	 * 		{description}
	 * 
	 * @return	void
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