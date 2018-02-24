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

importer::import("ESS", "Protocol", "server::AsCoProtocol");
importer::import("ESS", "Protocol", "server::RuntimeException");
importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Content", "analytics");
importer::import("API", "Security", "privileges");
importer::import("API", "Profile", "tester");
importer::import("API", "Developer", "components::moduleObject");
importer::import("API", "Developer", "components::units::modules::module");
importer::import("API", "Model", "units::modules::Smodule");
importer::import("UI", "Html", "HTMLPageReport");

importer::import("DEV", "Profiler", "logger");
importer::import("DEV", "Profiler", "debugger");

use \ESS\Protocol\server\AsCoProtocol;
use \ESS\Protocol\server\RuntimeException;
use \ESS\Protocol\client\BootLoader;
use \API\Content\analytics;
use \API\Security\privileges;
use \API\Profile\tester;
use \API\Developer\components\moduleObject;
use \API\Developer\components\units\modules\module as unitModule;
use \API\Model\units\modules\Smodule;
use \UI\Html\HTMLPageReport;

use \DEV\Profiler\logger;
use \DEV\Profiler\debugger;

/**
 * Module Loader
 * 
 * Loads and executes the module with the given id and action (in case of auxiliary)
 * 
 * @version	{empty}
 * @created	March 7, 2013, 11:43 (EET)
 * @revised	February 24, 2014, 14:53 (EET)
 */
class ModuleLoader
{
	/**
	 * Loads and executes a given module
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id.
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module
	 * 
	 * @return	string
	 * 		The module execution output.
	 */
	public static function load($moduleID, $action = "")
	{
		try
		{	
			// Get module access
			$access = privileges::moduleAccess($moduleID);
			
			// Log module call
			$rbData = array();
			$rbData['moduleID'] = $moduleID;
			$rbData['action'] = $action;
			$rbData['access'] = $access;
			$rbData['path'] = AsCoProtocol::getPath();
			

			// Check Execution Authentication
			$authStatus = self::checkAuth($access);
			if ($authStatus !== TRUE)
				return $authStatus;
				
			// Set GLOBALS
			$GLOBALS['moduleID'] = $moduleID;
			
			// Set Module Resource
			BootLoader::setModuleResource($moduleID);

			// Load Module Content
			$startTime = microtime(TRUE);
			$output = self::getContent($moduleID, $action);
			$endTime = microtime(TRUE);

			// If module not found, show error
			if (!isset($output) || $output == false)
			{
				$report = new HTMLPageReport();
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
			// Catches the exception thrown by the programmer's code
			return RuntimeException::get($ex);
		}
	}
	
	/**
	 * Checks the module execution with a condition, providing the key and its prefix.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	string	$key
	 * 		The key value (usually the project id, or the company id or anything else).
	 * 
	 * @param	string	$prefix
	 * 		The key prefix.
	 * 		It varies from service to service.
	 * 		It will be provided by an engine.
	 * 
	 * @return	mixed
	 * 		True is user can execute, the error report output otherwise.
	 */
	public static function checkKey($moduleID, $key, $prefix)
	{
		// Get key
		$key = self::getKey($prefix, $key);
		
		// Get module access
		$access = privileges::moduleAccess($moduleID, $key);
		
		// Return Execution Authentication
		return self::checkAuth($access);
	}
	
	/**
	 * Gets the key value.
	 * 
	 * @param	string	$prefix
	 * 		The service prefix for the key.
	 * 
	 * @param	string	$value
	 * 		The key value.
	 * 
	 * @return	string
	 * 		The hash key value.
	 */
	public static function getKey($prefix, $value)
	{
		return md5("accessKey_".$prefix."_".$value);
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
		$report = new HTMLPageReport();
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
	
	/**
	 * Executes the content of the module. This is the spot where the server decides
	 * whether to load from repository or from production. In case of published server, always from production.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to execute.
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module to execute
	 * 
	 * @return	mixed
	 * 		The module execution content.
	 */
	private static function getContent($moduleID, $action)
	{
		// Check if user is tester of this module
		if (tester::testerModule($moduleID))
		{			
			// Create module and run view
			$module = new unitModule($moduleID);
			return $module->runView($action);
		}
		else
			return Smodule::runView($moduleID, $action);
	}
}
//#section_end#
?>