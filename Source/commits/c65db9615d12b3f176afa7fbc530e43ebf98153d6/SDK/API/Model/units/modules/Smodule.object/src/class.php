<?php
//#section#[header]
// Namespace
namespace API\Model\units\modules;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Model
 * @namespace	\units\modules
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Model", "units::modules::SmoduleGroup");
importer::import("API", "Resources", "DOMParser");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Model\units\modules\SmoduleGroup;
use \API\Resources\DOMParser;

importer::import("API", "Developer", "components::units::modules::moduleGroup");
use \API\Developer\components\units\modules\moduleGroup;

/**
 * Module Manager
 * 
 * Gets information about modules for the runtime.
 * 
 * @version	{empty}
 * @created	November 6, 2013, 14:57 (EET)
 * @revised	December 6, 2013, 18:38 (EET)
 * 
 * @deprecated	This class is deprecated. Soon to be moved.
 */
class Smodule
{
	/**
	 * The modules library path.
	 * 
	 * @type	string
	 */
	const LIB_PATH = "/System/Library/Modules";
	
	/**
	 * Gets the info of a module.
	 * 
	 * @param	integer	$id
	 * 		The module id.
	 * 
	 * @return	array
	 * 		The module info as fetched from the database.
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
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function getDirectoryName($id)
	{
		return "m".$id.".module";
	}
	
	/**
	 * Get module filepath from index.
	 * 
	 * @param	integer	$id
	 * 		The module id.
	 * 
	 * @param	string	$action
	 * 		The auxiliary name.
	 * 
	 * @return	string
	 * 		The module or auxiliary filelpath.
	 */
	public static function filePath($id, $action = "")
	{
		// Get info
		$info = self::info($id);
		
		// Get trail
		$trail = SmoduleGroup::trail($info['group_id']);

		if ($action == "")
			$fileName = self::fileName($id);
		else
		{
			// Load indexing
			$parser = new DOMParser();
			$indexFilePath = self::LIB_PATH.$trail.self::directoryName($id)."index.xml";
			$parser->load($indexFilePath);
			// Get fileName
			$item = $parser->evaluate("//item[@title='$action']")->item(0);
			$seed = $parser->attr($item, "seed");
			$fileName = self::fileName($id, $seed);
		}
		
		
		return self::LIB_PATH.$trail.self::directoryName($id).$fileName.".php";
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$moduleID
	 * 		{description}
	 * 
	 * @param	{type}	$viewName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function runView($moduleID, $viewName = "")
	{
		$moduleInfo = self::info($moduleID);
		$modulePath = moduleGroup::getTrail($moduleInfo['group_id']).self::getDirectoryName($moduleID);
		
		if (empty($viewName))
			$viewName = $moduleInfo['module_title'];
		
		// Load indexing
		$parser = new DOMParser();
		$indexFilePath = "/System/Library/Modules/".$modulePath."/index.xml";
		$parser->load($indexFilePath);
		// Get fileName
		$item = $parser->evaluate("//view[@title='$viewName']")->item(0);
		$viewID = $parser->attr($item, "id");
		$viewFile = $viewID.".php";
		
		return importer::req("/System/Library/Modules/".$modulePath."/".$viewFile);
	}
	
	/**
	 * Gets whether this module has css code.
	 * 
	 * @param	{type}	$moduleID
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		True if module has css, false otherwise.
	 */
	public static function hasCSS($moduleID)
	{
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
	 * @param	{type}	$moduleID
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		True if module has css, false otherwise.
	 */
	public static function hasJS($moduleID)
	{
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