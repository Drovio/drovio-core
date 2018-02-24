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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Model", "modules::mGroup");
importer::import("API", "Model", "modules::mQuery");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Profile", "tester");
importer::import("DEV", "Modules", "module");
importer::import("DEV", "Profiler", "test::moduleTester");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Model\modules\mGroup;
use \API\Model\modules\mQuery;
use \API\Resources\DOMParser;
use \API\Profile\tester;
use \DEV\Modules\module as DEVModule;
use \DEV\Profiler\test\moduleTester;
/**
 * Module Model Manager
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	May 5, 2014, 12:10 (EEST)
 * @revised	May 14, 2014, 9:00 (EEST)
 */
class module
{
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
		// Get Module Detail Info
		$dbq = new dbQuery("361601426", "units.modules");
		$dbc = new interDbConnection();
		
		// Get Module Group Data
		$attr = array();
		$attr['id'] = $id;
		$result = $dbc->execute($dbq, $attr);
		$module = $dbc->fetch($result);
		
		return $module;
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
	 */
	public static function loadView($moduleID, $viewName = "")
	{
		// Check if moduletester is activated
		if (tester::testerModule($moduleID) && moduleTester::status($moduleID))
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
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	string	$queryName
	 * 		The query name.
	 * 
	 * @param	array	$attr
	 * 		An array of attributes by name and value for the query.
	 * 
	 * @return	object
	 * 		The db query object.
	 */
	public static function getQuery($moduleID, $queryName, $attr = array())
	{
		// Check if moduletester is activated
		if (tester::testerModule($moduleID) && moduleTester::status($moduleID))
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
		if (tester::testerModule($moduleID) && moduleTester::status($moduleID))
		{
			$mdl = new DEVModule($moduleID);
			return $mdl->hasCSS();
		}
		
		$moduleInfo = self::info($moduleID);
		$modulePath = moduleGroup::getTrail($moduleInfo['group_id']).self::getDirectoryName($moduleID);
		
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
		// Check if module is tster
		if (tester::testerModule($moduleID) && moduleTester::status($moduleID))
		{
			$mdl = new DEVModule($moduleID);
			return $mdl->hasJS();
		}
		
		$moduleInfo = self::info($moduleID);
		$modulePath = moduleGroup::getTrail($moduleInfo['group_id']).self::getDirectoryName($moduleID);
		
		// Load indexing
		$parser = new DOMParser();
		$indexFilePath = "/System/Library/Modules/".$modulePath."/index.xml";
		$parser->load($indexFilePath);
		// Get css
		$moduleBase = $parser->evaluate("//module")->item(0);
		return $parser->attr($moduleBase, "js") == "1";
	}
}
//#section_end#
?>