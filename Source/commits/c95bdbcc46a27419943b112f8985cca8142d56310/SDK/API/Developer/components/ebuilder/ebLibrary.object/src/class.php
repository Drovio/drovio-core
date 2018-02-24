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
 * @namespace	\components\ebuilder
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
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
 * Ebuilder Library Manager
 * 
 * Handles all operations with ebuilder libraries.
 * 
 * @version	{empty}
 * @created	May 28, 2013, 11:26 (EEST)
 * @revised	April 4, 2014, 11:24 (EEST)
 * 
 * @deprecated	Use \DEV\WebEngine\sdk\webLibrary instead.
 */
class ebLibrary
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $classMap;
	
	/**
	 * Initializes the ebLibrary.
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
	 * {description}
	 * 
	 * @param	{type}	$libName
	 * 		{description}
	 * 
	 * @return	void
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
	 * {description}
	 * 
	 * @return	void
	 */
	public function getList()
	{
		return $this->classMap->getLibraryList();
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$libName
	 * 		{description}
	 * 
	 * @return	void
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