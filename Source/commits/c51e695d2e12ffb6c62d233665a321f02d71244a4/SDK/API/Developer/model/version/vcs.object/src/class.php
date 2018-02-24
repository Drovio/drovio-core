<?php
//#section#[header]
// Namespace
namespace API\Developer\model\version;

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
 * @namespace	\model\version
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "DOM::DOMParser");
importer::import("API", "Content", "filesystem::folderManager");
importer::import("API", "Content", "filesystem::folderManager");
importer::import("API", "Developer", "model::version::repository");
importer::import("API", "Developer", "model::version::vcsTrunk");
importer::import("API", "Developer", "model::version::vcsBranch");
importer::import("API", "Developer", "model::version::vcsHistory");

use \API\Platform\DOM\DOMParser;
use \API\Content\filesystem\fileManager;
use \API\Content\filesystem\folderManager;
use \API\Developer\model\version\repository;
use \API\Developer\model\version\vcsTrunk;
use \API\Developer\model\version\vcsBranch;
use \API\Developer\model\version\vcsHistory;

/**
 * Abstract Version Control System Class
 * 
 * Handles all the version control system's information about an object or a directory.
 * 
 * @version	{empty}
 * @created	July 3, 2013, 12:59 (EEST)
 * @revised	July 3, 2013, 12:59 (EEST)
 * 
 * @deprecated	Use \API\Developer\versionControl\ instead.
 */
abstract class vcs
{
	/**
	 * The version control inner folder
	 * 
	 * @type	string
	 */
	const PATH = "/_vcs";
	
	/**
	 * The object's name
	 * 
	 * @type	string
	 */
	protected $name;
	/**
	 * The object's file type
	 * 
	 * @type	string
	 */
	protected $fileType;
	/**
	 * The repository folder
	 * 
	 * @type	string
	 */
	private $repository;
	
	/**
	 * The vcsTrunk manager object
	 * 
	 * @type	vcsTrunk
	 */
	protected $vcsTrunk;
	/**
	 * The vcsBranch manager object
	 * 
	 * @type	vcsBranch
	 */
	protected $vcsBranch;
	/**
	 * The vcshistory manager object
	 * 
	 * @type	vcsHistory
	 */
	protected $vcsHistory;
	/**
	 * The vcsProduction manager object
	 * 
	 * @type	vcsProduction
	 */
	protected $vcsProduction;
	
	/**
	 * Return the object's repository path
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function VCS_get_repository()
	{
		return $this->repository;
	}
	
	/**
	 * Initialize Version Control System
	 * 
	 * @param	string	$repository
	 * 		The repository path
	 * 
	 * @param	string	$name
	 * 		The object's name
	 * 
	 * @param	string	$type
	 * 		The object's filetype
	 * 
	 * @return	void
	 */
	protected function VCS_initialize($repository, $name, $type)
	{
		// Initialize Variables
		$this->repository = repository::PATH.$repository.self::PATH;
		$this->name = $name;
		$this->fileType = $type;
		
		// Initialize VCS
		$this->vcsTrunk = new vcsTrunk($this->repository, $this->name, $this->fileType);
		$this->vcsBranch = new vcsBranch($this->repository, $this->name, $this->fileType);
		$this->vcsHistory = new vcsHistory($this->repository, $this->name, $this->fileType);
		
		// TEMP
		// Remove Repository Production folder (if exists)
		$productionFolder = systemRoot.$this->repository."/production/";
		if (file_exists($productionFolder))
			folderManager::remove_full($productionFolder);
	}
	
	/**
	 * Create the inner repository directory structure
	 * 
	 * @return	void
	 */
	protected function VCS_create_structure()
	{
		// Create VCS Folder
		folderManager::create(systemRoot.$this->repository);
		
		// Create Trunk
		$this->vcsTrunk->initialize();
		
		// Create Branching
		$this->vcsBranch->initialize();
		
		// Create History
		$this->vcsHistory->initialize();
		
		// Create Master and Release Branches
		$this->vcsBranch->create("master");
		$this->vcsBranch->create("release");
	}
	
	/**
	 * Create a new object
	 * 
	 * @param	string	$branch
	 * 		The vcs branch where the object will be created
	 * 
	 * @return	void
	 */
	public function VCS_create_object($branch = "")
	{
		$branch = ($branch == "" ? vcsBranch::masterBranch : $branch);
		
		// Create branch
		$this->vcsBranch->create($branch);

		// Checkout branch
		$this->vcsBranch->checkout($branch);
	}
	
	/**
	 * Delete an object inside the repository
	 * 
	 * @return	void
	 */
	protected function VCS_remove()
	{
		// Get all branches
		$branchList = $this->vcsBranch->get_branches();

		$parser = new DOMParser();

		// Remove All Branches
		foreach ($branchList as $branch)
			$this->vcsBranch->delete($branch);
		
		// Delete the entire item
		$this->vcsBranch->delete_item();
	}
	
	/**
	 * Remove a repository
	 * 
	 * @param	string	$path
	 * 		The repository path.
	 * 
	 * @return	void
	 */
	protected function VCS_removeRepository($path)
	{
		repository::remove($path);
	}
}
//#section_end#
?>