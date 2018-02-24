<?php
//#section#[header]
// Namespace
namespace DEV\Apps\components\source;

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
 * @package	Apps
 * @namespace	\components\source
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Apps", "application");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Version", "vcs");

use \DEV\Apps\application;
use \DEV\Prototype\sourceMap;
use \DEV\Version\vcs;

/**
 * Application Source Library
 * 
 * Application Source Library Manager
 * 
 * @version	{empty}
 * @created	April 6, 2014, 1:06 (EEST)
 * @revised	April 6, 2014, 1:06 (EEST)
 */
class sourceLibrary
{
	/**
	 * The application object.
	 * 
	 * @type	application
	 */
	private $app;
	
	/**
	 * The sourceMap object.
	 * 
	 * @type	sourceMap
	 */
	private $sourceMap;
	
	/**
	 * Initializes the object.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	void
	 */
	public function __construct($appID)
	{
		// Init application
		$this->app = new application($appID);
	}
	
	/**
	 * Creates a new source library.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($library)
	{
		// Update map file
		$this->updateMapFile();
		
		// Create index
		$this->loadSourceMap();
		return $this->sourceMap->createLibrary($library);
	}
	
	/**
	 * Gets a list of all libraries.
	 * 
	 * @return	array
	 * 		An array of all libraries in the application source.
	 */
	public function getList()
	{
		$this->loadSourceMap();
		return $this->sourceMap->getLibraryList();
	}
	
	/**
	 * Get all packages in the given library
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @return	array
	 * 		An array of all packages in the library
	 */
	public function getPackageList($library)
	{
		$this->loadSourceMap();
		return $this->sourceMap->getPackageList($library);
	}
	
	/**
	 * Initializes the source map object for getting the source information from the source index.
	 * 
	 * @return	object
	 * 		The sourceMap object.
	 */
	private function loadSourceMap()
	{
		if (empty($this->sourceMap))
		{
			// Init vcs
			$repository = $this->app->getRepository();
			$vcs = new vcs($repository);
			
			// Get source index file path
			$itemID = $this->app->getItemID("sourceIndex");
			$mapFilePath = $vcs->getItemTrunkPath();
			$this->sourceMap = new sourceMap(dirname($mapFilePath), basename($mapFilePath));
		}
		
		return $this->sourceMap;
	}
	
	/**
	 * Updates the source map index file in the vcs.
	 * 
	 * @return	void
	 */
	private function updateMapFile()
	{
		// Init vcs
		$repository = $this->app->getRepository();
		$vcs = new vcs($repository);
		
		// Get source index file path
		$itemID = $this->app->getItemID("sourceIndex");
		$vcs->updateItem($itemID, TRUE);
	}
}
//#section_end#
?>