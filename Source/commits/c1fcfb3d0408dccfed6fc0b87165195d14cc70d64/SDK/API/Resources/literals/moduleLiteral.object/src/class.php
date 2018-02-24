<?php
//#section#[header]
// Namespace
namespace API\Resources\literals;

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
 * @package	Resources
 * @namespace	\literals
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "literals::literal");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Resources\literals\literal;

/**
 * Module Literal Handler
 * 
 * Handles all module's literals
 * 
 * @version	{empty}
 * @created	April 23, 2013, 14:00 (EEST)
 * @revised	March 24, 2014, 10:04 (EET)
 * 
 * @deprecated	Use \API\Literals\moduleLiteral instead.
 */
class moduleLiteral extends literal
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
	 * @param	{type}	$attributes
	 * 		{description}
	 * 
	 * @param	boolean	$wrapped
	 * 		Whether the literal will be wrapped inside a span.
	 * 
	 * @param	string	$locale
	 * 		The locale to get the literals from. If NULL, get the current system locale.
	 * 
	 * @return	mixed
	 * 		The literal's value as a string or DOMElement, depending on $wrapped parameter.
	 * 				Also returns an array of all literals in the default locale, if only the module's id is specified
	 */
	public static function get($moduleID, $name = "", $attributes = array(), $wrapped = TRUE, $locale = NULL)
	{
		// Set Module Scope
		$scope = "mdl.".$moduleID;
		
		// Compatibility
		if (is_bool($attributes))
		{
			$attributes = array();
			$wrapped = $attributes;
			if (func_num_args() == 4)
				$locale = $wrapped;
		}
		
		// Return literal with the updated scope
		return parent::get($scope, $name, $attributes, $wrapped, $locale);
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
	 * 		The literal's new value
	 * 
	 * @param	string	$description
	 * 		The literal's new description
	 * 
	 * @return	boolean
	 * 		Status of the addition
	 */
	public static function add($moduleID, $name, $value, $description = "")
	{
		// Set Module Scope
		$scope = "mdl.".$moduleID;
		
		// Add Module Scope (if not exists)
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1697604621", "resources.literals");
		
		// Set Query Attributes
		$attr = array();
		$attr['scope'] = $scope;
		$attr['module_id'] = $moduleID;
		$result = $dbc->execute($dbq, $attr);
		
		// Add the literal with the updated scope
		return parent::add($scope, $name, $value, $description);
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
	 * 		Status of the update
	 */
	public static function update($moduleID, $name, $value, $description = "")
	{
		// Set Module Scope
		$scope = "mdl.".$moduleID;
		
		// Add the literal with the updated scope
		return parent::update($scope, $name, $value, $description);
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
	 * 		Status of the removal.
	 */
	public static function remove($moduleID, $name)
	{
		// Set Module Scope
		$scope = "mdl.".$moduleID;
		
		// Remove the literal with the updated scope
		return parent::remove($scope, $name);
	}
}
//#section_end#
?>