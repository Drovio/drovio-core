<?php
//#section#[header]
// Namespace
namespace API\Developer\misc;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\misc
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Profile", "person");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::directory");
importer::import("API", "Resources", "archive::zipManager");
importer::import("API", "Security", "account");

use \API\Profile\person;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\directory;
use \API\Resources\archive\zipManager;
use \API\Security\account;

/**
 * Version Control System
 * 
 * Manages version control for a given repository.
 * 
 * @version	{empty}
 * @created	September 10, 2013, 16:14 (EEST)
 * @revised	February 12, 2014, 12:04 (EET)
 * 
 * @deprecated	Use DEV\Version\vcs instead.
 */
class vcs
{
	/**
	 * The current version.
	 * 
	 * @type	string
	 */
	const VERSION = "2.1c";
	
	/**
	 * The inner version control folder.
	 * 
	 * @type	string
	 */
	const VCS_FOLDER = ".vcs/";
	/**
	 * The author's working folder.
	 * 
	 * @type	string
	 */
	const WORKING_FOLDER = ".working/";
	/**
	 * The trunk folder.
	 * 
	 * @type	string
	 */
	const TRUNK_FOLDER = "trunk/";
	/**
	 * The items' folder.
	 * 
	 * @type	string
	 */
	const ITEMS_FOLDER = "items/";
	/**
	 * The branches folder.
	 * 
	 * @type	string
	 */
	const BRANCHES_FOLDER = "branches/";
	/**
	 * The commits folder.
	 * 
	 * @type	string
	 */
	const COMMITS_FOLDER = "commits/";
	/**
	 * The release folder.
	 * 
	 * @type	string
	 */
	const RELEASE_FOLDER = "release/";
	/**
	 * The history release folder.
	 * 
	 * @type	string
	 */
	const HISTORY_FOLDER = "history/";
	/**
	 * The current version release folder.
	 * 
	 * @type	string
	 */
	const CURRENT_VERSION_FOLDER = "current/";
	/**
	 * The initial master branch.
	 * 
	 * @type	string
	 */
	const MASTER_BRANCH = "master";
	
	/**
	 * The repository root folder
	 * 
	 * @type	string
	 */
	private $repositoryRoot = "";
	/**
	 * The repository folder.
	 * 
	 * @type	string
	 */
	private $repository = "";
	/**
	 * Current working branch for author.
	 * 
	 * @type	string
	 */
	private $workingBranch;
	/**
	 * Current author id.
	 * 
	 * @type	string
	 */
	private $authorID;
	/**
	 * Defines whether the repository will include release or not.
	 * 
	 * @type	boolean
	 */
	private $includeRelease;
	
	/**
	 * Constructor method. Initializes class' variables.
	 * 
	 * @param	string	$repository
	 * 		The repository root folder.
	 * 
	 * @param	boolean	$includeRelease
	 * 		Defines whether the repository will include release or not.
	 * 
	 * @return	void
	 */
	public function __construct($repository, $includeRelease = FALSE)
	{
		// Set repository path (without systemRoot)
		$this->repositoryRoot = systemRoot."/".$repository."/";
		$this->repository = $this->repositoryRoot;
		
		// Set whether the repository will include release
		$this->includeRelease = $includeRelease;
		if ($this->includeRelease)
			$this->repository .= ".repository/";
	}
	
	/**
	 * Creates the repository vcs structure.
	 * 
	 * @return	void
	 */
	public function createStructure()
	{
		// Check if vcs folder already exists
		if (file_exists($this->repository.self::VCS_FOLDER))
			return;
			
		// Create folder structure
		$this->createFolderStructure();
		
		// Create indexing
		$this->createIndexStructure();
		
		// Create initial master branch
		$this->createBranch(self::MASTER_BRANCH);
		$this->checkout(self::MASTER_BRANCH);
		
		// Update VCS Info
		$this->updateInfo();
	}
	
	/**
	 * Creates a new branch to the repository.
	 * 
	 * @param	string	$branchName
	 * 		The new branch name.
	 * 
	 * @param	string	$parent
	 * 		The parent of the new branch.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if the branch already exists.
	 */
	public function createBranch($branchName, $parent = "")
	{
		// Check if branch doesn't already exist
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER."branches.xml", FALSE);
		$branchIndexRoot = $parser->evaluate("//branches")->item(0);
		$branchIndexItem = $parser->find($branchName);
		if (empty($branchIndexItem))
		{
			// Create new branch entry
			$branchIndexItem = $parser->create("branch", "", $branchName);
			$parser->attr($branchIndexItem, "parent", $parent);
			$parser->append($branchIndexRoot, $branchIndexItem);
			
			// Save file
			$parser->save($this->repository.self::VCS_FOLDER, "branches.xml");
		}
		else
			return FALSE;
		
		// Create branch folders
		folderManager::create($this->repository.self::VCS_FOLDER.self::ITEMS_FOLDER, $branchName);
		folderManager::create($this->repository.self::TRUNK_FOLDER, $branchName);
		folderManager::create($this->repository.self::BRANCHES_FOLDER, $branchName);
		
		// If this is the first branch, create indices
		if (empty($parent))
		{
			// Create branch items index
			$parser = new DOMParser();
			$root = $parser->create("items");
			$parser->append($root);
			// __ save index
			$parser->save($this->repository.self::VCS_FOLDER.self::ITEMS_FOLDER.$branchName."/", "index.xml");
		}
		else
		{
			// Copy branch folders contents
			// __ trunk folder
			$parentTrunkFolder = $this->repository.self::TRUNK_FOLDER.$parent."/";
			$newTrunkFolder = $this->repository.self::TRUNK_FOLDER.$branchName."/";
			folderManager::copy($parentTrunkFolder, $newTrunkFolder, TRUE);
			// __ branch folder
			$parentBranchFolder = $this->repository.self::BRANCHES_FOLDER.$parent."/";
			$newBranchFolder = $this->repository.self::BRANCHES_FOLDER.$branchName."/";
			folderManager::copy($parentBranchFolder, $newBranchFolder, TRUE);
			
			// Copy item index file
			$parentItemsIndexFile = $this->repository.self::VCS_FOLDER.self::ITEMS_FOLDER.$parent."/index.xml";
			$newItemsIndexFile = $this->repository.self::VCS_FOLDER.self::ITEMS_FOLDER.$branchName."/index.xml";
			fileManager::copy($parentItemsIndexFile, $newItemsIndexFile);
		}
		
		// Commits branch index
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER.self::COMMITS_FOLDER."index.xml", FALSE);
		$root = $parser->evaluate("//commits")->item(0);
		$branchElement = $parser->create("branch", "", $branchName);
		$parser->append($root, $branchElement);
		// __ update index
		$parser->update();
		
		// Get parent's last commit and set to the new branch
		$lastCommitID = $this->getLastCommit($parent);
		if ($lastCommitID)
			$this->setLastCommit($branchName, $lastCommitID);
		
		// Update VCS Info
		$this->updateInfo();
		return TRUE;
	}
	
	/**
	 * Creates a new item in the repository.
	 * 
	 * @param	string	$id
	 * 		The unique id of the item in the repository.
	 * 
	 * @param	string	$path
	 * 		The item's inner path in the repository.
	 * 
	 * @param	string	$name
	 * 		The item's name with the extension.
	 * 
	 * @param	boolean	$isFolder
	 * 		Defines whether this item is an object/item and is represented as folder.
	 * 
	 * @return	mixed
	 * 		False if an item exists with the same id, the item's trunk path otherwise.
	 */
	public function createItem($id, $path, $name, $isFolder = FALSE)
	{
		// Get working branch
		$workingBranch = $this->getWorkingBranch();
		
		// Normalize path
		$path = directory::normalize($path);
		
		// Create a new index item to the working branch
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER.self::ITEMS_FOLDER.$workingBranch."/index.xml", FALSE);
		$root = $parser->evaluate("//items")->item(0);
		$itemElement = $parser->find($id);
		if (!empty($itemElement))
			return FALSE;
		
		// Create item Element
		$itemElement = $parser->create("item", "", $id);
		$parser->attr($itemElement, "path", $path);
		$parser->attr($itemElement, "name", $name);
		$parser->attr($itemElement, "folder", $isFolder);
		$parser->append($root, $itemElement);
		// Update file
		$parser->update();
		
		
		// Create item's commit index file
		$parser = new DOMParser();
		$itemRoot = $parser->create("item", "", $id);
		$parser->append($itemRoot);
		$commits = $parser->create("commits");
		$parser->append($itemRoot, $commits);
		// Update file
		$parser->save($this->repository.self::VCS_FOLDER.self::ITEMS_FOLDER.$workingBranch."/", $id.".xml");
		
		// Return item path to the trunk
		return $this->updateItem($id, TRUE);
	}
	
	/**
	 * Updates the item's info and sets the item to be included on the next commit.
	 * 
	 * @param	string	$id
	 * 		The item's id to update.
	 * 
	 * @param	boolean	$forceCommit
	 * 		Defines whether this item will be forced to commit (it is used for newly created items).
	 * 
	 * @return	string
	 * 		The item's trunk path.
	 */
	public function updateItem($id, $forceCommit = FALSE)
	{
		// Update working index
		$this->updateWorkingIndexItem($id, $forceCommit);
		
		// Return item's trunk path
		return $this->getItemTrunkPath($id);
	}
	
	/**
	 * Gets the item's path to the trunk.
	 * 
	 * @param	string	$id
	 * 		The item's id.
	 * 
	 * @return	string
	 * 		The item's trunk path.
	 */
	public function getItemTrunkPath($id)
	{
		// Get working branch
		$workingBranch = $this->getWorkingBranch();
		
		// Return item's trunk path
		return $this->repository.self::TRUNK_FOLDER.$workingBranch."/".$this->getItemPath($id);
	}
	
	/**
	 * Get the item's path to the head branch.
	 * 
	 * @param	string	$id
	 * 		The item's id.
	 * 
	 * @return	string
	 * 		The item's head branch path.
	 */
	public function getItemHeadPath($id)
	{
		// Get working branch
		$headBranch = $this->getHeadBranch();
		
		// Return item's trunk path
		return $this->repository.self::BRANCHES_FOLDER.$headBranch."/".$this->getItemPath($id);
	}
	
	/**
	 * Gets the item's inner path.
	 * 
	 * @param	string	$id
	 * 		The item id.
	 * 
	 * @param	string	$branch
	 * 		The parent branch of the item.
	 * 
	 * @return	string
	 * 		The item's inner path.
	 */
	private function getItemPath($id, $branch = "")
	{
		// Load item info
		$info = $this->getItemInfo($id, $branch);
		if (!$info)
			return FALSE;
		
		// Return item's path
		return $info['path']."/".$info['name'].($info['folder'] ? "/" : "");
	}
	
	/**
	 * Gets the given item's information.
	 * 
	 * @param	string	$id
	 * 		The item's id.
	 * 
	 * @param	string	$branch
	 * 		The branch of the item.
	 * 
	 * @return	array
	 * 		Returns an array of information as follows:
	 * 		info['path'] = The item's path.
	 * 		info['name'] = The item's name (including the extension).
	 * 		info['folder'] = Whether the item is an object folder [T/F].
	 */
	private function getItemInfo($id, $branch = "")
	{
		// Get working branch
		if (empty($branch))
			$branch = $this->getWorkingBranch();
		
		// Create a new index item to the working branch
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER.self::ITEMS_FOLDER.$branch."/index.xml", FALSE);
		$itemElement = $parser->find($id);
		if (empty($itemElement))
			return FALSE;
		
		$info = array();
		$info['path'] = $parser->attr($itemElement, "path");
		$info['name'] = $parser->attr($itemElement, "name");
		$info['folder'] = $parser->attr($itemElement, "folder");
		return $info;
	}
	
	/**
	 * Commits a given list of items.
	 * 
	 * @param	string	$summary
	 * 		The commit summary.
	 * 
	 * @param	string	$description
	 * 		The commit extended description.
	 * 
	 * @param	array	$commitItems
	 * 		An array of this repository's item ids to include in the commit.
	 * 		These ids must match with the ids that the author's working index has.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if there are no items to commit.
	 */
	public function commit($summary, $description = "", $commitItems = array())
	{
		$finalCommit = FALSE;
		$finalCommitItems = array();
		
		// Check if there are items to commit
		$parser = new DOMParser();
		$root = $this->loadWorkingIndex($parser);
		$items = $parser->evaluate("//item");
		foreach ($items as $item)
		{
			$itemID = $parser->attr($item, "id");
			$force = $parser->attr($item, "force");
			if ($force || in_array($itemID, $commitItems))
			{
				$finalCommit = TRUE;
				$finalCommitItems[] = $itemID;
			}
		}
		
		// If there are not items to commit, return FALSE
		if (!$finalCommit)
			return FALSE;
			
		// If there are items to commit, create commit entry and directory with items
		
		// Get working branch
		$workingBranch = $this->getWorkingBranch();
		
		// Get timestamp and create commit id
		$timestamp = time();
		$commitID = "c".hash("sha1", "commit.".$timestamp);
		
		// Get parent commit (last commit of this branch
		$parentCommitID = $this->getLastCommit($workingBranch);
		
		// Create commit index entry
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER.self::COMMITS_FOLDER."index.xml", FALSE);
		$branchRoot = $parser->find($workingBranch);
		$commitElement = $parser->create("commit", "", $commitID);
		$commitSum = $parser->create("summary", $summary);
		$parser->append($commitElement, $commitSum);
		$commitDesc = $parser->create("description", $description);
		$parser->append($commitElement, $commitDesc);
		$parser->attr($commitElement, "time", $timestamp);
		$parser->attr($commitElement, "parent", $parentCommitID);
		$parser->attr($commitElement, "author", $this->getAuthorID());
		$parser->append($branchRoot, $commitElement);
		// Update file
		$parser->update();
		
		// Create commit index file
		$parser = new DOMParser();
		$commitRoot = $parser->create("commit", "", $commitID);
		$parser->append($commitRoot);
		$commitItems = $parser->create("items");
		$parser->append($commitRoot, $commitItems);
		foreach ($finalCommitItems as $itemID)
		{
			$itemElement = $parser->create("item", "", $itemID);
			$parser->append($commitItems, $itemElement);
		}
		// Save file
		$parser->save($this->repository.self::VCS_FOLDER.self::COMMITS_FOLDER.$commitID.".xml");
		
		// Get folders
		$commitFolder = $this->getCommitFolder($commitID);
		$branchFolder = $this->repository.self::BRANCHES_FOLDER;
		$trunkFolder = $this->repository.self::TRUNK_FOLDER;
		
		// Copy all item files
		folderManager::create($commitFolder);
		foreach ($finalCommitItems as $itemID)
		{
			// Get item path
			$itemPath = $this->getItemPath($itemID);
			$trunkItemPath = $trunkFolder.$workingBranch."/".$itemPath;
			$branchItemPath = $branchFolder.$workingBranch."/".$itemPath;
			$commitItemPath = $commitFolder.$itemPath;
			
			// Copy items
			if (is_dir($trunkItemPath))
			{
				folderManager::create($commitItemPath);
				folderManager::copy($trunkItemPath, $commitItemPath, TRUE);
				if (is_dir($branchItemPath))
					folderManager::remove($branchItemPath);
				folderManager::create($branchItemPath);
				folderManager::copy($trunkItemPath, $branchItemPath, TRUE);
			}
			else
			{
				// Create files recursively first...
				fileManager::create($commitItemPath, "", TRUE);
				fileManager::create($branchItemPath, "", TRUE);
				
				// Copy contents
				fileManager::copy($trunkItemPath, $commitItemPath);
				fileManager::copy($trunkItemPath, $branchItemPath);
			}
		}
		
		// Update for each item the commitID
		$parser = new DOMParser();
		foreach ($finalCommitItems as $itemID)
		{
			$parser->load($this->repository.self::VCS_FOLDER.self::ITEMS_FOLDER.$workingBranch."/".$itemID.".xml", FALSE);
			$commitsRoot = $parser->evaluate("//commits")->item(0);
			$commitEntry = $parser->create("commit", "", $commitID);
			$parser->append($commitsRoot, $commitEntry);
			// Update file
			$parser->update();
		}
		
		// Create commit working index
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER.self::WORKING_FOLDER."working.xml", FALSE);
		foreach ($finalCommitItems as $itemID)
		{
			$itemElement = $parser->evaluate("//branch[@id='".$workingBranch."']/items/item[@id='".$itemID."']")->item(0);
			$parser->replace($itemElement, NULL);
		}
		$parser->update();
		
		// Clear working index from commited items
		$parser = new DOMParser();
		$this->loadWorkingIndex($parser);
		foreach ($finalCommitItems as $itemID)
		{
			$itemElement = $parser->find($itemID);
			$parser->replace($itemElement, NULL);
		}
		// Update file
		$parser->update();
		
		// Set working branch last commit
		$this->setLastCommit($workingBranch, $commitID);
		
		// Update VCS Info
		$this->updateInfo();
		
		return TRUE;
	}
	
	/**
	 * Gets the commit folder of the repository.
	 * 
	 * @param	string	$commitID
	 * 		The commit id.
	 * 
	 * @return	string
	 * 		The commit's folder.
	 */
	private function getCommitFolder($commitID)
	{
		return $this->repository.self::COMMITS_FOLDER.$commitID."/";
	}
	
	/**
	 * Sets the last commit of the given branch.
	 * 
	 * @param	string	$branchName
	 * 		The branch name.
	 * 
	 * @param	string	$commitID
	 * 		The last commit id.
	 * 
	 * @return	boolean
	 * 		True on success, false if the branch doesn't exist.
	 */
	private function setLastCommit($branchName, $commitID)
	{
		// Get branch
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER."branches.xml", FALSE);
		$currentBranch = $parser->find($branchName);
		if (empty($currentBranch))
			return FALSE;
		
		$parser->attr($currentBranch, "lastCommit", $commitID);
		$parser->update();
		return TRUE;
	}
	
	/**
	 * Gets the last commit id of the given branch.
	 * 
	 * @param	string	$branchName
	 * 		The branch name.
	 * 
	 * @return	string
	 * 		The commit id.
	 */
	private function getLastCommit($branchName)
	{
		// Get branch
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER."branches.xml", FALSE);
		$currentBranch = $parser->find($branchName);
		if (empty($currentBranch))
			return FALSE;
		
		// Get last commit id (if not exists, set to empty)
		$lastCommitID = $parser->attr($currentBranch, "lastCommit");
		return $lastCommitID;
	}
	
	/**
	 * Sets the head of this repository to the given branch.
	 * 
	 * @param	string	$branchName
	 * 		The branch name to checkout.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function checkout($branchName)
	{
		$branchName = trim($branchName);
		if (empty($branchName))
			return FALSE;
		
		// Load branches index
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER."branches.xml", FALSE);
		
		// Check if there is a branch with this name
		$branchIndexItem = $parser->find($branchName);
		if (empty($branchIndexItem))
			return FALSE;
		
		// Update Head Branch
		$headBranch = $parser->evaluate("//head")->item(0);
		$parser->nodeValue($headBranch, $branchName);
		// __ update file
		$parser->update();
		
		return TRUE;
	}
	
	/**
	 * Creates a release of this project, of the given branch.
	 * 
	 * @param	string	$branchName
	 * 		The branch name to release.
	 * 
	 * @param	string	$version
	 * 		The version of the release.
	 * 		If must be valid version number ({major}.{minor}).
	 * 
	 * @param	string	$title
	 * 		The release title.
	 * 
	 * @param	string	$description
	 * 		The release description.
	 * 
	 * @return	string
	 * 		The release folder.
	 */
	public function release($branchName, $version, $title, $description)
	{
		// Check if its a valid branch
		$branchName = trim($branchName);
		$vcsBranches = $this->getBranches();
		if (empty($branchName) || !isset($vcsBranches[$branchName]))
			return FALSE;
		
		// Get releases
		$vcsReleases = $this->getReleases();
		
		// Validate version
		if (!$this->validateVersion($version))
			return FALSE;
		
		// Check if there is version overlap
		// (this branch should have a version bigger than its parent's base version, if any)
		$parentBranch = $vcsBranches[$branchName]['parent'];
		$parentBaseVersion = $vcsReleases[$parentBranch]['base'];
		if (!empty($parentBaseVersion) && $this->versionCompare($parentBaseVersion, $version) <= 0)
			return FALSE;
		
		
		// Update release index
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER."releases.xml", FALSE);
		$root = $parser->evaluate("//releases")->item(0);
		$branchEntry = $parser->evaluate("//releases/branch[@name='".$branchName."']")->item(0);
		if (is_null($branchEntry))
		{
			$branchEntry = $parser->create("branch");
			$parser->attr($branchEntry, "name", $branchName);
			$parser->attr($branchEntry, "base", $version);
			$parser->append($root, $branchEntry);
			
			$branchHistory = $parser->create("history");
			$parser->append($branchEntry, $branchHistory);
			
			$parser->update();
		}
		
		// Set build number
		$versionBuild = $parser->attr($branchEntry, "build");
		$versionBuild = (empty($versionBuild) ? 0 : $versionBuild);
		$currentVersion = $parser->attr($branchEntry, "current");
		if (empty($currentVersion))
			$versionBuild = 1;
		else if (!empty($currentVersion) && $this->versionCompare($currentVersion, $version) < 0)
			$versionBuild = 1;
		else if (!empty($currentVersion) && $this->versionCompare($currentVersion, $version) > 0)
		{
			// If release version is lower than previously current, return FALSE
			return FALSE;
		}
		else
			$versionBuild = intval($versionBuild) + 1;
		
		// Update version and build
		$parser->attr($branchEntry, "build", "".$versionBuild);
		$parser->attr($branchEntry, "current", "".$version);
		
		// Set version id
		$versionID = "v".$version.".".$versionBuild;
		$versionTime = time();
		
		// Create version entry
		$versionEntry = $parser->create("release", $description, $versionID);
		$parser->attr($versionEntry, "title", $title);
		$parser->attr($versionEntry, "version", $version);
		$parser->attr($versionEntry, "build", $versionBuild);
		$parser->attr($versionEntry, "time", $versionTime);
		$parser->append($branchEntry, $versionEntry);
		
		$parser->update();
		
		
		// Get branch folder
		$branchFolder = $this->repository.self::BRANCHES_FOLDER.$branchName;
		
		// Copy files to current version
		$currentVersionFolder = $this->repository.self::RELEASE_FOLDER.self::CURRENT_VERSION_FOLDER.$branchName."/";
		folderManager::create($currentVersionFolder);
		folderManager::clean($currentVersionFolder);
		folderManager::copy($branchFolder, $currentVersionFolder, TRUE);
		
		// Create zip version zipManager
		$releaseZipFile = $this->repository.self::RELEASE_FOLDER.self::HISTORY_FOLDER.$branchName."_".$version.".zip";
		$releaseContents = directory::getContentList($currentVersionFolder."/", TRUE);
		zipManager::create($releaseZipFile, $releaseContents, TRUE, TRUE);
		
		// Update VCS Info
		$this->updateInfo();
		
		// Return path to release folder
		return $currentVersionFolder;
	}
	
	/**
	 * Merges a given branch to another branch.
	 * 
	 * @param	string	$branch1
	 * 		The branch to be merged.
	 * 
	 * @param	string	$branch2
	 * 		The branch to be merged to.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function merge($branch1, $branch2)
	{
		// Merge branch1 to branch2
	}
	
	/**
	 * Gets the current head branch of this repository.
	 * 
	 * @return	string
	 * 		The branch name.
	 */
	public function getHeadBranch()
	{
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER."branches.xml", FALSE);
		$headBranch = $parser->evaluate("//head")->item(0);
		return $parser->nodeValue($headBranch);
	}
	
	/**
	 * Sets the working branch for the current author.
	 * 
	 * @param	string	$branchName
	 * 		The branch to set as working.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setWorkingBranch($branchName)
	{
		// Check if its a valid branch
		$branchName = trim($branchName);
		$vcsBranches = $this->getBranches();
		if (empty($branchName) || !isset($vcsBranches[$branchName]))
			return FALSE;
		
		// Get Working index root
		$parser = new DOMParser();
		$root = $this->loadWorkingIndex($parser);
		
		// Get working branch element and update it (or create it)
		$workingBranch = $parser->evaluate("//branch")->item(0);
		if (empty($workingBranch))
		{
			// Create new working branch
			$workingBranch = $parser->create("branch", "", $branchName);
			$parser->prepend($root, $workingBranch);
		}
		else
		{
			// Working index must be empty to change
			$itemsCount = $parser->evaluate("//item")->length;
			if ($itemsCount > 0)
				return FALSE;
			
			// Update working branch
			$parser->attr($workingBranch, "id", $branchName);
		}
		
		// Update File
		$parser->update();
		$this->workingBranch = $branchName;
		return TRUE;
	}
	
	/**
	 * Gets the current author's working branch.
	 * 
	 * @return	string
	 * 		The branch name.
	 */
	public function getWorkingBranch()
	{
		// Get local variable (if set)
		if (!empty($this->workingBranch))
			return $this->workingBranch;
		
		// Load Working index file
		$parser = new DOMParser();
		$this->loadWorkingIndex($parser);
		
		// Get working branch (or set if not exists)
		$workingBranch = $parser->evaluate("//branch")->item(0);
		if (empty($workingBranch))
			$this->setWorkingBranch(self::MASTER_BRANCH);
		else
			$this->workingBranch = $parser->attr($workingBranch, "id");
		
		return $this->workingBranch;
	}
	
	/**
	 * Gets information about this repository.
	 * 
	 * @return	array
	 * 		Returns an array of information as follows:
	 * 		info['branches'] = the number of branches.
	 * 		info['commits'] = the total number of commits.
	 * 		info['releases'] = the total number of releases.
	 */
	public function getInfo()
	{
		$info = array();
		$parser = new DOMParser();
		
		// Load index file
		$parser->load($this->repository.self::VCS_FOLDER."index.xml", FALSE);
		$version = $parser->evaluate("//version")->item(0);
		$info['version'] = $parser->nodeValue($version);
		$branches = $parser->evaluate("//branches")->item(0);
		$info['branches'] = $parser->nodeValue($branches);
		$commits = $parser->evaluate("//commits")->item(0);
		$info['commits'] = $parser->nodeValue($commits);
		$releases = $parser->evaluate("//releases")->item(0);
		$info['releases'] = $parser->nodeValue($releases);
		
		return $info;
	}
	
	/**
	 * Gets the current author's full name.
	 * 
	 * @return	string
	 * 		The author's full name.
	 */
	public function getCurrentAuthor()
	{
		// Get all authors
		$authors = $this->getAuthors();
		
		// Get current author name
		return $authors[$this->getAuthorID()];
	}	
	
	/**
	 * Gets all the branches of this repository.
	 * 
	 * @return	array
	 * 		An array of all branches by name.
	 */
	public function getBranches()
	{
		$vcsBranches = array();
		$parser = new DOMParser();
		
		// Get number of branches
		$parser->load($this->repository.self::VCS_FOLDER."branches.xml", FALSE);
		$branches = $parser->evaluate("//branch");
		foreach ($branches as $branch)
		{
			$branchName = $parser->attr($branch, "id");
			
			$info = array();
			$info['name'] = $parser->attr($branch, "id");
			$info['parent'] = $parser->attr($branch, "parent");
			
			$vcsBranches[$branchName] = $info;
		}
		
		return $vcsBranches;
	}
	
	/**
	 * Gets all the releases of this repository per branch.
	 * 
	 * @return	array
	 * 		An array of information as follows:
	 * 		releases['branchName'] = 
	 * 		array['releaseID'] = 
	 * 		[title] = The release title
	 * 		[version] = The release version
	 * 		[build] = The release version build
	 * 		[time] = The release time
	 * 		[description] = The release description.
	 */
	public function getReleases()
	{
		$vcsReleases = array();
		
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER."releases.xml", FALSE);
		$branchEntries = $parser->evaluate("//releases/branch");
		foreach ($branchEntries as $be)
		{
			$branchName = $parser->attr($be, "name");
			
			// Branch info
			$branchInfo = array();
			$branchInfo['base'] = $parser->attr($be, "base");
			$branchInfo['current'] = $parser->attr($be, "current");
			$branchInfo['build'] = $parser->attr($be, "build");
			
			// Branch Releases
			$branchReleases = array();
			$releases = $parser->evaluate("release", $be);
			foreach ($releases as $rel)
			{
				$releaseVersionID = $parser->attr($rel, "id");
				
				// Release info
				$releaseInfo = array();
				$releaseInfo['title'] = $parser->attr($rel, "title");
				$releaseInfo['version'] = $parser->attr($rel, "version");
				$releaseInfo['build'] = $parser->attr($rel, "build");
				$releaseInfo['time'] = $parser->attr($rel, "time");
				$releaseInfo['description'] = $parser->nodeValue($rel);
				
				$branchReleases[$releaseVersionID] = $releaseInfo;
			}
			$branchInfo['releases'] = $branchReleases;
			
			$vcsReleases[$branchName] = $branchInfo;
		}
		
		return $vcsReleases;
	}
	
	/**
	 * Gets the current release folder path for the given branch.
	 * 
	 * @param	string	$branchName
	 * 		The branch name to get the release folder.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getCurrentRelease($branchName = self::MASTER_BRANCH)
	{
		return $this->repository.self::RELEASE_FOLDER.self::CURRENT_VERSION_FOLDER.$branchName."/";
	}
	
	/**
	 * Gets all the commits of a given branch.
	 * 
	 * @param	string	$branchName
	 * 		The banch to get the commits from.
	 * 
	 * @return	array
	 * 		An array of commits as follows:
	 * 		info[commitID] =
	 * 		[time] = "commit time"
	 * 		[parent] = "commit parent"
	 * 		[author] = "commit author"
	 * 		[description] = "commit description".
	 */
	public function getBranchCommits($branchName = self::MASTER_BRANCH)
	{
		$info = array();
		$parser = new DOMParser();
		
		// Load branch's commit index
		try
		{
			$parser->load($this->repository.self::VCS_FOLDER.self::COMMITS_FOLDER."index.xml", FALSE);
		}
		catch (Exception $ex)
		{
			// If the file doesn't exist, return empty info
			return $info;
		}
		$branchRoot = $parser->find($branchName);
		$commits = $parser->evaluate("commit", $branchRoot);
		foreach ($commits as $commit)
		{
			// Commit id
			$commitID = $parser->attr($commit, "id");
			
			$commitInfo = array();
			$commitInfo["time"] = $parser->attr($commit, "time");
			$commitInfo["parent"] = $parser->attr($commit, "parent");
			$commitInfo["author"] = $parser->attr($commit, "author");
			
			// Get summary and description
			$commitSum = $parser->evaluate("summary", $commit)->item(0);
			if (!is_null($commitSum))
				$commitInfo["summary"] = $commitSum->nodeValue;
			else
				$commitInfo["summary"] = $parser->innerHTML($commit);

			$commitDesc = $parser->evaluate("description", $commit)->item(0);
			if (!is_null($commitDesc))
				$commitInfo["description"] = $commitDesc->nodeValue;
			
			// Add info to result
			$info[$commitID] = $commitInfo;
		}
		
		return $info;
	}
	
	/**
	 * Gets all the commits that the given item is part of.
	 * 
	 * @param	string	$itemID
	 * 		The item's id.
	 * 
	 * @param	string	$branchName
	 * 		The branch name.
	 * 
	 * @return	array
	 * 		An array of commits as follows:
	 * 		info[commitID] =
	 * 		[time] = "commit time"
	 * 		[parent] = "commit parent"
	 * 		[author] = "commit author"
	 * 		[description] = "commit description".
	 */
	public function getItemCommits($itemID, $branchName = self::MASTER_BRANCH)
	{
		$info = array();
		$parser = new DOMParser();
		
		// Load item's commit index
		try
		{
			$parser->load($this->repository.self::VCS_FOLDER.self::ITEMS_FOLDER.$branchName."/".$itemID.".xml", FALSE);
		}
		catch (Exception $ex)
		{
			// If the file doesn't exist, return empty info
			return $info;
		}
		
		// Get item commits
		$commits = $parser->evaluate("//commit");
		$itemCommits = array();
		foreach ($commits as $commit)
			$itemCommits[] = $parser->attr($commit, "id");
		
		// Get commit info
		$parser->load($this->repository.self::VCS_FOLDER.self::COMMITS_FOLDER."index.xml", FALSE);
		foreach ($itemCommits as $commitID)
		{
			$commit = $parser->find($commitID);
			
			$commitInfo = array();
			$commitInfo["time"] = $parser->attr($commit, "time");
			$commitInfo["parent"] = $parser->attr($commit, "parent");
			$commitInfo["author"] = $parser->attr($commit, "author");
			$commitInfo["description"] = $parser->innerHTML($commit);
			
			// Add info to result
			$info[$commitID] = $commitInfo;
		}
		
		return $info;
	}
	
	/**
	 * Gets all item info that were included in the given commit id.
	 * 
	 * @param	string	$commitID
	 * 		The commit id.
	 * 
	 * @return	array
	 * 		An array of item information as follows:
	 * 		item['itemID'] = 
	 * 		info['path'] = "The item's path"
	 * 		info['name'] = "The item's name"
	 * 		info['folder'] = "Whether the item is object as folder [T/F]".
	 */
	public function getCommitItems($commitID)
	{
		$info = array();
		$parser = new DOMParser();
		
		// Get commit branch name
		$parser->load($this->repository.self::VCS_FOLDER.self::COMMITS_FOLDER."index.xml", FALSE);
		$commit = $parser->find($commitID);
		$commitBranchName = $parser->attr($commit->parentNode, "id");
		
		// Load item's commit index
		try
		{
			$parser->load($this->repository.self::VCS_FOLDER.self::COMMITS_FOLDER."/".$commitID.".xml", FALSE);
		}
		catch (Exception $ex)
		{
			// If the file doesn't exist, return empty info
			return $info;
		}
		$items = $parser->evaluate("//item");
		foreach ($items as $item)
		{
			$itemID = $parser->attr($item, "id");
			$info[$itemID] = $this->getItemInfo($itemID, $commitBranchName);
		}
		
		return $info;
	}
	
	/**
	 * Get the current working items.
	 * 
	 * @return	array
	 * 		An array of items as follows:
	 * 		item[itemID][path]
	 * 		item[itemID][force].
	 */
	public function getWorkingItems()
	{
		$workingItems = array();
		$parser = new DOMParser();
		
		// Load working file
		$root = $this->loadWorkingIndex($parser);
		$items = $parser->evaluate("//item");
		foreach ($items as $item)
		{
			// Get last edit author
			$mParser = new DOMParser();
			try
			{
				$mParser->load($this->repository.self::VCS_FOLDER.self::WORKING_FOLDER."working.xml", FALSE);
				$itemElement = $mParser->evaluate("//branch[@id='".$workingBranch."']/items/item[@id='".$itemID."']")->item(0);
				if (!is_null($itemElement))
				{
					$author = $mParser->attr($itemElement, "last-edit-author");
					$lastEdit = $mParser->attr($itemElement, "last-edit-time");
				}
				else
					$author = $this->getAuthorID();
			}
			catch (Exception $ex)
			{
				$author = $this->getAuthorID();
			}
			
			$itemID = $parser->attr($item, "id");
			$info = $this->getItemInfo($itemID);
			$workingItems[$itemID]['path'] = directory::normalize($info['path']."/".$info['name']);
			$workingItems[$itemID]['last-edit-author'] = $author;
			$workingItems[$itemID]['last-edit-time'] = (isset($lastEdit) ? $lastEdit : $parser->attr($item, "last-edit-time"));
			$workingItems[$itemID]['force'] = $parser->attr($item, "force");
		}
		
		return $workingItems;
	}
	
	/**
	 * Compares two given version numbers.
	 * 
	 * @param	string	$version1
	 * 		The version 1 to compare.
	 * 
	 * @param	string	$version2
	 * 		The version 2 to compare.
	 * 
	 * @return	integer
	 * 		Returns -1 if $version1 is smaller than $version2, 1 if $version1 is bigger than $version2 and 0 if $version1 equals $version2.
	 */
	private function versionCompare($version1, $version2)
	{
		$vparts1 = explode(".", $version1);
		$vparts2 = explode(".", $version2);
		foreach ($vparts1 as $key => $vpart1)
		{
			if (intval($vpart1) < intval($vparts2[$key]))
				return -1;
			else if (intval($vpart1) > intval($vparts2[$key]))
				return 1;
		}
		
		// If there are no differences in the common parts, check if version2 has more parts
		if (count($vparts1) < count($vparts2))
			return -1;
		
		return 0;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$version
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function validateVersion($version)
	{
		// Validate parts
		$vparts = explode(".", $version);
		foreach ($vparts as $vpart)
			if (!is_numeric($vpart))
				return FALSE;
		
		// Validate to be more than 0.0
		if ($this->versionCompare($version, "0.0") <= 0)
			return FALSE;
		
		return TRUE;
	}
	
	/**
	 * Calculates the information of this repository.
	 * 
	 * @return	array
	 * 		An array of information as follows:
	 * 		info['branches'] = the number of branches
	 * 		info['commits'] = the total number of commits
	 * 		info['releases'] = the number of releases.
	 */
	private function calculateInfo()
	{
		$info = array();
		$parser = new DOMParser();
		
		// Get number of branches
		$parser->load($this->repository.self::VCS_FOLDER."branches.xml", FALSE);
		$branches = $parser->evaluate("//branch");
		$info['branches'] = $branches->length;
		
		// Get number of commits for each branch
		$commitsCount = 0;
		foreach ($branches as $branch)
		{
			$branchName = $parser->attr($branch, "id");
			try
			{
				$parser->load($this->repository.self::VCS_FOLDER.self::COMMITS_FOLDER."index.xml", FALSE);
				$commits = $parser->evaluate("//commit");
				$commitsCount += $commits->length;
			}
			catch (Exception $ex)
			{
				$commitsCount += 0;
			}
		}
		$info['commits'] = $commitsCount;
		
		// Get Release count
		$parser->load($this->repository.self::VCS_FOLDER."releases.xml", FALSE);
		$releases = $parser->evaluate("//release");
		$info['releases'] = $releases->length;
		
		return $info;
	}
	
	/**
	 * Updates the vcs info given by the calculateInfo() function.
	 * 
	 * @return	void
	 */
	private function updateInfo()
	{
		// Get VCS Info
		$info = $this->calculateInfo();
		
		// Update Info
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER."index.xml", FALSE);
		
		// Update branches
		$branches = $parser->evaluate("//branches")->item(0);
		$parser->nodeValue($branches, $info['branches']);
		$commits = $parser->evaluate("//commits")->item(0);
		$parser->nodeValue($commits, $info['commits']);
		$releases = $parser->evaluate("//releases")->item(0);
		$parser->nodeValue($releases, $info['releases']);
		
		// Update file
		$parser->save($this->repository.self::VCS_FOLDER, "index.xml");
	}
	
	/**
	 * Updates the author's working item and inserts the given item id as item updated.
	 * 
	 * @param	string	$itemID
	 * 		The item id.
	 * 
	 * @param	boolean	$forceCommit
	 * 		Defines whether this item will be part of the next commit (for newly created items).
	 * 
	 * @return	void
	 */
	private function updateWorkingIndexItem($itemID, $forceCommit = FALSE)
	{
		$parser = new DOMParser();
		$timestamp = time();
		
		// Update general working index
		$workingBranch = $this->getWorkingBranch();
		try
		{
			$parser->load($this->repository.self::VCS_FOLDER.self::WORKING_FOLDER."working.xml", FALSE);
			$root = $parser->evaluate("//working")->item(0);
		}
		catch (Exception $ex)
		{
			// Create index file
			$root = $parser->create("working");
			$parser->append($root);
			$parser->save($this->repository.self::VCS_FOLDER.self::WORKING_FOLDER."working.xml", "", TRUE);
		}
		
		// Get branch root (or create it if doesn't exist)
		$branchRoot = $parser->find($workingBranch);
		if (is_null($branchRoot))
		{
			$branchRoot = $parser->create("branch", "", $workingBranch);
			$parser->append($root, $branchRoot);
			
			$itemsRoot = $parser->create("items");
			$parser->append($branchRoot, $itemsRoot);
			$parser->update();
		}
		else
			$itemsRoot = $parser->evaluate("items", $branchRoot)->item(0);
		
		// Search for item in the branch
		$itemElement = $parser->evaluate("//branch[@id='".$workingBranch."']/items/item[@id='".$itemID."']")->item(0);
		if (is_null($itemElement))
		{
			$itemElement = $parser->create("item", "", $itemID);
			$parser->append($itemsRoot, $itemElement);
		}
		
		// Extra data for item
		$authorID = $this->getAuthorID();
		$parser->attr($itemElement, "last-edit-author", $authorID);
		$parser->attr($itemElement, "last-edit-time", $timestamp);
		// Update working file
		$parser->update();
		
		// Load working index root
		$root = $this->loadWorkingIndex($parser);
		$itemElement = $parser->find($itemID);
		if (empty($itemElement))
		{
			// Create item element
			$itemsRoot = $parser->evaluate("//items")->item(0);
			$itemElement = $parser->create("item", "", $itemID);
			$parser->append($itemsRoot, $itemElement);
		}
		
		// Extra data for item
		$parser->attr($itemElement, "last-edit-time", $timestamp);
		
		if ($forceCommit)
			$parser->attr($itemElement, "force", $forceCommit);
		
		// Update file
		$parser->update();
	}
	
	/**
	 * The current author participates in this repository.
	 * 
	 * @return	boolean
	 * 		True on success, false if the author is already a member.
	 */
	public function contribute()
	{
		// Get author's id
		$authorID = $this->getAuthorID();
		
		// Load authors
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER."authors.xml", FALSE);
		$author = $parser->find($authorID);
		
		// If author exists, update author name
		if (!empty($author))
			return TRUE;
		/*
		{
			// Check if the account is shared
			if (account::isAdmin())
				$authorName = person::getFirstname()." ".person::getLastname();
			else
				$authorName = account::getAccountTitle();
			
			// Update
			//$parser->attr($author, "name", $authorName);
			$parser->update();
			
			return TRUE;
		}*/
		
		// Create author entry
		$root = $parser->evaluate("//authors")->item(0);
		$author = $parser->create("author", "", $authorID);
		$authorName = person::getFirstname()." ".person::getLastname();
		$parser->attr($author, "name", $authorName);
		$parser->append($root, $author);
		$parser->update();
		
		// Create working index
		$parser = new DOMParser();
		$root = $parser->create("author", "", $authorID);
		$parser->append($root);
		$items = $parser->create("items");
		$parser->append($root, $items);
		$parser->save($this->repository.self::VCS_FOLDER.self::WORKING_FOLDER, $authorID.".xml");
		
		return TRUE;
	}
	
	/**
	 * Return all the authors of the current repository.
	 * 
	 * @return	array
	 * 		An array of id=>authorName.
	 */
	public function getAuthors()
	{
		// Initialize authors
		$authors = array();
		
		// Load authors
		$parser = new DOMParser();
		$parser->load($this->repository.self::VCS_FOLDER."authors.xml", FALSE);
		$authorsElements = $parser->evaluate("//author");
		foreach ($authorsElements as $author)
		{
			$authorID = $parser->attr($author, "id");
			$authorName = $parser->attr($author, "name");
			$authors[$authorID] = $authorName;
		}
		
		return $authors;
	}
	
	/**
	 * Gets the current author's id.
	 * 
	 * @return	string
	 * 		The author id.
	 */
	private function getAuthorID()
	{
		if (empty($this->authorID))
		{
			// Get user's unique hashname
			$accountID = account::getAccountID();
			$this->authorID = "a".hash("md5", "author.".$accountID);
		}
		
		return $this->authorID;
	}
	
	/**
	 * Loads the current author's working index file. If the file doesn't exist, it is created and the author is registered to this repository.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser to load the file.
	 * 
	 * @return	DOMElement
	 * 		The root element.
	 */
	private function loadWorkingIndex($parser)
	{
		// Contribute author to repository
		$this->contribute();
		
		// Load working index
		$authorID = $this->getAuthorID();
		$parser->load($this->repository.self::VCS_FOLDER.self::WORKING_FOLDER.$authorID.".xml", FALSE);
		$root = $parser->evaluate("//author")->item(0);
		
		return $root;
	}
	
	/**
	 * Creates all the structure folders of this repository.
	 * 
	 * @return	void
	 */
	private function createFolderStructure()
	{
		// Create repository folder
		folderManager::create($this->repository);
		
		// Create vcs indexing folder (and subfolders)
		folderManager::create($this->repository, self::VCS_FOLDER);
		folderManager::create($this->repository.self::VCS_FOLDER, self::ITEMS_FOLDER);
		folderManager::create($this->repository.self::VCS_FOLDER, self::COMMITS_FOLDER);
		folderManager::create($this->repository.self::VCS_FOLDER, self::WORKING_FOLDER);
		
		// Create other folders
		folderManager::create($this->repository, self::TRUNK_FOLDER);
		folderManager::create($this->repository, self::BRANCHES_FOLDER);
		folderManager::create($this->repository, self::COMMITS_FOLDER);
		folderManager::create($this->repository, self::RELEASE_FOLDER);
		folderManager::create($this->repository.self::RELEASE_FOLDER, self::CURRENT_VERSION_FOLDER);
		folderManager::create($this->repository.self::RELEASE_FOLDER, self::HISTORY_FOLDER);
	}
	
	/**
	 * Creates all the index files of this repository.
	 * 
	 * @return	void
	 */
	private function createIndexStructure()
	{
		// VCS Index Folder
		$indexFolder = $this->repository.self::VCS_FOLDER;
		
		// VCS index
		$parser = new DOMParser();
		$root = $parser->create("vcs");
		$parser->append($root);
		// __ version
		$version = $parser->create("version", self::VERSION);
		$parser->append($root, $version);
		// __ commits, releases, branches
		$branches = $parser->create("branches");
		$parser->append($root, $branches);
		$commits = $parser->create("commits");
		$parser->append($root, $commits);
		$releases = $parser->create("releases");
		$parser->append($root, $releases);
		// __ save index
		$parser->save($indexFolder, "index.xml");
		
		// Branches index
		$parser = new DOMParser();
		$root = $parser->create("branches");
		$parser->append($root);
		// __ head
		$head = $parser->create("head");
		$parser->append($root, $head);
		// __ save index
		$parser->save($indexFolder, "branches.xml");
		
		// Releases index
		$parser = new DOMParser();
		$root = $parser->create("releases");
		$parser->append($root);
		// __ save index
		$parser->save($indexFolder, "releases.xml");
		
		// Commits index
		$parser = new DOMParser();
		$root = $parser->create("commits");
		$parser->append($root);
		// __ save index
		$parser->save($indexFolder.self::COMMITS_FOLDER, "index.xml");
		
		// Authors index
		$parser = new DOMParser();
		$root = $parser->create("authors");
		$parser->append($root);
		// __ save index
		$parser->save($indexFolder, "authors.xml");
	}
}
//#section_end#
?>