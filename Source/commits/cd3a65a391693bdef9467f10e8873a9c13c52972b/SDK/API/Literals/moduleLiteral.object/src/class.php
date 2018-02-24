<?php
//#section#[header]
// Namespace
namespace API\Literals;

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
 * @package	Literals
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("DEV", "Literals", "literal");

use \SYS\Comm\db\dbConnection;
use \API\Model\units\sql\dbQuery;
use \DEV\Literals\literal as DEVLiteral;

/**
 * Module Literal Handler
 * 
 * Handles all module's literals
 * 
 * @version	0.1-3
 * @created	March 24, 2014, 9:17 (EET)
 * @revised	July 21, 2014, 13:03 (EEST)
 */
class moduleLiteral extends DEVLiteral
{
	/**
	 * Get a module's literal
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id
	 * 
	 * @param	string	$name
	 * 		The literal's name
	 * 
	 * @param	array	$attributes
	 * 		An array of attributes to pass to the literal.
	 * 
	 * @param	boolean	$wrapped
	 * 		Whether the literal will be wrapped inside a span.
	 * 
	 * @param	string	$locale
	 * 		The locale to get the literals from. If NULL, get the current system locale.
	 * 
	 * @return	mixed
	 * 		The literal's value as a string or DOMElement, depending on $wrapped parameter.
	 * 		Also returns an array of all literals in the default locale, if only the module's id is specified.
	 */
	public static function get($moduleID, $name = "", $attributes = array(), $wrapped = TRUE, $locale = NULL)
	{
		// Set Module Scope
		$projectID = 2;
		$scope = "mdl.".$moduleID;
		
		// Return literal with the updated scope
		return parent::get($projectID, $scope, $name, $attributes, $wrapped, $locale);
	}
	
	/**
	 * Create a new module's literal
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id
	 * 
	 * @param	string	$name
	 * 		The literal's name
	 * 
	 * @param	string	$value
	 * 		The literal's value
	 * 
	 * @param	string	$description
	 * 		The literal's description
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function add($moduleID, $name, $value, $description = "")
	{
		// Set Module Scope
		$projectID = 2;
		$scope = "mdl.".$moduleID;
		
		// Add Module Scope (if not exists)
		$dbc = new dbConnection();
		$dbq = new dbQuery("1697604621", "resources.literals");
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = 2;
		$attr['scope'] = $scope;
		$result = $dbc->execute($dbq, $attr);
		
		// Add the literal with the updated scope
		return parent::add($projectID, $scope, $name, $value, $description);
	}
	
	/**
	 * Update a module's literal to the default locale
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id
	 * 
	 * @param	string	$name
	 * 		The literal's name
	 * 
	 * @param	string	$value
	 * 		The literal's new value
	 * 
	 * @param	string	$description
	 * 		The literal's new description
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function update($moduleID, $name, $value, $description = "")
	{
		// Set Module Scope
		$projectID = 2;
		$scope = "mdl.".$moduleID;
		
		// Add the literal with the updated scope
		return parent::update($projectID, $scope, $name, $value, $description);
	}
	
	/**
	 * Remove a module's literal from the system
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id
	 * 
	 * @param	string	$name
	 * 		The literal's name
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($moduleID, $name)
	{
		// Set Module Scope
		$projectID = 2;
		$scope = "mdl.".$moduleID;
		
		// Remove the literal with the updated scope
		return parent::remove($projectID, $scope, $name);
	}
}
//#section_end#
?>