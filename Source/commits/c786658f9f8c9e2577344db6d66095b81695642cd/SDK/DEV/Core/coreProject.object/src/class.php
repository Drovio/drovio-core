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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Core", "ajax/ajaxDirectory");
importer::import("DEV", "Core", "sql/sqlDomain");
importer::import("DEV", "Core", "sdk/sdkLibrary");
importer::import("DEV", "Projects", "project");
importer::import("UI", "Layout", "layoutManager");

use \DEV\Core\ajax\ajaxDirectory;
use \DEV\Core\sql\sqlDomain;
use \DEV\Core\sdk\sdkLibrary;
use \DEV\Projects\project;
use \UI\Layout\layoutManager;

/**
 * Core Project
 * 
 * It is the core project class.
 * Manages core publishing.
 * 
 * @version	0.1-2
 * @created	December 18, 2014, 11:20 (EET)
 * @revised	December 18, 2014, 11:25 (EET)
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
	 * 
	 * Publish the sql library.
	 * Publish the sdk library.
	 * Publish all ajax files.
	 * Export layouts.
	 * Publish all resources (media and files).
	 * 
	 * @param	string	$branchName
	 * 		The source's branch name to publish.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function publish($branchName = vcs::MASTER_BRANCH)
	{
		// Validate account
		if (!$this->validate())
			return FALSE;
		
		// Check branch name
		$branchName = (empty($branchName) ? vcs::MASTER_BRANCH : $branchName);
		
		// Publish core project
		// THIS IS A STRICT ORDER!!!
		
		// Publish SQL Library
		sqlDomain::publish($branchName);
		
		// Publish SDK
		sdkLibrary::publish($branchName);
		
		// Publish ajax pages
		ajaxDirectory::publish($branchName);
		
		// Publish layouts
		layoutManager::export();
		
		// Publish Media and Resources
		$this->publishResources("/Library/Media/c/", $innerFolder = "/media/");
		$this->publishResources("/System/Resources/SDK/", $innerFolder = "/resources/");
		
		return TRUE;
	}
}
//#section_end#
?>