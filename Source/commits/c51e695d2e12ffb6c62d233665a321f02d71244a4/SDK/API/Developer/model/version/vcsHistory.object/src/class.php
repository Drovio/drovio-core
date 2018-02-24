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
 * Version Control History Manager
 * 
 * Manages all the history in the repositories
 * 
 * @version	{empty}
 * @created	July 3, 2013, 13:00 (EEST)
 * @revised	July 3, 2013, 13:00 (EEST)
 * 
 * @deprecated	Use \API\Developer\versionControl\ instead.
 */
class vcsHistory
{
	/**
	 * The history inner folder
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
	 * The object's filetype
	 * 
	 * @type	string
	 */
	private $type;
	
	/**
	 * Constructor method.
	 * Initializes the object's properties
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
		// Set Branch Directory
		$this->directory = $directory;
		$this->name = $name;
		$this->type = $type;
	}
	
	/**
	 * Initializes history index information
	 * 
	 * @return	void
	 */
	public function initialize()
	{
		// Create branch folder
		folderManager::create(systemRoot.$this->directory, self::DIR);
	}
	
	/**
	 * Get the branch history item base
	 * 
	 * @param	DOMParser	$parser
	 * 		The parser used to parse the index file
	 * 
	 * @param	string	$branch
	 * 		The history's branch
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function get_base($parser, $branch = "")
	{
		$branch = ($branch == "" ? vcsBranch::masterBranch : $branch);
		
		$parser->load($this->directory.self::DIR."/".$branch."/".self::INDEX, TRUE);
		$base = $parser->find($this->name);
		
		// If doesn't exist, create one
		if (is_null($base))
		{
			$base = $parser->create("entry", "", $this->name);
			$root = $parser->evaluate("//history")->item(0);
			$parser->append($root, $base);
			$parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
		}
		
		return $base;
	}
	
	/**
	 * Create a new history branch
	 * 
	 * @param	string	$branch
	 * 		The history's branch
	 * 
	 * @return	void
	 */
	public function create_branch($branch = "")
	{
		$branch = ($branch == "" ? vcsBranch::masterBranch : $branch);
		
		// Create history folder
		folderManager::create(systemRoot.$this->directory.self::DIR, $branch."/");
		
		// Create history indexing
		$builder = new DOMParser();
		$base = $builder->create("history");
		DOMParser::append($base);
		$builder->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
	}
	
	/**
	 * Deletes a history branch
	 * 
	 * @param	string	$branch
	 * 		The history's branch
	 * 
	 * @return	void
	 */
	public function delete_branch($branch = "")
	{
		$branch = ($branch == "" ? vcsBranch::masterBranch : $branch);
		
		if (!isset($this->directory))
			return FALSE;
		
		$parser = new DOMParser();
		// Delete all the branch history elements
		$branchHistoryFolder = systemRoot.$this->directory.self::DIR."/".$branch."/";
		//_____ Delete the indexing
		$history_base = $this->get_base($parser, $branch);
		$history_base->parentNode->removeChild($history_base);
		$parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
		//_____ Delete the history files
		$branchHistoryfiles = glob(preg_quote($branchHistoryFolder.$this->name."_")."*".preg_quote(".".$this->type));
		print_r($$branchHistoryfiles);
		foreach ($branchHistoryfiles as $file)
		{
			if (is_dir($file."/"))
				folderManager::remove_full($file."/");
			else
				fileManager::remove($file);
		}
	}
	
	/**
	 * Creates a new entry in the history log file
	 * 
	 * @param	string	$timestamp
	 * 		The timestamp of the entry
	 * 
	 * @param	string	$description
	 * 		The entry description
	 * 
	 * @param	string	$branch
	 * 		The history's branch
	 * 
	 * @return	void
	 */
	public function create_entry($timestamp, $description, $branch = "")
	{
		$branch = ($branch == "" ? vcsBranch::masterBranch : $branch);
		
		$parser = new DOMParser();
		// Copy trunk index entries to branch history index entries
		$trunk = new vcsTrunk($this->directory, $this->name, $this->type);
		$trunkBase = $trunk->get_base($parser, $branch);
		
		$history_parser = new DOMParser();
		$history_item = $this->get_base($history_parser, $branch);
		if (is_null($history_item))
		{
			$history_item = $history_parser->create("entry", "", $this->name);
			$root = $history_parser->evaluate("//history");
			DOMParser::append($root, $history_item);
		}
		
		$history_entry = $history_parser->import($trunkBase);
		$old_id = DOMParser::attr($history_entry, "id");
		DOMParser::attr($history_entry, "id", $old_id."_".$timestamp);
		DOMParser::attr($history_entry, "timestamp", $timestamp);
		
		//_____ Create commit description
		$commitDescription = $history_parser->create("commitDescription", $description);
		DOMParser::prepend($history_entry, $commitDescription);
		DOMParser::append($history_item, $history_entry);
		$history_parser->save(systemRoot.$this->directory.self::DIR."/".$branch."/", self::INDEX, TRUE);
	}
}
//#section_end#
?>