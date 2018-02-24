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
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Developer", "versionControl::vcsBranch");
importer::import("API", "Developer", "versionControl::vcsTrunk");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Developer\versionControl\vcsBranch;
use \API\Developer\versionControl\vcsTrunk;

/**
 * Version Control System History Manager
 * 
 * Manages the source history of a file.
 * 
 * @version	{empty}
 * @created	March 13, 2013, 19:21 (EET)
 * @revised	November 14, 2013, 10:44 (EET)
 * 
 * @deprecated	Use misc\vcs instead.
 */
class vcsHistory
{
	/**
	 * The history folder
	 * 
	 * @type	string
	 */
	const DIR = "/history";
	/**
	 * The history index file
	 * 
	 * @type	string
	 */
	const INDEX = "index.xml";
	
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
	 * The object's type
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
	 * The vcsBranch object.
	 * 
	 * @type	vcsBranch
	 */
	private $vcsBranch;
	
	/**
	 * Constructor method.
	 * Initializes the object's properties
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
		// Set Branch Directory
		$this->directory = $directory;
		$this->name = $name;
		$this->type = $type;
		$this->path = $path;
		
		// Initialize vcsBranch
		$this->vcsBranch = new vcsBranch($this->directory, $this->name, $this->type, $this->path);
	}
	
	/**
	 * Creates the history folder structure.
	 * 
	 * @return	void
	 */
	public function createStructure()
	{
		// Create branch folder
		folderManager::create(systemRoot.$this->directory, self::DIR);
	}
	
	/**
	 * Gets the history item base of the given branch.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file.
	 * 
	 * @param	string	$branch
	 * 		The preferable branch.
	 * 
	 * @param	{type}	$forceCreate
	 * 		{description}
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	private function getBase($parser, $branch, $forceCreate = FALSE)
	{
		// Check if its a valid (existing) branch
		if (!$this->vcsBranch->checkBranchExists($branch))
			return FALSE;
		
		$parser->load($this->directory.self::DIR."/".$branch."/".self::INDEX, TRUE);
		$base = $parser->find($this->name);
		
		// If doesn't exist, create one
		if (is_null($base) && $forceCreate)
		{
			$base = $parser->create("entry", "", $this->name);
			$root = $parser->evaluate("//history")->item(0);
			$parser->append($root, $base);
			$parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
		}
		
		return $base;
	}
	
	/**
	 * Get a specific timestamp entry of an item.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file.
	 * 
	 * @param	string	$branch
	 * 		The branch's name.
	 * 
	 * @param	string	$timestamp
	 * 		The entry's timestamp.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	private function getEntryBase($parser, $branch, $timestamp)
	{
		// Check if its a valid (existing) branch
		if (!$this->vcsBranch->checkBranchExists($branch))
			return FALSE;
		
		$parser->load($this->directory.self::DIR."/".$branch."/".self::INDEX, TRUE);
		$base = $parser->find($this->name."_".$timestamp);
		
		return $base;
	}
	
	/**
	 * Creates a new history branch.
	 * 
	 * @param	string	$branch
	 * 		The branch's name
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function createBranch($branch)
	{
		// Create history folder
		folderManager::create(systemRoot.$this->directory.self::DIR, $branch."/");
		
		// Create history indexing
		$builder = new DOMParser();
		$base = $builder->create("history");
		$builder->append($base);
		$builder->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
		
		return TRUE;
	}
	
	/**
	 * Deletes an existing history branch.
	 * 
	 * @param	string	$branch
	 * 		The branch's name
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function deleteBranch($branch)
	{
		// Check if its a valid (existing) branch
		if (!$this->vcsBranch->checkBranchExists($branch))
			return FALSE;
		
		$parser = new DOMParser();
		// Delete all the branch history elements
		$branchHistoryFolder = systemRoot.$this->directory.self::DIR."/".$branch."/";
		//_____ Delete the indexing
		$history_base = $this->getBase($parser, $branch, TRUE);
		$history_base->parentNode->removeChild($history_base);
		$parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
		//_____ Delete the history files
		$branchHistoryfiles = glob(preg_quote($branchHistoryFolder.$this->path."/".$this->name."_")."*".preg_quote(".".$this->type));
		foreach ($branchHistoryfiles as $file)
		{
			if (is_dir($file."/"))
				folderManager::remove($file."/", "", TRUE);
			else
				fileManager::remove($file);
		}
	}
	
	/**
	 * Creates a new entry in the history log file
	 * 
	 * @param	string	$branch
	 * 		The branch's name
	 * 
	 * @param	string	$description
	 * 		The description of the log record
	 * 
	 * @param	string	$timestamp
	 * 		The timestamp when this action triggered.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function createEntry($branch, $description, $timestamp)
	{
		// Check if its a valid (existing) branch
		if (!$this->vcsBranch->checkBranchExists($branch))
			return FALSE;

		$parser = new DOMParser();
		// Copy trunk index entries to branch history index entries
		$trunk = new vcsTrunk($this->directory, $this->name, $this->type, $this->path);
		$trunkBase = $trunk->getBase($parser, $branch);

		$history_parser = new DOMParser();
		$historyEntry = $this->getBase($history_parser, $branch, TRUE);

		$historyBase = $history_parser->import($trunkBase);
		$history_parser->append($historyEntry, $historyBase);
		$historyId = $history_parser->attr($historyBase, "id");
		$history_parser->attr($historyBase, "id", $historyId."_".$timestamp);
		$history_parser->attr($historyBase, "timestamp", $timestamp);

		//_____ Create commit description
		$commitDescription = $history_parser->create("commitDescription", $description);
		$history_parser->prepend($historyBase, $commitDescription);
		$history_parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);

		// Copy File		
		$trunkItem = systemRoot.$this->directory.vcsTrunk::DIR."/".$branch."/".$this->path."/".$this->name.".".$this->type;
		$historyItem = systemRoot.$this->directory.self::DIR."/".$branch."/".$this->path."/".$this->name."_".$timestamp.".".$this->type;
		
		// Check Folder
		if (is_dir($trunkItem."/"))
		{
			if (!is_dir($historyItem))
				folderManager::create($historyItem."/");
			folderManager::copy($trunkItem."/", $historyItem."/", TRUE);
		}
		else
			fileManager::copy($trunkItem, $historyItem);
		
		
		return TRUE;
	}
	
	/**
	 * Restores a history entry to the trunk.
	 * 
	 * @param	string	$branch
	 * 		The branch that will be restored.
	 * 
	 * @param	string	$timestamp
	 * 		The timestamp of the file which will be restored.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function restoreEntry($branch, $timestamp)
	{
		// Check if its a valid (existing) branch
		if (!$this->vcsBranch->checkBranchExists($branch))
			return FALSE;
		
		// Initialize vcsTrunk
		$vcsTrunk = new vcsTrunk($this->directory, $this->name, $this->type, $this->path);
		
		// Update Trunk Base
		$parser = new DOMParser();
		$entryBase = $this->getEntryBase($parser, $branch, $timestamp);
		$vcsTrunk->updateBase($branch, $newBase);
		
		// Copy File		
		$trunkItem = systemRoot.$this->directory.vcsTrunk::DIR."/".$branch."/".$this->path."/".$this->name.".".$this->type;
		$historyItem = systemRoot.$this->directory.self::DIR."/".$branch."/".$this->path."/".$this->name."_".$timestamp.".".$this->type;
		
		// Check Folder
		if (is_dir($historyItem."/"))
			folderManager::copy($historyItem."/", $trunkItem."/", TRUE);
		else
			fileManager::copy($historyItem, $historyItem);
		
		return TRUE;
	}
}
//#section_end#
?>