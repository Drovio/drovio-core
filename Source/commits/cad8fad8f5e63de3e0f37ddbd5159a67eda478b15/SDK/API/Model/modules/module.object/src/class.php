<?php
//#section#[header]
// Namespace
namespace API\Model\modules;

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
 * @namespace	\modules
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Model", "modules/mGroup");
importer::import("API", "Model", "modules/mQuery");
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Profile", "tester");
importer::import("DEV", "Modules", "module");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Model\modules\mGroup;
use \API\Model\modules\mQuery;
use \API\Resources\DOMParser;
use \DEV\Profile\tester;
use \DEV\Modules\module as DEVModule;

/**
 * Module Model Manager
 * 
 * {description}
 * 
 * @version	3.0-5
 * @created	May 5, 2014, 12:10 (EEST)
 * @updated	April 30, 2015, 16:17 (EEST)
 */
class module
{
	/**
	 * All modules information in a single array to work as cache.
	 * 
	 * @type	array
	 */
	private static $mInfo = array();
	
	/**
	 * All modules by group id to work as cache.
	 * 
	 * @type	array
	 */
	private static $mList = array();
	
	/**
	 * All module scopes to work as cache.
	 * 
	 * @type	array
	 */
	private static $mScopes = array();
	
	/**
	 * All module statuses to work as cache.
	 * 
	 * @type	array
	 */
	private static $mStatus = array();
	
	/**
	 * All the security key types for modules. Expands incrementally.
	 * 
	 * @type	array
	 */
	private static $moduleKeyTypes = array();
	
	/**
	 * Gets the module's information.
	 * 
	 * @param	integer	$id
	 * 		The module id.
	 * 
	 * @return	array
	 * 		The module information in an array.
	 */
	public static function info($id)
	{
		// Check cache
		if (empty(self::$mInfo) || empty(self::$mInfo[$id]))
		{
			// Get Module Detail Info from database
			$dbq = new dbQuery("16932236558867", "modules");
			$dbc = new dbConnection();
			
			// Get All module information
			$result = $dbc->execute($dbq);
			while ($info = $dbc->fetch($result))
				self::$mInfo[$info['module_id']] = $info;
		}
		
		return self::$mInfo[$id];
	}
	
	/**
	 * Gets the module's directory name.
	 * 
	 * @param	integer	$id
	 * 		The module id.
	 * 
	 * @return	string
	 * 		The module's directory name.
	 */
	public static function getDirectoryName($id)
	{
		return "m".$id.".module";
	}
	
	/**
	 * Load a module's view and return the content.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	string	$viewName
	 * 		The module view name.
	 * 
	 * @return	mixed
	 * 		The view return value.
	 * 
	 * @deprecated	Use loadView() instead.
	 */
	public static function runView($moduleID, $viewName = "")
	{
		return self::loadview($moduleID, $viewName);
	}
	
	/**
	 * Load a module's view and return the content.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	string	$viewName
	 * 		The module view name.
	 * 
	 * @return	mixed
	 * 		The view return value.
	 * 
	 * @throws	Exception
	 */
	public static function loadView($moduleID, $viewName = "")
	{
		// Check if module tester is on
		if (tester::testerModule($moduleID))
		{
			$module = new DEVModule($moduleID);
			$mView = $module->getView($viewName);
			if (is_null($mView))
				return FALSE;

			return $mView->run();
		}
		
		// Load view from deployed
		$moduleInfo = self::info($moduleID);
		$modulePath = mGroup::getTrail($moduleInfo['group_id']).self::getDirectoryName($moduleID);
		
		if (empty($viewName))
			$viewName = $moduleInfo['module_title'];
		
		// Load indexing
		$parser = new DOMParser();
		$indexFilePath = "/System/Library/Modules/".$modulePath."/index.xml";
		$parser->load($indexFilePath);
		// Get fileName
		$item = $parser->evaluate("//view[@name='$viewName']")->item(0);
		if (is_null($item))
			return FALSE;
		$viewID = $parser->attr($item, "id");
		$viewFile = $viewID.".php";
		
		return importer::req("/System/Library/Modules/".$modulePath."/v/".$viewFile);
	}
	
	/**
	 * Get a module's query as a dbQuery object to be executed by the interDbConnection class.
	 * It will get the query from tester or from release based on module tester status.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	string	$queryName
	 * 		The query name.
	 * 
	 * @return	object
	 * 		The db query object.
	 */
	public static function getQuery($moduleID, $queryName)
	{
		// Check if module tester is on
		if (tester::testerModule($moduleID))
		{
			$module = new DEVModule($moduleID);
			return $module->getQuery($queryName);
		}
		
		// Return executable module query
		return new mQuery($moduleID, $queryName);
	}
	
	/**
	 * Gets whether this module has css code.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @return	boolean
	 * 		True if module has css, false otherwise.
	 */
	public static function hasCSS($moduleID)
	{
		// Check if module tester is on
		if (tester::testerModule($moduleID))
		{
			$mdl = new DEVModule($moduleID);
			return $mdl->hasCSS();
		}
		
		$moduleInfo = self::info($moduleID);
		$modulePath = mGroup::getTrail($moduleInfo['group_id']).self::getDirectoryName($moduleID);
		
		// Load indexing
		$parser = new DOMParser();
		$indexFilePath = "/System/Library/Modules/".$modulePath."/index.xml";
		$parser->load($indexFilePath);
		// Get css
		$moduleBase = $parser->evaluate("//module")->item(0);
		return $parser->attr($moduleBase, "css") == "1";
	}
	
	/**
	 * Gets whether this module has js code.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @return	boolean
	 * 		True if module has js, false otherwise.
	 */
	public static function hasJS($moduleID)
	{
		// Check if module tester is on
		if (tester::testerModule($moduleID))
		{
			$mdl = new DEVModule($moduleID);
			return $mdl->hasJS();
		}
		
		$moduleInfo = self::info($moduleID);
		$modulePath = mGroup::getTrail($moduleInfo['group_id']).self::getDirectoryName($moduleID);
		
		// Load indexing
		$parser = new DOMParser();
		$indexFilePath = "/System/Library/Modules/".$modulePath."/index.xml";
		$parser->load($indexFilePath);
		// Get css
		$moduleBase = $parser->evaluate("//module")->item(0);
		return $parser->attr($moduleBase, "js") == "1";
	}
	
	/**
	 * Get all modules in the system.
	 * 
	 * @param	integer	$groupID
	 * 		If set, get all modules in the given group.
	 * 		It is NULL by default.
	 * 
	 * @return	array
	 * 		An array of all modules with their information.
	 */
	public static function getAllModules($groupID = NULL)
	{
		// Get from cache
		$gKey = (empty($groupID) ? -1 : $groupID);
		if (empty(self::$mList[$gKey]))
		{
			// Initialize connection
			$dbc = new dbConnection();
			$attr = array();
			
			if (empty($groupID))
				$dbq = new dbQuery("1799521085478", "modules");
			else
			{
				$attr['gid'] = $groupID;
				$dbq = new dbQuery("22227285267984", "modules");
			}
			
			// Execute
			$result = $dbc->execute($dbq, $attr);
			self::$mList[$gKey] = $dbc->fetch($result, TRUE);
		}
		
		// Return
		return self::$mList[$gKey];
	}
	
	/**
	 * Get all module scopes.
	 * 
	 * @return	array
	 * 		An array of all module scopes.
	 */
	public static function getModuleScopes()
	{
		// Check cache
		if (empty(self::$mScopes))
		{
			// Initialize connection
			$dbc = new dbConnection();
			$dbq = new dbQuery("34939257826993", "modules.info");
			$result = $dbc->execute($dbq);
			// Update cache
			self::$mScopes = $dbc->fetch($result, TRUE);
		}
		
		// Return cached
		return self::$mScopes;
	}
	
	/**
	 * Get a specific module's status or get all module statuses.
	 * 
	 * @param	integer	$moduleID
	 * 		If set, get the status of the given module id.
	 * 		It is NULL by default.
	 * 
	 * @return	mixed
	 * 		An array of all statuses or an array of the status of the given module.
	 */
	public static function getModuleStatus($moduleID = NULL)
	{
		if (!empty($moduleID))
		{
			$mInfo = self::info($moduleID);
			return $mInfo['status_id'];
		}
		
		// Get from cache
		if (empty(self::$mStatus))
		{
			// Initialize connection
			$dbc = new dbConnection();
			$dbq = new dbQuery("29583970473884", "modules.info");
			$result = $dbc->execute($dbq);
			self::$mStatus = $dbc->fetch($result, TRUE);
		}
		
		return self::$mStatus;
	}
	
	/**
	 * Get inner modules of a given module.
	 * If the module is in tester mode, the inner modules come from the trunk, otherwise from deployed.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to get the inner modules from.
	 * 
	 * @return	array
	 * 		An array of all inner module ids.
	 */
	public static function getInnerModules($moduleID)
	{
		// Init inner modules array
		$innerModules = array();
		
		// Check if module tester is on
		if (tester::testerModule($moduleID))
		{
			$mdl = new DEVModule($moduleID);
			$views = $mdl->getViews();
			foreach ($views as $viewID => $viewName)
			{
				$mdl_view = $mdl->getView($viewName, $viewID);
				$inner = $mdl_view->getInnerModules();
				$innerModules = array_merge($innerModules, $inner);
				$innerModules = array_unique($innerModules);
			}
			
			return $innerModules;
		}
		
		// Load module
		$moduleInfo = self::info($moduleID);
		$modulePath = mGroup::getTrail($moduleInfo['group_id']).self::getDirectoryName($moduleID);
		
		// Load indexing
		$parser = new DOMParser();
		$indexFilePath = "/System/Library/Modules/".$modulePath."/index.xml";
		$parser->load($indexFilePath);
		
		// Get inner modules
		$innerEntries = $parser->evaluate("//im");
		foreach ($innerEntries as $entry)
			$innerModules[] = $parser->nodeValue($entry);
			
		return $innerModules;
	}
	
	/**
	 * Get all security key types assigned to this module.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to get the key types from.
	 * 
	 * @return	array
	 * 		An array of all key type ids.
	 */
	public static function getKeyTypes($moduleID)
	{
		if (isset(self::$moduleKeyTypes[$moduleID]))
			return self::$moduleKeyTypes[$moduleID];
			
		// Get Module Detail Info
		$dbc = new dbConnection();
		$dbq = new dbQuery("33290334334259", "security.privileges.keys");
		
		// Get Module Group Data
		$attr = array();
		$attr['id'] = $moduleID;
		$result = $dbc->execute($dbq, $attr);
		
		self::$moduleKeyTypes[$moduleID] = array();
		while ($t = $dbc->fetch($result))
			self::$moduleKeyTypes[$moduleID][] = $t['keyType_id'];
		
		return self::$moduleKeyTypes[$moduleID];
	}
}
//#section_end#
?>