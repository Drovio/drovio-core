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

importer::import("AEL", "Resources", "fileManager");

use \AEL\Resources\fileManager as AELFileManager;

/**
 * Application File manager for teams
 * 
 * Manages all team files for the current running application.
 * 
 * NOTE: For each call it checks if there is an active application. If not, returns false every time.
 * All paths are relative to the application root folder or the application shared folder root.
 * The shared folder is one for all applications, so be careful of what you are storing there.
 * 
 * @version	4.0-1
 * @created	December 1, 2014, 10:32 (EET)
 * @updated	January 14, 2015, 10:03 (EET)
 * 
 * @deprecated	Use \AEL\Resources\fileManager instead.
 */
class fileManager extends AELFileManager
{
	/**
	 * Creates a new file in the specified location in the team application folder.
	 * 
	 * @param	boolean	$shared
	 * 		Set true to get to the application shared folder.
	 * 		If false, it will get the application private folder.
	 * 		It is false by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function __construct($shared = FALSE)
	{
		parent::__construct(parent::TEAM_MODE, $shared);
	}
}
//#section_end#
?>