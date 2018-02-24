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

importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("ESS", "Protocol", "BootLoader");
importer::import("ESS", "Protocol", "debug/RuntimeException");
importer::import("API", "Model", "modules/module");
importer::import("API", "Platform", "engine");
importer::import("API", "Security", "privileges");
importer::import("UI", "Core", "RCPageReport");
importer::import("UI", "Forms", "Form");
importer::import("DEV", "Analytics", "old/analytics");
importer::import("DEV", "Profile", "tester");
importer::import("DEV", "Profiler", "log/errorLogger");
importer::import("DEV", "Profiler", "logger");
importer::import("DEV", "Profiler", "debugger");
importer::import("DEV", "Modules", "module");
importer::import("DEV", "Modules", "modulesProject");
importer::import("BT", "Issues", "btException");

use \ESS\Protocol\AsCoProtocol;
use \ESS\Protocol\BootLoader;
use \ESS\Protocol\debug\RuntimeException;
use \API\Model\modules\module;
use \API\Platform\engine;
use \API\Security\privileges;
use \UI\Core\RCPageReport;
use \UI\Forms\Form;
use \DEV\Analytics\old\analytics;
use \DEV\Profile\tester;
use \DEV\Profiler\log\errorLogger;
use \DEV\Profiler\logger;
use \DEV\Profiler\debugger;
use \DEV\Modules\module as DEVModule;
use \DEV\Modules\modulesProject;
use \BT\Issues\btException;

/**
 * Module Loader
 * 
 * Loads and executes the module with the given id and action (in case of auxiliary)
 * 
 * @version	3.3-2
 * @created	March 7, 2013, 11:43 (EET)
 * @updated	January 29, 2015, 9:54 (EET)
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
		if (engine::isPost())
		{
			$status = Form::validate();
			if ($status === FALSE)
			{
				logger::log("ModuleLoader: Posting module [".$moduleID."] is invalidated.", logger::ERROR);
				$report = new RCPageReport();
				return $report->build("error", "error", "err.invalidate")->getReport();
			}
		}
		
		// Log success for executing module
		logger::log("ModuleLoader: Module [".$moduleID."] is being executed with access status [".$access."].", logger::INFO);
		
		// Log analytics
		$rbData = array();
		$rbData['moduleID'] = $moduleID;
		$rbData['action'] = $viewName;
		$rbData['access'] = $access;
		$rbData['path'] = AsCoProtocol::getPath();
		//analytics::log($rbData);
		
		try
		{

			// Load Module Content
			$output = module::loadView($moduleID, $viewName);
			if (empty($output))
			{
				logger::log("ModuleLoader: Module [".$moduleID."] returns empty response.", logger::ERROR);
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
			// Log error to debugger
			logger::log("ModuleLoader: Module [".$moduleID."] threw an Exception.", logger::ERROR);
			
			// Log error to logger
			$erLogger = new errorLogger();
			$erLogger->log($ex);
			
			// Log exception
			$btExc = new btException(modulesProject::PROJECT_ID);
			$btExc->create($ex);
		
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
	 * @param	integer	$moduleID
	 * 		The module id to get the resources for.
	 * 		Leave empty to get the current loaded module.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of resource id as key and as value an array of resource css and js data.
	 */
	public static function getModuleResources($moduleID = "")
	{
		// Check module existance
		$moduleID = (empty($moduleID) ? self::$moduleID : $moduleID);
		if (empty($moduleID))
			return array();
		
		// Init resources array
		$resources = array();
		
		$modules = array();
		$modules[] = $moduleID;
		// Get inner modules for the host module
		$innerModules = module::getInnerModules($moduleID);
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
		$tester = FALSE;
		if (tester::testerModule($moduleID))
		{
			$tester = TRUE;
			$moduleObject = new DEVModule($moduleID);
			if ($moduleObject->hasCSS())
				$cssResourceUrl = BootLoader::getTesterResourceUrl("/ajax/modules/css.php", "Modules", $moduleID);
			if ($moduleObject->hasJS())
				$jsResourceUrl = BootLoader::getTesterResourceUrl("/ajax/modules/js.php", "Modules", $moduleID);
		}
		else
		{
			$cssResourceUrl = BootLoader::getResourceUrl(BootLoader::RSRC_CSS, modulesProject::PROJECT_ID, "Modules", $moduleID);
			$jsResourceUrl = BootLoader::getResourceUrl(BootLoader::RSRC_JS, modulesProject::PROJECT_ID, "Modules", $moduleID);
		}
		
		return BootLoader::getResourceArray(2, "Modules", $moduleID, $cssResourceUrl, $jsResourceUrl, $tester);
	}
	
	/**
	 * Checks if the user can execute given the access status.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to check the auth for.
	 * 
	 * @param	string	$access
	 * 		The access status for the given module id.
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