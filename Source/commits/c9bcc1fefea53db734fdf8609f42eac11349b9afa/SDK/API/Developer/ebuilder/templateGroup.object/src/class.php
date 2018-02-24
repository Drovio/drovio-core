<?php
//#section#[header]
// Namespace
namespace API\Developer\ebuilder;

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
 * @package	Developer
 * @namespace	\ebuilder
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	June 28, 2013, 19:59 (EEST)
 * @revised	December 30, 2013, 17:37 (EET)
 * 
 * @deprecated	Use API/Developer/projects/projectCategory instead
 */
class templateGroup
{
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public static function getAllCategories()
	{
		
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$description
	 * 		{description}
	 * 
	 * @param	{type}	$parent
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function createCategory($name = '', $description = '', $parent = '')
	{
		
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$groupId
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function deleteCategory($groupId = '')
	{ 
		//Get all tempates / or array type => templateName
		$dbq = new dbQuery(self::$query['deleteGroup'], "ebuilder.template");
		$dbc = new interDbConnection();			
		
		// Set Query Attributes						
		$attr = array();
		$attr['id'] = $groupId;
		
		$defaultResult = $dbc->execute_query($dbq, $attr);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$oldGroup
	 * 		{description}
	 * 
	 * @param	{type}	$newGroup
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function mashGroupChange($oldGroup = '', $newGroup = '')
	{
		
	}
}
//#section_end#
?>