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
 * @package	Websites
 * @namespace	\source
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Websites", "website");
importer::import("DEV", "Version", "vcs");

use \DEV\Prototype\sourceMap;
use \DEV\Websites\website;
use \DEV\Version\vcs;

/**
 * Website Source Library Manager
 * 
 * Handles all operations with website's source libraries.
 * 
 * @version	0.1-1
 * @created	September 19, 2014, 13:45 (EEST)
 * @revised	September 19, 2014, 13:45 (EEST)
 */
class srcLibrary
{
	/**
	 * The website object.
	 * 
	 * @type	website
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
	
	/**
	 * Initializes the object.
	 * 
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @return	void
	 */
	public function __construct($websiteID)
	{
		// Init application
		$this->website = new website($websiteID);
		$this->vcs = new vcs($websiteID);
		
		// Load source map
		$this->loadSourceMap();
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
		
		// Library name must be capitals
		$library = strtoupper($library);
		
		// Create index
		$this->loadSourceMap();
		return $this->sourceMap->createLibrary($library);
	}
	
	/**
	 * Remove a library from source.
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
	 * Gets a list of all libraries in the source.
	 * 
	 * @return	array
	 * 		An array of all libraries.
	 */
	public function getList()
	{
		$this->loadSourceMap();
		return $this->sourceMap->getLibraryList();
	}
	
	/**
	 * Get all packages in the given library.
	 * 
	 * @param	string	$library
	 * 		An array of all packages in the library
	 * 
	 * @return	array
	 * 		An array of all packages in the library.
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
		// Get vcs item id
		$itemID = $this->website->getItemID("sourceIndex");
		
		// Update in vcs (with force_commit)
		$this->vcs->updateItem($itemID, TRUE);
	}
}
//#section_end#
?>