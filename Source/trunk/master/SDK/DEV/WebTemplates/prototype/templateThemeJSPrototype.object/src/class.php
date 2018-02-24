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
importer::import("DEV", "Tools", "codeParser");
importer::import("DEV", "WebTemplates", "prototype/templatePrototype");

use \API\Resources\filesystem\fileManager;
use \DEV\Tools\codeParser;
use \DEV\WebTemplates\prototype\templatePrototype;

/**
 * Template Theme JS Prototype
 * 
 * Manages a template javascript object.
 * 
 * @version	0.1-2
 * @created	September 17, 2015, 19:57 (EEST)
 * @updated	September 18, 2015, 1:55 (EEST)
 */
class templateThemeJSPrototype
{
	/**
	 * The template theme prototype object.
	 * 
	 * @type	string
	 */
	private $templateTheme;
	
	/**
	 * The script file path.
	 * 
	 * @type	string
	 */
	private $jsFilePath;
	
	/**
	 * Create a new instance of the theme js prototype.
	 * 
	 * @param	string	$indexFilePath
	 * 		The template's index file path.
	 * 
	 * @param	string	$themeFolderPath
	 * 		The theme folder path.
	 * 
	 * @param	string	$jsFilePath
	 * 		The script file path.
	 * 		Leave empty for new scripts.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($indexFilePath, $themeFolderPath, $jsFilePath = "")
	{
		// Initialize
		$this->templateTheme = new templateThemePrototype($indexFilePath, $themeFolderPath);
		$this->jsFilePath = $jsFilePath;
	}
	
	/**
	 * Create a new javascript file.
	 * 
	 * @param	string	$jsFilePath
	 * 		The script file path.
	 * 
	 * @param	string	$name
	 * 		The javascript name.
	 * 		This is needed for indexing.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($jsFilePath, $name)
	{
		// Check that name is not empty and valid
		if (empty($name))
			return FALSE;
			
		// Create object index
		$status = $this->templateTheme->addJS($name);
		if (!$status)
			return FALSE;
		
		// Create theme structure
		$this->jsFilePath = $jsFilePath;
		fileManager::create($this->jsFilePath, "");
		
		return TRUE;
	}
	
	/**
	 * Update the script's js.
	 * 
	 * @param	string	$js
	 * 		The script's js.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($js = "")
	{
		// Update File
		$js = codeParser::clear($js);
		return fileManager::put($this->jsFilePath, $js);
	}
	
	/**
	 * Get the script's js.
	 * 
	 * @return	string
	 * 		The script's js.
	 */
	public function get()
	{
		// Get html content
		return fileManager::get($this->jsFilePath);
	}
	
	/**
	 * Remove the script from the theme.
	 * 
	 * @param	string	$name
	 * 		The script's name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($name)
	{
		// Remove object index
		$status = $this->templateTheme->removeJS($name);
		if ($status)
			fileManager::remove($this->jsFilePath);
		
		return $status;
	}
}
//#section_end#
?>