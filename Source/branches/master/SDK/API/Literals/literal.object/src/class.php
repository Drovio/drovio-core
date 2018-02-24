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
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Model", "literals/literal");
importer::import("DEV", "Core", "coreProject");

use \API\Model\literals\literal as APILiteral;
use \DEV\Core\coreProject;

/**
 * Literal Handler
 * 
 * Handles all translated and non-translated literals for Redback Core project.
 * 
 * @version	2.0-3
 * @created	March 21, 2014, 18:03 (EET)
 * @updated	July 6, 2015, 11:02 (EEST)
 */
class literal extends APILiteral
{
	/**
	 * Get a dictionary literal.
	 * 
	 * @param	string	$name
	 * 		The literal's name
	 * 
	 * @param	boolean	$wrapped
	 * 		Whether the literal will be wrapped inside a span.
	 * 
	 * @return	mixed
	 * 		The literal span if wrap is requested or the literal value.
	 */
	public static function dictionary($name = "", $wrapped = TRUE)
	{
		return parent::get(coreProject::PROJECT_ID, "global.dictionary", $name, array(), $wrapped);
	}
	
	/**
	 * Get a literal.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
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
	 * 		The literal span if wrap is requested or the literal value.
	 */
	public static function get($scope, $name = "", $attributes = array(), $wrapped = TRUE, $locale = NULL)
	{
		return parent::get(coreProject::PROJECT_ID, $scope, $name, $attributes, $wrapped, $locale);
	}
	
	/**
	 * Add a new literal to the default locale.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
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
	public static function add($scope, $name, $value, $description = "")
	{
		return parent::add(coreProject::PROJECT_ID, $scope, $name, $value, $description);
	}
	
	/**
	 * Update a literal to the default locale
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
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
	public static function update($scope, $name, $value, $description = "")
	{
		return parent::update(coreProject::PROJECT_ID, $scope, $name, $value, $description);
	}
	
	/**
	 * Remove a literal.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @param	string	$name
	 * 		The literal's name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($scope, $name)
	{
		return parent::remove(coreProject::PROJECT_ID, $scope, $name);
	}
}
//#section_end#
?>