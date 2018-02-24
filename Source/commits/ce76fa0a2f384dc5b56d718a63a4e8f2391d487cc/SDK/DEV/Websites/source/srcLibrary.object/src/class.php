<?php
//#section#[header]
// Namespace
namespace DEV\Websites\source;

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
 * @package	Core
 * @namespace	\sdk
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Prototype", "classObject");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Websites", "website");
importer::import("DEV", "Version", "vcs");

use \API\Resources\filesystem\fileManager;
use \DEV\Prototype\classObject;
use \DEV\Prototype\sourceMap;
use \DEV\Websites\website;
use \DEV\Version\vcs;

/**
 * Core SDK Library Manager
 * 
 * Handles all operations with core SDK libraries.
 * 
 * @version	{empty}
 * @created	April 1, 2014, 12:53 (EEST)
 * @revised	May 27, 2014, 18:32 (EEST)
 */
class srcLibrary
{
	/**
	 * The application object.
	 * 
	 * @type	application
	 */
	private $website;
	
	/**
	 * The sourceMap object.
	 * 
	 * @type	sourceMap
	 */
	private $sourceMap;
	
	/**
	 * The source vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	public function __construct($websiteID, $library = "")
	{
		// Init application
		$this->website = new website($websiteID);
		$this->vcs = new vcs($websiteID);
		
		// Load source map
		$this->loadSourceMap();
	}
	
	/**
	 * Creates a new SDK library.
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
		
		// Library name must be capitals
		$library = strtoupper($library);
		
		// Create index
		$this->loadSourceMap();
		return $this->sourceMap->createLibrary($library);
	}
	
	/**
	 * Remove a library from the core SDK.
	 * The library must be empty.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($library)
	{
		// Library name must be capitals
		$library = strtoupper($library);
		
		// Create index
		$this->loadSourceMap();
		$status = FALSE;
		try
		{
			$status = $this->sourceMap->deleteLibrary($library);
		}
		catch (Exception $ex)
		{
			$status = FALSE;
		}
		
		// Update vcs map file
		if ($status)
			$this->updateMapFile();
		
		return $status;
	}
	
	/**
	 * Gets a list of all libraries.
	 * 
	 * @return	array
	 * 		An array of all libraries in the SDK.
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
			// Get map file trunk path
			$itemID = $this->website->getItemID("sourceIndex");
			$mapFilePath = $this->vcs->getItemTrunkPath($itemID);
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
		// Get map item id
		$itemID = $this->website->getItemID("sourceIndex");
		
		// Create map file if not exist
		
		
		// Update map file
		$this->vcs->updateItem($itemID, TRUE);
	}
}
//#section_end#
?>