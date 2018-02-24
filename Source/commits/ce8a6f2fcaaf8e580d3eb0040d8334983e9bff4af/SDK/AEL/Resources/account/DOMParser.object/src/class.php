<?php
//#section#[header]
// Namespace
namespace AEL\Resources\account;

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
 * @namespace	\account
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
 * Class for parsing xml files in the account's application folder.
 * 
 * @version	0.1-1
 * @created	December 5, 2014, 16:55 (EET)
 * @revised	December 5, 2014, 16:55 (EET)
 */
class DOMParser extends APIDOMParser
{
	/**
	 * Loads an existing xml document.
	 * 
	 * @param	string	$path
	 * 		The document relative path to the account's application folder.
	 * 
	 * @param	boolean	$preserve
	 * 		Indicates whether the parser will preserve whitespaces during load.
	 * 
	 * @return	DOMDocument
	 * 		The document loaded.
	 */
	public function load($path, $preserve = FALSE)
	{
		// Get root folder
		$rootFolder = systemRoot.application::getAccountApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Load parser
		return parent::load($rootFolder."/".$path, $rootRelative = FALSe, $preserve);
	}
	
	/**
	 * Saves the file in the given filepath.
	 * 
	 * @param	string	$path
	 * 		The relative path in the account's application folder.
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
		$rootFolder = systemRoot.application::getAccountApplicationPath();
		if (empty($rootFolder))
			return FALSE;
		
		// DOMParser save
		return parent::save($rootFolder."/".$path, $fileName, $format);
	}
}
//#section_end#
?>