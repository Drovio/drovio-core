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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Model", "apps/application");
importer::import("API", "Profile", "account");
importer::import("API", "Resources", "DOMParser");

use \API\Model\apps\application;
use \API\Profile\account;
use \API\Resources\DOMParser as APIDOMParser;

/**
 * DOMParser for Applications
 * 
 * Class for parsing xml files in the account's application folder.
 * 
 * NOTE: For each call it checks if there is an active application. If not, returns false every time.
 * All paths are relative to the application root folder or the application shared folder root.
 * The shared folder is one for all applications, so be careful of what you are storing there.
 * 
 * @version	2.0-1
 * @created	December 5, 2014, 16:55 (EET)
 * @updated	January 13, 2015, 12:22 (EET)
 */
class DOMParser extends APIDOMParser
{
	/**
	 * Shared or private application data.
	 * 
	 * @type	boolean
	 */
	private $shared;
	
	/**
	 * Create a new instance of a DOMParser
	 * 
	 * @param	boolean	$shared
	 * 		If set to true, the DOMParser will have access to the shared application data folder.
	 * 		It is FALSE by default.
	 * 
	 * @return	void
	 */
	public function __construct($shared = FALSE)
	{
		$this->shared = $shared;
		parent::__construct();
	}
	
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
		$rootFolder = systemRoot.$this->getPath();
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
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// DOMParser save
		return parent::save($rootFolder."/".$path, $fileName, $format);
	}
	
	/**
	 * Updates the file loaded before by the load() function.
	 * 
	 * @param	boolean	$format
	 * 		Indicator whether the parser will save formatted xml or not.
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
		$rootFolder = systemRoot.$this->getPath();
		if (empty($rootFolder))
			return FALSE;
		
		// Clean filepath
		$filepath = str_replace($rootFolder, "", $this->filePath);
		
		// Save
		return $this->save($filepath, "", $format);
	}
	
	/**
	 * Get the root folder for the object.
	 * 
	 * @return	string
	 * 		The root folder, according to shared variable.
	 */
	private function getPath()
	{
		if ($this->shared)
			return account::getServicesFolder("/SharedAppData/");
		else
			return application::getAccountApplicationPath();
	}
}
//#section_end#
?>