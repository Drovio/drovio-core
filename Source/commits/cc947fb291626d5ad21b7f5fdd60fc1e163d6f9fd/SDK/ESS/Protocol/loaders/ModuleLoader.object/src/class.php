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

importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("ESS", "Protocol", "BootLoader");
importer::import("ESS", "Protocol", "debug::RuntimeException");
importer::import("API", "Content", "analytics");
importer::import("API", "Security", "privileges");
importer::import("API", "Model", "modules::module");
importer::import("UI", "Core", "RCPageReport");
importer::import("DEV", "Profiler", "logger");
importer::import("DEV", "Profiler", "debugger");

use \ESS\Protocol\AsCoProtocol;
use \ESS\Protocol\BootLoader;
use \ESS\Protocol\debug\RuntimeException;
use \API\Content\analytics;
use \API\Security\privileges;
use \API\Model\modules\module;
use \UI\Core\RCPageReport;
use \DEV\Profiler\logger;
use \DEV\Profiler\debugger;

/**
 * Module Loader
 * 
 * Loads and executes the module with the given id and action (in case of auxiliary)
 * 
 * @version	2.0-3
 * @created	March 7, 2013, 11:43 (EET)
 * @revised	September 1, 2014, 14:21 (EEST)
 */
class ModuleLoader
{
	/**
	 * The module loading depth.
	 * 
	 * @type	integer
	 */
	private static $loadingDepth = 0;
	
	/**
	 * Loads and executes a given module
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 		If empty, get the default module's view name.
	 * 		It is empty by default.
	 * 
	 * @return	string
	 * 		The module execution output.
	 */
	public static function load($moduleID, $viewName = "")
	{
		// Log module loading
		logger::log("ModuleLoader: Loading module [".$moduleID.(empty($viewName) ? "" : ":".$viewName)."].", logger::INFO);
		try
		{	
			// Get generic module access
			$access = privileges::moduleAccess($moduleID);
			
			// Log module call
			$rbData = array();
			$rbData['moduleID'] = $moduleID;
			$rbData['action'] = $viewName;
			$rbData['access'] = $access;
			$rbData['path'] = AsCoProtocol::getPath();
			

			// Check Execution Authentication
			$authStatus = self::checkAuth($access);
			if ($authStatus !== TRUE)
			{
				logger::log("ModuleLoader: Module [".$moduleID."] is not allowed for current user.", logger::WARNING);
				return $authStatus;
			}
			
			// See if there are security keys to check for extra security
			$keyAccess = $access;
			$keyTypes = module::getKeyTypes($moduleID);
			foreach ($keyTypes as $keyType)
				if (privileges::keyAccess($moduleID, $keyType) !== FALSE)
				{
					$keyAccess = $access;
					break;
				}
				else
					$keyAccess = "no";
			
			// Check Execution Authentication again for keys
			$authStatus = self::checkAuth($keyAccess);
			if ($authStatus !== TRUE)
			{
				logger::log("ModuleLoader: Module [".$moduleID."] is not allowed for current user. Key error.", logger::WARNING);
				return $authStatus;
			}
				
			// Set GLOBALS
			$GLOBALS['moduleID'] = $moduleID;
			
			// Set Module Resource
			BootLoader::setModuleResource($moduleID);

			// Load Module Content
			$startTime = microtime(TRUE);
			$output = module::loadView($moduleID, $viewName);
			$endTime = microtime(TRUE);

			// If module not found, show error
			if (empty($output))
			{
				logger::log("ModuleLoader: Module [".$moduleID."] returns empty response.", logger::ERROR);
				$report = new RCPageReport();
				$output = $report->build("error", "debug", "dbg.content_not_found")->getReport();
			}
			
			// Calculate time and log analytics data			
			$rbData['execTime'] = $startTime - $endTime;
			analytics::log($rbData);
			
			// Clean the buffer if the debugger is off
			if (!debugger::status())
				ob_clean();
			
			// Return the output
			return $output;
		}
		catch (Exception $ex)
		{
			logger::log("ModuleLoader: Module [".$moduleID."] threw an Exception.", logger::ERROR);
			// Catches the exception thrown by the programmer's code
			return RuntimeException::get($ex);
		}
	}
	
	/**
	 * Increase the loading depth of modules by one.
	 * 
	 * @return	void
	 */
	public static function incLoadingDepth()
	{
		self::$loadingDepth++;
	}
	
	/**
	 * Decrease the loading depth of modules by one.
	 * 
	 * @return	void
	 */
	public static function decLoadingDepth()
	{
		self::$loadingDepth--;
	}
	
	/**
	 * Get the current module's loading depth, starting from 0.
	 * 
	 * @return	integer
	 * 		The current loading depth.
	 */
	public static function getLoadingDepth()
	{
		return self::$loadingDepth;
	}
	
	/**
	 * Checks if the user can execute given the access status.
	 * 
	 * @param	string	$access
	 * 		The access status.
	 * 
	 * @return	mixed
	 * 		True if user can execute, HTMLPageReport content otherwise.
	 */
	private static function checkAuth($access)
	{
		// Check if user can proceed with execution
		$proceed = privileges::canProceed($access);
		if ($proceed)
			return $proceed;

		// If user cannot procced, show notification report
		$report = new RCPageReport();
		switch ($access)
		{
			case "uc":
				$output = $report->build("warning", "warning", "wrn.content_uc")->getReport();
				break;
			case "off":
				$output = $report->build("error", "debug", "dbg.content_off")->getReport();
				break;
			case "no":
				$output = $report->build("error", "error", "err.access_denied")->getReport();
				break;
		}
		
		// Send
		return $output;
	}
}
//#section_end#
?>