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
 * @namespace	\prototype
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * Web Template Prototype Object
 * 
 * This class manages a template within a root folder.
 * Extend this class to enable editing a web template.
 * 
 * @version	1.0-1
 * @created	September 17, 2015, 18:46 (EEST)
 * @updated	September 17, 2015, 19:57 (EEST)
 */
class templatePrototype
{
	/**
	 * The index file name.
	 * 
	 * @type	string
	 */
	const INDEX_FILE = "index.xml";
	
	/**
	 * The pages' folder name.
	 * 
	 * @type	string
	 */
	const PAGES_FOLDER = "pages";
	
	/**
	 * The themes' folder name.
	 * 
	 * @type	string
	 */
	const THEMES_FOLDER = "themes";

	/**
	 * The local xml parser.
	 * 
	 * @type	DOMParser
	 */
	private $parser;
	
	/**
	 * The template's index file path.
	 * 
	 * @type	string
	 */
	private $indexFilePath;

	/**
	 * Create a template instance.
	 * 
	 * @param	string	$indexFilePath
	 * 		The index file path.
	 * 		Leave empty for new templates.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($indexFilePath = "")
	{
		// Create index map file (if first time)
		$this->indexFilePath = $indexFilePath;
		$this->createMapFile();
	}
	
	/**
	 * Create the template index map file.
	 * 
	 * @return	boolean
	 * 		True on success, false if the file already exists.
	 */
	private function createMapFile()
	{
		// Check if structure is already there
		if (empty($this->indexFilePath) || file_exists($this->indexFilePath))
			return FALSE;
		
		// Create an empty file
		fileManager::create($this->indexFilePath, "", TRUE);
		
		// Set xml parser
		$parser = new DOMParser();
		
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
		return $parser->save($this->indexFilePath);
	}
	
	/**
	 * Get all template pages.
	 * 
	 * @return	array
	 * 		An array of all page names.
	 */
	public function getPages()
	{
		return $this->getIndexObjects(self::PAGES_FOLDER, 'page');
	}
	
	/**
	 * Get all template themes.
	 * 
	 * @return	array
	 * 		An array of all theme names.
	 */
	public function getThemes()
	{
		return $this->getIndexObjects(self::THEMES_FOLDER, 'theme');
	}
	
	
	/**
	 * Add an object entry to the main template index.
	 * 
	 * @param	string	$group
	 * 		The name of the group.
	 * 
	 * @param	string	$type
	 * 		The object type (the tag name).
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if there is another object of the same type with the same name.
	 */
	public function addObjectIndex($group, $type, $name)
	{
		// Set xml parser
		$parser = new DOMParser();
		$parser->load($this->indexFilePath, FALSE);
		
		// Check if item already exists
		$item = $parser->evaluate("//".$group."/".$type."[@name='".$name."']")->item(0);
		if (!is_null($item))
			return FALSE;
		
		// Get The root
		$root = $parser->evaluate("//".$group)->item(0);
		
		// Create object
		$obj = $parser->create($type);
		$parser->attr($obj, "name", $name);
		$parser->append($root, $obj);
		
		// Update file
		return $parser->update();
	}
	
	/**
	 * Remove an object entry from the template index.
	 * 
	 * @param	string	$type
	 * 		The object type (the tag name).
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeObjectIndex($type, $name)
	{
		// Set xml parser
		$parser = new DOMParser();
		$parser->load($this->indexFilePath, FALSE);
		
		// Check if item already exists
		$item = $parser->evaluate("//".$type."[@name='".$name."']")->item(0);
		if (is_null($item))
			return FALSE;
		
		// Remove item and update file
		$parser->replace($item, NULL);
		return $parser->update();
	}
	
	/**
	 * Get all index object entries.
	 * 
	 * @param	string	$group
	 * 		The object group.
	 * 
	 * @param	string	$type
	 * 		The object type.
	 * 
	 * @return	array
	 * 		An array of all objects by name.
	 */
	private function getIndexObjects($group, $type)
	{
		// Set xml parser
		$parser = new DOMParser();
		$parser->load($this->indexFilePath, FALSE);
		
		// Get The root
		$root = $parser->evaluate("//".$group)->item(0);

		// Get all packages
		$objects = array();
		$elements = $parser->evaluate($type, $root);
		foreach ($elements as $elem)
			$objects[] = $parser->attr($elem, "name");
		
		return $objects;
	}
}
//#section_end#
?>