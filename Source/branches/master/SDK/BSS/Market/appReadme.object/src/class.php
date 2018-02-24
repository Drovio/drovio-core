<?php
//#section#[header]
// Namespace
namespace BSS\Market;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	BSS
 * @package	Market
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("BSS", "Market", "appMarket");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Projects", "projectReadme");

use \BSS\Market\appMarket;
use \DEV\Projects\projectLibrary;
use \DEV\Projects\projectReadme;

/**
 * Application Readme document manager.
 * 
 * Reads the application's production readme document.
 * 
 * @version	0.1-1
 * @created	July 8, 2015, 15:41 (EEST)
 * @updated	July 8, 2015, 15:41 (EEST)
 */
class appReadme extends projectReadme
{
	/**
	 * Initialize the document manager.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	string	$version
	 * 		The application version.
	 * 		Leave empty for latest version.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($applicationID, $version = "")
	{
		// Get last version if empty
		$version = (empty($version) ? appMarket::getLastApplicationVersion($applicationID) : $version);
		
		// Get application production path
		$applicationPath = projectLibrary::getPublishedPath($applicationID, $version);
		
		// Construct project readme
		parent::__construct($applicationPath, TRUE);
	}
}
//#section_end#
?>