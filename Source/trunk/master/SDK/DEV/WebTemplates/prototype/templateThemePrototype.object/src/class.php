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
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("DEV", "Tools", "codeParser");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Tools", "parsers/scssParser");
importer::import("DEV", "WebTemplates", "prototype/templatePrototype");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Tools\codeParser;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\scssParser;
use \DEV\WebTemplates\prototype\templatePrototype;

/**
 * Web Template Theme Prototype
 * 
 * Manages the theme prototype object given the theme path (inside a template, managed by the template).
 * 
 * @version	0.1-3
 * @created	September 17, 2015, 19:56 (EEST)
 * @updated	September 18, 2015, 13:03 (EEST)
 */
class templateThemePrototype
{
	/**
	 * The index file name.
	 * 
	 * @type	string
	 */
	const INDEX_FILE = "index.xml";
	
	/**
	 * The theme folder type extension.
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "theme";
	
	/**
	 * The javascripts' folder name.
	 * 
	 * @type	string
	 */
	const JS_FOLDER = "javascripts";
	
	/**
	 * The styles' folder name.
	 * 
	 * @type	string
	 */
	const CSS_FOLDER = "styles";
	
	/**
	 * The template prototype object.
	 * 
	 * @type	templatePrototype
	 */
	private $template;
	
	/**
	 * The theme folder path.
	 * 
	 * @type	string
	 */
	private $indexFilePath;

	/**
	 * Create a template theme prototype instance.
	 * 
	 * @param	string	$indexFilePath
	 * 		The template's index file path.
	 * 
	 * @param	string	$themeIndexFilePath
	 * 		The theme's index file path.
	 * 		Leave it empty for new themes.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($indexFilePath, $themeIndexFilePath = "")
	{
		// Initialize
		$this->template = new templatePrototype($indexFilePath);
		$this->indexFilePath = $themeIndexFilePath;
		$this->createStructure();
	}
	
	/**
	 * Create a new template theme.
	 * 
	 * @param	string	$indexFilePath
	 * 		The theme's index file path.
	 * 
	 * @param	string	$name
	 * 		The theme name.
	 * 		This is needed for indexing.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if there is a theme with the same name.
	 */
	public function create($indexFilePath, $name)
	{
		// Check that name is not empty and valid
		if (empty($name))
			return FALSE;
			
		// Create object index
		$status = $this->template->addObjectIndex(templatePrototype::THEMES_FOLDER, "theme", $name);
		if (!$status)
			return FALSE;
		
		// Create theme structure
		$this->indexFilePath = $indexFilePath;
		$this->createStructure();
		
		return TRUE;
	}
	
	/**
	 * Get all theme javascripts.
	 * 
	 * @return	array
	 * 		An array of all javascript names.
	 */
	public function getAllJS()
	{
		return $this->getIndexObjects(self::JS_FOLDER, 'script');
	}
	
	/**
	 * Add a javascript file to the index.
	 * 
	 * @param	string	$name
	 * 		The javascript file name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if there is a javascript file with the same name.
	 */
	public function addJS($name)
	{
		// Add index entry
		return $this->addObjectIndex(self::JS_FOLDER, "script", $name);
	}
	
	/**
	 * Remove a javascript object from the index file.
	 * 
	 * @param	string	$name
	 * 		The javascript name to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeJS($name)
	{
		// Remove object index
		return $this->removeObjectIndex("script", $name);
	}
	
	/**
	 * Get all theme styles.
	 * 
	 * @return	array
	 * 		An array of all style names.
	 */
	public function getAllCSS()
	{
		return $this->getIndexObjects(self::CSS_FOLDER, 'style');
	}
	
	/**
	 * Add a css file to the index.
	 * 
	 * @param	string	$name
	 * 		The css file name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if there is a css file with the same name.
	 */
	public function addCSS($name)
	{
		// Add index entry
		return $this->addObjectIndex(self::CSS_FOLDER, "style", $name);
	}
	
	/**
	 * Remove a style object from the index file.
	 * 
	 * @param	string	$name
	 * 		The style name to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeCSS($name)
	{
		// Remove object index
		return $this->removeObjectIndex("style", $name);
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
	
	/**
	 * Remove the entire theme from the template.
	 * It must be empty of scripts and styles.
	 * 
	 * @param	string	$name
	 * 		The theme name for index updates.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($name)
	{
		// Check if there are objects inside the theme
		$scripts = $this->getAllJS();
		if (count($scripts) > 0)
			return FALSE;
		
		$styles = $this->getAllCSS();
		if (count($styles) > 0)
			return FALSE;
		
		// Remove page from index
		$status = $this->template->removeObjectIndex("theme", $name);
		folderManager::remove($this->pageFilePath);
		
		return $status;
	}
	
	/**
	 * Create the page structure with the necessary files.
	 * 
	 * @return	void
	 */
	private function createStructure()
	{
		// Validate theme folder
		if (empty($this->indexFilePath))
			return;
		
		// Create map index
		if (file_exists($this->indexFilePath))
			return FALSE;
		
		// Create an empty file
		fileManager::create($this->indexFilePath, "", TRUE);
		
		// Set xml parser
		$parser = new DOMParser();
		
		// Create root
		$root = $parser->create("theme");
		$parser->append($root);
		
		// Set javascript index
		$entry = $parser->create(self::JS_FOLDER);
		$parser->append($root, $entry);
		
		// Set styles index
		$entry = $parser->create(self::CSS_FOLDER);
		$parser->append($root, $entry);
		
		// Save file and return
		return $parser->save($this->indexFilePath);
	}
	
	/**
	 * Get the name of the theme smart object folder.
	 * 
	 * @param	string	$name
	 * 		The theme name.
	 * 
	 * @return	string
	 * 		The theme folder name.
	 */
	public static function getThemeFolder($name)
	{
		return $name.".".self::FILE_TYPE;
	}
}
//#section_end#
?>