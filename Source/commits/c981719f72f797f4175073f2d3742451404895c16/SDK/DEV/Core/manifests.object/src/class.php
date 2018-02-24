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
 * @version	2.0-1
 * @created	February 17, 2015, 18:33 (EET)
 * @updated	March 5, 2015, 17:43 (EET)
 */
class manifests
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
	 * 		An array of all manifests information.
	 * 		See info() for more information on the return context.
	 */
	public function getAll()
	{
		// Initialize list and parser
		$manifestList = array();
		$parser = $this->xmlParser;

		// Get manifests
		$manifests = $parser->evaluate("//manifest");
		foreach ($manifests as $manifest)
		{
			// Get manifest id
			$mfID = $parser->attr($manifest, "id");
			
			// Get manifest info
			$manifestList[$mfID] = $this->info($mfID);
		}
		
		// Return manifest list
		return $manifestList;
	}
	
	/**
	 * Get information about a manifest given the manifest id.
	 * 
	 * @param	string	$mfID
	 * 		The manifest id to get the info for.
	 * 
	 * @return	array
	 * 		An array of manifest information
	 * 		Includes info (name and enabled status) and a list of all sdk packages grouped by library.
	 */
	public function info($mfID)
	{
		// Initialize parser
		$parser = $this->xmlParser;
		
		// Check if manifest with the same name already exists
		$manifest = $parser->find($mfID);
		if (empty($manifest))
			return NULL;
		
		// Get manifest information
		$manifestInfo['info']['name'] = $parser->attr($manifest, "name");
		$manifestInfo['info']['enabled'] = $parser->attr($manifest, "enabled");
		$manifestInfo['info']['private'] = $parser->attr($manifest, "private");
		
		// Get manifest packages
		$packages = $parser->evaluate("plist/library/package", $manifest);
		$packageList = array();
		foreach ($packages as $package)
		{
			// Set names
			$libraryName = $parser->attr($package->parentNode, "name");
			$packageName = $parser->attr($package, "name");
			
			// Append to array
			$manifestInfo['packages'][$libraryName][] = $packageName;
		}
		
		return $manifestInfo;
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
		$mfID = "mf".mt_rand();
		$manifest = $parser->create("manifest", "", $mfID);
		$parser->attr($manifest, "name", $name);
		$parser->append($root, $manifest);
		
		// Add package list container
		$plist = $parser->create("plist");
		$parser->append($manifest, $plist);
		
		// Update file
		$parser->update(TRUE);
		
		// Disable and set to private
		$this->setEnabled($mfID, FALSE);
		$this->setPrivate($mfID, TRUE);
		
		// Set manifest packages
		return $this->update($mfID, $packages);
	}
	
	/**
	 * Set the enable status of the manifest.
	 * 
	 * @param	string	$mfID
	 * 		The manifest id to update.
	 * 
	 * @param	boolean	$enabled
	 * 		The manifest enabled status.
	 * 		Default value is TRUE.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setEnabled($mfID, $enabled = TRUE)
	{
		// Initialize parser
		$parser = $this->xmlParser;
		
		// Get manifest to update by name
		$manifest = $parser->find($mfID);
		if (empty($manifest))
			return FALSE;

		// Set manifest enabled on/off
		$enabled = ($enabled ? 1 : NULL);
		$parser->attr($manifest, "enabled", $enabled);

		// Update file
		return $parser->update(TRUE);
	}
	
	/**
	 * Set the private status of the manifest.
	 * Private manifest packages are used only by the Redback team as private resources.
	 * 
	 * @param	string	$mfID
	 * 		The manifest id to update.
	 * 
	 * @param	boolean	$private
	 * 		The manifest private status.
	 * 		Default value is FALSE.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function setPrivate($mfID, $private = FALSE)
	{
		// Initialize parser
		$parser = $this->xmlParser;
		
		// Get manifest to update by name
		$manifest = $parser->find($mfID);
		if (empty($manifest))
			return FALSE;

		// Set manifest private on/off
		$private = ($private ? 1 : NULL);
		$parser->attr($manifest, "private", $private);

		// Update file
		return $parser->update(TRUE);
	}
	
	/**
	 * Update the manifest information and packages.
	 * 
	 * @param	string	$mfID
	 * 		The manifest id to update.
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
	public function update($mfID, $packages = array(), $newName = "")
	{
		// Initialize parser
		$parser = $this->xmlParser;
		
		// Get manifest to update by name
		$manifest = $parser->find($mfID);
		if (empty($manifest))
			return FALSE;
		
		// Set new name (if not empty)
		$mName = $parser->attr($manifest, "name");
		if (!empty($newName) && $mName != $newName)
		{
			// Check that there is no manifest with the same name
			$otherManifest = $parser->evaluate("//manifest[@name='".$newName."']")->item(0);
			if (!empty($otherManifest))
				return FALSE;
			
			// Update name
			$parser->attr($manifest, "name", $newName);
		}
		
		// Update packages
		$plist = $parser->evaluate("plist", $manifest)->item(0);
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
	 * @param	string	$mfID
	 * 		The manifest id to be removed.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($mfID)
	{
		// Initialize parser
		$parser = $this->xmlParser;
		
		// Get the manifest element
		$manifest = $parser->find($mfID);
		if (!empty($manifest))
			return FALSE;
		
		// Remove manifest
		$parser->replace($manifest, NULL);
		
		// Update file
		return $parser->update(TRUE);
	}
	
	/**
	 * Check whether a given library package is included in the given manifest id and the manifest is enabled.
	 * 
	 * @param	string	$mfID
	 * 		The manifest id.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @return	boolean
	 * 		True if the package is valid, false otherwise.
	 */
	public static function validate($mfID, $library, $package)
	{
		// Load manifest info
		$mfManager = new manifests();
		$mfInfo = $mfManager->info($mfID);
		
		// Validate package
		return ($mfInfo['info']['enabled'] && isset($mfInfo['packages'][$library]) && in_array($package, $mfInfo['packages'][$library]));
	}
}
//#section_end#
?>