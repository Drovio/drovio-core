<?php
//#section#[header]
// Namespace
namespace DEV\WebExtensions\components\source;

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
 * @package	WebExtensions
 * @namespace	\components\source
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "WebExtensions", "extension");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Version", "vcs");

use \DEV\WebExtensions\extension;
use \DEV\Prototype\sourceMap;
use \DEV\Version\vcs;

/**
 * Extension Source Library
 * 
 * Extension Source Library Manager
 * 
 * @version	{empty}
 * @created	May 22, 2014, 18:52 (EEST)
 * @revised	May 22, 2014, 18:52 (EEST)
 */
class sourceLibrary
{
	/**
	 * The extension object.
	 * 
	 * @type	extension
	 */
	private $ext;
	
	/**
	 * The sourceMap object.
	 * 
	 * @type	sourceMap
	 */
	private $sourceMap;
	
	/**
	 * Initializes the object.
	 * 
	 * @param	integer	$extID
	 * 		The extension id.
	 * 
	 * @return	void
	 */
	public function __construct($extID)
	{
		// Init application
		$this->ext = new extension($extID);
	}
	
	/**
	 * Create a new source library in the extension.
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
	 * 		An array of all libraries in the extension source.
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
	 * 		An array of all packages in the extension's library.
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
			$vcs = new vcs($this->ext->getID());
			
			// Get source index file path
			$itemID = $this->ext->getItemID("sourceIndex");
			$mapFilePath = $vcs->getItemTrunkPath($itemID);
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
		$vcs = new vcs($this->ext->getID());
			
		// Get source index file path
		$itemID = $this->ext->getItemID("sourceIndex");
		$vcs->updateItem($itemID, TRUE);
	}
}
//#section_end#
?>