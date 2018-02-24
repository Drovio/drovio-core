<?php
//#section#[header]
// Namespace
namespace API\Developer\components\prime;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\components\prime
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Developer", "components::prime::indexing::packageIndex");
importer::import("API", "Developer", "versionControl::vcsManager");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");

use \ESS\Protocol\client\BootLoader;
use \API\Developer\components\prime\indexing\packageIndex;
use \API\Developer\versionControl\vcsManager;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;

/**
 * Prime Package Object Handler
 * 
 * Handles all operations with library packages.
 * 
 * @version	{empty}
 * @created	May 17, 2013, 15:36 (EEST)
 * @revised	November 27, 2013, 9:48 (EET)
 * 
 * @deprecated	Use \API\Developer\components\prime\indexing\pacakgeIndex instead.
 */
class packageObject extends vcsManager
{
	/**
	 * The index filename.
	 * 
	 * @type	string
	 */
	const INDEX_FILE = "index.xml";
	
	/**
	 * The root repository.
	 * 
	 * @type	string
	 */
	protected $repositoryRoot;
	/**
	 * The inner repository folder for the vcs.
	 * 
	 * @type	string
	 */
	protected $repository;
	
	/**
	 * The package release folder.
	 * 
	 * @type	string
	 */
	protected $releaseFolder;
	
	/**
	 * The package domain.
	 * 
	 * @type	string
	 */
	protected $domain;
	
	/**
	 * Creates a new package.
	 * 
	 * @param	string	$libName
	 * 		The library name of the package.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function create($libName, $packageName)
	{
		// Repository Package Library Path
		folderManager::create(systemRoot.$this->repositoryRoot."/".$this->repository."/".$libName."/", $packageName);
			
		// Initialize VCS
		$this->VCS_initialize($this->repositoryRoot, $this->repository."/".$libName."/".$packageName."/", "packageRoot_".$packageName, "pkg");
		
		// Create vcs repository structure
		$this->VCS_createStructure();
		
		// Production Package Library Path
		folderManager::create(systemRoot.$this->releaseFolder."/".$libName."/", $packageName);
		
		return TRUE;
	}
	
	/**
	 * Sets the repository variables for this object.
	 * 
	 * @param	string	$root
	 * 		The root repository folder.
	 * 
	 * @param	string	$repository
	 * 		The inner repository folder.
	 * 
	 * @return	void
	 */
	protected function setRepository($root, $repository)
	{
		$this->repositoryRoot = $root;
		$this->repository = str_replace("::", "/", $repository);
	}
	
	/**
	 * Sets the release folder.
	 * 
	 * @param	string	$folder
	 * 		The release folder path.
	 * 
	 * @return	void
	 */
	protected function setReleaseFolder($folder)
	{
		$this->releaseFolder = $folder;
	}
	
	/**
	 * Sets the package's domain.
	 * 
	 * @param	string	$domain
	 * 		The domain of the package.
	 * 
	 * @return	void
	 */
	protected function setDomain($domain)
	{
		$this->domain = $domain;
	}
	
	/**
	 * Create a new namespace inside a given package.
	 * 
	 * @param	string	$libName
	 * 		The library of the pagkage.
	 * 
	 * @param	string	$packageName
	 * 		The package of the namespace.
	 * 
	 * @param	string	$nsName
	 * 		The namespace name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::"), if any.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function createNS($libName, $packageName, $nsName, $parentNs = "")
	{
		// Repository Package Library Path
		$parentNs = str_replace("::", "/", $parentNs);
		folderManager::create(systemRoot.$this->repositoryRoot."/".$this->repository."/".$libName."/".$packageName."/".$parentNs."/", $nsName);
		
		$this->name = $nsName;
		$this->VCS_initialize($this->repositoryRoot, $this->repository."/".$libName."/".$packageName."/".$parentNs."/".$nsName."/", "namespaceRoot_".$nsName, "ns");
		
		// Create vcs repository structure
		$this->VCS_createStructure();
		
		// Production Package Library Path
		folderManager::create(systemRoot.$this->releaseFolder."/".$libName."/".$packageName."/".$parentNs."/", $nsName);
		
		return TRUE;
	}
	
	/**
	 * Get all the namespaces of a given package.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::"), if any.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Developer\components\prime\indexing\packageIndex::getNSList() instead.
	 */
	public function getNSList($libName, $packageName, $parentNs = "")
	{
		return packageIndex::getNSList(paths::getDevRsrcPath()."/Mapping/Library/".$this->domain, $libName, $packageName, $parentNs);
	}
	
	/**
	 * Get all objects of a package in the given library (by namespace).
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::"), if any.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Developer\components\prime\indexing\packageIndex::getPackageObjects() instead.
	 */
	public function getPackageObjects($libName, $packageName, $parentNs = "")
	{
		return packageIndex::getPackageObjects(paths::getDevRsrcPath()."/Mapping/Library/".$this->domain, $libName, $packageName, $parentNs);
	}
	
	/**
	 * Get all direct children objects of a package in the given library (by namespace).
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::"), if any.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Developer\components\prime\indexing\packageIndex::getNSObjects() instead.
	 */
	public function getNSObjects($libName, $packageName, $parentNs = "")
	{
		return packageIndex::getNSObjects(paths::getDevRsrcPath()."/Mapping/Library/".$this->domain, $libName, $packageName, $parentNs);
	}
	
	/**
	 * Get all released objects of a given package.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Developer\components\prime\indexing\packageIndex::getReleasePackageObjects() instead.
	 */
	public function getReleasePackageObjects($libName, $packageName)
	{
		return packageIndex::getReleasePackageObjects($this->releaseFolder, $libName, $packageName);
	}
	
	/**
	 * Create a new release entry for a given object.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$namespace
	 * 		The namespace name.
	 * 
	 * @param	string	$objectName
	 * 		The object's name.
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Developer\components\prime\indexing\packageIndex::addObjectReleaseEntry() instead.
	 */
	public function addObjectReleaseEntry($libName, $packageName, $namespace, $objectName)
	{
		return packageIndex::addObjectReleaseEntry($this->releaseFolder, $libName, $packageName, $namespace, $objectName);
	}
	
	/**
	 * Get the release path of an object.
	 * 
	 * @param	string	$libName
	 * 		The object's library.
	 * 
	 * @param	string	$packageName
	 * 		The object's package.
	 * 
	 * @param	string	$objectName
	 * 		The object's name.
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected function getReleaseObjectPath($libName, $packageName, $objectName)
	{
		return $this->releaseFolder."/".$libName."/".$packageName."/".str_replace("::", "/", $objectName).".php";
	}
}
//#section_end#
?>