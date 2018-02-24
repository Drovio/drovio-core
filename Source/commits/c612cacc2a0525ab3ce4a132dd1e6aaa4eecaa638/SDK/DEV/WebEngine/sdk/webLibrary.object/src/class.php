<?php
//#section#[header]
// Namespace
namespace DEV\WebEngine\sdk;

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
 * @package	WebEngine
 * @namespace	\sdk
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Tools", "parsers::jsParser");

use \ESS\Protocol\client\BootLoader;
use \API\Resources\filesystem\fileManager;
use \DEV\Prototype\sourceMap;
use \DEV\Projects\project;
use \DEV\Version\vcs;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;

importer::import("DEV", "Temp", "vcs2");
use \DEV\Temp\vcs2;

/**
 * Web SDK Library Manager
 * 
 * Handles all operations with web SDK libraries.
 * 
 * @version	{empty}
 * @created	April 4, 2014, 11:23 (EEST)
 * @revised	April 14, 2014, 17:00 (EEST)
 */
class webLibrary
{
	/**
	 * The SDK release path.
	 * 
	 * @type	string
	 */
	const RELEASE_PATH = "/System/Library/Web/SDK/";
	
	/**
	 * The sourceMap object.
	 * 
	 * @type	sourceMap
	 */
	private $sourceMap;
	
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
	 * Get the map file item id for the vcs.
	 * 
	 * @return	string
	 * 		The hash item id.
	 */
	public static function getMapfileID()
	{
		return "m".md5("map__map.xml");
	}
	
	/**
	 * Loads the SDK's source map file.
	 * 
	 * @return	sourceMap
	 * 		The sourceMap object with the map file loaded.
	 */
	private function loadSourceMap()
	{
		if (empty($this->sourceMap))
		{
			// Get map file trunk path
			$vcs = new vcs2(3);
			$itemID = $this->getMapfileID();
			$mapFilePath = $vcs->getItemTrunkPath($itemID);
			$this->sourceMap = new sourceMap(dirname($mapFilePath), basename($mapFilePath));
		}
		
		return $this->sourceMap;
	}
	
	/**
	 * Updates the source map index file.
	 * 
	 * @return	void
	 */
	private function updateMapFile()
	{
		// Update map file
		$vcs = new vcs2(3);
		$itemID = $this->getMapfileID();
		$vcs->updateItem($itemID, TRUE);
	}
}
//#section_end#
?>