<?php
//#section#[header]
// Namespace
namespace API\Developer\components\ebuilder;

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
 * @namespace	\components\ebuilder
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::prime::libraryManager");
importer::import("API", "Resources", "filesystem::directory");
importer::import("API", "Resources", "archive::zipManager");

use \API\Developer\components\prime\libraryManager;
use \API\Resources\filesystem\directory;
use \API\Resources\archive\zipManager;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;

/**
 * Ebuilder Library Manager
 * 
 * Handles all operations with ebuilder libraries.
 * 
 * @version	{empty}
 * @created	May 28, 2013, 11:26 (EEST)
 * @revised	July 23, 2013, 17:28 (EEST)
 */
class ebLibrary extends libraryManager
{
	/**
	 * Initializes the ebLibrary.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	void
	 */
	public function __construct($libName = "")
	{
		// Set Repository
		$this->setRepository(paths::getDevPath()."/Repositories/", "/Library/devKit/eBuilder/");
		
		// Set Release Folder
		$this->setReleaseFolder("/System/Library/devKit/eBuilder/");
		
		// Set Domain
		$this->setDomain("eBuilder");
		
		// Set Parent Constructor
		parent::__construct($libName);
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
		$libRoot = $this->releaseFolder."/".$libName;
		$contents = directory::getContentList(systemRoot.$libRoot, TRUE);
		
		if (!empty($archiveName))
			return zipManager::append($archivePath."/".$archiveName, $contents, $innerArchiveDirectory);
		
		$archiveName = "/".$libName.".zip";
		return zipManager::create($archivePath."/".$archiveName, $contents);
	}
}
//#section_end#
?>