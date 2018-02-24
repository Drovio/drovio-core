<?php
//#section#[header]
// Namespace
namespace API\Security\akeys;

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
 * @package	Security
 * @namespace	\akeys
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Environment", "session");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");

use \ESS\Environment\session;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;

/**
 * API Key types
 * 
 * {description}
 * 
 * @version	1.0-1
 * @created	October 10, 2015, 15:56 (EEST)
 * @updated	October 10, 2015, 17:48 (EEST)
 */
class apiKeyType
{
	/**
	 * Get all key types.
	 * 
	 * @param	boolean	$compact
	 * 		If compact, get all keys by id and name.
	 * 		Otherwise, get full key types array.
	 * 		It is FALSE by default.
	 * 
	 * @return	array
	 * 		An array of all key types.
	 */
	public static function getAllKeyTypes($compact = FALSE)
	{
		// Check cache
		$apiKeyTypes = session::get($name = "type_list", $default = array(), $namespace = "api_keys");
		if (empty($apiKeyTypes))
		{
			// Set Database Connection
			$dbc = new dbConnection();
			$q = new dbQuery("29822542446462", "security.akeys");
			$result = $dbc->execute($q);
			$apiKeyTypes = $dbc->fetch($result, TRUE);
			
			// Set session
			session::set($name = "type_list", $apiKeyTypes, $namespace = "api_keys");
		}
		
		// Get compact
		if ($compact)
		{
			$compactList = array();
			foreach ($apiKeyTypes as $typeInfo)
				$compactList[$typeInfo['id']] = $typeInfo['name'];
			
			return $compactList;
		}
			
		// Return full key types
		return $apiKeyTypes;
	}
	
	/**
	 * Get the api key type that matches the given user group name.
	 * 
	 * @param	string	$groupName
	 * 		The group name.
	 * 
	 * @return	array
	 * 		The key type information array.
	 */
	public static function getKeyTypeByGroupName($groupName)
	{
		// Get all key types
		$apiKeyTypes = self::getAllKeyTypes();
		
		// Get only the one with groupname
		foreach ($apiKeyTypes as $typeInfo)
			if ($typeInfo['user_group_name'] == $groupName)
				return $typeInfo;
			
		// No key type found
		return NULL;
	}
	
	/**
	 * Get the api key type that matches the given user group id.
	 * 
	 * @param	integer	$groupID
	 * 		The group id.
	 * 
	 * @return	array
	 * 		The key type information array.
	 */
	public static function getKeyTypeByGroupID($groupID)
	{
		// Get all key types
		$apiKeyTypes = self::getAllKeyTypes();
		
		// Get only the one with groupname
		foreach ($apiKeyTypes as $typeInfo)
			if ($typeInfo['user_group_id'] == $groupID)
				return $typeInfo;
			
		// No key type found
		return NULL;
	}
}
//#section_end#
?>