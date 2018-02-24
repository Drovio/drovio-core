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
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Tools", "parsers/scssParser");
importer::import("DEV", "WebTemplates", "prototype/templatePrototype");

use \API\Resources\filesystem\fileManager;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\scssParser;
use \DEV\WebTemplates\prototype\templatePrototype;

/**
 * Template Theme CSS Prototype
 * 
 * Manages a template style object.
 * 
 * @version	0.1-1
 * @created	September 17, 2015, 19:56 (EEST)
 * @updated	September 17, 2015, 19:56 (EEST)
 */
class templateThemeCSSPrototype
{
	/**
	 * The style file type extension.
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "style";
	
	/**
	 * The template theme prototype object.
	 * 
	 * @type	templateThemePrototype
	 */
	private $templateTheme;
	
	/**
	 * The css folder path.
	 * 
	 * @type	string
	 */
	private $cssFolderPath;
	
	/**
	 * Create a new instance of the theme css prototype.
	 * 
	 * @param	string	$indexFilePath
	 * 		The template's index file path.
	 * 
	 * @param	string	$themeFolderPath
	 * 		The theme folder path.
	 * 
	 * @param	string	$cssFolderPath
	 * 		The style folder path.
	 * 		Leave empty for new styles.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($indexFilePath, $themeFolderPath, $cssFolderPath = "")
	{
		// Initialize
		$this->templateTheme = new templateThemePrototype($indexFilePath, $themeFolderPath);
		$this->cssFolderPath = $cssFolderPath;
	}
	
	/**
	 * Create a new css object.
	 * 
	 * @param	string	$cssFolderPath
	 * 		The style folder path.
	 * 
	 * @param	string	$name
	 * 		The css name.
	 * 		This is needed for indexing.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($cssFolderPath, $name)
	{
		// Check that name is not empty and valid
		if (empty($name))
			return FALSE;
			
		// Create object index
		$status = $this->templateTheme->addCSS($name);
		if (!$status)
			return FALSE;
		
		// Create theme structure
		$this->cssFolderPath = $cssFolderPath;
		fileManager::create($this->cssFolderPath."/style.scss", "");
		fileManager::create($this->cssFolderPath."/style.css", "");
		
		return TRUE;
	}
	
	/**
	 * Update the style's css.
	 * 
	 * @param	string	$code
	 * 		The style css.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($code = "")
	{
		// Update scss and css
		$scss = cssParser::clear($code);
		$status1 = fileManager::create($this->cssFolderPath."/style.scss", $scss);
		
		// Compile scss to css
		$css = scssParser::toCSS($scss);
		$status2 = fileManager::create($this->cssFolderPath."/style.css", $css);
		
		return $status1 && $status2;
	}
	
	/**
	 * Get the style's css.
	 * 
	 * @param	boolean	$normalCss
	 * 		Get normal css or scss content.
	 * 
	 * @return	string
	 * 		The style's css.
	 */
	public function get($normalCss = FALSE)
	{
		// Get scss
		$scss = fileManager::get($this->cssFolderPath."/style.scss");
		if (empty($scss) || $normalCss)
		{
			// If the scss is empty or the user requested the css specificly
			return fileManager::get($this->cssFolderPath."/style.css");
		}
		
		// Return scss
		return $scss;
	}
	
	/**
	 * Remove the current style from the theme.
	 * 
	 * @param	string	$name
	 * 		The style's name to remove from indexing.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($name)
	{
		// Remove object index
		$status = $this->templateTheme->removeCSS($name);
		if ($status)
		{
			fileManager::remove($this->cssFolderPath."/style.scss");
			fileManager::remove($this->cssFolderPath."/style.css");
		}
		
		return $status;
	}
	
	/**
	 * Get the name of the style smart object folder.
	 * 
	 * @param	string	$name
	 * 		The style name.
	 * 
	 * @return	string
	 * 		The style folder name.
	 */
	public static function getStyleFolder($name)
	{
		return $name.".".self::FILE_TYPE;
	}
}
//#section_end#
?>