<?php
//#section#[header]
// Namespace
namespace DEV\Websites\settings;

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
 * @package	Websites
 * @namespace	\settings
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Resources", "settingsManager");
importer::import("DEV", "Websites", "website");

use \API\Resources\settingsManager;
use \DEV\Websites\website;

/**
 * Website Meta Settings
 * 
 * Manages meta website settings like keywords, description and open graph (meta_settings.xml).
 * 
 * @version	1.0-1
 * @created	January 2, 2015, 12:28 (EET)
 * @updated	January 4, 2015, 1:50 (EET)
 */
class metaSettings extends settingsManager
{
	/**
	 * Initializes the website meta settings..
	 * 
	 * @param	integer	$id
	 * 		The website id.
	 * 
	 * @return	void
	 */
	public function __construct($id)
	{
		// Init website
		$website = new website($id);
		
		// Init settingsManager
		$settingsFolder = $website->getRootFolder()."/".website::SETTINGS_FOLDER;
		parent::__construct($settingsFolder, "meta_settings");
	}
}
//#section_end#
?>