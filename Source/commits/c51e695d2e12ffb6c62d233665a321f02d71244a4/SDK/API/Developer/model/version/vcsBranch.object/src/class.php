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
importer::import("API", "Developer", "model::version::vcsHistory");

use \API\Platform\DOM\DOMParser;
use \API\Content\filesystem\fileManager;
use \API\Content\filesystem\folderManager;
use \API\Developer\model\version\repository;
use \API\Developer\model\version\vcsTrunk;
use \API\Developer\model\version\vcsHistory;

/**
 * Version Control Branch Manager
 * 
 * Manages all functions that involve branches
 * 
 * @version	{empty}
 * @created	July 3, 2013, 12:59 (EEST)
 * @revised	July 3, 2013, 12:59 (EEST)
 * 
 * @deprecated	Use \API\Developer\versionControl\ instead.
 */
class vcsBranch
{
	/**
	 * The inner folder for branches
	 * 
	 * @type	string
	 */
	const DIR = "/branches";
	/**
	 * The branch indexing file
	 * 
	 * @type	string
	 */
	const INDEX = "index.xml";
	/**
	 * The master branch name
	 * 
	 * @type	string
	 */
	const masterBranch = "master";
	
	/**
	 * The reserved branch names
	 * 
	 * @type	array
	 */
	private $reserved = array("master", "release");
	
	/**
	 * The repository directory
	 * 
	 * @type	string
	 */
	private $directory;
	/**
	 * The name of the branch
	 * 
	 * @type	string
	 */
	private $name;
	/**
	 * The type of the file
	 * 
	 * @type	string
	 */
	private $type;
	
	/**
	 * Constructor method.
	 * Initializes branch's properties
	 * 
	 * @param	string	$directory
	 * 		The repository directory
	 * 
	 * @param	string	$name
	 * 		The object's name
	 * 
	 * @param	string	$type
	 * 		The object's filetype
	 * 
	 * @return	void
	 */
	public function __construct($directory, $name, $type)
	{
		// Set Branch Directory (There is no systemRoot)
		$this->directory = $directory;
		$this->name = $name;
		$this->type = $type;
	}
	
	/**
	 * Initialize branch folder and branch information
	 * 
	 * @return	void
	 */
	public function initialize()
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
	 * Gets the branch base from the index
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function get_indexBase($parser)
	{
		$parser->load($this->directory.self::DIR."/".self::INDEX, TRUE);
		$base = $parser->find($this->name);
		
		// If doesn't exist, create one
		if (is_null($base))
		{
			$base = $parser->create("item", "", $this->name);
			DOMParser::attr($base, "type", $this->type);
			$root = $parser->evaluate("//branches")->item(0);
			$parser->append($root, $base);
			$parser->save(systemRoot.$this->directory.self::DIR."/", self::INDEX, TRUE);
		}

		return $base;
	}
	
	/**
	 * Get the branch item base
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file
	 * 
	 * @param	string	$branch
	 * 		The branch to get the base
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function get_base($parser, $branch = "")
	{
		$branch = ($branch == "" ? self::masterBranch : $branch);
		
		$parser->load($this->directory.self::DIR."/".$branch."/".self::INDEX, TRUE);
		$base = $parser->find($this->name);
		
		// If doesn't exist, create one
		if (is_null($base))
		{
			$base = $parser->create("item", "", $this->name);
			DOMParser::attr($base, "type", $this->type);
			$root = $parser->evaluate("//branch")->item(0);
			$parser->append($root, $base);
			$parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
		}
		
		return $base;
	}
	
	/**
	 * Returns the HEAD branch
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function get_head($parser)
	{
		$parser->load($this->directory.self::DIR."/".self::INDEX, TRUE);
		$base = $parser->find($this->name);
		return $parser->evaluate("branch[@head]", $base)->item(0)->nodeValue;
	}
	
	/**
	 * Get all repository branches
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function get_branches()
	{
		$parser = new DOMParser();

		$base = $this->get_indexBase($parser);

		$branches = $parser->evaluate("branch", $base);
		$branchList = array();
		foreach ($branches as $branch)
			$branchList[] = $branch->nodeValue;

		return $branchList;
	}
	
	/**
	 * Create a new branch
	 * 
	 * @param	string	$branch
	 * 		The name of the branch
	 * 
	 * @param	boolean	$head
	 * 		Indicator whether to set this branch as head
	 * 
	 * @return	void
	 */
	public function create($branch = "", $head = FALSE)
	{
		// Check if branch name is reserved
		//if (in_array(strtolower($branch), $this->reserved))
			//return FALSE;
			
		// If branch name not given, select master
		$branch = ($branch == "" ? self::masterBranch : $branch);

		// No repository
		if (!isset($this->directory))
			return FALSE;

		// Check if branch with the same name for the file exists!
		$parser = new DOMParser();
		$index_entry = $this->get_indexBase($parser);
		$duplicate_branch = $parser->evaluate("branch[text()='$branch']", $index_entry)->item(0);
		$duplicate_generalBranch = $parser->evaluate("//branch[text()='$branch']")->item(0);

		// If Branch exists, return TRUE (created)
		if (!is_null($duplicate_branch))
			return TRUE;
		
		if (is_null($duplicate_generalBranch))
		{
			// Create branch directory
			folderManager::create(systemRoot.$this->directory.self::DIR, $branch."/");
			// Create branch indexing
			$builder = new DOMParser();
			$base = $builder->create("branch");
			DOMParser::append($base);
			$builder->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
			
			// Create trunk files
			$trunk = new vcsTrunk($this->directory, $this->name, $this->type);
			$trunk->create_branch($branch);
			
			// Create history files
			$history = new vcsHistory($this->directory, $this->name, $this->type);
			$history->create_branch($branch);
		}

		// Update '$this->repository.self::branchFolder' index.xml
		if (is_null($index_entry))
		{
			// No entry for object
			$index_entry = $parser->create("item", "", $this->name);
			DOMParser::attr($index_entry, "type", $this->type);
			$root = $parser->evaluate("//branches");
			DOMParser::append($root, $index_entry);
		}
		
		// Set this branch as head and insert branch entry in file
		$branch_entry = $parser->create("branch", $branch);
		DOMParser::append($index_entry, $branch_entry);
		$parser->save(systemRoot.$this->directory.self::DIR."/", self::INDEX, TRUE);
		

		// Set head to branch
		if ($head)
			$this->checkout($branch);
		
		return TRUE;
	}
	
	/**
	 * Deletes a branch
	 * 
	 * @param	string	$branch
	 * 		The branch name
	 * 
	 * @return	void
	 */
	public function delete($branch = "")
	{
		$branch = ($branch == "" ? self::masterBranch : $branch);

		if (!isset($this->directory))
			return FALSE;

		// Delete the branch index
		$parser = new DOMParser();
		$base = $this->get_indexBase($parser);
		$branch_element = $parser->evaluate("branch[text()='$branch']", $base)->item(0);
		$branch_element->parentNode->removeChild($branch_element);
		$parser->save(systemRoot.$this->directory.self::DIR."/", self::INDEX, TRUE);
		
		// Delete all the branch elements
		$branchFolder = systemRoot.$this->directory.self::DIR."/".$branch."/";
		//_____ Delete the indexing
		$parser = new DOMParser();
		$branch_base = $this->get_base($parser, $branch);
		$branch_base->parentNode->removeChild($branch_base);
		$parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
		//_____ Delete the branch file
		$branchFile = $branchFolder.$this->name.".".$this->type;
		if (is_dir($branchFile."/"))
			folderManager::remove_full($branchFile."/");
		else
			fileManager::remove($branchFile);
		
		// Remove Branch from history
		$history = new vcsHistory($this->directory, $this->name, $this->type);
		$history->delete_branch($branch);
		
		// Remove Branch from trunk
		$trunk = new vcsTrunk($this->directory, $this->name, $this->type);
		$trunk->delete_branch($branch);
	}
	
	/**
	 * Delete the entire item
	 * 
	 * @return	void
	 */
	public function delete_item()
	{
		// Delete the branch index
		$parser = new DOMParser();
		$base = $this->get_indexBase($parser);
		$base->parentNode->removeChild($base);
		$parser->save(systemRoot.$this->directory.self::DIR."/", self::INDEX, TRUE);
	}
	
	/**
	 * Commits the HEAD file from trunk to branch
	 * 
	 * @param	string	$description
	 * 		The commit description
	 * 
	 * @return	void
	 */
	public function commit($description)
	{
		// Get head branch
		$parser = new DOMParser();
		$head = $this->get_head($parser);
		$index_entry = $this->get_indexBase($parser);
		$objectName = $this->name;
		$objectType = $this->type;

		// Recursive copy of object from trunk to branch and history (with timestamp)
		// Destination Folders
		$trunkFolder = systemRoot.$this->directory.vcsTrunk::DIR."/".$head."/";
		$branchFolder = systemRoot.$this->directory.self::DIR."/".$head."/";
		$historyFolder = systemRoot.$this->directory.vcsHistory::DIR."/".$head."/";
		
		// Create commit timestamp
		$timestamp = time();
		$trunkItem = $trunkFolder."/".$objectName.".".$objectType;
		$branchItem = $branchFolder.$objectName.".".$objectType;
		$historyItem = $historyFolder.$objectName."_".$timestamp.".".$objectType;
		
		// Check if item is Folder
		if (is_dir($trunkItem."/"))
		{
			// Copy To Branch
			folderManager::copy_folder($trunkItem."/", $branchFolder);
			// Copy to history
			folderManager::copy_folder($trunkItem."/", $historyItem."/", TRUE);
		}
		else
		{
			// Copy To Branch
			fileManager::copy_file($trunkItem, $branchItem);
			// Copy to history
			fileManager::copy_file($trunkItem, $historyItem);
		}

		// Create History entry
		$history = new vcsHistory($this->directory, $this->name, $this->type);
		$history->create_entry($timestamp, $description, $head);

		$trunk = new vcsTrunk($this->directory, $this->name, $this->type);
		$trunkBase = $trunk->get_base($parser, $head);
		//_____ Update branch index
		$branch_item = $this->get_base($parser, $head);
		$branchItem_imported = $parser->import($trunkBase);
		
		//_____ Create commit description
		DOMParser::replace($branch_item, $branchItem_imported);
		$parser->save(systemRoot.$this->directory.self::DIR."/".$head."/", self::INDEX, TRUE);

	}
	
	/**
	 * Checkout a branch (sets the HEAD)
	 * 
	 * @param	string	$branch
	 * 		The branch name to checkout
	 * 
	 * @return	void
	 */
	public function checkout($branch = "")
	{

		// If not branch given, get masterBranch
		$parser = new DOMParser();
		
		if ($branch == "")
			$branch = $this->get_head($parser);
		else
		{
			$branchIndex_base = $this->get_indexBase($parser);
			$heads = $parser->evaluate("branch[@head]", $branchIndex_base);
			foreach($heads as $head)
				DOMParser::attr($head, "head", "");

			// Update Head
			$branchHead = $parser->evaluate("branch[text()='$branch']", $branchIndex_base)->item(0);
			DOMParser::attr($branchHead, "head", "head");
	
			// Save file
			$parser->save(systemRoot.$this->directory.self::DIR."/", self::INDEX, TRUE);
		}
		
		// Return the file path to checkout
		return systemRoot.$this->directory.self::DIR."/".$branch."/".$this->name.".".$this->type;
	}
}
//#section_end#
?>