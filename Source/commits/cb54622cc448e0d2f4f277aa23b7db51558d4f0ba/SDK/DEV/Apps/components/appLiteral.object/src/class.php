<?php
//#section#[header]
// Namespace
namespace DEV\Apps\components;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Apps
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Literals", "literal");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Literals\literal;

/**
 * Application Literal Handler
 * 
 * Handles all application's literals
 * 
 * @version	{empty}
 * @created	April 6, 2014, 20:27 (EEST)
 * @revised	April 6, 2014, 20:27 (EEST)
 */
class appLiteral extends literal
{
	/**
	 * Gets an application's literal.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
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
	 * 		Also returns an array of all literals in the default locale, if only the application's id is specified.
	 */
	public static function get($appID, $name = "", $attributes = array(), $wrapped = TRUE, $locale = NULL)
	{
		// Set Application Scope
		$scope = "app.".$appID;
		
		// Return literal with the updated scope
		return parent::get($scope, $name, $attributes, $wrapped, $locale);
	}
	
	/**
	 * Create a new application's literal.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
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
	public static function add($appID, $name, $value, $description = "")
	{
		// Set Application Scope
		$scope = "app.".$appID;
		
		// Add Module Scope (if not exists)
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1697604621", "resources.literals");
		
		// Set Query Attributes
		$attr = array();
		$attr['scope'] = $scope;
		$attr['module_id'] = "NULL";
		$result = $dbc->execute($dbq, $attr);
		
		// Add the literal with the updated scope
		return parent::add($scope, $name, $value, $description);
	}
	
	/**
	 * Update an application's literal to the default locale
	 * 
	 * @param	integer	$appID
	 * 		The application id.
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
	public static function update($appID, $name, $value, $description = "")
	{
		// Set Application Scope
		$scope = "app.".$appID;
		
		// Add the literal with the updated scope
		return parent::update($scope, $name, $value, $description);
	}
	
	/**
	 * Remove an application's literal from the system.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$name
	 * 		The literal's name
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($appID, $name)
	{
		// Set Application Scope
		$scope = "app.".$appID;
		
		// Remove the literal with the updated scope
		return parent::remove($scope, $name);
	}
}
//#section_end#
?>