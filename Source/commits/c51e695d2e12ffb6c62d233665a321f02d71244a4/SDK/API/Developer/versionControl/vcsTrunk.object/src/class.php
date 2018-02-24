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

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Developer\versionControl\vcsBranch;

/**
 * Version Control System Trunk Manager
 * 
 * Handles all the repository's trunk files
 * 
 * @version	{empty}
 * @created	March 13, 2013, 19:42 (EET)
 * @revised	November 14, 2013, 10:44 (EET)
 * 
 * @deprecated	Use misc\vcs instead.
 */
class vcsTrunk
{
	/**
	 * The trunk folder
	 * 
	 * @type	string
	 */
	const DIR = "/trunk";
	/**
	 * The trunk index file
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
	 * The branch manager object
	 * 
	 * @type	vcsBranch
	 */
	private $vcsBranch;
	
	/**
	 * Constructor method.
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
		// Set Branch Directory
		$this->directory = $directory;
		$this->name = $name;
		$this->type = $type;
		$this->path = $path;
		
		// Initialize vcsBranch
		$this->vcsBranch = new vcsBranch($this->directory, $this->name, $this->type, $this->path);
	}
	
	/**
	 * Creates the trunk folder structure.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function createStructure()
	{
		// Create branch folder
		return folderManager::create(systemRoot.$this->directory, self::DIR);
	}
	
	/**
	 * Creates a branch in the trunk's folder.
	 * 
	 * @param	string	$branch
	 * 		The branch's name.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function createBranch($branch)
	{
		// Create branch folder
		folderManager::create(systemRoot.$this->directory.self::DIR, $branch."/");
		
		// Create branch indexing
		$builder = new DOMParser();
		$base = $builder->create("trunk");
		$builder->append($base);
		return $builder->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
	}
	
	/**
	 * Deletes a branch from the trunk's folder
	 * 
	 * @param	string	$branch
	 * 		The branch's name.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function deleteBranch($branch)
	{
		// Check if its a valid (existing) branch
		if (!$this->vcsBranch->checkBranchExists($branch))
			return FALSE;
		
		// Remove index
		$this->updateBase(NULL, $branch);
		
		// Get trunk file path
		$trunk_file = systemRoot.$this->directory.self::DIR."/".$branch."/".$this->path."/".$this->name.".".$this->type;
		if (is_dir($trunk_file."/"))
			return folderManager::remove($trunk_file."/", "", TRUE);
		else
			return fileManager::remove($trunk_file);
	}
	
	/**
	 * Gets the object's trunk base from the given branch.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file.
	 * 
	 * @param	string	$branch
	 * 		The branch's name.
	 * 
	 * @param	boolean	$forceCreate
	 * 		If TRUE and the base doesn't exist, it creates it. Otherwise, it returns the base (NULL if not exists).
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function getBase($parser, $branch, $forceCreate = FALSE)
	{
		// Check if its a valid (existing) branch
		if (!$this->vcsBranch->checkBranchExists($branch) && !$forceCreate)
			return NULL;

		$parser->load($this->directory.self::DIR."/".$branch."/".self::INDEX, TRUE);
		$base = $parser->find($this->name);
		
		// If doesn't exist, create one
		if (is_null($base) && $forceCreate)
		{
			$base = $parser->create("item", "", $this->name);
			$parser->attr($base, "type", $this->type);
			$parser->attr($base, "path", $this->path);
			$root = $parser->evaluate("//trunk")->item(0);
			$parser->append($root, $base);
			$parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
		}
		
		return $base;
	}
	
	/**
	 * Gets the object's trunk base from the given branch by object title.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file.
	 * 
	 * @param	string	$branch
	 * 		The branch's name.
	 * 
	 * @param	string	$title
	 * 		The object's title.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function getBaseByTitle($parser, $branch, $title)
	{
		// Check if its a valid (existing) branch
		if (!$this->vcsBranch->checkBranchExists($branch))
			return FALSE;
		
		// Load Base
		$parser->load($this->directory.self::DIR."/".$branch."/".self::INDEX, TRUE);
		return $parser->evaluate("//item[@title='$title']")->item(0);
	}
	
	/**
	 * Gets the object's relative path to trunk of the given branch.
	 * 
	 * @param	string	$branch
	 * 		The branch's name.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getPath($branch)
	{
		// Check if its a valid (existing) branch
		if (!$this->vcsBranch->checkBranchExists($branch))
			return FALSE;
		
		$itemPath = systemRoot.$this->directory.self::DIR."/".$branch."/".$this->path."/".$this->name.".".$this->type;
		return $itemPath.(is_dir($itemPath) ? "/" : "");
	}
	
	/**
	 * Updates the trunk's base
	 * 
	 * @param	string	$branch
	 * 		The branch where the update will be done.
	 * 
	 * @param	DOMElement	$newBase
	 * 		The new base element. If NULL, the base will be deleted.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function updateBase($branch, $newBase)
	{
		// Check if its a valid (existing) branch
		if (!$this->vcsBranch->checkBranchExists($branch))
			return FALSE;
		
		$parser = new DOMParser();
		$trunkBase = $this->getBase($parser, $branch, TRUE);
		$newBase = $parser->import($newBase);
		$parser->replace($trunkBase, $newBase);
		return $parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
	}
	
	/**
	 * Updates the contents of the base only.
	 * 
	 * @param	string	$branch
	 * 		The branch where the update will be done.
	 * 
	 * @param	DOMElement	$contents
	 * 		The new contents of the item base.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function updateBaseContent($branch, $contents)
	{
		// Check if its a valid (existing) branch
		if (!$this->vcsBranch->checkBranchExists($branch))
			return FALSE;
		
		$parser = new DOMParser();
		$trunkBase = $this->getBase($parser, $branch, TRUE);
		$newContents = $parser->import($contents);
		$parser->setInnerHTML($trunkBase, $parser->getInnerHTML($newContents));
		return $parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
	}
	
	/**
	 * Gets all items in the given repository.
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser to load the xml file.
	 * 
	 * @param	string	$branch
	 * 		The given branch to search for items.
	 * 
	 * @return	array
	 * 		An array of node items.
	 */
	public function getAllItems($parser, $branch)
	{
		// Check if its a valid (existing) branch
		if (!$this->vcsBranch->checkBranchExists($branch))
			return FALSE;
		
		$parser->load($this->directory.self::DIR."/".$branch."/".self::INDEX, TRUE);
		$items = $parser->evaluate("//item");
		
		return $items;
	}
}
//#section_end#
?>