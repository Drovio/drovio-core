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
importer::import("ESS", "Protocol", "debug/RuntimeException");
importer::import("API", "Model", "modules/module");
importer::import("API", "Security", "privileges");
importer::import("UI", "Core", "RCPageReport");
importer::import("UI", "Forms", "formFactory");
importer::import("DEV", "Analytics", "old/analytics");
importer::import("DEV", "Profile", "tester");
importer::import("DEV", "Profiler", "logger");
importer::import("DEV", "Profiler", "debugger");
importer::import("DEV", "Modules", "module");

use \ESS\Protocol\AsCoProtocol;
use \ESS\Protocol\BootLoader;
use \ESS\Protocol\debug\RuntimeException;
use \API\Model\modules\module;
use \API\Security\privileges;
use \UI\Core\RCPageReport;
use \UI\Forms\formFactory;
use \DEV\Analytics\old\analytics;
use \DEV\Profile\tester;
use \DEV\Profiler\logger;
use \DEV\Profiler\debugger;
use \DEV\Modules\module as DEVModule;

/**
 * Module Loader
 * 
 * Loads and executes the module with the given id and action (in case of auxiliary)
 * 
 * @version	3.1-1
 * @created	March 7, 2013, 11:43 (EET)
 * @revised	December 26, 2014, 10:47 (EET)
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
	 * The current module id loading.
	 * 
	 * @type	integer
	 */
	private static $moduleID;
	
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
		// Set module loading and log
		logger::log("ModuleLoader: Loading module [".$moduleID.(empty($viewName) ? "" : ":".$viewName)."].", logger::INFO);
		self::$moduleID = $moduleID;
		
		// Get generic module access
		$access = privileges::moduleAccess($moduleID);
		
		// Log module call
		$rbData = array();
		$rbData['moduleID'] = $moduleID;
		$rbData['action'] = $viewName;
		$rbData['access'] = $access;
		$rbData['path'] = AsCoProtocol::getPath();
		

		// Check Execution Authentication
		$authStatus = self::checkAuth($moduleID, $access);
		if ($authStatus !== TRUE)
			return $authStatus;
		
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
		
		// Log key access
		logger::log("ModuleLoader: Key access for [".$moduleID."] is [".$keyAccess."].", logger::INFO);
		
		// Check Execution Authentication again for keys
		$authStatus = self::checkAuth($moduleID, $keyAccess);
		if ($authStatus !== TRUE)
			return $authStatus;
			
		// Set GLOBALS
		$GLOBALS['moduleID'] = $moduleID;
		
		// See if it's a form post and validate module
		if ($_SERVER['REQUEST_METHOD'] == "POST")
		{
			$status = formFactory::validate();
			if ($status === FALSE)
			{
				logger::log("ModuleLoader: Posting module [".$moduleID."] is invalidated.", logger::ERROR);
				$report = new RCPageReport();
				return $report->build("error", "debug", "dbg.invalidate")->getReport();
			}
		}
		
		// Log success for executing module
		logger::log("ModuleLoader: Module [".$moduleID."] is being executed with access status [".$access."].", logger::INFO);
		
		try
		{

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
	 * Get all the loading module related resources.
	 * 
	 * @return	array
	 * 		An array of resource id as key and as value an array of resource css and js data.
	 */
	public static function getModuleResources()
	{
		// Check module existance
		if (empty(self::$moduleID))
			return array();
		
		// Init resources array
		$resources = array();
		
		$modules = array();
		$modules[] = self::$moduleID;
		// Get inner modules for the host module
		$innerModules = module::getInnerModules(self::$moduleID);
		$modules = array_merge($modules, $innerModules);
		
		// Get all resources for modules
		foreach ($modules as $moduleID)
		{
			$rsrc = self::getModuleRsrcArray($moduleID);
			if (!empty($rsrc))
				$resources[$rsrc['id']] = $rsrc;
		}
		
		// Return all resources
		return $resources;
	}
	
	/**
	 * Get the given module's resource array including the inner modules.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to get the resource from.
	 * 
	 * @return	array
	 * 		An array of resources by id.
	 * 		The array has a key of the resource id and a value of the resource attributes.
	 * 		For testers, it includes as extra information the module id and the tester mode.
	 */
	private static function getModuleRsrcArray($moduleID)
	{
		// Check if user is tester for this module
		$tester = FALSE;
		if (tester::testerModule($moduleID))
		{
			$moduleObject = new DEVModule($moduleID);
			$rsrcCss = $moduleObject->hasCSS();
			$rsrcJs = $moduleObject->hasJS();
			$tester = TRUE;
		}
		else
		{
			// Get module prefix
			$prfx = BootLoader::getPrefix("Modules");
			try
			{
				if (module::hasCSS($moduleID))
					$rsrcCss = $prfx."/". BootLoader::getFileName($prfx, "Modules", $moduleID, "css");
			}
			catch (Exception $ex)
			{
				$rsrcCss = FALSE;
			}
			
			try
			{
				if (module::hasJS($moduleID))
					$rsrcJs = $prfx."/". BootLoader::getFileName($prfx, "Modules", $moduleID, "js");
			}
			catch (Exception $ex)
			{
				$rsrcJs = FALSE;
			}
		}
		
		// Get resource from BootLoader
		return BootLoader::getResourceArray("Modules", "Modules", $moduleID, $rsrcCss, $rsrcJs, $tester);
	}
	
	/**
	 * Checks if the user can execute given the access status.
	 * 
	 * @param	{type}	$moduleID
	 * 		{description}
	 * 
	 * @param	string	$access
	 * 		The access status.
	 * 
	 * @return	mixed
	 * 		True if user can execute, HTMLPageReport content otherwise.
	 */
	private static function checkAuth($moduleID, $access)
	{
		// Check if user can proceed with execution
		$proceed = privileges::canProceed($access);
		if ($proceed)
			return $proceed;
		
		// Error on privileges, log and proceed
		logger::log("ModuleLoader: Module [".$moduleID."] is not allowed for current user.", logger::ERROR);

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