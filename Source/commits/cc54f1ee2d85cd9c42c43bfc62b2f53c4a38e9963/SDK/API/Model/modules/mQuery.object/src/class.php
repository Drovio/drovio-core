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

importer::import("API", "Model", "modules::mGroup");
importer::import("API", "Model", "modules::module");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Model\modules\mGroup;
use \API\Model\modules\module;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * Module Query Manager
 * 
 * Loads the module sql query from the module's deployed folder.
 * 
 * @version	0.1-1
 * @created	May 5, 2014, 15:11 (EEST)
 * @revised	July 23, 2014, 19:22 (EEST)
 */
class mQuery
{
	/**
	 * The module id.
	 * 
	 * @type	integer
	 */
	private $moduleID;
	
	/**
	 * The module's query name.
	 * 
	 * @type	string
	 */
	private $queryName;
	
	/**
	 * Constructor Method.
	 * Initializes the object.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	string	$queryName
	 * 		The module's query name.
	 * 
	 * @return	void
	 */
	public function __construct($moduleID, $queryName)
	{
		$this->moduleID = $moduleID;
		$this->queryName = $queryName;
	}
	
	/**
	 * Gets the query in sql code.
	 * 
	 * @param	array	$attr
	 * 		An array of attributes as name => value to be replaced inside the query.
	 * 
	 * @return	string
	 * 		The sql query.
	 */
	public function getQuery($attr = array())
	{
		// Load query from deployed
		$moduleInfo = module::info($this->moduleID);
		$modulePath = mGroup::getTrail($moduleInfo['group_id']).module::getDirectoryName($this->moduleID);
		
		// Load indexing
		$parser = new DOMParser();
		$indexFilePath = "/System/Library/Modules/".$modulePath."/index.xml";
		$parser->load($indexFilePath);
		
		// Get fileName
		$item = $parser->evaluate("//query[@name='$this->queryName']")->item(0);
		$queryID = $parser->attr($item, "id");
		$queryFile = "/System/Library/Modules/".$modulePath."/q/".$queryID.".sql";
		
		if (file_exists(systemRoot.$queryFile))
		{
			$query = fileManager::get(systemRoot.$queryFile);
			$query = trim($query);
			
			// Replace Attributes
			foreach ($attr as $key => $value)
			{
				$query = str_replace("$".$key, $value, $query);
				$query = str_replace("{".$key."}", $value, $query);
			}
		}
		else
			throw new Exception("Module Query '$this->queryName' not found.");
			
		return $query;
	}
}
//#section_end#
?>