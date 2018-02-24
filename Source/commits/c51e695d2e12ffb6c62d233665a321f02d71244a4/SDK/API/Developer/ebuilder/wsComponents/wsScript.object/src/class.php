<?php
//#section#[header]
// Namespace
namespace API\Developer\ebuilder\wsComponents;

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
 * @namespace	\ebuilder\wsComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "versionControl::vcsManager");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Developer\versionControl\vcsManager;
use \API\Developer\content\document\parsers\phpParser;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * Website Script
 * 
 * Responsible for the script of a website
 * 
 * @version	{empty}
 * @created	July 3, 2013, 11:47 (EEST)
 * @revised	July 3, 2013, 11:47 (EEST)
 */
class wsScript extends vcsManager
{
	/**
	 * The object's file type.
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "js";
	
	/**
	 * The object's developer path.
	 * 
	 * @type	string
	 */
	private $devPath;
	
	/**
	 * Constructor Method. Initializes the script object.
	 * 
	 * @param	string	$devPath
	 * 		The website inner path for the object.
	 * 
	 * @param	string	$name
	 * 		The script object's name.
	 * 
	 * @return	void
	 */
	public function __construct($devPath, $name = "")
	{
		// Put your constructor method code here.
		$this->devPath = $devPath;
		if (!empty($name))
			$this->VCS_initialize($this->devPath, "", $name, self::FILE_TYPE);
	}
	
	/**
	 * Creates a new object.
	 * 
	 * @param	string	$scriptName
	 * 		The script's name.
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	public function create($scriptName)
	{
		// Initialize VCS
		$this->VCS_initialize($this->devPath, "", $scriptName, self::FILE_TYPE);

		// Create script
		$this->VCS_createObject();
		$this->update();
		
		return TRUE;
	}
	
	/**
	 * Updates the script source code.
	 * 
	 * @param	string	$code
	 * 		The new source code.
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	public function update($code = "")
	{
		// Get Object Folder Path
		$sourceFile = $this->vcsTrunk->getPath($this->getWorkingBranch($this->name));
		
		// If code is empty, create an empty Javascript file
		if (empty($code))
			$code = phpParser::comment("Write Your Javascript Code Here", $multi = TRUE);
		
		// Clear Code
		$code = phpParser::clearCode($code);
		
		// Save javascript file
		return fileManager::create($sourceFile, $code);
	}
	
	/**
	 * Returns the script source code.
	 * 
	 * @return	string
	 * 		The returned source code.
	 */
	public function getSourceCode()
	{
		// Get Object Folder Path
		$sourceFile = $this->vcsTrunk->getPath($this->getWorkingBranch($this->name));
		
		// Get code
		return fileManager::get($sourceFile);
	}
	
	/**
	 * Commits the object to the version control.
	 * 
	 * @param	string	$description
	 * 		The commit description.
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	public function commit($description)
	{
		return $this->vcsBranch->commit($this->getWorkingBranch(), $description);
	}
}
//#section_end#
?>