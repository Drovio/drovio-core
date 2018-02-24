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
importer::import("API", "Developer", "versionControl::vcsHistory");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Developer\versionControl\vcsTrunk;
use \API\Developer\versionControl\vcsHistory;

/**
 * Version Control System Branch Manager
 * 
 * Manages all functions in branches.
 * 
 * @version	{empty}
 * @created	March 13, 2013, 19:23 (EET)
 * @revised	November 14, 2013, 10:43 (EET)
 * 
 * @deprecated	Use misc\vcs instead.
 */
class vcsBranch
{
	/**
	 * The branch folder
	 * 
	 * @type	string
	 */
	const DIR = "/branches";
	/**
	 * The branch index file
	 * 
	 * @type	string
	 */
	const INDEX = "index.xml";
	
	/**
	 * The reserved name for the release branch
	 * 
	 * @type	string
	 */
	const RELEASE_BRANCH = "release";
	
	/**
	 * The reserved branch names that the user cannot create.
	 * 
	 * @type	string
	 */
	private $reserved = array("master", "release");
	
	/**
	 * The repository directory
	 * 
	 * @type	string
	 */
	private $directory;
	/**
	 * The object's name
	 * 
	 * @type	string
	 */
	private $name;
	/**
	 * The object's filetype
	 * 
	 * @type	string
	 */
	private $type;
	
	/**
	 * The object's inner path.
	 * 
	 * @type	string
	 */
	private $path;
	
	/**
	 * Initializes the object's properties.
	 * 
	 * @param	string	$directory
	 * 		The repository directory.
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
	public function __construct($directory, $name, $type, $path = "")
	{
		// Set Branch Directory (There is no systemRoot)
		$this->directory = $directory;
		$this->name = $name;
		$this->type = $type;
		$this->path = $path;
	}
	
	/**
	 * Creates the branch folder structure.
	 * 
	 * @return	void
	 */
	public function createStructure()
	{
		// Create branch folder
		folderManager::create(systemRoot.$this->directory, self::DIR);
		
		// Create branch indexing
		$builder = new DOMParser();
		$base = $builder->create("branches");
		DOMParser::append($base);
		$builder->save(systemRoot.$this->directory.self::DIR."/", self::INDEX);
	}
	
	/**
	 * Gets or creates (if doesn't exist) the branch's index base.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file.
	 * 
	 * @param	boolean	$forceCreate
	 * 		If TRUE and the base doesn't exist, it creates it. Otherwise, it returns the base (NULL if not exists).
	 * 
	 * @return	NULL
	 * 		{description}
	 */
	public function getBranchBase($parser, $forceCreate = FALSE)
	{
		$parser->load($this->directory.self::DIR."/".self::INDEX, TRUE);
		$base = $parser->find($this->name);
		
		// If doesn't exist, create one
		if (is_null($base) && $forceCreate)
		{
			$base = $parser->create("item", "", $this->name);
			$parser->attr($base, "type", $this->type);
			$parser->attr($base, "path", $this->path);
			$root = $parser->evaluate("//branches")->item(0);
			$parser->append($root, $base);
			$parser->save(systemRoot.$this->directory.self::DIR."/", self::INDEX, TRUE);
		}

		return $base;
	}
	
	/**
	 * Gets or creates (if doesn't exist) the item's branch index base.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file.
	 * 
	 * @param	string	$branch
	 * 		The item's branch.
	 * 
	 * @param	boolean	$forceCreate
	 * 		If TRUE and the base doesn't exist, it creates it. Otherwise, it returns the base (NULL if not exists).
	 * 
	 * @return	NULL
	 * 		{description}
	 */
	public function getBase($parser, $branch, $forceCreate = FALSE)
	{
		// Check if its a valid (existing) branch
		if (!$this->checkBranchExists($branch) && !$forceCreate)
			return NULL;
		
		$parser->load($this->directory.self::DIR."/".$branch."/".self::INDEX, TRUE);
		$base = $parser->find($this->name);
		
		// If doesn't exist, create one
		if (is_null($base) && $forceCreate)
		{
			$base = $parser->create("item", "", $this->name);
			$parser->attr($base, "type", $this->type);
			$parser->attr($base, "path", $this->path);
			$root = $parser->evaluate("//branch")->item(0);
			$parser->append($root, $base);
			$parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
		}
		
		return $base;
	}
	
	/**
	 * Create a new branch.
	 * 
	 * @param	string	$branch
	 * 		The branch's name.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function create($branch)
	{
		// Check if branch with the same name for the file exists!
		$parser = new DOMParser();
		$branchBase = $this->getBranchBase($parser, TRUE);
		$duplicateBranch = $parser->evaluate("branch[text()='$branch']", $branchBase)->item(0);
		$duplicateGeneralBranch = $parser->evaluate("//branch[text()='$branch']")->item(0);

		// If Branch exists, return TRUE (created)
		if (!is_null($duplicateBranch))
			return TRUE;
		
		if (is_null($duplicateGeneralBranch))
		{
			// Create branch directory
			folderManager::create(systemRoot.$this->directory.self::DIR, $branch."/");
			
			// Create branch indexing
			$builder = new DOMParser();
			$base = $builder->create("branch");
			$builder->append($base);
			$builder->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
			
			// Create trunk files
			$trunk = new vcsTrunk($this->directory, $this->name, $this->type, $this->path);
			$trunk->createBranch($branch);
			
			// Create history files
			$history = new vcsHistory($this->directory, $this->name, $this->type, $this->path);
			$history->createBranch($branch);
		}
		
		// Set this branch as head and insert branch entry in file
		$branchEntry = $parser->create("branch", $branch);
		DOMParser::append($branchBase, $branchEntry);
		$parser->save(systemRoot.$this->directory.self::DIR."/", self::INDEX, TRUE);
		
		return TRUE;
	}
	
	/**
	 * Deletes an existing branch.
	 * 
	 * @param	string	$branch
	 * 		The branch's name.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function delete($branch)
	{
		// Check if its a valid (existing) branch
		if (!$this->checkBranchExists($branch))
			return FALSE;

		// Delete the branch index
		$parser = new DOMParser();
		$base = $this->getBranchBase($parser, TRUE);
		//$base = $this->getBranchBase($parser);
		$branchBase = $parser->evaluate("branch[text()='$branch']", $base)->item(0);
		
		// Check if items exists to the given branch
		if(!is_null($branchBase))
		{
			$branchBase->parentNode->removeChild($branchBase);
			$parser->save(systemRoot.$this->directory.self::DIR."/", self::INDEX, TRUE);
		}
		
		// Delete all the branch elements
		$branchFolder = systemRoot.$this->directory.self::DIR."/".$branch."/";
		//_____ Delete the indexing
		$parser = new DOMParser();
		$branchBase = $this->getBase($parser, $branch, TRUE);
		$branchBase->parentNode->removeChild($branchBase);
		$parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
		//_____ Delete the branch file
		$branchFile = $branchFolder.$this->path."/".$this->name.".".$this->type;
		if (is_dir($branchFile."/"))
			folderManager::remove($branchFile."/", "", TRUE);
		else
			fileManager::remove($branchFile);
		
		// Remove Branch from history
		$history = new vcsHistory($this->directory, $this->name, $this->type, $this->path);
		$history->deleteBranch($branch);
		
		// Remove Branch from trunk
		$trunk = new vcsTrunk($this->directory, $this->name, $this->type, $this->path);
		$trunk->deleteBranch($branch);
		
		return TRUE;
	}
	
	/**
	 * Deletes the entire branch from the branch indexing.
	 * 
	 * @return	void
	 */
	public function deleteIndex()
	{
		// Delete the branch index
		$parser = new DOMParser();
		$base = $this->getBranchBase($parser);
		
		// Check if item belongs to any branches
		if(!($base->hasChildNodes()))
		{
			//Item does not belong to any branches -> remove it
			//$base->parentNode->removeChild($base);
			$parser->replace($base, NULL);
		}		
		$parser->save(systemRoot.$this->directory.self::DIR."/", self::INDEX, TRUE);
	}
	
	/**
	 * Commits the object from the trunk to the given branch.
	 * 
	 * @param	string	$branch
	 * 		The branch to commit to.
	 * 
	 * @param	string	$description
	 * 		The commit description.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function commit($branch, $description)
	{
		// Check if its a valid (existing) branch
		if (!$this->checkBranchExists($branch))
			return FALSE;
			
		// Get branch base
		$parser = new DOMParser();
		$index_entry = $this->getBranchBase($parser, TRUE);
		$objectName = $this->name;
		$objectType = $this->type;

		// Recursive copy of object from trunk to branch and history (with timestamp)		
		// Create commit timestamp
		$timestamp = time();
		$trunkItem = systemRoot.$this->directory.vcsTrunk::DIR."/".$branch."/".$this->path."/".$objectName.".".$objectType;
		$branchItem = systemRoot.$this->directory.self::DIR."/".$branch."/".$this->path."/".$objectName.".".$objectType;
		
		// Create Branch Entry
		if (is_dir($trunkItem."/"))
		{
			if (!is_dir($branchItem))
				folderManager::create($branchItem."/");
			folderManager::copy($trunkItem."/", $branchItem."/", TRUE);
		}
		else
			fileManager::copy($trunkItem, $branchItem);

		// Update Branch Index Base
		$trunk = new vcsTrunk($this->directory, $this->name, $this->type, $this->path);
		$trunkBase = $trunk->getBase($parser, $branch, TRUE);
		$branchBase = $this->getBase($parser, $branch, TRUE);
		$branchBaseNew = $parser->import($trunkBase);
		$parser->replace($branchBase, $branchBaseNew);
		$parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
		
		// Create History entry
		$history = new vcsHistory($this->directory, $this->name, $this->type, $this->path);
		$history->createEntry($branch, $description, $timestamp);
		
		return TRUE;
	}
	
	/**
	 * Sets the HEAD index for the object and returns the object's path in order to be exported properly.
	 * 
	 * @param	string	$branch
	 * 		The branch which will be checked out.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function checkout($branch)
	{
		// Check if its a valid (existing) branch
		if (!$this->checkBranchExists($branch))
			return FALSE;
		
		// Clear all heads
		$parser = new DOMParser();
		$branchIndex_base = $this->getBranchBase($parser, TRUE);
		$heads = $parser->evaluate("branch[@head]", $branchIndex_base);
		foreach ($heads as $head)
			$parser->attr($head, "head", "");

		// Update Head
		$branchHead = $parser->evaluate("branch[text()='$branch']", $branchIndex_base)->item(0);
		$parser->attr($branchHead, "head", "head");

		// Save index file
		$parser->save(systemRoot.$this->directory.self::DIR."/", self::INDEX, TRUE);
		
		// Return object path
		return systemRoot.$this->directory.self::DIR."/".$branch."/".$this->path."/".$this->name.".".$this->type;
	}
	
	/**
	 * Returns the head's path to the object.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getHeadPath()
	{
		$parser = new DOMParser();
		$headBranch = $this->getHead($parser);
		return systemRoot.$this->directory.self::DIR."/".$headBranch."/".$this->path."/".$this->name.".".$this->type;
	}
	
	/**
	 * Creates a new release entry from the given branch.
	 * 
	 * @param	string	$branch
	 * 		The branch which will be the next release.
	 * 
	 * @param	string	$description
	 * 		A release description.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function release($branch, $description)
	{
		// Check if its a valid (existing) branch
		if (!$this->checkBranchExists($branch))
			return FALSE;
			
		// Get branch base
		$parser = new DOMParser();
		$index_entry = $this->getBranchBase($parser, TRUE);
		$objectName = $this->name;
		$objectType = $this->type;

		// Recursive copy of object from trunk to branch and history (with timestamp)
		// Destination Folders
		$branchFolder = systemRoot.$this->directory.self::DIR."/".$branch."/";
		$releaseFolder = systemRoot.$this->directory.self::DIR."/".self::RELEASE_BRANCH."/";
		$historyFolder = systemRoot.$this->directory.vcsHistory::DIR."/".$branch."/";
		
		// Create commit timestamp
		$timestamp = time();
		$branchItem = $branchFolder.$this->path."/".$objectName.".".$objectType;
		$releaseItem = $releaseFolder.$this->path."/".$objectName.".".$objectType;
		$historyItem = $historyFolder.$this->path."/".$objectName."_".$timestamp.".".$objectType;
		
		// Create Release Entry
		if (is_dir($trunkItem."/"))
			folderManager::copy($branchItem."/", $releaseItem."/", TRUE);
		else
			fileManager::copy($branchItem, $releaseItem);

		// Update Branch Index Base
		$trunk = new vcsTrunk($this->directory, $this->name, $this->type, $this->path);
		$branchBase = $this->getBase($parser, $branch, TRUE);
		$releaseBase = $this->getBase($parser, self::RELEASE_BRANCH, TRUE);
		$releaseBaseNew = $parser->import($branchBase);
		$parser->replace($releaseBase, $releaseBaseNew);
		$parser->save(systemRoot.$this->directory.self::DIR."/".self::RELEASE_BRANCH."/", self::INDEX, TRUE);
		
		// Create History entry
		$history = new vcsHistory($this->directory, $this->name, $this->type, $this->path);
		$history->createEntry(self::RELEASE_BRANCH, $description, $timestamp);
		
		return TRUE;
	} 
	
	/**
	 * Gets all the branches in an array.
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function getBranchList()
	{
		// Load Branch Index
		$parser = new DOMParser();
		$base = $this->getBranchBase($parser);
		$branches = $parser->evaluate("//branch", $base);
		
		
		// Parse List
		$branchList = array();
		foreach ($branches as $branch)
			$branchList[] = $branch->nodeValue;
		
		return $branchList;
	}
	
	/**
	 * Returns the head branch's name
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getHead($parser)
	{
		$parser->load($this->directory.self::DIR."/".self::INDEX, TRUE);
		$base = $parser->find($this->name);
		return $parser->evaluate("branch[@head]", $base)->item(0)->nodeValue;
	}
	
	/**
	 * Checks if the given branch is a reserved branch
	 * 
	 * @param	string	$branch
	 * 		The branch to check.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function checkBranchReserved($branch)
	{
		return in_array($branch, $this->reserved);
	}
	
	/**
	 * Checks if a given branch exists to the given repository.
	 * 
	 * @param	string	$branch
	 * 		The branch to check.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function checkBranchExists($branch)
	{
		// Get All Branches
		$branches = $this->getBranchList();

		// Check Existance
		return in_array($branch, $branches);
	}
}
//#section_end#
?>