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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "debug::RuntimeException");
importer::import("DEV", "Profiler", "logger");
importer::import("DEV", "Profiler", "debugger");
importer::import("AEL", "Platform", "application");
importer::import("UI", "Core", "RCPageReport");

use \ESS\Protocol\debug\RuntimeException;
use \DEV\Profiler\logger;
use \DEV\Profiler\debugger;
use \AEL\Platform\application as appPlayer;
use \UI\Core\RCPageReport;

/**
 * Application Loader
 * 
 * Responsible for loading safely application views.
 * 
 * @version	0.1-1
 * @created	September 4, 2014, 16:44 (EEST)
 * @revised	September 4, 2014, 16:44 (EEST)
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
		logger::log("AppLoader: Loading application view [".$appID.(empty($viewName) ? "" : ":".$viewName)."].", logger::INFO);
		
		try
		{
			// Load Application View Content
			appPlayer::init($appID);
			$output = appPlayer::loadView($viewName);

			// If module not found, show error
			if (empty($output))
			{
				logger::log("AppLoader: Application View [".$appID.(empty($viewName) ? "" : ":".$viewName)."] returns empty response.", logger::ERROR);
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
			logger::log("AppLoader: Application View [".$appID.(empty($viewName) ? "" : ":".$viewName)."] threw an Exception.", logger::ERROR);
			// Catches the exception thrown by the programmer's code
			return RuntimeException::get($ex);
		}
	}
}
//#section_end#
?>