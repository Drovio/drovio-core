<?php
//#section#[header]
// Namespace
namespace API\Developer\ebuilder;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\ebuilder
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Comm", "database::connections::interDbConnection");

use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	June 28, 2013, 19:59 (EEST)
 * @revised	June 28, 2013, 19:59 (EEST)
 */
class templateGroup
{
	/**
	 * Mapping (string => code) array of all db queries used by the class.
	 * 
	 * @type	array
	 */
	private static $query = array(			
			'getAllGroups' => '1144462857',
			'getGroupInfo' => '1034585811',			
			'addGroup' => '439375368',
			'addGroupLiteral' => '1539903242',
			'deleteGroup' => '1302372906',			
			'updateGroupLiteral' => '1529248592');	

	private static $defaultLocale = 'en_US';

	public static function getAllGroups()
	{
		$groupsArray = array();
		
		//Get all tempates / or array type => templateName
		$dbq = new dbQuery(self::$query['getAllGroups'], "ebuilder.template");
		$dbc = new interDbConnection();			
		
		// Set Query Attributes						
		$attr = array();
		$attr['locale'] = self::$defaultLocale;
		
		$defaultResult = $dbc->execute_query($dbq, $attr);
		
		while ($row = $dbc->fetch($defaultResult))
			$groupsArray[$row['groupID']] = $row['groupTitle'];		
		
		return $groupsArray;
	}
	
	public static function createGroup($name = '', $description = '', $parent = '')
	{
		$dbc = new interDbConnection();
		
		$dbq = new dbQuery(self::$query['addGroup'], "ebuilder.template");
					
		$attr = array();
		$attr['parent'] = $this->templateId;
		
		
		$defaultResult = $dbc->execute_query($dbq, $attr);
		$row = $dbc->fetch($defaultResult);
		$groupId = $row['last_id'];
		
		// Check if template db entry is created
		if(empty($groupId) || is_null($groupId))
			return FALSE;
			
		$this->addGroupLiteral($groupId, self::$defaultLocale, $name, $description);
	}
	
	public static function deleteGroup($groupId = '')
	{ 
		//Get all tempates / or array type => templateName
		$dbq = new dbQuery(self::$query['deleteGroup'], "ebuilder.template");
		$dbc = new interDbConnection();			
		
		// Set Query Attributes						
		$attr = array();
		$attr['id'] = $groupId;
		
		$defaultResult = $dbc->execute_query($dbq, $attr);
	}
	
	public static function mashGroupChange($oldGroup = '', $newGroup = '')
	{
		
	}
	
	
	public static function updateGroupLiterals($id,  $name, $description)
	{
		// if one is empty ???????		
	
		$dbc = new interDbConnection();		
			
		$dbq = new dbQuery(self::$query['updateGroupLiteral'], "ebuilder.template");
		
		// Set Query Attributes						
		$attr = array();
		$attr['id'] = $id;
		$attr['locale'] = self::$defaultLocale;
		$attr['title'] = $tname;
		$attr['description'] = $description;
		
		$defaultResult = $dbc->execute_query($dbq, $attr);
	}
}
//#section_end#
?>