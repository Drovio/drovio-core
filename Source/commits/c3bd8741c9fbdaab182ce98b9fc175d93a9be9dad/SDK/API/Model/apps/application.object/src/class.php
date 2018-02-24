<?php
//#section#[header]
// Namespace
namespace API\Model\apps;

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
 * @package	Model
 * @namespace	\apps
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("AEL", "Platform", "application");
importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");

use \AEL\Platform\application as AELApplication;
use \API\Profile\account;
use \API\Profile\team;

/**
 * Application Model Manager
 * 
 * Provides na interface for some basic functionality for applications.
 * 
 * @version	0.1-2
 * @created	December 5, 2014, 16:38 (EET)
 * @updated	January 13, 2015, 11:52 (EET)
 */
class application
{
	/**
	 * Get the application service path inside the team folder.
	 * 
	 * @return	mixed
	 * 		The application path or NULL if there is no active application.
	 */
	public static function getTeamApplicationPath()
	{
		// Get application id
		$applicationID = AELApplication::init();
		if (empty($applicationID))
			return NULL;
		
		// Get 'service' folder inside the account foler
		$serviceName = self::getServiceName($applicationID);
		return team::getServicesFolder("/AppData/".$serviceName);
	}
	
	/**
	 * Get the application service path inside the account folder.
	 * 
	 * @return	mixed
	 * 		The application path or NULL if there is no active application.
	 */
	public static function getAccountApplicationPath()
	{
		// Get application id
		$applicationID = AELApplication::init();
		if (empty($applicationID))
			return NULL;
		
		// Get 'service' folder inside the account foler
		$serviceName = self::getServiceName($applicationID);
		return account::getServicesFolder("/AppData/".$serviceName);
	}
	
	/**
	 * Get the application folder name as a service.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @return	string
	 * 		The application service folder name.
	 */
	private static function getServiceName($applicationID)
	{
		return "app".md5("app_service_".$applicationID);
	}
}
//#section_end#
?>