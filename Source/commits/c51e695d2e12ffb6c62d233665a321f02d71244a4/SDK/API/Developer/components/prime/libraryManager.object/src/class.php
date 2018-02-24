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

importer::import("API", "Developer", "components::prime::indexing::libraryIndex");
importer::import("API", "Developer", "versionControl::vcsManager");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");

use \API\Developer\components\prime\indexing\libraryIndex;
use \API\Developer\versionControl\vcsManager;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;

/**
 * Developer Library Manager
 * 
 * Handles all operations with developer's library.
 * 
 * @version	{empty}
 * @created	May 18, 2013, 15:33 (EEST)
 * @revised	November 27, 2013, 9:46 (EET)
 * 
 * @deprecated	Use \API\Developer\components\prime\indexing\libraryIndex instead.
 */
class libraryManager
{
	/**
	 * The root repository path.
	 * 
	 * @type	string
	 */
	protected $repositoryRoot;
	
	/**
	 * The inner repository path.
	 * 
	 * @type	string
	 */
	protected $repository;
	
	/**
	 * The release folder path for the library.
	 * 
	 * @type	string
	 */
	protected $releaseFolder;
	
	/**
	 * The library's domain.
	 * 
	 * @type	string
	 */
	protected $domain;
	
	/**
	 * The library name (for editing).
	 * 
	 * @type	string
	 */
	protected $libName;
	
	/**
	 * Constructor method. Initialies the library name (for editing).
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	void
	 */
	public function __construct($libName = "")
	{
		$this->libName = $libName;
	}
	
	/**
	 * Creates a new library.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function create($libName)
	{
		// Library name must be capitals
		$libName = strtoupper($libName);
		
		// Set Library name
		$this->libName = $libName;
		
		// Repository Library Path
		folderManager::create(systemRoot.$this->repositoryRoot."/".$this->repository."/", $this->libName);
		
		// Release Library Path
		folderManager::create(systemRoot.$this->releaseFolder."/", $this->libName);
		
		return TRUE;
	}
	
	/**
	 * Sets the repository folders for this library.
	 * 
	 * @param	string	$root
	 * 		The repository root folder.
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
	 * Sets the release folder for this library.
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
	 * Sets the domain of the library.
	 * 
	 * @param	string	$domain
	 * 		The library domain.
	 * 
	 * @return	void
	 */
	protected function setDomain($domain)
	{
		$this->domain = $domain;
	}
	
	/**
	 * Returns a list of all libraries in the given domain.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Developer\components\prime\indexing\libraryIndex::getList() instead.
	 */
	public function getList()
	{
		return libraryIndex::getList(paths::getDevRsrcPath()."/Mapping/Library/".$this->domain);
	}
	
	/**
	 * Returns a list of all objects in the developer's library.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Developer\components\prime\indexing\libraryIndex::getLibraryObjects() instead.
	 */
	public function getLibraryObjects()
	{
		return libraryIndex::getLibraryObjects(paths::getDevRsrcPath()."/Mapping/Library/".$this->domain, $this->libName);
	}
	
	/**
	 * Returns a list of all released objects in the library.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Developer\components\prime\indexing\libraryIndex::getReleaseLibraryObjects() instead.
	 */
	public function getReleaseLibraryObjects()
	{
		return libraryIndex::getReleaseLibraryObjects($this->releaseFolder, $this->libName);
	}
	
	/**
	 * Returns a list of all the packages in the given library.
	 * 
	 * @param	string	$libName
	 * 		The library name. If not given, gets for all libraries.
	 * 
	 * @param	boolean	$fullNames
	 * 		Indicates full names or nested arrays.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Developer\components\prime\indexing\libraryIndex::getPackageList() instead.
	 */
	public function getPackageList($libName = "", $fullNames = TRUE)
	{
		return libraryIndex::getPackageList(paths::getDevRsrcPath()."/Mapping/Library/".$this->domain, $libName, $fullNames);
	}
}
//#section_end#
?>