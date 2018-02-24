<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\loaders;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\loaders
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("AEL", "Platform", "application");
importer::import("AEL", "Profiler", "logger");
importer::import("API", "Model", "apps/application");
importer::import("BT", "Issues", "btException");
importer::import("ESS", "Protocol", "BootLoader");
importer::import("ESS", "Protocol", "debug/RuntimeException");
importer::import("DEV", "Apps", "application");
importer::import("DEV", "Apps", "test/appTester");
importer::import("DEV", "Apps", "test/viewTester");
importer::import("DEV", "Apps", "views/appView");
importer::import("DEV", "Apps", "views/appViewManager");
importer::import("DEV", "Profiler", "logger");
importer::import("DEV", "Profiler", "debugger");
importer::import("UI", "Core", "RCPageReport");

use \AEL\Platform\application as appPlayer;
use \AEL\Profiler\logger as appLogger;
use \API\Model\apps\application;
use \BT\Issues\btException;
use \ESS\Protocol\BootLoader;
use \ESS\Protocol\debug\RuntimeException;
use \DEV\Apps\application as DEVApp;
use \DEV\Apps\test\appTester;
use \DEV\Apps\test\viewTester;
use \DEV\Apps\views\appView;
use \DEV\Apps\views\appViewManager;
use \DEV\Profiler\logger;
use \DEV\Profiler\debugger;
use \UI\Core\RCPageReport;

/**
 * Application Loader
 * 
 * Responsible for loading safely application views.
 * 
 * @version	1.0-3
 * @created	September 4, 2014, 14:44 (BST)
 * @updated	December 10, 2015, 18:52 (GMT)
 */
class AppLoader
{
	/**
	 * Loads and executes a given application view.
	 * 
	 * @param	integer	$appID
	 * 		The application id to load.
	 * 
	 * @param	string	$viewName
	 * 		The application view name.
	 * 		If empty, get the default/startup application view name.
	 * 		It is empty by default.
	 * 
	 * @return	string
	 * 		The application view execution output.
	 */
	public static function load($appID, $viewName = "")
	{
		// Log application loading
		logger::getInstance()->log("AppLoader: Loading application view [".$appID.(empty($viewName) ? "" : ":".$viewName)."].", logger::INFO);
		
		try
		{
			// Load Application View Content
			appPlayer::init($appID);
			$output = appPlayer::loadView($viewName);

			// If view returned an empty output, log error and show notification
			if (empty($output))
			{
				appLogger::getInstance()->log("AppLoader: Application View [".$appID.(empty($viewName) ? "" : ":".$viewName)."] returned empty response.", logger::ERROR);
				$report = new RCPageReport();
				$output = $report->build("error", "debug", "dbg.content_not_found")->getReport();
			}
			
			// Clean the buffer if the debugger is off
			if (!debugger::status())
				ob_clean();
			
			// Return the output
			return $output;
		}
		catch (Exception $ex)
		{
			// Log error/exception to file
			appLogger::getInstance()->log("AppLoader: Application View [".$appID.(empty($viewName) ? "" : ":".$viewName)."] threw an Exception. See the Project's Issue Tracker for more details.", logger::ERROR);
			
			// Log exception to Bug Tracker
			$btExc = new btException($appID);
			$btExc->create($ex);
			
			// Catches the exception thrown by the programmer's code
			return RuntimeException::get($ex);
		}
	}
	
	/**
	 * Get all the loading application related resources.
	 * 
	 * @param	integer	$appID
	 * 		The application id to get the resources for.
	 * 
	 * @return	array
	 * 		An array of resource id as key and as value an array of resource css and js data.
	 */
	public static function getAppResources($appID)
	{
		// Check application id existance
		$appID = (empty($appID) ? appPlayer::init() : $appID);
		if (empty($appID))
			return array();
		
		// Get all resources for application views
		$resources = array();
		$appViews = application::getAppViews($appID);
		foreach ($appViews as $folderName => $folderViews)
			foreach ($folderViews as $viewName)
			{
				$rsrc = self::getAppViewRsrcArray($appID, $folderName."/".$viewName);
				if (!empty($rsrc))
					$resources[$rsrc['id']] = $rsrc;
			}
		
		// Return all resources
		return $resources;
	}
	
	/**
	 * Get the given application's resource array including all the app views.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$viewName
	 * 		Thew application view name.
	 * 
	 * @return	array
	 * 		An array of resources by id.
	 * 		The array has a key of the resource id and a value of the resource attributes.
	 * 		For testers, it includes as extra information the application id and the view.
	 */
	private static function getAppViewRsrcArray($appID, $viewName)
	{
		$tester = FALSE;
		if (appPlayer::onDEV() || !appTester::publisherLock())
		{
			$tester = TRUE;
			$viewObject = new appView($appID, trim(dirname($viewName), "/"), basename($viewName));
			if ($viewObject->hasCSS())
				$cssResourceUrl = BootLoader::getTesterResourceUrl("/ajax/apps/views/css.php", $appID, $viewName);
			if ($viewObject->hasJS())
				$jsResourceUrl = BootLoader::getTesterResourceUrl("/ajax/apps/views/js.php", $appID, $viewName);
		}
		else
		{
			$cssResourceUrl = BootLoader::getResourceUrl(BootLoader::RSRC_CSS, $appID, "Views", $viewName);
			$jsResourceUrl = BootLoader::getResourceUrl(BootLoader::RSRC_JS, $appID, "Views", $viewName);
		}
		
		return BootLoader::getResourceArray(DEVApp::PROJECT_TYPE, $appID, $viewName, $cssResourceUrl, $jsResourceUrl, $tester);
	}
}
//#section_end#
?>