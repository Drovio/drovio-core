<?php
//#section#[header]
// Namespace
namespace API\Developer\versionControl;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\versionControl
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Developer", "versionControl::vcsTrunk");
importer::import("API", "Developer", "versionControl::vcsBranch");
importer::import("API", "Developer", "versionControl::vcsHistory");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Developer\versionControl\vcsTrunk;
use \API\Developer\versionControl\vcsBranch;
use \API\Developer\versionControl\vcsHistory;

/**
 * Version Control System Manager
 * 
 * Manages all the versioning of an object.
 * 
 * @version	{empty}
 * @created	March 14, 2013, 11:34 (EET)
 * @revised	November 14, 2013, 10:44 (EET)
 * 
 * @deprecated	Use misc\vcs instead.
 */
class vcsManager
{
	/**
	 * The root directory for repositories
	 * 
	 * @type	string
	 */
	const REPOSITORY_PATH = "/Developer/Repositories";
	/**
	 * The inner repository directory
	 * 
	 * @type	string
	 */
	const PATH = "/_vcs";
	/**
	 * The object's full directory.
	 * 
	 * @type	string
	 */
	protected $directory;
	/**
	 * The object's name.
	 * 
	 * @type	string
	 */
	protected $name;
	/**
	 * The object's filetype.
	 * 
	 * @type	string
	 */
	protected $fileType;
	/**
	 * The repository root directory.
	 * 
	 * @type	string
	 */
	protected $repository;
	
	/**
	 * The trunk manager.
	 * 
	 * @type	vcsTrunk
	 */
	protected $vcsTrunk;
	/**
	 * The branch manager.
	 * 
	 * @type	vcsBranch
	 */
	protected $vcsBranch;
	/**
	 * The history manager.
	 * 
	 * @type	vcsHistory
	 */
	protected $vcsHistory;
	
	/**
	 * The object's inner path.
	 * 
	 * @type	string
	 */
	protected $innerFilePath;
	
	/**
	 * Initializes the version control manager and the trunk, branch and history managers.
	 * 
	 * @param	string	$repository
	 * 		The repository root directory
	 * 
	 * @param	string	$directory
	 * 		The object's directory in the repository
	 * 
	 * @param	string	$name
	 * 		The object's name
	 * 
	 * @param	string	$type
	 * 		The object's filetype
	 * 
	 * @param	string	$path
	 * 		The object's inner path.
	 * 
	 * @return	void
	 */
	protected function VCS_initialize($repository, $directory, $name, $type, $path = "")
	{
		// Initialize Variables
		$this->repository = $repository;
		$this->directory = $repository.$directory.self::PATH;
		$this->name = $name;
		$this->fileType = $type;
		$this->innerFilePath = $path;
		
		// Initialize Version Control
		$this->vcsTrunk = new vcsTrunk($this->directory, $this->name, $this->fileType, $this->innerFilePath);
		$this->vcsBranch = new vcsBranch($this->directory, $this->name, $this->fileType, $this->innerFilePath);
		$this->vcsHistory = new vcsHistory($this->directory, $this->name, $this->fileType, $this->innerFilePath);
	}
	
	/**
	 * Create all the version control structure, including trunk, branches and history.
	 * 
	 * @return	void
	 */
	protected function VCS_createStructure()
	{
		// Create VCS Folder
		folderManager::create(systemRoot.$this->directory);
		
		// Create Trunk
		$this->vcsTrunk->createStructure();
		
		// Create Branching
		$this->vcsBranch->createStructure();
		
		// Create History
		$this->vcsHistory->createStructure();
		
		// Set Working Branch
		$this->setWorkingBranch($this->name.".".$this->fileType, "master");
	}
	
	/**
	 * Creates the object as index in the repository and returns the object's path to inherited class in order to proceed with the creation of files or folders.
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected function VCS_createObject()
	{
		// Get working branch
		$branch = $this->getWorkingBranch();
		
		// Create branch
		$this->vcsBranch->create($branch);

		// Checkout branch
		return $this->vcsBranch->checkout($branch);
	}
	
	/**
	 * Removes the object entirely from the repository.
	 * 
	 * @return	void
	 */
	protected function VCS_removeObject()
	{
		// Get all branches
		$branchList = $this->vcsBranch->getBranchList();
		
		// Remove All Branches
		foreach ($branchList as $branch)
			$this->vcsBranch->delete($branch);
		
		// Delete the item's index
		$this->vcsBranch->deleteIndex();
		
		return TRUE;
	}
	
	/**
	 * Sets the working branch for the current user/programmer for the current object.
	 * 
	 * @param	string	$id
	 * 		The object's unique id.
	 * 
	 * @param	string	$branch
	 * 		The branch to be set as working branch.
	 * 
	 * @return	void
	 */
	protected function setWorkingBranch($id, $branch)
	{
	}
	
	/**
	 * Gets the current object's working branch.
	 * 
	 * @param	string	$id
	 * 		The object's unique id.
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected function getWorkingBranch($id = "")
	{
		return "master";
	}
	
	/**
	 * Creates a new repository directory.
	 * 
	 * @param	string	$repository
	 * 		The repository root folder.
	 * 
	 * @param	string	$path
	 * 		The object's folder path.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	protected static function VCS_createRepository($repository, $path)
	{
		$ns_path = str_replace("::", "/", $path);
		return folderManager::create(systemRoot.$repository.$ns_path);
	}
	
	/**
	 * Removes an existing repository.
	 * 
	 * @param	string	$repository
	 * 		The repository root folder.
	 * 
	 * @param	string	$path
	 * 		The object's folder path.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	protected static function VCS_removeRepository($repository, $path)
	{
		return folderManager::remove_full(systemRoot.$repository.$path);
	}
}
//#section_end#
?>