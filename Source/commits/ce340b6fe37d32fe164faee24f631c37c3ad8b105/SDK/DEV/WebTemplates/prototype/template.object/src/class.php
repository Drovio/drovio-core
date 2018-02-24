<?php
//#section#[header]
// Namespace
namespace DEV\WebTemplates\prototype;

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
 * @package	WebTemplates
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "filesystem/directory");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\directory;

/**
 * Web Template Project
 * 
 * Object class to manage web template projects
 * 
 * @version	1.0-1
 * @created	July 7, 2014, 20:34 (EEST)
 * @revised	July 21, 2014, 21:13 (EEST)
 */
class template
{
	/**
	 * The index file name.
	 * 
	 * @type	string
	 */
	const INDEX_FILE = "index.xml";
	
	/**
	 * The pages folder name
	 * 
	 * @type	string
	 */
	const PAGES_FOLDER = "pages";
	
	/**
	 * The themes folder name
	 * 
	 * @type	string
	 */
	const THEMES_FOLDER = "themes";

	/**
	 * Global DOMparser object, loaded with templates setting.xml
	 * 
	 * @type	DOMparser
	 */
	private $parser;
	
	private $templateFolderPath;

	/**
	 * The contructor method
	 * 
	 * @param	sting	$id
	 * 		The projects' id
	 * 
	 * @param	sting	$name
	 * 		The projects' name
	 * 
	 * @return	void
	 */
	public function __construct($templateFolderPath, $rootRelative = TRUE)
	{
		// Initialize template
		$this->templateFolderPath = directory::normalize(($rootRelative ? systemRoot : "").$templateFolderPath."/");
		
		// Create structure (if first time)
		$this->createStructure();
	}
	
	/**
	 * Creates the (folder / file) structure of the project at creation.
	 * 
	 * @return	boolean
	 * 		True on success, false elsewhere
	 */
	private function createStructure()
	{
		// Check if structure is already there
		if (file_exists($this->templateFolderPath.self::INDEX_FILE))
			return FALSE;
		
		// Create index file
		fileManager::create($this->templateFolderPath.self::INDEX_FILE, "", TRUE);
	
		// Create Objects' sub folders
		folderManager::create($this->templateFolderPath.self::PAGES_FOLDER);
		folderManager::create($this->templateFolderPath.self::THEMES_FOLDER);
		
		// Create map files
		$this->createMapFiles();
	}
	
	/**
	 * Creates the mapping / index file of the project at creation.
	 * 
	 * @return	boolean
	 * 		True on success, false elsewhere
	 */
	private function createMapFile()
	{
		// Set xml parser
		$parser = new DOMParser();
		$indexFilePath = $this->templateFolderPath.self::INDEX_FILE;
		
		// Create root
		$root = $parser->create("template");
		$parser->append($root);
		
		// Set pages index
		$entry = $parser->create(self::PAGES_FOLDER);
		$parser->append($root, $entry);
		
		// Set themes index
		$entry = $parser->create(self::THEMES_FOLDER);
		$parser->append($root, $entry);
		
		// Save file and return
		return $parser->save($indexFilePath);
	}
	
	/**
	 * Fetches the description of all available pages in the project
	 * 
	 * @return	array
	 * 		Array of items' names
	 */
	public function getPages()
	{
		return $this->getIndexObjects(self::PAGES_FOLDER, 'page');
	}
	
	/**
	 * Fetches the description of all available themes in the project
	 * 
	 * @return	array
	 * 		Array of items' names
	 */
	public function getThemes()
	{
		return $this->getIndexObjects(self::THEMES_FOLDER, 'theme');
	}
	
	
	/**
	 * Adds an objects' entry to the projects' mapping index file.
	 * 
	 * @param	string	$group
	 * 		The object group.
	 * 
	 * @param	string	$type
	 * 		The object type.
	 * 
	 * @param	string	$name
	 * 		The object name.
	 * 
	 * @return	vobooleanid
	 * 		True on success, false if object already exist.
	 */
	public function addObjectIndex($group, $type, $name)%0
//#section_end#
?>