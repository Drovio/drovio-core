<?php
//#section#[header]
// Namespace
namespace API\Developer\ebuilder\extComponents;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\ebuilder\extComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

//importer::import("API", "Developer", "versionControl::vcsManager");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "content::document::coders::phpCoder");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");

//use \API\Developer\versionControl\vcsManager;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\coders\phpCoder;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	July 1, 2013, 18:44 (EEST)
 * @revised	July 11, 2013, 13:54 (EEST)
 */
class extScript //extends vcsManager
{	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const FILE_TYPE = "js";
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $devPath;
			
	/**
	 * {description}
	 * 
	 * @param	{type}	$devPath
	 * 		{description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
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
	 * {description}
	 * 
	 * @param	{type}	$scriptName
	 * 		{description}
	 * 
	 * @return	void
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
	 * {description}
	 * 
	 * @param	{type}	$code
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function update($code = "")
	{
		// Get Object Folder Path
		$sourceFile = $this->vcsTrunk->getPath($this->getWorkingBranch($this->name));
		
		// If code is empty, create an empty Javascript file
		if ($code == "")
			$code = phpParser::comment("Write Your Javascript Code Here", $multi = TRUE);
		
		// Clear Code
		$code = phpParser::clearCode($code);
		
		// Save javascript file
		return fileManager::create($sourceFile, $code);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getSourceCode()
	{
		// Get Object Folder Path
		$sourceFile = $this->vcsTrunk->getPath($this->getWorkingBranch($this->name));
		/*
		// If code is empty, create an empty Javascript file
		if ($code == "")
			$code = phpParser::comment("Write Your Javascript Code Here", $multi = TRUE);
		
		// Clear Code
		$code = phpParser::clearCode($code);
		*/
		// Save javascript file
		return fileManager::get($sourceFile);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$description
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function commit($description)
	{
		return $this->vcsBranch->commit($this->getWorkingBranch(), $description);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function delete()
	{	
		// Remove Repository
		return $this->VCS_removeObject();
	}
	
	public function export($exportPath)
	{	
		// Get Head Object Path
		$headPath = $this->vcsBranch->getHeadPath();
		if (is_dir($headPath."/"))
			return FALSE;
		
		$sourceFile = $headPath;
		$phpCheck = phpParser::check_syntax($sourceFile);
		if (!$phpCheck)
			return $phpCheck;
		
		// Export Object Path
		$sourceCodeObjectPath = $exportPath."/".$this->name.".".SELF::FILE_TYPE;

		// Export Source Code
		$finalCode = fileManager::get_contents($sourceFile);
		fileManager::create($sourceCodeObjectPath, $finalCode);
		
		return TRUE;
	}
}
//#section_end#
?>