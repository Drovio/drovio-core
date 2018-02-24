<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\loaders;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\loaders
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::AsCoProtocol");
importer::import("ESS", "Protocol", "server::RuntimeException");
importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Content", "analytics");
importer::import("API", "Debug", "log::logger");
importer::import("API", "Security", "privileges");
importer::import("API", "Profile", "tester");
importer::import("API", "Developer", "components::moduleObject");
importer::import("API", "Developer", "components::units::modules::module");
importer::import("API", "Model", "units::modules::Smodule");
importer::import("UI", "Html", "HTMLPageReport");

use \ESS\Protocol\server\AsCoProtocol;
use \ESS\Protocol\server\RuntimeException;
use \ESS\Protocol\client\BootLoader;
use \API\Content\analytics;
use \API\Debug\log\logger;
use \API\Security\privileges;
use \API\Profile\tester;
use \API\Developer\components\moduleObject;
use \API\Developer\components\units\modules\module as unitModule;
use \API\Model\units\modules\Smodule;
use \UI\Html\HTMLPageReport;

/**
 * Module Loader
 * 
 * Loads and executes the module with the given id and action (in case of auxiliary)
 * 
 * @version	{empty}
 * @created	March 7, 2013, 11:43 (EET)
 * @revised	December 6, 2013, 11:02 (EET)
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
			analytics::log($rbData);

			// Check Execution Authentication
			$authStatus = self::checkAuth($access);
			if ($authStatus !== TRUE)
				return $authStatus;
				
			// Set GLOBALS
			$GLOBALS['moduleID'] = $moduleID;
			
			// Set Module Resource
			BootLoader::setModuleResource($moduleID);

			// Load Module Content
			$output = self::getContent($moduleID, $action);

			// If module not found, show error
			if (!isset($output) || $output == false)
			{
				$report = new HTMLPageReport();
				$output = $report->build("error", "debug", "dbg.content_not_found")->getReport();
			}
			
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
			case "auth":
			case "onauth":
				$output = $report->build("warning", "warning", "wrn.access_auth")->getReport();
				break;
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
			/*
			// Load Module
			$module = new moduleObject($moduleID);
			return $module->getModule($action)->run();
			/*/
			
			// Create module and run view
			$module = new unitModule($moduleID);
			return $module->runView($action);
			//*/
		}
		else
		{
			//return importer::req(Smodule::filePath($moduleID, $action));
			return Smodule::runView($moduleID, $action);
		}
	}
}
//#section_end#
?>