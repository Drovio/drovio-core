<?php
//#section#[header]
// Namespace
namespace DEV\Apps\test;

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
 * @package	Apps
 * @namespace	\test
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::RuntimeException");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Profiler", "tester");
importer::import("DEV", "Apps", "appPlayer");
importer::import("DEV", "Apps", "components::appSettings");
importer::import("DEV", "Apps", "components::appView");

use \ESS\Protocol\server\RuntimeException;
use \UI\Html\DOM;
use \DEV\Profiler\tester;
use \DEV\Apps\appPlayer;
use \DEV\Apps\components\appView;
use \DEV\Apps\components\appSettings;

/**
 * Application Tester
 * 
 * Manages the application tester environment.
 * 
 * @version	{empty}
 * @created	April 7, 2014, 11:18 (EEST)
 * @revised	April 7, 2014, 18:16 (EEST)
 */
class appTester extends tester
{
	/**
	 * Initialize the application tester and set the classLoader for testing.
	 * 
	 * @return	void
	 */
	public static function init()
	{
		// Import classLoader
		appPlayer::init();
		\AEL\Platform\classLoader::setTester(TRUE);
	}
	
	/**
	 * Run the application given view from the repository.
	 * Load the html (if any) in the given container (if not null) and then run the view php code to fill the html.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	DOMElement	$container
	 * 		The DOMElement container for the application view content (html).
	 * 
	 * @param	string	$viewName
	 * 		The view name.
	 * 
	 * @return	mixed
	 * 		The php code return value.
	 */
	public static function play($appID, $container, $viewName = "")
	{
		// Get view name
		// If view name is empty, get startup view from app settings
		$viewName = (empty($viewName) ? "MainView" : $viewName);
		/*
		if (empty($viewName))
		{
			// Get Settings
			$appSettings = new appSettings($appID);
			$viewName = $appSettings->get("STARTUP_VIEW");
		}*/
		
		// Init app view
		$appView = new appView($appID, $viewName);
		
		try
		{
			// Load the view html
			if (!empty($container))
			{
				$viewHTML =  $appView->getHTML();
				DOM::innerHTML($container, $viewHTML);
			}
			
			return $appView->run();
		}
		catch (Exception $ex)
		{
			// Catches the exception thrown by the programmer's code
			return RuntimeException::get($ex);
		}
	}
	
	/**
	 * Activates the application tester mode.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate($appID)
	{
		return parent::activate("appTester_id".$appID);
	}
	
	/**
	 * Deactivates the application tester mode.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate($appID)
	{
		return parent::deactivate("appTester_id".$appID);
	}
	
	/**
	 * Gets the application tester status.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	boolean
	 * 		True if application is in tester mode, false otherwise.
	 */
	public static function status($appID)
	{
		return parent::status("appTester_id".$appID);
	}
}
//#section_end#
?>