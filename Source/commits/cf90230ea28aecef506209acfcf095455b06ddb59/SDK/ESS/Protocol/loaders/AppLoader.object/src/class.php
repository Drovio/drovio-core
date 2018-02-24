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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("AEL", "Platform", "application");
importer::import("AEL", "Profiler", "logger");
importer::import("BT", "Issues", "btException");
importer::import("ESS", "Protocol", "debug/RuntimeException");
importer::import("DEV", "Profiler", "logger");
importer::import("DEV", "Profiler", "debugger");
importer::import("UI", "Core", "RCPageReport");

use \AEL\Platform\application as appPlayer;
use \AEL\Profiler\logger as appLogger;
use \BT\Issues\btException;
use \ESS\Protocol\debug\RuntimeException;
use \DEV\Profiler\logger;
use \DEV\Profiler\debugger;
use \UI\Core\RCPageReport;

/**
 * Application Loader
 * 
 * Responsible for loading safely application views.
 * 
 * @version	0.1-3
 * @created	September 4, 2014, 16:44 (EEST)
 * @updated	March 12, 2015, 13:06 (EET)
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
}
//#section_end#
?>