<?php
//#section#[header]
// Namespace
namespace AEL\Resources\team;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	AEL
 * @package	Resources
 * @namespace	\team
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("AEL", "Resources", "folderManager");

use \AEL\Resources\folderManager as AELFolderManager;

/**
 * Application Folder manager for teams
 * 
 * Manages all team folders for the current running application.
 * 
 * NOTE: For each call it checks if there is an active application. If not, returns false every time.
 * All paths are relative to the application root folder or the application shared folder root.
 * The shared folder is one for all applications, so be careful of what you are storing there.
 * 
 * @version	3.0-1
 * @created	December 1, 2014, 10:55 (EET)
 * @updated	January 14, 2015, 10:20 (EET)
 * 
 * @deprecated	Use \AEL\Resources\folderManager instead.
 */
class folderManager extends AELFolderManager
{
	/**
	 * Create a new instance of a folderManager.
	 * 
	 * @param	boolean	$shared
	 * 		If set to true, the DOMParser will have access to the shared application data folder.
	 * 		It is FALSE by default.
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	public function __construct($shared = FALSE)
	{
		parent::__construct(parent::TEAM_MODE, $shared);
	}
}
//#section_end#
?>