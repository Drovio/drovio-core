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
importer::import("API", "Content", "filesystem::fileManager");
importer::import("API", "Content", "filesystem::folderManager");

use \API\Platform\DOM\DOMParser;
use \API\Content\filesystem\fileManager;
use \API\Content\filesystem\folderManager;

/**
 * Version Control Trunk Manager
 * 
 * Handles all the repository's trunk files
 * 
 * @version	{empty}
 * @created	July 3, 2013, 13:00 (EEST)
 * @revised	July 3, 2013, 13:00 (EEST)
 * 
 * @deprecated	Use \API\Developer\versionControl\ instead.
 */
class vcsTrunk
{
	/**
	 * The trunk inner folder
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
	 * The repository's directory
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
	 * Constructor method.
	 * Initializes the object's properties.
	 * 
	 * @param	string	$directory
	 * 		The repository's directory
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
		// Set Branch Directory
		$this->directory = $directory;
		$this->name = $name;
		$this->type = $type;
	}
	
	/**
	 * Initialize trunk folder and trunk information
	 * 
	 * @return	void
	 */
	public function initialize()
	{
		// Create branch folder
		folderManager::create(systemRoot.$this->directory, self::DIR);
	}
	
	/**
	 * Get the branch trunk item base
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file
	 * 
	 * @param	string	$branch
	 * 		The branch name
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function get_base($parser, $branch = "")
	{
		// If not branch given, get masterBranch
		$branch = ($branch == "" ? vcsBranch::masterBranch : $branch);
		
		$parser->load($this->directory.self::DIR."/".$branch."/".self::INDEX, TRUE);
		$base = $parser->find($this->name);
		
		// If doesn't exist, create one
		if (is_null($base))
		{
			$base = $parser->create("item", "", $this->name);
			$root = $parser->evaluate("//trunk")->item(0);
			$parser->append($root, $base);
			$parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
		}
		
		return $base;
	}
	
	/**
	 * Returns all the items in the given branch
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file
	 * 
	 * @param	string	$branch
	 * 		The branch name
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function get_allItems($parser, $branch = "")
	{
		// If not branch given, get masterBranch
		$branch = ($branch == "" ? vcsBranch::masterBranch : $branch);
		
		$parser->load($this->directory.self::DIR."/".$branch."/".self::INDEX, TRUE);
		$items = $parser->evaluate("//item");
		
		return $items;
	}
	
	/**
	 * Return the item's index base by given title
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file
	 * 
	 * @param	string	$title
	 * 		The item's title
	 * 
	 * @param	string	$branch
	 * 		The branch name
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function get_baseByTitle($parser, $title, $branch = "")
	{
		// If not branch given, get masterBranch
		$branch = ($branch == "" ? vcsBranch::masterBranch : $branch);

		$parser->load($this->directory.self::DIR."/".$branch."/".self::INDEX, TRUE);
		$base = $parser->evaluate("//item[@title='$title']")->item(0);

		return $base;
	}
	
	/**
	 * Get the item's path to the trunk
	 * 
	 * @param	string	$branch
	 * 		The branch name
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function get_itemPath($branch = "")
	{
		$branch = ($branch == "" ? vcsBranch::masterBranch : $branch);
		
		$itemPath = systemRoot.$this->directory.self::DIR."/".$branch."/".$this->name.".".$this->type;
		return $itemPath.(is_dir($itemPath) ? "/" : "");
	}
	
	/**
	 * Update trunk index base item
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file
	 * 
	 * @param	DOMElement	$newBase
	 * 		The new base item
	 * 
	 * @param	string	$branch
	 * 		The branch name
	 * 
	 * @return	void
	 */
	public function update_indexBase($parser, $newBase, $branch = "")
	{
		// If not branch given, get masterBranch
		$branch = ($branch == "" ? vcsBranch::masterBranch : $branch);
		
		$trunk_base = $this->get_base($parser, $branch);
		$newBase = $parser->import($newBase);
		$parser->replace($trunk_base, $newBase);
		
		$parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
	}
	
	/**
	 * Update the trunk index content
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file
	 * 
	 * @param	DOMElement	$contents
	 * 		The contents of the item base
	 * 
	 * @param	string	$branch
	 * 		The branch name
	 * 
	 * @return	void
	 */
	public function update_indexContent($parser, $contents, $branch = "")
	{
		// If not branch given, get masterBranch
		$branch = ($branch == "" ? vcsBranch::masterBranch : $branch);
		
		$trunk_base = $this->get_base($parser, $branch);
		$parser->setInnerHTML($trunk_base, $parser->getInnerHTML($contents));
		
		$parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
	}
	
	/**
	 * Create a new trunk branch
	 * 
	 * @param	string	$branch
	 * 		The branch name
	 * 
	 * @return	void
	 */
	public function create_branch($branch = "")
	{
		$branch = ($branch == "" ? vcsBranch::masterBranch : $branch);
		
		// Create branch folder
		folderManager::create(systemRoot.$this->directory.self::DIR, $branch."/");
		
		// Create branch indexing
		$builder = new DOMParser();
		$base = $builder->create("trunk");
		DOMParser::append($base);
		$builder->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
	}
	
	/**
	 * Delete a branch inside the trunk
	 * 
	 * @param	string	$branch
	 * 		The branch name to be deleted
	 * 
	 * @return	void
	 */
	public function delete_branch($branch = "")
	{
		$branch = ($branch == "" ? vcsBranch::masterBranch : $branch);
		$parser = new DOMParser();
		
		// Remove index
		$this->update_indexBase($parser, NULL, $branch);
		
		// Get trunk file path
		$trunk_file = systemRoot.$this->directory.self::DIR."/".$branch."/".$this->name.".".$this->type;
		echo $trunk_file;
		if (is_dir($trunk_file."/"))
			folderManager::remove_full($trunk_file."/");
		else
			fileManager::remove($trunk_file);
	}
}
//#section_end#
?>