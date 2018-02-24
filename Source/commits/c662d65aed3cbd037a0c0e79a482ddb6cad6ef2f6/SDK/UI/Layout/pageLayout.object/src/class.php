<?php
//#section#[header]
// Namespace
namespace UI\Layout;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Layout
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "Profiler", "logger");
importer::import("DEV", "Tools", "parsers::phpParser");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Profiler\logger;
use \DEV\Tools\parsers\phpParser;

/**
 * Page layout manager
 * 
 * This is a page layout class object.
 * It is responsible for storing a page layout for every category.
 * 
 * @version	{empty}
 * @created	July 4, 2014, 10:50 (EEST)
 * @revised	July 4, 2014, 12:32 (EEST)
 */
class pageLayout
{
	/**
	 * The layout folder extension.
	 * 
	 * @type	string
	 */
	const FOLDER_EXT = '.layout';
	
	/**
	 * The layout name.
	 * 
	 * @type	string
	 */
	protected $name;
	
	/**
	 * The layout full path.
	 * 
	 * @type	string
	 */
	protected $path;
	
	/**
	 * The layout's category root folder.
	 * 
	 * @type	string
	 */
	private $categoryPath;

	/**
	 * Initializes the layout.
	 * 
	 * @param	string	$category
	 * 		The layout category to load the content from.
	 * 
	 * @param	string	$name
	 * 		The layout name (if any).
	 * 		For new layouts, leave this empty.
	 * 
	 * @return	void
	 */
	public function __construct($category, $name = "")
	{
		$this->categoryPath = "/System/Resources/Layouts/".$category;
		if (!empty($name))
		{
			$this->name = $name;
			$this->path = $this->categoryPath."/".$this->name.self::FOLDER_EXT;
		}
	}
	
	/**
	 * Creates a new layout with the given name.
	 * 
	 * @param	string	$name
	 * 		The layout name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($name)
	{
		// Set layout properties
		$this->name = $name;
		$this->path = $this->categoryPath."/".$this->name.self::FOLDER_EXT;
	
		// Create index entry
		$parser = new DOMParser();
		$parser->load($this->categoryPath."/index.xml", TRUE);		
		$root = $parser->evaluate("//layouts")->item(0);
		
		// Check if entry exists and exit function
		$layout = $parser->evaluate('lt[@name=\''.$name.'\']')->item(0);
		if (!is_null($layout))
			return TRUE;
		
		// Create Entry
		$layoutEntry = $parser->create("lt");
		$parser->attr($layoutEntry, "name", $name);
		$parser->append($root, $layoutEntry);
		$parser->update();
		
		// Create layout folder
		folderManager::create(systemRoot.$this->categoryPath);
		folderManager::create(systemRoot.$this->path);
		
		// Update layout
		$this->updateStructure();
		$this->updateCSS();
		
		return TRUE;
	}
	
	/**
	 * Get all layouts from the given category.
	 * 
	 * @return	array
	 * 		An array of layout names in the given category.
	 */
	public function getAllLayouts()
	{
		// Init dom parser
		$parser = new DOMParser();
		$parser->load($this->categoryPath."/index.xml", TRUE);
		
		// Load layouts from xml
		$layouts  = array();
		$layoutNodes = $parser->evaluate('//lt');
		foreach ($layoutNodes as $node)
			$layouts[] = $parser->attr($node, 'name');
		
		
		return $layouts;		
	}
	
	/**
	 * Gets the layout structure html code.
	 * 
	 * @param	boolean	$format
	 * 		Indicates whether it will be returned an xml formatted string or a plain, non whitespace string.
	 * 
	 * @return	string
	 * 		The layout's structure html code.
	 */
	public function getStructure($format = FALSE)
	{
		$parser = new DOMParser();
		
		try
		{
			// Load structure file
			$parser->load($this->path."/structure.xml", TRUE, $format);
			$root = $parser->evaluate("//structure")->item(0);
		}
		catch (Exception $ex)
		{
			logger::log("Layout Structure Loading Error.", logger::ERROR, $ex);
			return "";
		}
		
		// Set structure
		$structure = trim($parser->innerHTML($root));
		
		return $structure;
	}
	
	/**
	 * Gets the layout's css code.
	 * 
	 * @return	string
	 * 		The layout's css.
	 */
	public function getCSS()
	{
		return fileManager::get(systemRoot.$this->path."/style.css");
	}
	
	/**
	 * Updates the layout's css with the given css code.
	 * 
	 * @param	string	$code
	 * 		The new css code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateCSS($code = '')
	{
		// If code is empty, create an empty CSS file
		if ($code == '')
			$code = phpParser::comment("Write Your CSS Style Rules Here", $multi = TRUE);
		
		// Clear Code
		$code = phpParser::clear($code);
		
		// Save css file
		return fileManager::create(systemRoot.$this->path."/style.css", $code);
	}
	
	/**
	 * Save the layout's structure in file
	 * 
	 * @param	string	$code
	 * 		The layout's new structure code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateStructure($code = '')
	{
		// Clear code from unwanted chars
		$code = phpParser::clear($code);
		
		// Create structure
		$parser = new DOMParser();
		$root = $parser->create("structure", "", $this->name);
		$parser->append($root);
		$parser->innerHTML($root, $code);
		
		return $parser->save(systemRoot.$this->path."/structure.xml");
	}
	
		
	/**
	 * Removes the given layout from the category completely.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Delete index entry
		$parser = new DOMParser();
		$parser->load($this->categoryPath."/index.xml", TRUE);		
		$root = $parser->evaluate("//layouts")->item(0);
		
		// Check if entry exists
		$layout = $parser->evaluate('lt[@name=\''.$this->name.'\']')->item(0);
		if (is_null($layout))
			return TRUE;
			
		// Delete entry
		$parser->replace($layout, NULL);
		$parser->update();
		
		// Delete Directory
		return folderManager::remove(systemRoot.$this->path, "", TRUE);
	}
}
//#section_end#
?>