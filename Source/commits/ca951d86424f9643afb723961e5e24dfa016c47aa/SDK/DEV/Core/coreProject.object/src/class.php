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

importer::import("ESS", "Protocol", "BootLoader");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("UI", "Layout", "layoutManager");
importer::import("DEV", "Core", "ajax/ajaxDirectory");
importer::import("DEV", "Core", "sql/sqlDomain");
importer::import("DEV", "Core", "sdk/sdkLibrary");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Version", "vcs");

use \ESS\Protocol\BootLoader;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \UI\Layout\layoutManager;
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
 * @version	0.2-6
 * @created	December 18, 2014, 11:20 (EET)
 * @updated	March 2, 2015, 16:27 (EET)
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
	 * 
	 * Publish the sql library.
	 * Publish the sdk library.
	 * Publish all ajax files.
	 * Export layouts.
	 * Publish all resources (media and files).
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
		
		// Publish core project
		// THIS IS A STRICT ORDER!!!
		
		// Publish SQL Library
		sqlDomain::publish($branchName);
		
		// Publish SDK
		sdkLibrary::publish($version, $branchName);
		
		// Publish ajax pages
		ajaxDirectory::publish($branchName);
		
		// Publish layouts - TO BE DEPRECATED IN THE NEAR FUTURE
		layoutManager::export();
		
		// Publish Media and Resources
		$this->publishResources($publishFolder."/media/", $innerFolder = "/media/");
		$this->publishResources("/System/Resources/SDK/", $innerFolder = "/resources/");
		
		// Publish jQuery scripts
		$jqScripts = array();
		$jqScripts['jquery'] = "jquery.js";
		$jqScripts['jquery.easing'] = "jquery.easing.js";
		$jqScripts['jquery.dotimeout'] = "jquery.ba-dotimeout.min.js";
		foreach ($jqScripts as $scriptId => $scriptName)
		{
			$jsContent = fileManager::get(systemRoot.$this->getResourcesFolder()."/jQuery/".$scriptName);
			BootLoader::exportJS(self::PROJECT_ID, "jQuery", $scriptId, $jsContent, $version);
		}
		
		return TRUE;
	}
}
//#section_end#
?>