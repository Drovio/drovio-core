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

importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("DEV", "Tools", "parsers/phpParser");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Tools", "parsers/scssParser");
importer::import("DEV", "WebTemplates", "prototype/templatePrototype");

use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Tools\parsers\phpParser;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\scssParser;
use \DEV\WebTemplates\prototype\templatePrototype;

/**
 * Web Templage Page Prototype
 * 
 * Manages the page prototype object given the page path (inside a template, managed by the template).
 * 
 * @version	1.0-1
 * @created	September 17, 2015, 19:03 (EEST)
 * @updated	September 17, 2015, 19:57 (EEST)
 */
class templatePagePrototype
{
	/**
	 * The page file type extension.
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "page";
	
	/**
	 * The template prototype object.
	 * 
	 * @type	templatePrototype
	 */
	private $template;
	
	/**
	 * The page folder path.
	 * 
	 * @type	string
	 */
	private $pageFolderPath;

	/**
	 * Create a template page prototype instance.
	 * 
	 * @param	string	$indexFilePath
	 * 		The template's index file path.
	 * 
	 * @param	string	$pageFolderPath
	 * 		The page folder path.
	 * 		Leave it empty for new pages.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($indexFilePath, $pageFolderPath = "")
	{
		// Initialize
		$this->template = new templatePrototype($indexFilePath);
		$this->pageFolderPath = $pageFolderPath;
	}
	
	/**
	 * Create a new template page.
	 * 
	 * @param	string	$pageFolderPath
	 * 		The page folder path, as given from the template manager.
	 * 
	 * @param	string	$name
	 * 		The page name.
	 * 		This is needed for indexing.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if there is a page with the same name.
	 */
	public function create($pageFolderPath, $name)
	{
		// Check that name is not empty and valid
		if (empty($name))
			return FALSE;
			
		// Create object index
		$status = $this->template->addObjectIndex(templatePrototype::PAGES_FOLDER, "page", $name);
		if (!$status)
			return FALSE;
		
		// Create page structure
		$this->pageFolderPath = $pageFolderPath;
		$this->createStructure();
		
		return TRUE;
	}
	
	/**
	 * Update page's html.
	 * 
	 * @param	string	$html
	 * 		The page's html content.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateHTML($html = "")
	{
		// Update File
		$html = phpParser::clear($html);
		return fileManager::put($this->pageFolderPath."/page.html", $html);
	}
	
	/**
	 * Get the page html content.
	 * 
	 * @return	string
	 * 		The page html content.
	 */
	public function getHTML()
	{
		// Get html content
		return fileManager::get($this->pageFolderPath."/page.html");
	}
	
	/**
	 * Update page's css.
	 * 
	 * @param	string	$code
	 * 		The page's css content.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateCSS($code = "")
	{
		// Update scss and css
		$scss = cssParser::clear($code);
		$status1 = fileManager::put($this->pageFolderPath."/style.scss", $scss);
		
		// Compile scss to css
		$css = scssParser::toCSS($scss);
		$status2 = fileManager::put($this->pageFolderPath."/style.css", $css);
		
		return $status1 && $status2;
	}
	
	/**
	 * Get page's css content.
	 * 
	 * @param	boolean	$normalCss
	 * 		Get normal css or scss content.
	 * 
	 * @return	string
	 * 		The page css.
	 */
	public function getCSS($normalCss = FALSE)
	{
		// Get scss
		$scss = fileManager::get($this->pageFolderPath."/style.scss");
		if (empty($scss) || $normalCss)
		{
			// If the scss is empty or the user requested the css specificly
			return fileManager::get($this->pageFolderPath."/style.css");
		}
		
		// Return scss
		return $scss;
	}
	
	/**
	 * Remove the page from the template.
	 * 
	 * @param	string	$name
	 * 		The page name for indexing.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($name)
	{
		// Remove page from index
		$status = $this->template->removeObjectIndex("page", $name);
		folderManager::remove($this->pageFolderPath);
		
		return $status;
	}
	
	/**
	 * Get the name of the page smart object folder.
	 * 
	 * @param	string	$name
	 * 		The page name.
	 * 
	 * @return	string
	 * 		The page folder name
	 */
	public static function getPageFolder($name)
	{
		return $name.".".self::FILE_TYPE;
	}
	
	/**
	 * Create the page structure with the necessary files.
	 * 
	 * @return	void
	 */
	private function createStructure()
	{
		// Create page folder
		folderManager::create($this->pageFolderPath);
		
		// Create files
		fileManager::create($this->pageFolderPath."/page.html", "");
		fileManager::create($this->pageFolderPath."/style.css", "");
	}
}
//#section_end#
?>