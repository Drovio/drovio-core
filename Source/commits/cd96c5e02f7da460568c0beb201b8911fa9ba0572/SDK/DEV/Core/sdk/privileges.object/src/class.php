<?php
//#section#[header]
// Namespace
namespace DEV\Core\sdk;

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
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Projects", "project");

use \API\Resources\filesystem\fileManager;
use \API\Resources\DOMParser;
use \DEV\Projects\project;

/**
 * Open SDK Packages Manager
 * 
 * Handles all SDK packages that are open to developers.
 * 
 * @version	0.1-1
 * @created	October 24, 2014, 15:17 (EEST)
 * @revised	October 24, 2014, 15:17 (EEST)
 */
class privileges
{
	/**
	 * The DOMParser object that edits the privileges file.
	 * 
	 * @type	DOMParser
	 */
	private $xmlParser;
	
	/**
	 * Initialize the class and load the privileges file.
	 * The file will be created, if accidentally erased.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Get project resources folder
		$project = new project(1);
		$resourcesFolder = $project->getResourcesFolder();
		$privilegesFile = $resourcesFolder."/resources/privileges.xml";
		
		// Initialize parser and load privileges file
		$this->xmlParser = new DOMParser();
		try
		{
			$this->xmlParser->load($privilegesFile);
		}
		catch (Exception $ex)
		{
			$root = $this->xmlParser->create("OpenAPI");
			$this->xmlParser->append($root);
			
			// Create file
			fileManager::create(systemRoot.$privilegesFile, "", TRUE);
			$this->xmlParser->save(systemRoot.$privilegesFile, "", TRUE);
		}
	}
	
	/**
	 * Get all Open SDK Packages by library name.
	 * 
	 * @return	array
	 * 		An array of all packages as nested arrays for every library.
	 */
	public function getPackages()
	{
		// Set open packages
		$packageList = array();
		$parser = $this->xmlParser;

		// Get packages
		$packages = $parser->evaluate("//package");
		foreach ($packages as $package)
		{
			// Set names
			$libraryName = $parser->attr($package->parentNode, "name");
			$packageName = $parser->attr($package, "name");
			
			// Append to array
			$packageList[$libraryName][] = $packageName;
		}
		
		return $packageList;
	}
	
	/**
	 * Set the Open SDK packages.
	 * 
	 * @param	array	$packages
	 * 		An array of all packages as nested arrays for every library.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setPackages($packages = array())
	{
		$parser = $this->xmlParser;
		
		// Get root
		$root = $parser->evaluate("/OpenAPI")->item(0);
		$parser->innerHTML($root, "");
		
		foreach ($packages as $library => $packageList)
		{
			// Get library (or create if not exist)
			$libElement = $parser->evaluate("//library[name='".$library."']")->item(0);
			if (empty($libElement))
			{
				$libElement = $parser->create("library");
				$parser->attr($libElement, "name", $library);
				$parser->append($root, $libElement);
			}
			
			// Add packages
			foreach ($packageList as $packageName)
			{
				$pkgElement = $parser->create("package");
				$parser->attr($pkgElement, "name", $packageName);
				$parser->append($libElement, $pkgElement);
			}
		}
		
		return $parser->update(TRUE);
	}
}
//#section_end#
?>