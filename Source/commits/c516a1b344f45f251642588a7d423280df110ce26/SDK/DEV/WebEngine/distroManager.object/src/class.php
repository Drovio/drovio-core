<?php
//#section#[header]
// Namespace
namespace DEV\WebEngine;

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
 * @namespace	\
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
 * Web Distribution Package Manager
 * 
 * It is responsible for creating the mapping file for distribution packages for web core.
 * 
 * @version	0.1-1
 * @created	November 7, 2014, 13:12 (EET)
 * @revised	November 7, 2014, 13:12 (EET)
 */
class distroManager
{
	/**
	 * The DOMParser object that edits the map file.
	 * 
	 * @type	DOMParser
	 */
	private $xmlParser;
	
	/**
	 * Initialize the class and load the map file.
	 * The file will be created, if accidentally erased.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Get project root folder
		$project = new project(3);
		$rootFolder = $project->getRootFolder();
		$distrosFile = $rootFolder."/distros.xml";
		
		// Initialize parser and load distros file
		$this->xmlParser = new DOMParser();
		try
		{
			$this->xmlParser->load($distrosFile);
		}
		catch (Exception $ex)
		{
			$root = $this->xmlParser->create("WebDistros");
			$this->xmlParser->append($root);
			
			// Create file
			fileManager::create(systemRoot.$distrosFile, "", TRUE);
			$this->xmlParser->save(systemRoot.$distrosFile, "", TRUE);
		}
	}
	
	/**
	 * Create a new distribution package.
	 * 
	 * @param	string	$name
	 * 		The distribution name.
	 * 
	 * @param	array	$libPackages
	 * 		An array of all SDK library packages to include in the distribution.
	 * 		[library] => list(packageName).
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if the distribution name already exist.
	 */
	public function create($name, $libPackages = array())
	{
		// Get parser
		$parser = $this->xmlParser;
		
		// Check if distribution name already exists
		$distro = $parser->evaluate("//distro[name='".$name"']")->item(0);
		if (!is_null($distro))
			return FALSE;
		
		// Create distro
		$distro = $parser->create("distro");
		$parser->attr($distro, "name", $name);
		
		// Update file with new distro
		$parser->update();
		
		// Update packages
		return $this->update($name, $libPackages);
	}
	
	/**
	 * Update a distribution with the given library packages and a new name.
	 * 
	 * @param	string	$name
	 * 		The distribution name.
	 * 
	 * @param	array	$libPackages
	 * 		An array of all SDK library packages to include in the distribution.
	 * 		[library] => list(packageName).
	 * 
	 * @param	string	$newName
	 * 		The new distribution name (optional).
	 * 		Leave empty not to change the name.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if the distribution doesn't exist.
	 */
	public function update($name, $libPackages = array(), $newName = NULL)
	{
		// Get parser
		$parser = $this->xmlParser;
		
		// Check if distribution name already exists
		$distro = $parser->evaluate("//distro[name='".$name"']")->item(0);
		if (empty($distro))
			return FALSE;
		
		// Update name (if given)
		if (!empty($newName))
			$parser->attr($distro, "name", $name);
		
		// Empty distro
		$parser->innerHTML($distro, "");
		
		// Add packages
		foreach ($libPackages as $libName => $packages)
			foreach ($packages as $packageName)
			{
				$pkg = $parser->create("package");
				$parser->attr($pkg, "library", $libName);
				$parser->attr($pkg, "package", $packageName);
				
				$parser->append($distro, $pkg);
			}
		
		// Update file
		return $parser->update();
	}
	
	/**
	 * Remove a given distribution from the distribution list.
	 * 
	 * @param	string	$name
	 * 		The distribution name to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($name)
	{
		// Get parser
		$parser = $this->xmlParser;
		
		// Check if distribution name already exists
		$distro = $parser->evaluate("//distro[name='".$name"']")->item(0);
		if (empty($distro))
			return FALSE;
		
		// Remove distro
		$parser->replace($distro, NULL);
		
		// Update file
		return $parser->update();
	}
	
	/**
	 * Get all web core distributions.
	 * 
	 * @return	array
	 * 		An array of all distributions by name that include all library packages.
	 * 		[distrName] => list(libraries), where
	 * 		libraries => list(packageName).
	 */
	public function getDistros()
	{
		// Get parser
		$parser = $this->xmlParser;
		
		// Get all distros
		$distros = array();
		$distrosElements = $parser->evaluate("//distro");
		foreach ($distrosElements as $distr)
		{
			$distrName = $parser->attr($distr, "name");
			
			// Get packages
			$libPackages = array();
			$packages = $parser->evaluate("/package", $distr);
			foreach ($packages as $pkg)
			{
				// Get package details
				$libName = $parser->attr($pkg, "library");
				$packageName = $parser->attr($pkg, "package");
				$libPackages[$libName][] = $packageName;
			}
			
			// Add distro
			$distros[$distrName] = $libPackages;
		}
		
		return $distros;
	}
}
//#section_end#
?>