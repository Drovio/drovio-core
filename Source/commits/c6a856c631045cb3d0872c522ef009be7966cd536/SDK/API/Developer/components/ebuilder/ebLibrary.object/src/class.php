<?php
//#section#[header]
// Namespace
namespace API\Developer\components\ebuilder;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\components\sdk
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::prime::classMap");
importer::import("API", "Developer", "projects::project");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "filesystem::directory");
importer::import("API", "Resources", "archive::zipManager");

use \API\Developer\components\prime\classMap;
use \API\Developer\projects\project;
use \API\Developer\resources\paths;
use \API\Resources\filesystem\directory;
use \API\Resources\archive\zipManager;

/**
 * SDK Library Manager
 * 
 * Handles all operations with sdk libraries.
 * 
 * @version	{empty}
 * @created	March 21, 2013, 12:05 (EET)
 * @revised	January 13, 2014, 16:51 (EET)
 */
class ebLibrary
{
	/**
	 * The classMap object.
	 * 
	 * @type	classMap
	 */
	private $classMap;
	
	/**
	 * Constructor method. Initializes class variables.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Create Developer Index
		$repository = project::getRepository(3);
		$this->classMap = new classMap($repository, FALSE, "");
	}
	
	/**
	 * Creates a new library.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($libName)
	{
		// Library name must be capitals
		$libName = strtoupper($libName);
		
		// Create index
		$this->classMap->createLibrary($libName);
		
		// If library already exists, abort
		if (!$proceed)
			return FALSE;
		
		// Create Library Index
		return TRUE;
	}
	
	/**
	 * Gets a list of all libraries.
	 * 
	 * @return	array
	 * 		A list of all libraries in the SDK.
	 */
	public function getList()
	{
		return $this->classMap->getLibraryList();
	}
	
	/**
	 * Get all packages in the given library
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	array
	 * 		An array of all packages.
	 */
	public function getPackageList($libName = "")
	{
		return $this->classMap->getPackageList($libName);
	}
	
	/**
	 * Packs a library in a new or existing archive
	 * 
	 * @param	string	$libName
	 * 		Name of the library to pack
	 * 
	 * @param	string	$archivePath
	 * 		Directory of the new or existing archive
	 * 
	 * @param	string	$archiveName
	 * 		Name of the archive. If empty, a new archive will be created in $directory
	 * 
	 * @param	string	$innerArchiveDirectory
	 * 		Inner directory inside the archive, where the library will be packed. Only has meaning if $archive is supplied
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	public function pack($libName, $archivePath, $archiveName = "", $innerArchiveDirectory = "")
	{	
		$repository = project::getRepository(3);
		$libRoot = $repository."/".$libName;
		$contents = directory::getContentList(systemRoot.$libRoot, TRUE);
		
		if (!empty($archiveName))
			return zipManager::append($archivePath."/".$archiveName, $contents, $innerArchiveDirectory);
		
		$archiveName = "/".$libName.".zip";
		return zipManager::create($archivePath."/".$archiveName, $contents);
	}
}
//#section_end#
?>