<?php
//#section#[header]
// Namespace
namespace API\Developer\resources\layouts;

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
 * @namespace	\resources\layouts
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Developer", "profiler::logger");

importer::import("UI", "Html", "DOM");
use \UI\Html\DOM;

use \API\Developer\profiler\logger;
use \API\Developer\content\document\parsers\phpParser;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;

/**
 * Layout Object
 * 
 * Main for system and ebuilder layout manipulation
 * 
 * @version	{empty}
 * @created	April 8, 2013, 13:19 (EEST)
 * @revised	April 26, 2013, 13:47 (EEST)
 */
abstract class AbstractLayout
{
	/**
	 * The layout folder extension.
	 * 
	 * @type	string
	 */
	const FOLDER_EXT = '.layout';

	/**
	 * The root element in strucrure.xml file
	 * 
	 * @type	string
	 */
	const STRUCT_ROOT = 'structure';
	
	/**
	 * The root resource folder that containig all layouts.
	 * 
	 * @type	string
	 */
	const LAYOUT_ROOT_FOLDER = '/System/Resources/Layouts';
	
	/**
	 * The current working folder path, with all layouts
	 * 
	 * @type	string
	 */
	private $folderPath;
	
	/**
	 * Holds the layout type name, given by inheritance class
	 * 
	 * @type	string
	 */
	private $layoutType;
	
	/**
	 * The layout name.
	 * 
	 * @type	string
	 */
	protected $name;
	
	/**
	 * The layout structure.  Plain, non whitespace string.
	 * 
	 * @type	string
	 */
	protected $path;

	/**
	 * Constructor Method
	 * 
	 * @param	string	$filePath
	 * 		That subfolder that contains the layouts under layout root path
	 * 
	 * @return	void
	 */
	protected function __construct($filePath)
	{
		$this->folderPath = self::LAYOUT_ROOT_FOLDER."/".$filePath;
		$this->layoutType = $filePath;
	}	
	
	/**
	 * Loads the layout given by name
	 * Return FALSE if layout not found
	 * 
	 * @param	string	$name
	 * 		Layout name for loading
	 * 
	 * @return	boolean
	 */
	protected function initialize($name)
	{
		$this->name = $name;
		$this->path = $this->folderPath."/".$name.self::FOLDER_EXT;
		
		return $this;
	}
	
	/**
	 * Lists all layouts in current folder
	 * 
	 * @return	string
	 */
	public function getAllLayouts()
	{
		$layouts  = array();
		$dom_parser = new DOMParser();
		$dom_parser->load($this->folderPath."/index.xml", TRUE);
		
		$layoutNodes = $dom_parser->evaluate('//lt');
		
		foreach ($layoutNodes as $node)
		{
			array_push($layouts, $dom_parser->attr($node, 'name'));
		}		
		return $layouts;		
	}
	
	/**
	 * Returns the structure from loaded layout, returns NULL if nothing is loaded
	 * 
	 * @param	Bolean	$format
	 * 		Indicates whatever it will be returned an xml formated string or a plain, non whitespace string.
	 * 
	 * @return	string
	 */
	public function getStructure($format = FALSE, $wrapped = FALSE)
	{
		$parser = new DOMParser();
		
		try
		{
			// Load structure file
			$parser->load($this->path."/structure.xml", TRUE, $format);
			$root = $parser->evaluate("//".self::STRUCT_ROOT)->item(0);
		}
		catch (Exception $ex)
		{
			logger::log("Exception ".$ex, logger::DEBUG);
		}
		if($wrapped) 
		{
			$wrappedContent = $this->getWrapper($parser, $parser->innerHTML($root));
			$codeStr = trim($parser->innerHTML($wrappedContent));
		}
		else
		{
			$codeStr = trim($parser->innerHTML($root));
		}
		return $codeStr;
	}
	
	/**
	 * Returns the model from loaded layout, returns NULL if nothing is loaded
	 * 
	 * @return	string
	 */
	public function getModel()
	{
		return fileManager::get_contents(systemRoot.$this->path."/style.css");
	}
	
	/**
	 * Creates a new layout with the given name.
	 * Return true if successful , false if not
	 * 
	 * @param	string	$name
	 * 		The layout's name
	 * 
	 * @return	boolean
	 */
	public function create($name)
	{
		$saveFlag = FALSE;
	
		//Create index entry
		// Get index root
		$dom_parser = new DOMParser();
		$dom_parser->load($this->folderPath."/index.xml", TRUE);		
		$root = $dom_parser->evaluate("//layouts")->item(0);
		
		//Check if entry exists
		//If not $layout if null, thus FALSE->item(0) => null
		$layout = $dom_parser->evaluate('lt[@name=\''.$name.'\']')->item(0);
		
		if(is_null($layout))
		{
			// Create Entry
			$layoutEntry = $dom_parser->create("lt");
			$dom_parser->attr($layoutEntry, "name", $name);
			$dom_parser->append($root, $layoutEntry);
			
			// Save File
			$saveFlag = $dom_parser->save(systemRoot.$this->folderPath."/", "index.xml", TRUE);				
		}
		else
		{
			$saveFlag = TRUE;		
		}
		
		//If entry exists or entry added successfully, continue
		//else return
		if(!$saveFlag)
			return FALSE;		
		
		//Create folder
		if (!file_exists(systemRoot.$this->folderPath."/".$name.self::FOLDER_EXT));
			$success = folderManager::create(systemRoot.$this->folderPath."/".$name.self::FOLDER_EXT);
		
		if(!$success)
			return FALSE;
		
		//Set layout name		
		$this->name = $name;
		
		//create empty structure
		$status = $this->saveModel();
		//create empty model
		$status = $this->saveStructure();
		
		return $status;
	}
	
	/**
	 * Save the layout's model in file
	 * Return true on success, false elsewhere
	 * 
	 * @param	string	$code
	 * 		The layout's model
	 * 
	 * @return	boolean
	 */
	public function saveModel($code = '')
	{
		// If code is empty, create an empty CSS file
		if ($code == '')
			$code = phpParser::get_comment("Write Your CSS Style Rules Here", $multi = TRUE);
		
		// Clear Code
		$code = phpParser::clearCode($code);
		
		// Save css file
		return fileManager::create(systemRoot.$this->folderPath."/".$this->name.self::FOLDER_EXT."/style.css", $code);
	}
	
	/**
	 * Save the layout's structure in file
	 * 
	 * @param	string	$code
	 * 		The layout's structure
	 * 
	 * @return	boolean
	 */
	public function saveStructure($code = '', $stripWrapper = FALSE)
	{
		$dom_parser = new DOMParser();
		
		$code = phpParser::clearCode($code);
		
		$root = $dom_parser->create(self::STRUCT_ROOT);
		$dom_parser->attr($root , "id", $this->name);
		$dom_parser->append($root);
		$dom_parser->innerHTML($root, $code);
		
		if($stripWrapper)
		{
			$wrapper = $dom_parser->find('content');
			$content = $dom_parser->innerHTML($wrapper);
			//$dom_parser->innerHTML($root, "");
			//$content = preg_replace('<!--(.*?)-->', "", $content);
			
			$dom_parser->innerHTML($root, $content);
			$node = $dom_parser->query('//comment()');
			
 			foreach( $node as $p ) {
				$dom_parser->replace($p, NULL);
			}	
		}
		
		return $dom_parser->save(systemRoot.$this->folderPath."/".$this->name.self::FOLDER_EXT."/", "structure.xml", TRUE);	
	}
	
		
	/**
	 * Deletes the layout given by name. Removes index.xml entry and deletes layout's folder and files
	 * 
	 * @param	string	$name
	 * 		The layout's name
	 * 
	 * @return	boolean
	 */
	public function delete($name)
	{
		//Delete index entry
		// Get index root
		$dom_parser = new DOMParser();
		$dom_parser->load($this->folderPath."/index.xml", TRUE);		
		$root = $dom_parser->evaluate("//layouts")->item(0);
		
		//Check if entry exists
		//If not $layout if null, thus FALSE->item(0) => null
		$layout = $dom_parser->evaluate('lt[@name=\''.$name.'\']')->item(0);
		
		if(!is_null($layout))
		{
			//Delete entry
			$dom_parser->replace($layout, NULL);
			// Save File
			$saveFlag = $dom_parser->save(systemRoot.$this->folderPath."/", "index.xml", TRUE);	
		}
		
		//Delete Directory
		if (file_exists(systemRoot.$this->folderPath."/".$name.self::FOLDER_EXT))
			$status = folderManager::remove_full(systemRoot.$this->folderPath."/".$name.self::FOLDER_EXT);
		
		return $status;
	}
	
	protected abstract function getWrapper($parser = '',  $content = '');
	
}
//#section_end#
?>