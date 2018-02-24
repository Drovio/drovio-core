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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "sql::dbQuery");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::directory");
importer::import("API", "Resources", "archive::zipManager");
importer::import("API", "Security", "account");
importer::import("DEV", "Projects", "project");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\directory;
use \API\Resources\archive\zipManager;
use \API\Security\account;
use \DEV\Projects\project;

/**
 * Version Control System
 * 
 * Manages version control for a given repository.
 * 
 * @version	2.0-2
 * @created	February 12, 2014, 11:55 (EET)
 * @revised	November 12, 2014, 10:40 (EET)
 */
class vcs
{
	/**
	 * The current version.
	 * 
	 * @type	string
	 */
	const VERSION = "2.2";
	
	/**
	 * The inner version control folder.
	 * 
	 * @type	string
	 */
	const VCS_FOLDER = ".vcs/";
	/**
	 * The trunk folder.
	 * 
	 * @type	string
	 */
	const TRUNK_FOLDER = "trunk/";
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
	 * The repository root folder.
	 * 
	 * @type	string
	 */
	private $dbc;
	
	/**
	 * Defines whether the repository will include release or not.
	 * 
	 * @type	string
	 */
	private $projectID;
	
	/**
	 * The repository folder.
	 * 
	 * @type	string
	 */
	private $repository;
	
	/**
	 * All the project's items.
	 * 
	 * @type	array
	 */
	private static $items;
	/**
	 * All the project's authors.
	 * 
	 * @type	array
	 */
	private static $authors;
	/**
	 * All the project's branches.
	 * 
	 * @type	array
	 */
	private static $branches;
	/**
	 * All the project's releases.
	 * 
	 * @type	array
	 */
	private static $releases;
	/**
	 * Current working branch for author.
	 * 
	 * @type	string
	 */
	private static $workingBranch;
	
	/**
	 * Constructor method. Initializes class' variables.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @return	void
	 */
	public function __construct($projectID)
	{
		// Set project id
		$this->projectID = $projectID;
		$this->dbc = new dbConnection();
		
		// Get project repository
		$project = new project($projectID);
		$repository = $project->getRepository();
		$this->repository = systemRoot."/".$repository."/";
		
		// Get current version and update to new version
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
		// Add branch to database
		$dbq = new dbQuery("18172138902899", "developer.vcs", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$attr['name'] = $branchName;
		$attr['parent'] = $parent;
		$result = $this->dbc->execute($dbq, $attr);
		
		if ($result)
		{
			// Create branch folders
			folderManager::create($this->repository.self::TRUNK_FOLDER, $branchName);
			folderManager::create($this->repository.self::BRANCHES_FOLDER, $branchName);
			
			// Add branch to current list
			self::$branches[$this->projectID][$branchName] = $branchName;
		}
		
		// Copy branch folders contents
		if (!empty($parent))
		{
			// Trunk folder
			$parentTrunkFolder = $this->repository.self::TRUNK_FOLDER.$parent."/";
			$newTrunkFolder = $this->repository.self::TRUNK_FOLDER.$branchName."/";
			folderManager::copy($parentTrunkFolder, $newTrunkFolder, TRUE);
			
			// Branch folder
			$parentBranchFolder = $this->repository.self::BRANCHES_FOLDER.$parent."/";
			$newBranchFolder = $this->repository.self::BRANCHES_FOLDER.$branchName."/";
			folderManager::copy($parentBranchFolder, $newBranchFolder, TRUE);
		}
		
		// Get parent's last commit and set to the new branch
		$lastCommitID = $this->getLastCommit($parent);
		if ($lastCommitID)
			$this->setLastCommit($branchName, $lastCommitID);
			
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
	 * @param	boolean	$smart
	 * 		Whether the item is a smart object.
	 * 
	 * @return	mixed
	 * 		False if an item exists with the same id, the item's trunk path otherwise.
	 */
	public function createItem($id, $path, $name, $smart = FALSE)
	{
		// Get working branch
		$workingBranch = $this->getWorkingBranch();
		
		// Normalize path
		$path = directory::normalize($path);
		
		// Add item to database
		$dbq = new dbQuery("27285167763142", "developer.vcs", TRUE);
		$attr = array();
		$attr['id'] = $id;
		$attr['pid'] = $this->projectID;
		$attr['path'] = $path;
		$attr['name'] = $name;
		$attr['smart'] = ($smart ? 1 : 0);
		$result = $this->dbc->execute($dbq, $attr);
		if (!$result)
			return FALSE;
		
		// Return item path to the trunk
		return $this->updateItem($id, TRUE);
	}
	
	/**
	 * Delete an item from the vcs db and repository.
	 * 
	 * @param	string	$id
	 * 		The item id to be deleted.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function deleteItem($id)
	{
		// Get working branch
		$workingBranch = $this->getWorkingBranch();
		
		// Get item info
		$itemInfo = $this->getItemInfo($id, $workingBranch);
		
		// Get trunk and branch paths
		$trunkPath = $this->getItemTrunkPath($id);
		$branchPath = $this->repository.self::BRANCHES_FOLDER.$workingBranch."/".$this->getItemPath($id, $workingBranch);
		
		// Update database
		$dbq = new dbQuery("26542995519318", "developer.vcs", TRUE);
		$attr = array();
		$attr['id'] = $id;
		$attr['pid'] = $this->projectID;
		$attr['branch'] = $workingBranch;
		$result = $this->dbc->execute($dbq, $attr);
		if (!$result)
			return FALSE;
		
		// If it's a smart item, remove folders else files
		if ($itemInfo['smart'])
		{
			folderManager::remove($trunkPath, "", TRUE);
			folderManager::remove($branchPath, "", TRUE);
		}
		else
		{
			fileManager::remove($trunkPath, "", TRUE);
			fileManager::remove($branchPath, "", TRUE);
		}
		
		return TRUE;
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
	 * @return	mixed
	 * 		The item's trunk path or FALSE if the item id doesn't exist.
	 */
	public function updateItem($id, $forceCommit = FALSE)
	{
		// Update working index
		$dbq = new dbQuery("35062959983206", "developer.vcs", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$attr['branch'] = $this->getWorkingBranch();
		$attr['author'] = $this->getAuthorID();
		$attr['item'] = $id;
		$attr['time'] = time();
		$attr['force_commit'] = ($forceCommit ? 1 : 0);
		$result = $this->dbc->execute($dbq, $attr);
		
		// Return item's trunk path
		if ($result)
			return $this->getItemTrunkPath($id);
		
		return FALSE;
	}
	
	/**
	 * Gets the item's path to the trunk.
	 * 
	 * @param	string	$id
	 * 		The item's id.
	 * 
	 * @return	mixed
	 * 		The item's trunk path or FALSE if the item id doesn't exist.
	 */
	public function getItemTrunkPath($id)
	{
		// Get working branch
		$workingBranch = $this->getWorkingBranch();
		
		// Return item's trunk path
		$itemPath = $this->getItemPath($id, $workingBranch);
		if ($itemPath)
			return $this->repository.self::TRUNK_FOLDER.$workingBranch."/".$itemPath;
		
		return FALSE;
	}
	
	/**
	 * Gets the item's path to the working branch.
	 * 
	 * @param	string	$id
	 * 		The item's id.
	 * 
	 * @return	mixed
	 * 		The item's working branch path or FALSE if the item id doesn't exist.
	 */
	public function getItemBranchPath($id)
	{
		// Get working branch
		$workingBranch = $this->getWorkingBranch();
		
		// Return item's branch path
		$itemPath = $this->getItemPath($id, $workingBranch);
		if ($itemPath)
			return $this->repository.self::BRANCHES_FOLDER.$workingBranch."/".$itemPath;
		
		return FALSE;
	}
	
	/**
	 * Get the item's path to the head branch.
	 * 
	 * @param	string	$id
	 * 		The item's id.
	 * 
	 * @return	mixed
	 * 		The item's head branch path or FALSE if the item id doesn't exist.
	 */
	public function getItemHeadPath($id)
	{
		// Get working branch
		$headBranch = $this->getHeadBranch();
		
		// Return item's head path
		$itemPath = $this->getItemPath($id, $headBranch);
		if ($itemPath)
			return $this->repository.self::BRANCHES_FOLDER.$headBranch."/".$itemPath;
		
		return FALSE;
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
	private function getItemPath($id, $branch = self::MASTER_BRANCH)
	{
		// Load item info
		$info = $this->getItemInfo($id, $branch);
		if (empty($info))
			return FALSE;
		
		// Return item's path
		return $info['path']."/".$info['name'].($info['smart'] ? "/" : "");
	}
	
	/**
	 * Gets the given item's information.
	 * 
	 * @param	string	$id
	 * 		The item's id.
	 * 
	 * @return	array
	 * 		Returns an array of information as follows:
	 * 		info['path'] = The item's path.
	 * 		info['name'] = The item's name (including the extension).
	 * 		info['folder'] = Whether the item is an object folder [T/F].
	 */
	private function getItemInfo($id)
	{
		// Check if the item info is empty and reload items from database
		if (empty(self::$items[$this->projectID][$id]))
		{
			// Get all project items
			$dbq = new dbQuery("3442352180892", "developer.vcs", TRUE);
			$attr = array();
			$attr['pid'] = $this->projectID;
			$result = $this->dbc->execute($dbq, $attr);
			
			// Fetch items
			self::$items[$this->projectID] = array();
			while ($item = $this->dbc->fetch($result))
				self::$items[$this->projectID][$item['id']] = $item;
		}
		
		// Return item info
		return self::$items[$this->projectID][$id];
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
		
		// Get working items
		$items = $this->getWorkingItems();
		foreach ($items as $itemID => $item)
		{
			$force = $item['force_commit'];
			if ($force || in_array($itemID, $commitItems))
			{
				$finalCommit = TRUE;
				$finalCommitItems[] = $itemID;
			}
		}
		
		// If there are not items to commit, return FALSE
		if (!$finalCommit || empty($summary))
			return FALSE;
			
		// If there are items to commit, create commit entry and directory with items
		
		// Get working branch
		$workingBranch = $this->getWorkingBranch();
		
		// Get timestamp and create commit id
		$timestamp = time();
		$commitID = "c".hash("sha1", "commit.".$timestamp);
		
		// Get parent commit (last commit of this branch
		$parentCommitID = $this->getLastCommit($workingBranch);
		
		// Create commit in the database
		$dbq = new dbQuery("29497584563464", "developer.vcs", TRUE);
		$attr = array();
		$attr['id'] = $commitID;
		$attr['pid'] = $this->projectID;
		$attr['branch'] = $workingBranch;
		$attr['parent'] = $parentCommitID;
		$attr['time'] = $timestamp;
		$attr['author'] = $this->getAuthorID();
		$attr['summary'] = $summary;
		$attr['description'] = $description;
		$result = $this->dbc->execute($dbq, $attr);
		
		// Add commit items to database
		foreach ($finalCommitItems as $itemID)
		{
			$dbq = new dbQuery("34372830013116", "developer.vcs", TRUE);
			$attr = array();
			$attr['cid'] = $commitID;
			$attr['pid'] = $this->projectID;
			$attr['item'] = $itemID;
			$this->dbc->execute($dbq, $attr);
		}
		
		// Get folders
		$commitFolder = $this->getCommitFolder($commitID);
		$branchFolder = $this->repository.self::BRANCHES_FOLDER;
		$trunkFolder = $this->repository.self::TRUNK_FOLDER;
		
		// Copy all item files
		folderManager::create($commitFolder);
		foreach ($finalCommitItems as $itemID)
		{
			// Get item path
			$itemPath = $this->getItemPath($itemID, $workingBranch);
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
		
		// Clear working index from committed items
		foreach ($finalCommitItems as $itemID)
		{
			$dbq = new dbQuery("1434532560983", "developer.vcs", TRUE);
			$attr = array();
			$attr['pid'] = $this->projectID;
			$attr['item'] = $itemID;
			$attr['branch'] = $workingBranch;
			$this->dbc->execute($dbq, $attr);
		}
		
		// Set working branch last commit
		$this->setLastCommit($workingBranch, $commitID);
		
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
		$dbq = new dbQuery("29795470621483", "developer.vcs", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$attr['branch'] = $branchName;
		$attr['commit'] = $commitID;
		return $this->dbc->execute($dbq, $attr);
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
		$dbq = new dbQuery("21149627706753", "developer.vcs", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$attr['branch'] = $branchName;
		$result = $this->dbc->execute($dbq, $attr);
		
		$info = $this->dbc->fetch($result);
		return $info['last_commit_id'];
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
		
		$dbq = new dbQuery("23590657828833", "developer.vcs", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$attr['branch'] = $branchName;
		return $this->dbc->execute($dbq, $attr);
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
		//$vcsReleases = $this->getReleases();
		
		// Validate version
		if (!$this->validateVersion($version))
			return FALSE;
			
		// Check if there is version overlap
		// (this branch should have a version bigger than its parent's base version, if any)
		//$parentBranch = $vcsBranches[$branchName]['parent'];
		$parentBaseVersion = "0.1";//$vcsReleases[$parentBranch]['base'];
		if (!empty($parentBaseVersion) && version_compare($parentBaseVersion, $version) > 0)
			return FALSE;
			
		// Get branch's current version and package's current build number
		$dbq = new dbQuery("22290513142726", "developer.vcs", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$attr['branch'] = $branchName;
		$result = $this->dbc->execute($dbq, $attr);
		
		$info = $this->dbc->fetch($result);
		$currentVersion = $info['currentVersion'];
		$versionBuild = $info['versionBuild'];
		if (empty($currentVersion))
			$versionBuild = 1;
		else if (!empty($currentVersion) && version_compare($currentVersion, $version) < 0)
			$versionBuild = 1;
		else if (!empty($currentVersion) && version_compare($currentVersion, $version) > 0)
		{
			// If release version is lower than previously current, return FALSE
			return FALSE;
		}
		else
			$versionBuild = intval($versionBuild) + 1;
			
		$versionTime = time();
		
		// Create release package database entry
		$dbq = new dbQuery("15431180218003", "developer.vcs", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$attr['branch'] = $branchName;
		$attr['version'] = $version;
		$attr['build'] = $versionBuild;
		$attr['time'] = $versionTime;
		$attr['title'] = $title;
		$attr['description'] = $description;
		$this->dbc->execute($dbq, $attr);
		
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
	 * Restore an item from a given commit id to the working branch trunk.
	 * 
	 * @param	string	$commitID
	 * 		The commit id to load the item from.
	 * 
	 * @param	string	$itemID
	 * 		The item id to restore.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function restore($commitID, $itemID)
	{
		// Get working branch
		$workingBranch = $this->getWorkingBranch();
		
		// Get folders
		$commitFolder = $this->getCommitFolder($commitID);
		$trunkFolder = $this->repository.self::TRUNK_FOLDER."/".$workingBranch;
		
		// Get item info and path
		$itemInfo = $this->getItemInfo($itemID, $workingBranch);
		$itemPath = $this->getItemPath($itemID, $workingBranch);
		
		$commitPath = $commitFolder."/".$itemPath;
		$trunkPath = $trunkFolder."/".$itemPath;
		
		// Copy item to trunk
		// If it's a smart item, copy folder else file
		if ($itemInfo['smart'])
		{
			folderManager::clean($trunkPath);
			folderManager::copy($commitPath, $trunkPath, TRUE);
		}
		else
			fileManager::copy($commitPath, $trunkPath);
		
		// Update working item
		$this->updateItem($itemID);
		
		return TRUE;
	}
	
	/**
	 * Gets the current head branch of this repository.
	 * 
	 * @return	string
	 * 		The branch name.
	 */
	public function getHeadBranch()
	{
		// Get the head branch from the db
		$dbq = new dbQuery("33160866393643", "developer.vcs", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$result = $this->dbc->execute($dbq, $attr);
		$info = $this->dbc->fetch($result);
		return $info['head_branch'];
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
			
		$authorID = $this->getAuthorID();
		// Set working branch in the authors working index db
		$dbq = new dbQuery("29533814216138", "developer.vcs", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$attr['author'] = $authorID;
		$attr['branch'] = $branchName;
		$result = $this->dbc->execute($dbq, $attr);
		
		// Update File
		self::$workingBranch[$this->projectID][$authorID] = $branchName;
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
		$authorID = $this->getAuthorID();
		if (empty(self::$workingBranch[$this->projectID][$authorID]))
		{
			// Get working branch from db
			$dbq = new dbQuery("25354310262439", "developer.vcs", TRUE);
			$attr = array();
			$attr['pid'] = $this->projectID;
			$attr['author'] = $authorID;
			$result = $this->dbc->execute($dbq, $attr);
			
			if ($this->dbc->get_num_rows($result) > 0)
			{
				$info = $this->dbc->fetch($result);
				self::$workingBranch[$this->projectID][$authorID] = $info['working_branch'];
			}
			else
				$this->setWorkingBranch(self::MASTER_BRANCH);
		}
		
		if (empty(self::$workingBranch[$this->projectID][$authorID]))
			Throw New Exception("Working branch cannot be defined for this project [".$this->projectID."].");
		else
			return self::$workingBranch[$this->projectID][$authorID];
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
		// Get project vcs info
		$dbq = new dbQuery("31364061415925", "developer.vcs", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$result = $this->dbc->execute($dbq, $attr);
		return $this->dbc->fetch($result);
	}	
	
	/**
	 * Gets all the branches of this repository.
	 * 
	 * @return	array
	 * 		An array of all branches by name.
	 */
	public function getBranches()
	{
		if (empty(self::$branches[$this->projectID]))
		{
			// Load branches from db
			$dbq = new dbQuery("2426181917428", "developer.vcs", TRUE);
			$attr = array();
			$attr['pid'] = $this->projectID;
			$result = $this->dbc->execute($dbq, $attr);
			
			// Fetch branches
			self::$branches[$this->projectID] = array();
			$info = $this->dbc->fetch($result, TRUE);
			foreach ($info as $branchInfo)
				self::$branches[$this->projectID][$branchInfo['branch_name']] = $branchInfo;
		}
		
		return self::$branches[$this->projectID];
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
		
		$branches = $this->getBranches();
		foreach ($branches as $branchName => $branchData)
		{
			// Branch info
			$branchInfo = array();
			$branchInfo['base'] = $branchData["base_version"];
			$branchInfo['current'] = $branchData["current_version"];
			
			// Get release packages
			$dbq = new dbQuery("14677962695274", "developer.vcs", TRUE);
			$attr = array();
			$attr['pid'] = $this->projectID;
			$attr['branch'] = $branchName;
			$result = $this->dbc->execute($dbq, $attr);
			$packages = $this->dbc->fetch($result, TRUE);
			
			$branchPackages = array();
			foreach ($packages as $package)
			{
				$packageID = "v".$package["version"];
				$packageInfo = array();
				$packageInfo['version'] = $package["version"];
				$packageInfo['build'] = $package["build"];
				
				// Get Release package history
				$dbq = new dbQuery("1647945435111", "developer.vcs", TRUE);
				$attr = array();
				$attr['pid'] = $this->projectID;
				$attr['branch'] = $branchName;
				$attr['version'] = $package["version"];
				$result = $this->dbc->execute($dbq, $attr);
				$releases = $this->dbc->fetch($result, TRUE);
				
				$packageReleases = array();
				foreach ($releases as $release)
				{
					$releaseVersionID = "v".$release['version'].".".$release["build"];
					
					// Release info
					$releaseInfo = array();
					$releaseInfo['title'] = $release["title"];
					$releaseInfo['version'] = $release["version"];
					$releaseInfo['build'] = $release["build"];
					$releaseInfo['time'] = $release["time"];
					$releaseInfo['description'] = $release["description"];
					
					$packageReleases[$releaseVersionID] = $releaseInfo;
				}
				$packageInfo['releases'] = $packageReleases;
				
				$branchPackages[$packageID] = $packageInfo;
			}
			$branchInfo['packages'] = $branchPackages;
			
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
	 * 		The path to the release folder.
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
		// Get branch commits from db
		$dbq = new dbQuery("2388701037161", "developer.vcs", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$attr['branch'] = $branchName;
		$result = $this->dbc->execute($dbq, $attr);
		
		$commits = array();
		while ($commit = $this->dbc->fetch($result))
			$commits[$commit['id']] = $commit;
		
		return $commits;
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
		// Get working items
		$dbq = new dbQuery("22949425783019", "developer.vcs", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$attr['branch'] = $branchName;
		$attr['item'] = $itemID;
		$result = $this->dbc->execute($dbq, $attr);
		return $this->dbc->fetch($result, TRUE);
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
		// Get commit items
		$dbq = new dbQuery("22861257219923", "developer.vcs", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$attr['cid'] = $commitID;
		$result = $this->dbc->execute($dbq, $attr);
		$items = $this->dbc->fetch($result, TRUE);
		
		$commitItems = array();
		foreach ($items as $item)
			$commitItems[$item['item_id']] = $this->getItemInfo($item['item_id']);
		
		return $commitItems;
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
		$workingBranch = $this->getWorkingBranch();
		
		// Get working items
		$dbq = new dbQuery("26266178353633", "developer.vcs", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$attr['branch'] = $workingBranch;
		$attr['author'] = $this->getAuthorID();
		$result = $this->dbc->execute($dbq, $attr);
		
		$workingItems = array();
		$items = $this->dbc->fetch($result, TRUE);
		foreach ($items as $item)
		{
			// Get item info
			$itemID = $item['id'];
			$info = $this->getItemInfo($itemID, $workingBranch);
		
			$itemInfo = array();
			$itemInfo['path'] = directory::normalize($info['path']."/".$info['name']);
			$itemInfo['force_commit'] = $item['force_commit'];
			$itemInfo['last-edit-author-id'] = $item['last_author_id'];
			$itemInfo['last-edit-author'] = $item['last_author'];
			$itemInfo['last-edit-time'] = $item['last_update'];
			$workingItems[$itemID] = $itemInfo;
		}
		
		return $workingItems;
	}
	
	/**
	 * Validates a given version.
	 * It must be bigger than 0.0
	 * 
	 * @param	string	$version
	 * 		The version string.
	 * 
	 * @return	boolean
	 * 		True if valid, false otherwise.
	 */
	private function validateVersion($version)
	{
		// Validate parts
		$vparts = explode(".", $version);
		foreach ($vparts as $vpart)
			if (!is_numeric($vpart))
				return FALSE;
		
		// Validate to be more than 0.0
		if (version_compare($version, "0.0") <= 0)
			return FALSE;
		
		return TRUE;
	}
	
	/**
	 * Gets the current author's id.
	 * 
	 * @return	string
	 * 		The author id.
	 */
	private function getAuthorID()
	{
		return account::getAccountID();
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
		// __ save index
		$parser->save($indexFolder, "index.xml");
	}
	
	/**
	 * Return all the authors of the current repository.
	 * 
	 * @return	array
	 * 		An array of id=>authorName.
	 */
	public function getAuthors()
	{
		$dbq = new dbQuery("32597504132115", "developer.projects", TRUE);
		$attr = array();
		$attr['pid'] = $this->projectID;
		$result = $this->dbc->execute($dbq, $attr);
		$accounts = $this->dbc->fetch($result, TRUE);
		
		$authors = array();
		foreach ($accounts as $account)
			$authors[$account['id']] = $account['title'];
		
		return $authors;
	}
}
//#section_end#
?>