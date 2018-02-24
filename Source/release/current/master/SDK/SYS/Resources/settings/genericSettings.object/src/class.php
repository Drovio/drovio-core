<?php
//#section#[header]
// Namespace
namespace SYS\Resources\settings;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	SYS
 * @package	Resources
 * @namespace	\settings
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Resources", "settingsManager");

use \API\Resources\settingsManager;

/**
 * System Generic Settings
 * 
 * Manages all system generic settings.
 * Includes:
 * - page settings
 * 
 * @version	0.1-1
 * @created	February 13, 2015, 12:31 (EET)
 * @updated	February 13, 2015, 12:31 (EET)
 */
class genericSettings extends settingsManager
{
	/**
	 * Initialize the settings manager.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Construct settingsManager
		parent::__construct(systemConfig."/Settings/", "Generic", TRUE);
		
		// Create (if not exist)
		parent::create();
	}
}
//#section_end#
?>