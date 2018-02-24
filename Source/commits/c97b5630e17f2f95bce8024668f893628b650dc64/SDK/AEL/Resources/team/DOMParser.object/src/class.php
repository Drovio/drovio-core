<?php
//#section#[header]
// Namespace
namespace AEL\Resources\team;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	AEL
 * @package	Resources
 * @namespace	\team
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Model", "apps/application");
importer::import("API", "Resources", "DOMParser");

use \API\Model\apps\application;
use \API\Resources\DOMParser as APIDOMParser;

/**
 * DOMParser for Applications
 * 
 * Class for parsing xml files in the application folder.
 * 
 * @version	1.0-1
 * @created	December 5, 2014, 16:52 (EET)
 * @revised	December 8, 2014, 14:22 (EET)
 */
class DOMParser extends APIDOMParser
{
	/**
	 * Loads an existing xml document.
	 * 
	 * @param	string	$path
	 * 		The document relative path to the team's application folder.
	 * 
	 * @param	boolean	$preserve
	 * 		Indicates whether the parser will preserve whitespaces during load.
	 * 
	 * @return	DOMDocument
	 * 		The document loaded.
	 * 
	 * @throws	Exception
	 */
	public function load($path, $preserve = FALSE)
	{
		// Get root folder
		$rootFolder = systemRoot.application::getTeamApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Load parser
		return parent::load($rootFolder."/".$path, $rootRelative = FALSe, $preserve);
	}
	
	/**
	 * Saves the file in the given filepath.
	 * 
	 * @param	string	$path
	 * 		The relative path in the team's application folder.
	 * 
	 * @param	string	$fileName
	 * 		The filename.
	 * 		Leave empty if the file name is included in the path.
	 * 
	 * @param	boolean	$format
	 * 		Indicator whether the parser will save formated xml or unformatted.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	public function save($path, $fileName = "", $format = FALSE)
	{
		// Get root folder
		$rootFolder = systemRoot.application::getTeamApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// DOMParser save
		return parent::save($rootFolder."/".$path, $fileName, $format);
	}
	
	/**
	 * Updates the file loaded before by the load() function.
	 * 
	 * @param	boolean	$format
	 * 		Indicator whether the parser will save formated xml or not.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	public function update($format = FALSE)
	{
		// Check filepath
		if (empty($this->filePath))
			return FALSE;
		
		// Get root folder
		$rootFolder = systemRoot.application::getTeamApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Clean filepath
		$filepath = str_replace($rootFolder, "", $this->filePath);
		
		// Save
		return $this->save($filepath, "", $format);
	}
}
//#section_end#
?>