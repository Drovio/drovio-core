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
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Resources", "filesystem/folderManager");
importer::import("DEV", "Core", "ajax/ajaxDirectory");
importer::import("DEV", "Core", "sql/sqlDomain");
importer::import("DEV", "Core", "sdk/sdkLibrary");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Version", "vcs");

use \API\Resources\filesystem\folderManager;
use \DEV\Core\ajax\ajaxDirectory;
use \DEV\Core\sql\sqlDomain;
use \DEV\Core\sdk\sdkLibrary;
use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Version\vcs;

/**
 * Core Project
 * 
 * It is the core project class.
 * Manages core publishing.
 * 
 * @version	0.2-13
 * @created	December 18, 2014, 11:20 (EET)
 * @updated	July 16, 2015, 17:36 (EEST)
 */
class coreProject extends project
{
	/**
	 * The core project id.
	 * 
	 * @type	integer
	 */
	const PROJECT_ID = 1;
	
	/**
	 * The core project type id.
	 * 
	 * @type	integer
	 */
	const PROJECT_TYPE = 1;
	
	/**
	 * Initialize Core Project
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Construct project
		parent::__construct(self::PROJECT_ID);
	}
	
	/**
	 * Publish the core project.
	 * Publish all the core components in a strict order.
	 * 
	 * 1. SQL library.
	 * 2. SDK library.
	 * 3. Ajax files.
	 * 4. Resources (media and files).
	 * 
	 * @param	string	$version
	 * 		The core project release version.
	 * 
	 * @param	string	$branchName
	 * 		The source's branch name to publish.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function publish($version, $branchName = vcs::MASTER_BRANCH)
	{
		// Validate account
		if (!$this->validate())
			return FALSE;
		
		// Check branch name
		$branchName = (empty($branchName) ? vcs::MASTER_BRANCH : $branchName);
		
		// Create Publish Folder (if doesn't already exist)
		$publishFolder = projectLibrary::getPublishedPath($this->getID(), $version);
		folderManager::create(systemRoot.$publishFolder);
		
		// Publish core project components
		// THIS IS A STRICT ORDER!!!
		
		// Publish SQL Library
		sqlDomain::publish($branchName);
		
		// Publish SDK
		sdkLibrary::publish($version, $branchName);
		
		// Publish ajax pages
		ajaxDirectory::publish($branchName);
		
		// Publish project common assets
		parent::publish($version);
		
		// COMPATIBILITY
		$this->publishResources($publishFolder.projectLibrary::RSRC_FOLDER, $innerFolder = "/media/", $clean = FALSE);
		$this->publishResources("/System/Resources/SDK/", $innerFolder = "/resources/");
		
		return TRUE;
	}
}
//#section_end#
?>