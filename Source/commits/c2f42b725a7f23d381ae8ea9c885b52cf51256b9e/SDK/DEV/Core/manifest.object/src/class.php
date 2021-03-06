<?php
//#section#[header]
// Namespace
namespace DEV\Core;

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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Core", "coreProject");

use \API\Resources\filesystem\fileManager;
use \API\Resources\DOMParser;
use \DEV\Core\coreProject;

/**
 * Platform SDK Manifest Manager
 * 
 * Manages all manifests that are going to be used by the application developers.
 * Foreach manifest, a list of packages are assigned. The application will have access to those packages through the manifest rules.
 * The application user will be notified of the manifest use as "Application Requests Access".
 * 
 * @version	0.1-1
 * @created	February 17, 2015, 14:36 (EET)
 * @updated	February 17, 2015, 14:36 (EET)
 */
class manifest
{
	/**
	 * The DOMParser object that edits the manifest file.
	 * 
	 * @type	DOMParser
	 */
	private $xmlParser;
	
	/**
	 * Initialize the class and load the manifest file.
	 * The file will be created from scratch, if accidentally erased.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Get project resources folder
		$project = new coreProject();
		$resourcesFolder = $project->getResourcesFolder();
		$manifestsFile = $resourcesFolder."/resources/manifests.xml";
		
		// Initialize parser and load privileges file
		$this->xmlParser = new DOMParser();
		try
		{
			$this->xmlParser->load($manifestsFile);
		}
		catch (Exception $ex)
		{
			$root = $this->xmlParser->create("Manifests");
			$this->xmlParser->append($root);
			
			// Create file
			fileManager::create(systemRoot.$manifestsFile, "", TRUE);
			$this->xmlParser->save(systemRoot.$manifestsFile, "", TRUE);
		}
	}
	
	/**
	 * Get all manifests by id.
	 * 
	 * @return	array
	 * 		An array of all manifests by id.
	 * 		Each manifest includes info (name and enabled status) and a list of all sdk packages grouped by library.
	 */
	public function getManifests()
	{
		// Set open packages
		$manifestList = array();
		$parser = $this->xmlParser;

		// Get manifests
		$manifests = $parser->evaluate("//manifest");
		foreach ($manifests as $manifest)
		{
			// Create manifest array
			$manifest = array();
			
			// Get manifest id
			$mID = $parser->attr($manifest, "id");
			
			// Get manifest information
			$manifest['info']['name'] = $parser->attr($manifest, "name");
			$manifest['info']['enabled'] = $parser->attr($manifest, "enabled");
			
			// Get manifest packages
			$packages = $parser->evaluate("/package", $manifest);
			$packageList = array();
			foreach ($packages as $package)
			{
				// Set names
				$libraryName = $parser->attr($package->parentNode, "name");
				$packageName = $parser->attr($package, "name");
				
				// Append to array
				$packageList[$libraryName][] = $packageName;
			}
			
			// Add package list
			$manifest['list'] = $packageList;
			
			// Add to list
			$manifestList[$mID] = $manifest;
		}
		
		// Return manifest list
		return $manifestList;
	}
	
	/**
	 * Create new manifest entry.
	 * 
	 * @param	string	$name
	 * 		The manifest name.
	 * 		It must be unique.
	 * 
	 * @param	array	$packages
	 * 		An array of all packages grouped by library.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($name, $packages = array())
	{
		// Initialize parser
		$parser = $this->xmlParser;
		$root = $parser->evaluate("/Manifests")->item(0);
		
		// Check if manifest with the same name already exists
		$manifest = $parser->evaluate("//manifest[@name='".$name."']")->item(0);
		if (!empty($manifest))
			return FALSE;
			
		// Create new manifest
		$manifest = $parser->create("manifest");
		$parser->attr($manifest, "name", $name);
		$parser->append($root, $manifest);
		
		// Add package list container
		$plist = $parser->create("plist");
		$parser->append($manifest, $plist);
		
		// Update file
		$parser->update(TRUE);
		
		// Set manifest packages
		return $this->update($name, $packages);
	}
	
	/**
	 * Set the enable status of the manifest.
	 * 
	 * @param	string	$name
	 * 		The manifest name to update.
	 * 
	 * @param	boolean	$enabled
	 * 		The manifest enabled status.
	 * 		It is TRUE by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setEnabled($name, $enabled = TRUE)
	{
		// Initialize parser
		$parser = $this->xmlParser;
		
		// Get manifest to update by name
		$manifest = $parser->evaluate("//manifest[@name='".$name."']")->item(0);
		if (empty($manifest))
			return FALSE;
		
		// Set manifest enabled on/off
		$parser->attr($manifest, "enabled", $enabled);
		
		// Update file
		return $parser->update(TRUE);
	}
	
	/**
	 * Update the manifest information and packages.
	 * 
	 * @param	string	$name
	 * 		The manifest name to update.
	 * 
	 * @param	array	$packages
	 * 		An array of all packages grouped by library.
	 * 		It is empty by default.
	 * 
	 * @param	string	$newName
	 * 		The new name of the manifest (if you wish to change it).
	 * 		Leave empty for no changes.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($name, $packages = array(), $newName = "")
	{
		// Initialize parser
		$parser = $this->xmlParser;
		
		// Get manifest to update by name
		$manifest = $parser->evaluate("//manifest[@name='".$name."']")->item(0);
		if (empty($manifest))
			return FALSE;
		
		// Set new name (if not empty)
		if (!empty($newName) && $name != $newName)
		{
			// Check that there is no manifest with the same name
			$otherManifest = $parser->evaluate("//manifest[@name='".$newName."']")->item(0);
			if (!empty($otherManifest))
				return FALSE;
			
			// Update name
			$parser->attr($manifest, "name", $newName);
		}
		
		// Update packages
		$plist = $parser->evaluate("/plist", $manifest)->item(0);
		$parser->innerHTML($plist, "");
		foreach ($packages as $library => $packageList)
		{
			// Get library (or create if not exist)
			$libElement = $parser->evaluate("//plist/library[name='".$library."']")->item(0);
			if (empty($libElement))
			{
				$libElement = $parser->create("library");
				$parser->attr($libElement, "name", $library);
				$parser->append($plist, $libElement);
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
	
	/**
	 * Remove the manifest from the list.
	 * 
	 * @param	string	$name
	 * 		The manifest name to be removed.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($name)
	{
		// Initialize parser
		$parser = $this->xmlParser;
		
		// Get the manifest element
		$manifest = $parser->evaluate("//manifest[@name='".$name."']")->item(0);
		if (!empty($manifest))
			return FALSE;
		
		// Remove manifest
		$parser->replace($manifest, NULL);
		
		// Update file
		return $parser->update(TRUE);
	}
}
//#section_end#
?>