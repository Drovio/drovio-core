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

/**
 * Module Manager
 * 
 * Gets information about modules for the runtime.
 * 
 * @version	{empty}
 * @created	November 6, 2013, 14:57 (EET)
 * @revised	November 6, 2013, 14:57 (EET)
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
	const LIB_PATH = "/System/Library/Units/Modules";
	
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
	 * Gets the module's directory name.
	 * 
	 * @param	integer	$id
	 * 		The module id.
	 * 
	 * @return	string
	 * 		The directory name.
	 */
	public static function directoryName($id)
	{
		return "_mdl_".$id."/";
	}
	
	/**
	 * The module's filename.
	 * 
	 * @param	integer	$id
	 * 		The module's id.
	 * 
	 * @param	integer	$seed
	 * 		The auxiliary's seed.
	 * 
	 * @return	string
	 * 		The php filename.
	 */
	public static function fileName($id, $seed = "")
	{
		if ($seed != "")
			return "ax.".hash("md5", "_aux_".$id."_".$seed);
		else
			return "mdl.".hash("md5", "_mdl_".$id);
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
	 * Gets whether this module has css code.
	 * 
	 * @param	integer	$id
	 * 		The module id.
	 * 
	 * @return	boolean
	 * 		True if module has css, false otherwise.
	 */
	public static function hasCSS($id)
	{
		// Get trail
		$info = self::info($id);
		$trail = SmoduleGroup::trail($info['group_id']);
		
		// Load indexing
		$parser = new DOMParser();
		$indexFilePath = self::LIB_PATH.$trail.self::directoryName($id)."index.xml";
		$parser->load($indexFilePath);
		// Get css
		$fileName = self::fileName($id);
		$moduleItem = $parser->find($fileName);
		return $parser->attr($moduleItem, "css") == "1";
	}
	
	/**
	 * Gets whether this module has js code.
	 * 
	 * @param	integer	$id
	 * 		The module id.
	 * 
	 * @return	boolean
	 * 		True if module has css, false otherwise.
	 */
	public static function hasJS($id)
	{
		// Get trail
		$info = self::info($id);
		$trail = SmoduleGroup::trail($info['group_id']);
		
		// Load indexing
		$parser = new DOMParser();
		$indexFilePath = self::LIB_PATH.$trail.self::directoryName($id)."index.xml";
		$parser->load($indexFilePath);
		// Get css
		$fileName = self::fileName($id);
		$moduleItem = $parser->find($fileName);
		return $parser->attr($moduleItem, "js") == "1";
	}
}
//#section_end#
?>